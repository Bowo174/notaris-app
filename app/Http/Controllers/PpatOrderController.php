<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\Service;
use App\Models\WorkSheet;
use App\Services\PpatReceiptDocument;
use App\Services\WorkSheetCodeGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class PpatOrderController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        $query = Order::query()
            ->where(function ($query) {
                $query->whereHas('service', fn ($service) => $service->where('service_type', 'PPAT'))
                    ->orWhere(fn ($custom) => $custom->whereNull('service_id')->whereNotNull('other_service'));
            })
            ->with(['services:id,name,code', 'files:id,order_id,label,original_name'])
            ->withCount('files');

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addColumn('status_control', fn (Order $order) => view('ppat.orders.status', compact('order'))->render())
                ->addColumn('service_ids', fn (Order $order) => $order->services->pluck('id')->all())
                ->addColumn('order_files', fn (Order $order) => $order->files->map->only(['id', 'label', 'original_name'])->values()->all())
                ->addColumn('actions', fn (Order $order) => view('ppat.orders.actions', compact('order'))->render())
                ->editColumn('created_at', fn (Order $order) => $order->created_at->format('d/m/Y'))
                ->rawColumns(['status_control', 'actions'])
                ->toJson();
        }

        return view('ppat.orders.index', $this->context() + [
            'statuses' => Order::STATUSES,
            'services' => Service::query()->where('service_type', 'PPAT')->orderBy('name')->get(['id', 'code', 'name']),
        ]);
    }

    public function create(): View
    {
        return view('ppat.orders.create', $this->context() + [
            'services' => Service::query()->where('service_type', 'PPAT')->orderBy('name')->get(['id', 'code', 'name']),
            'selectedClient' => old('client_profile_id')
                ? ClientProfile::find(old('client_profile_id'))
                : null,
        ]);
    }

    public function searchClients(Request $request)
    {
        $term = trim($request->string('q')->toString());
        if (mb_strlen($term) < 2) {
            return response()->json([]);
        }

        return ClientProfile::query()
            ->where(fn ($query) => $query->where('name', 'like', "%{$term}%")
                ->orWhere('nik', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%"))
            ->orderBy('name')
            ->limit(15)
            ->get(['id', 'name', 'nik', 'client_type', 'phone'])
            ->map(fn (ClientProfile $client) => [
                'id' => $client->id,
                'name' => $client->name,
                'nik' => $client->nik,
                'type' => $client->client_type,
                'phone' => $client->phone,
            ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'client_mode' => ['required', Rule::in(['existing', 'new'])],
            'client_profile_id' => ['nullable', 'required_if:client_mode,existing', 'exists:client_profiles,id'],
            'client.name' => ['required_if:client_mode,new', 'nullable', 'string', 'max:255'],
            'client.nik' => ['nullable', 'digits:16'],
            'client.client_type' => ['required_if:client_mode,new', 'nullable', Rule::in(['perorangan', 'badan_usaha', 'lembaga_non_ahu', 'individu', 'kuasa'])],
            'client.represented_party_name' => ['nullable', 'required_if:client.client_type,kuasa', 'string', 'max:255'],
            'client.phone' => ['required_if:client_mode,new', 'nullable', 'string', 'max:24'],
            'client.email' => ['nullable', 'email', 'max:255'],
            'client.address' => ['required_if:client_mode,new', 'nullable', 'string', 'max:5000'],
            'services' => ['required_without:other_service', 'array', 'min:1'],
            'services.*' => ['required', 'integer', 'distinct', Rule::exists('services', 'id')->where(fn ($query) => $query->where('service_type', 'PPAT'))],
            'other_service' => ['nullable', 'required_without:services', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'object_location' => ['nullable', 'string', 'max:5000'],
            'related_parties' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'files' => ['sometimes', 'array', 'max:20'],
            'files.*.label' => ['required_with:files.*.file', 'string', 'max:255'],
            'files.*.file' => ['required_with:files.*.label', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ], [
            'services.required_without' => 'Pilih minimal satu jenis akta atau isi jenis akta lainnya.',
            'services.min' => 'Pilih minimal satu jenis akta.',
            'services.*.exists' => 'Jenis akta yang dipilih tidak tersedia untuk PPAT.',
            'client.nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'files.*.file.mimes' => 'Berkas harus berformat PDF, DOC, atau DOCX.',
            'files.*.file.max' => 'Ukuran setiap berkas maksimal 10 MB.',
        ]);

        $services = $this->selectedServices($validated['services'] ?? []);
        $otherService = $validated['other_service'] ?? null;
        $primaryService = $services->first();
        $serviceNameSnapshot = $this->serviceNameSnapshot($services, $otherService);

        $order = DB::transaction(function () use ($request, $validated, $services, $otherService, $primaryService, $serviceNameSnapshot) {
            $client = $validated['client_mode'] === 'existing'
                ? ClientProfile::findOrFail($validated['client_profile_id'])
                : ClientProfile::create($validated['client']);

            $order = Order::create([
                'client_profile_id' => $client->id,
                'service_id' => $primaryService?->id,
                'other_service' => $otherService,
                'created_by' => $request->user()->id,
                'service_code_snapshot' => $primaryService?->code,
                'service_name_snapshot' => $serviceNameSnapshot,
                'client_name_snapshot' => $client->name,
                'client_nik_snapshot' => $client->nik,
                'client_type_snapshot' => $client->client_type,
                'represented_party_snapshot' => $client->represented_party_name,
                'client_phone_snapshot' => $client->phone,
                'client_email_snapshot' => $client->email,
                'client_address_snapshot' => $client->address,
                'status' => 'Konsultasi',
                'title' => $validated['title'],
                'object_location' => $validated['object_location'] ?? null,
                'related_parties' => $validated['related_parties'] ?? null,
                'internal_notes' => $validated['internal_notes'] ?? null,
            ]);

            $order->services()->sync($this->servicePivotData($services));

            $workSheetCode = app(WorkSheetCodeGenerator::class)->generate();
            while (WorkSheet::where('code', $workSheetCode)->exists()) {
                $workSheetCode = app(WorkSheetCodeGenerator::class)->generate();
            }
            $order->workSheet()->create(['code' => $workSheetCode]);

            $this->storeFiles($request, $order);

            return $order;
        });

        return redirect()->route('ppat.orders.show', $order)->with('success', 'Pemesanan berhasil disimpan.');
    }

    public function update(Request $request, Order $order): JsonResponse
    {
        $order->load(['client', 'service']);
        abort_unless($this->isPpatOrder($order), 404);

        $validated = $request->validate([
            'client.name' => ['required', 'string', 'max:255'],
            'client.nik' => ['nullable', 'digits:16'],
            'client.client_type' => ['required', Rule::in(['perorangan', 'badan_usaha', 'lembaga_non_ahu', 'individu', 'kuasa'])],
            'client.represented_party_name' => ['nullable', 'string', 'max:255'],
            'client.phone' => ['required', 'string', 'max:24'],
            'client.email' => ['nullable', 'email', 'max:255'],
            'client.address' => ['required', 'string', 'max:5000'],
            'services' => ['required_without:other_service', 'array', 'min:1'],
            'services.*' => ['required', 'integer', 'distinct', Rule::exists('services', 'id')->where(fn ($query) => $query->where('service_type', 'PPAT'))],
            'other_service' => ['nullable', 'required_without:services', 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'object_location' => ['nullable', 'string', 'max:5000'],
            'related_parties' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'files' => ['sometimes', 'array', 'max:20'],
            'files.*.label' => ['required_with:files.*.file', 'string', 'max:255'],
            'files.*.file' => ['required_with:files.*.label', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ], [
            'services.required_without' => 'Pilih minimal satu jenis akta atau isi jenis akta lainnya.',
            'services.min' => 'Pilih minimal satu jenis akta.',
            'services.*.exists' => 'Jenis akta yang dipilih tidak tersedia untuk PPAT.',
            'client.nik.digits' => 'NIK harus terdiri dari tepat 16 digit angka.',
            'files.*.file.mimes' => 'Berkas harus berformat PDF, DOC, atau DOCX.',
            'files.*.file.max' => 'Ukuran setiap berkas maksimal 10 MB.',
        ]);

        $services = $this->selectedServices($validated['services'] ?? []);
        $otherService = $validated['other_service'] ?? null;
        $primaryService = $services->first();

        DB::transaction(function () use ($request, $order, $validated, $services, $otherService, $primaryService) {
            $client = $order->client;
            $client->update($validated['client']);

            $order->update([
                'service_id' => $primaryService?->id,
                'other_service' => $otherService,
                'service_code_snapshot' => $primaryService?->code,
                'service_name_snapshot' => $this->serviceNameSnapshot($services, $otherService),
                'client_name_snapshot' => $client->name,
                'client_nik_snapshot' => $client->nik,
                'client_type_snapshot' => $client->client_type,
                'represented_party_snapshot' => $client->represented_party_name,
                'client_phone_snapshot' => $client->phone,
                'client_email_snapshot' => $client->email,
                'client_address_snapshot' => $client->address,
                'title' => $validated['title'],
                'object_location' => $validated['object_location'] ?? null,
                'related_parties' => $validated['related_parties'] ?? null,
                'internal_notes' => $validated['internal_notes'] ?? null,
            ]);
            $order->services()->sync($this->servicePivotData($services));
            $this->storeFiles($request, $order);
        });

        return response()->json(['message' => 'Pemesanan berhasil diperbarui.']);
    }

    public function downloadReceipt(Order $order, PpatReceiptDocument $receiptDocument)
    {
        $order->load(['client', 'services', 'files', 'service']);
        abort_unless($this->isPpatOrder($order), 404);

        $path = $receiptDocument->generate($order);
        $filename = 'Tanda-Terima-'.(Str::slug($order->client_name_snapshot) ?: 'pemohon').'-'.now()->format('Y-m-d').'.docx';

        return response()->download($path, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        ])->deleteFileAfterSend(true);
    }

    public function destroy(Order $order): JsonResponse
    {
        $order->load(['service', 'files', 'workSheet.files']);
        abort_unless($this->isPpatOrder($order), 404);

        $paths = $order->files->pluck('path')
            ->merge($order->workSheet?->files->pluck('path') ?? collect())
            ->filter()
            ->unique()
            ->values();

        DB::transaction(fn () => $order->delete());
        Storage::disk('local')->delete($paths->all());

        return response()->json(['message' => 'Pemesanan dan lembar kerja terkait berhasil dihapus.']);
    }

    public function show(Order $order): View
    {
        $order->load(['client', 'service', 'creator:id,name', 'files']);
        abort_unless($this->isPpatOrder($order), 404);

        return view('ppat.orders.show', $this->context() + [
            'order' => $order,
            'statuses' => Order::STATUSES,
        ]);
    }

    public function updateStatus(Request $request, Order $order): RedirectResponse
    {
        $order->load('service');
        abort_unless($this->isPpatOrder($order), 404);

        $validated = $request->validate([
            'status' => ['required', Rule::in(Order::STATUSES)],
        ]);

        $order->update(['status' => $validated['status']]);

        return back()->with('success', 'Status pemesanan berhasil diperbarui.');
    }

    public function previewFile(OrderFile $orderFile)
    {
        $orderFile->load('order.service');
        abort_unless($orderFile->order && $this->isPpatOrder($orderFile->order), 404);
        abort_unless(Storage::disk('local')->exists($orderFile->path), 404);
        $isPdf = strtolower(pathinfo($orderFile->original_name, PATHINFO_EXTENSION)) === 'pdf';

        return Storage::disk('local')->response($orderFile->path, $orderFile->original_name, [
            'Content-Type' => $orderFile->mime_type ?: 'application/octet-stream',
            'X-Content-Type-Options' => 'nosniff',
        ], $isPdf ? 'inline' : 'attachment');
    }

    private function context(): array
    {
        return [
            'role' => 'PPAT',
            'workspaceRoute' => 'ppat',
            'dashboardRoute' => 'ppat.dashboard',
            'accessibleServices' => Service::query()->where('service_type', 'PPAT')->orderBy('name')->get(['id', 'code', 'name', 'service_type']),
        ];
    }

    private function isPpatOrder(Order $order): bool
    {
        return $order->service?->service_type === 'PPAT'
            || ($order->service_id === null && filled($order->other_service));
    }

    private function selectedServices(array $serviceIds): Collection
    {
        $services = Service::query()
            ->where('service_type', 'PPAT')
            ->whereIn('id', $serviceIds)
            ->get()
            ->keyBy('id');

        return collect($serviceIds)->map(fn ($id) => $services->get($id))->filter()->values();
    }

    private function serviceNameSnapshot(Collection $services, ?string $otherService): string
    {
        $names = $services->pluck('name')->all();
        if (filled($otherService) && ! in_array($otherService, $names, true)) {
            $names[] = $otherService;
        }

        return implode(', ', $names);
    }

    private function servicePivotData(Collection $services): array
    {
        return $services->mapWithKeys(fn (Service $service) => [
            $service->id => [
                'service_code_snapshot' => $service->code,
                'service_name_snapshot' => $service->name,
            ],
        ])->all();
    }

    private function storeFiles(Request $request, Order $order): void
    {
        foreach ($request->input('files', []) as $index => $fileInput) {
            $file = $request->file("files.{$index}.file");
            if (! $file) {
                continue;
            }

            $path = $file->store("order-files/{$order->id}", 'local');
            $order->files()->create([
                'label' => $fileInput['label'],
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/octet-stream',
                'size' => $file->getSize(),
            ]);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\ClientProfile;
use App\Models\Order;
use App\Models\OrderFile;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
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
            ->withCount('files');

        if ($request->ajax()) {
            return DataTables::eloquent($query)
                ->addColumn('status_control', fn (Order $order) => view('ppat.orders.status', compact('order'))->render())
                ->addColumn('actions', fn (Order $order) => view('ppat.orders.actions', compact('order'))->render())
                ->editColumn('created_at', fn (Order $order) => $order->created_at->format('d/m/Y'))
                ->rawColumns(['status_control', 'actions'])
                ->toJson();
        }

        return view('ppat.orders.index', $this->context() + [
            'statuses' => Order::STATUSES,
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
            'client.nik' => ['nullable', 'string', 'max:32'],
            'client.client_type' => ['required_if:client_mode,new', 'nullable', Rule::in(['perorangan', 'badan_usaha', 'lembaga_non_ahu', 'individu', 'kuasa'])],
            'client.represented_party_name' => ['nullable', 'required_if:client.client_type,kuasa', 'string', 'max:255'],
            'client.phone' => ['required_if:client_mode,new', 'nullable', 'string', 'max:24'],
            'client.email' => ['nullable', 'email', 'max:255'],
            'client.address' => ['required_if:client_mode,new', 'nullable', 'string', 'max:5000'],
            'service_id' => ['required', Rule::in(Service::query()->where('service_type', 'PPAT')->pluck('id')->map(fn ($id) => (string) $id)->push('other')->all())],
            'other_service' => ['nullable', Rule::requiredIf($request->input('service_id') === 'other'), 'string', 'max:255'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:10000'],
            'object_location' => ['nullable', 'string', 'max:5000'],
            'related_parties' => ['nullable', 'string', 'max:5000'],
            'internal_notes' => ['nullable', 'string', 'max:5000'],
            'files' => ['sometimes', 'array', 'max:20'],
            'files.*.label' => ['required_with:files.*.file', 'string', 'max:255'],
            'files.*.file' => ['required_with:files.*.label', 'file', 'mimes:pdf,doc,docx', 'max:10240'],
        ], [
            'service_id.in' => 'Pilih layanan PPAT yang tersedia atau Layanan Lainnya.',
            'other_service.required' => 'Nama layanan lainnya wajib diisi.',
            'files.*.file.mimes' => 'Berkas harus berformat PDF, DOC, atau DOCX.',
            'files.*.file.max' => 'Ukuran setiap berkas maksimal 10 MB.',
        ]);

        $order = DB::transaction(function () use ($request, $validated) {
            $client = $validated['client_mode'] === 'existing'
                ? ClientProfile::findOrFail($validated['client_profile_id'])
                : ClientProfile::create($validated['client']);
            $service = $validated['service_id'] === 'other'
                ? null
                : Service::findOrFail($validated['service_id']);
            $serviceName = $service?->name ?? $validated['other_service'];

            $order = Order::create([
                'client_profile_id' => $client->id,
                'service_id' => $service?->id,
                'other_service' => $service ? null : $serviceName,
                'created_by' => $request->user()->id,
                'service_code_snapshot' => $service?->code,
                'service_name_snapshot' => $serviceName,
                'client_name_snapshot' => $client->name,
                'client_nik_snapshot' => $client->nik,
                'client_type_snapshot' => $client->client_type,
                'represented_party_snapshot' => $client->represented_party_name,
                'client_phone_snapshot' => $client->phone,
                'client_email_snapshot' => $client->email,
                'client_address_snapshot' => $client->address,
                'status' => 'Konsultasi',
                'title' => $validated['title'],
                'description' => $validated['description'] ?? null,
                'object_location' => $validated['object_location'] ?? null,
                'related_parties' => $validated['related_parties'] ?? null,
                'internal_notes' => $validated['internal_notes'] ?? null,
            ]);

            $workSheetCode = 'LK-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
            while (\App\Models\WorkSheet::where('code', $workSheetCode)->exists()) {
                $workSheetCode = 'LK-'.now()->format('ymd').'-'.Str::upper(Str::random(5));
            }
            $order->workSheet()->create(['code' => $workSheetCode]);

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

            return $order;
        });

        return redirect()->route('ppat.orders.show', $order)->with('success', 'Pemesanan berhasil disimpan.');
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
}

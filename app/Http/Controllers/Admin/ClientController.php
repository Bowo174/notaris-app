<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\ClientFile;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ClientController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(Client::query()->with(['service:id,name'])->withCount('files'))
                ->addColumn('service_name', fn (Client $client) => $client->service?->name ?: $client->other_service ?: 'Layanan tidak tersedia')
                ->addColumn('actions', fn (Client $client) => view('admin.clients.actions', compact('client'))->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.clients.index', [
            'role' => 'Admin',
            'dashboardRoute' => 'admin.dashboard',
            'services' => Service::query()->orderBy('name')->get(['id', 'name']),
        ]);
    }

    public function show(Client $client): JsonResponse
    {
        return response()->json($client->load([
            'service:id,name',
            'files:id,client_id,label,original_name,size',
        ]));
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $this->validatedData($request);

        $client = DB::transaction(function () use ($request, $validated) {
            $client = Client::create($validated);
            $this->storeFiles($request, $client);

            return $client->load('files');
        });

        return response()->json(['message' => 'Data client berhasil ditambahkan.', 'data' => $client], 201);
    }

    public function update(Request $request, Client $client): JsonResponse
    {
        $validated = $this->validatedData($request);

        $client = DB::transaction(function () use ($request, $validated, $client) {
            $client->update($validated);
            $this->storeFiles($request, $client);

            return $client->fresh()->load('files');
        });

        return response()->json(['message' => 'Data client berhasil diperbarui.', 'data' => $client]);
    }

    public function destroy(Client $client): JsonResponse
    {
        $paths = $client->files()->pluck('path');
        $client->delete();
        Storage::disk('local')->delete($paths->all());

        return response()->json(['message' => 'Data client berhasil dihapus.']);
    }

    public function downloadFile(ClientFile $clientFile)
    {
        abort_unless(Storage::disk('local')->exists($clientFile->path), 404);

        return Storage::disk('local')->response($clientFile->path, $clientFile->original_name, [
            'Content-Type' => 'application/pdf',
            'X-Content-Type-Options' => 'nosniff',
        ], 'inline');
    }

    public function destroyFile(ClientFile $clientFile): JsonResponse
    {
        Storage::disk('local')->delete($clientFile->path);
        $clientFile->delete();

        return response()->json(['message' => 'Berkas berhasil dihapus.']);
    }

    private function validatedData(Request $request): array
    {
        $validated = $request->validate([
            'service_id' => ['nullable', 'integer', 'exists:services,id', 'required_without:other_service'],
            'other_service' => ['nullable', 'required_without:service_id', 'string', 'max:255'],
            'deed_title' => ['required', 'string', 'max:255'],
            'nik' => ['required', 'digits:16'],
            'client_type' => ['required', Rule::in(['individu', 'badan_usaha', 'kuasa'])],
            'phone' => ['required', 'string', 'max:24'],
            'email' => ['nullable', 'email', 'max:255'],
            'address' => ['required', 'string', 'max:5000'],
            'files' => ['sometimes', 'array', 'max:20'],
            'files.*.label' => ['required_with:files.*.file', 'string', 'max:255'],
            'files.*.file' => ['required_with:files.*.label', 'file', 'mimes:pdf', 'max:10240'],
        ], [
            'service_id.required_without' => 'Pilih layanan atau isi layanan lainnya.',
            'other_service.required_without' => 'Isi layanan lainnya jika belum memilih layanan.',
            'nik.digits' => 'NIK harus terdiri dari 16 digit angka.',
            'client_type.in' => 'Tipe client yang dipilih tidak valid.',
            'files.max' => 'Maksimal 20 berkas dapat ditambahkan sekaligus.',
            'files.*.file.mimes' => 'Berkas harus berformat PDF.',
            'files.*.file.max' => 'Ukuran setiap berkas maksimal 10 MB.',
        ]);

        if (! empty($validated['service_id'])) {
            $validated['other_service'] = null;
        }

        return collect($validated)->except('files')->all();
    }

    private function storeFiles(Request $request, Client $client): void
    {
        foreach ($request->input('files', []) as $index => $fileInput) {
            $file = $request->file("files.{$index}.file");

            if (! $file) {
                continue;
            }

            $path = $file->store("client-files/{$client->id}", 'local');

            $client->files()->create([
                'label' => $fileInput['label'],
                'original_name' => $file->getClientOriginalName(),
                'path' => $path,
                'mime_type' => $file->getMimeType() ?: 'application/pdf',
                'size' => $file->getSize(),
            ]);
        }
    }
}

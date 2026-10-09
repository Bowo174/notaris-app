<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use Yajra\DataTables\Facades\DataTables;

class ServiceController extends Controller
{
    public function index(Request $request): View|JsonResponse
    {
        if ($request->ajax()) {
            return DataTables::eloquent(Service::query()->select([
                'id',
                'code',
                'service_type',
                'name',
                'base_price',
                'estimated_duration',
                'estimate_unit',
            ]))
                ->addColumn('actions', fn (Service $service) => view('admin.services.actions', compact('service'))->render())
                ->rawColumns(['actions'])
                ->toJson();
        }

        return view('admin.services.index', [
            'role' => 'Admin',
            'dashboardRoute' => 'admin.dashboard',
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $data = $this->validatedData($request);
        $service = DB::transaction(fn () => Service::create($data));

        return response()->json([
            'message' => 'Layanan berhasil ditambahkan dengan kode '.$service->code.'.',
            'data' => $service,
        ], 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $data = $this->validatedData($request, $service);
        DB::transaction(function () use ($service, $data) {
            if ($service->service_type === $data['service_type']) {
                unset($data['code']);
            }

            $service->update($data);
        });

        return response()->json([
            'message' => 'Layanan berhasil diperbarui.',
            'data' => $service->fresh(),
        ]);
    }

    public function destroy(Service $service): JsonResponse
    {
        $service->delete();

        return response()->json(['message' => 'Layanan berhasil dihapus.']);
    }

    public function nextCode(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'service_type' => ['required', 'in:Notaris,PPAT'],
        ]);

        return response()->json(['code' => $this->generateCode($validated['service_type'])]);
    }

    private function validatedData(Request $request, ?Service $service = null): array
    {
        $uniqueCode = Rule::unique('services', 'code');
        if ($service) {
            $uniqueCode->ignore($service->id);
        }

        $data = $request->validate([
            'code' => ['required', 'string', 'regex:/^(PPAT|NOTARIS)-\d{5}$/', $uniqueCode],
            'service_type' => ['required', 'in:Notaris,PPAT'],
            'name' => ['required', 'string', 'max:255'],
            'base_price' => ['nullable', 'integer', 'min:0'],
            'estimated_duration' => ['nullable', 'integer', 'min:1'],
            'estimate_unit' => ['nullable', 'required_with:estimated_duration', 'in:hari,bulan,tahun'],
        ], [
            'code.required' => 'Kode otomatis belum tersedia. Pilih ulang jenis layanan.',
            'code.regex' => 'Format kode layanan tidak valid.',
            'code.unique' => 'Kode layanan sudah terpakai. Pilih ulang jenis layanan untuk membuat kode lain.',
            'service_type.required' => 'Jenis layanan wajib dipilih.',
            'service_type.in' => 'Jenis layanan harus Notaris atau PPAT.',
            'name.required' => 'Nama layanan wajib diisi.',
            'base_price.integer' => 'Harga dasar harus berupa angka bulat.',
            'base_price.min' => 'Harga dasar tidak boleh kurang dari Rp 0.',
            'estimated_duration.integer' => 'Estimasi harus berupa angka bulat.',
            'estimated_duration.min' => 'Estimasi minimal 1.',
            'estimate_unit.required_with' => 'Pilih satuan estimasi.',
            'estimate_unit.in' => 'Satuan estimasi harus hari, bulan, atau tahun.',
        ]);

        $expectedPrefix = $data['service_type'] === 'PPAT' ? 'PPAT-' : 'NOTARIS-';
        if (! str_starts_with($data['code'], $expectedPrefix)) {
            throw ValidationException::withMessages([
                'code' => 'Kode otomatis tidak sesuai dengan jenis layanan. Pilih ulang jenis layanan.',
            ]);
        }

        if (empty($data['estimated_duration'])) {
            $data['estimated_duration'] = null;
            $data['estimate_unit'] = null;
        }

        return $data;
    }

    private function generateCode(string $serviceType): string
    {
        $prefix = $serviceType === 'PPAT' ? 'PPAT' : 'NOTARIS';

        do {
            $code = $prefix.'-'.str_pad((string) random_int(1, 99999), 5, '0', STR_PAD_LEFT);
        } while (Service::query()->where('code', $code)->exists());

        return $code;
    }
}

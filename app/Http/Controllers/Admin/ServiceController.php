<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
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
                'name',
                'base_price',
                'estimated_days',
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
        $service = Service::create($this->validatedData($request));

        return response()->json([
            'message' => 'Layanan berhasil ditambahkan.',
            'data' => $service,
        ], 201);
    }

    public function update(Request $request, Service $service): JsonResponse
    {
        $service->update($this->validatedData($request, $service));

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

    private function validatedData(Request $request, ?Service $service = null): array
    {
        return $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:services,code'.($service ? ','.$service->id : '')],
            'name' => ['required', 'string', 'max:255'],
            'base_price' => ['required', 'integer', 'min:0'],
            'estimated_days' => ['required', 'integer', 'min:1'],
        ], [
            'code.required' => 'Kode layanan wajib diisi.',
            'code.unique' => 'Kode layanan sudah digunakan.',
            'name.required' => 'Nama layanan wajib diisi.',
            'base_price.required' => 'Harga dasar wajib diisi.',
            'base_price.integer' => 'Harga dasar harus berupa angka bulat.',
            'base_price.min' => 'Harga dasar tidak boleh kurang dari Rp 0.',
            'estimated_days.required' => 'Estimasi hari wajib diisi.',
            'estimated_days.integer' => 'Estimasi hari harus berupa angka bulat.',
            'estimated_days.min' => 'Estimasi hari minimal 1 hari.',
        ]);
    }
}

@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Dashboard ' . $role)

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1 text-gray-800">Dashboard {{ $role }}</h1>
        <p class="mb-0 text-gray-600">Ringkasan layanan dan aktivitas {{ $role }}.</p>
    </div>

    @if ($role === 'PPAT')
        <div class="row mb-4">
            <div class="col-md-6 col-xl-4 mb-3"><div class="bg-white border-left-primary shadow-sm p-4 h-100"><div class="small text-muted mb-1">Total Pemesanan</div><div class="h3 mb-3 font-weight-bold text-gray-800">{{ $orderCount }}</div><a href="{{ route('ppat.orders.index') }}" class="btn btn-sm btn-primary">Buka Pemesanan</a></div></div>
            <div class="col-md-6 col-xl-4 mb-3"><div class="bg-white border-left-success shadow-sm p-4 h-100 d-flex flex-column justify-content-between"><div><div class="small text-muted mb-1">Pemesanan Baru</div><div class="text-gray-800 mb-3">Catat permintaan layanan PPAT dan data client dalam satu alur.</div></div><a href="{{ route('ppat.orders.create') }}" class="btn btn-sm btn-outline-primary align-self-start"><i class="fas fa-plus mr-1"></i>Tambah Pemesanan</a></div></div>
        </div>
    @endif

    <section class="mb-4">
        <h2 class="h5 font-weight-bold text-gray-800 mb-3">Layanan {{ $role }}</h2>
        @if ($services->isEmpty())
            <p class="text-muted">Belum ada layanan untuk role ini.</p>
        @else
            <div class="table-responsive bg-white border rounded shadow-sm">
                <table class="table table-hover mb-0">
                    <thead class="thead-light">
                        <tr><th>Kode</th><th>Nama Layanan</th><th>Harga Dasar</th><th>Estimasi</th><th>{{ $role === 'PPAT' ? 'Pemesanan' : 'Client' }}</th><th></th></tr>
                    </thead>
                    <tbody>
                        @foreach ($services as $service)
                            <tr>
                                <td>{{ $service->code }}</td>
                                <td>{{ $service->name }}</td>
                                <td>{{ $service->base_price === null ? '-' : 'Rp ' . number_format($service->base_price, 0, ',', '.') }}</td>
                                <td>{{ $service->estimated_duration === null ? '-' : $service->estimated_duration . ' ' . $service->estimate_unit }}</td>
                                <td>{{ $role === 'PPAT' ? $service->orders_count : $service->clients_count }}</td>
                                <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route($workspaceRoute . '.services.show', $service) }}">Lihat</a></td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </section>

    <section class="border-left-primary bg-white shadow-sm p-4">
        <div class="font-weight-bold text-primary text-uppercase small mb-1">Hak akses</div>
        <div class="text-gray-700">@if ($role === 'PPAT') Akun PPAT dapat mencatat dan melihat pemesanan dengan layanan PPAT. @else Akun Notaris dapat melihat layanan dan data client sesuai jenis layanan, tanpa akses untuk mengubah atau menghapus data. @endif</div>
    </section>
@endsection

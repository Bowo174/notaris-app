@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', $service->name)

@section('content')
    <div class="mb-4">
        <a href="{{ route($dashboardRoute) }}" class="small text-primary"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>Kembali ke ringkasan</a>
        <h1 class="h3 mt-2 mb-1 text-gray-800">{{ $service->name }}</h1>
        <p class="mb-0 text-gray-600">Detail layanan {{ $role }}.</p>
    </div>

    <div class="row mb-4">
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="border-left-primary bg-white shadow-sm p-3 h-100">
                <div class="small text-muted mb-1">Kode Layanan</div><div class="font-weight-bold">{{ $service->code }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="border-left-success bg-white shadow-sm p-3 h-100">
                <div class="small text-muted mb-1">Jenis</div><div class="font-weight-bold">{{ $service->service_type }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="border-left-info bg-white shadow-sm p-3 h-100">
                <div class="small text-muted mb-1">Harga Dasar</div><div class="font-weight-bold">{{ $service->base_price === null ? '-' : 'Rp ' . number_format($service->base_price, 0, ',', '.') }}</div>
            </div>
        </div>
        <div class="col-md-6 col-xl-3 mb-3">
            <div class="border-left-warning bg-white shadow-sm p-3 h-100">
                <div class="small text-muted mb-1">Estimasi</div><div class="font-weight-bold">{{ $service->estimated_duration === null ? '-' : $service->estimated_duration . ' ' . $service->estimate_unit }}</div>
            </div>
        </div>
    </div>

    <section class="mb-3"><h2 class="h5 font-weight-bold text-gray-800">{{ $role === 'PPAT' ? 'Pemesanan untuk layanan ini' : 'Client untuk layanan ini' }}</h2></section>
    @if ($role === 'PPAT')
        <div class="table-responsive bg-white border rounded shadow-sm">
            <table class="table table-hover mb-0"><thead class="thead-light"><tr><th>Pemohon</th><th>Judul</th><th>Status</th><th>Tanggal</th><th></th></tr></thead><tbody>
                @forelse ($orders as $order)<tr><td>{{ $order->client_name_snapshot }}<div class="small text-muted">{{ $order->client_nik_snapshot ?: '-' }}</div></td><td>{{ $order->title }}</td><td>{{ $order->status }}</td><td>{{ $order->created_at->format('d/m/Y') }}</td><td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route('ppat.orders.show', $order) }}">Detail</a></td></tr>
                @empty<tr><td colspan="5" class="text-center text-muted py-4">Belum ada pemesanan untuk layanan ini.</td></tr>@endforelse
            </tbody></table>
        </div>
        <div class="mt-3">{{ $orders->links() }}</div>
    @else
    <div class="table-responsive bg-white border rounded shadow-sm">
        <table class="table table-hover mb-0">
            <thead class="thead-light"><tr><th>Judul Akta</th><th>NIK KTP</th><th>Tipe Client</th><th>No. WA</th><th>Berkas</th><th></th></tr></thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>{{ $client->deed_title }}</td>
                        <td>{{ $client->nik }}</td>
                        <td>{{ ['individu' => 'Individu', 'badan_usaha' => 'Badan usaha/badan hukum', 'kuasa' => 'Kuasa'][$client->client_type] ?? $client->client_type }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>{{ $client->files_count }}</td>
                        <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route($workspaceRoute . '.clients.show', $client) }}">Lihat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-center text-muted py-4">Belum ada client untuk layanan ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $clients->links() }}</div>
    @endif
@endsection

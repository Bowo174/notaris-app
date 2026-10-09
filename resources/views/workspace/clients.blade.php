@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Data Client')

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1 text-gray-800">Data Client</h1>
        <p class="mb-0 text-gray-600">Client dengan layanan bertipe {{ $role }}.</p>
    </div>

    <div class="table-responsive bg-white border rounded shadow-sm">
        <table class="table table-hover mb-0">
            <thead class="thead-light"><tr><th>Judul Akta</th><th>Jenis Layanan</th><th>NIK KTP</th><th>Tipe Client</th><th>No. WA</th><th>Email</th><th>Berkas</th><th></th></tr></thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr>
                        <td>{{ $client->deed_title }}</td>
                        <td>{{ $client->service->name }}</td>
                        <td>{{ $client->nik }}</td>
                        <td>{{ ['individu' => 'Individu', 'badan_usaha' => 'Badan usaha/badan hukum', 'kuasa' => 'Kuasa'][$client->client_type] ?? $client->client_type }}</td>
                        <td>{{ $client->phone }}</td>
                        <td>{{ $client->email ?: '-' }}</td>
                        <td>{{ $client->files_count }}</td>
                        <td class="text-right"><a class="btn btn-sm btn-outline-primary" href="{{ route($workspaceRoute . '.clients.show', $client) }}">Lihat</a></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-4">Belum ada data client untuk jenis layanan {{ $role }}.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    <div class="mt-3">{{ $clients->links() }}</div>
@endsection

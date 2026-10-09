@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Detail Client')

@section('content')
    <div class="mb-4">
        <a href="{{ url()->previous() }}" class="small text-primary"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>Kembali</a>
        <h1 class="h3 mt-2 mb-1 text-gray-800">{{ $client->deed_title }}</h1>
        <p class="mb-0 text-gray-600">Detail client untuk layanan {{ $client->service->name }}.</p>
    </div>

    <section class="bg-white border rounded shadow-sm p-4 mb-4">
        <h2 class="h5 font-weight-bold mb-3">Informasi Client</h2>
        <dl class="row mb-0">
            <dt class="col-sm-3">Jenis Layanan</dt><dd class="col-sm-9">{{ $client->service->name }} ({{ $client->service->service_type }})</dd>
            <dt class="col-sm-3">Judul Akta</dt><dd class="col-sm-9">{{ $client->deed_title }}</dd>
            <dt class="col-sm-3">NIK KTP</dt><dd class="col-sm-9">{{ $client->nik }}</dd>
            <dt class="col-sm-3">Tipe Client</dt><dd class="col-sm-9">{{ ['individu' => 'Individu', 'badan_usaha' => 'Badan usaha/badan hukum', 'kuasa' => 'Kuasa'][$client->client_type] ?? $client->client_type }}</dd>
            <dt class="col-sm-3">No. WA</dt><dd class="col-sm-9">{{ $client->phone }}</dd>
            <dt class="col-sm-3">Email</dt><dd class="col-sm-9">{{ $client->email ?: '-' }}</dd>
            <dt class="col-sm-3">Alamat</dt><dd class="col-sm-9">{!! nl2br(e($client->address)) !!}</dd>
        </dl>
    </section>

    <section class="bg-white border rounded shadow-sm">
        <div class="p-4 border-bottom"><h2 class="h5 font-weight-bold mb-0">Berkas Pendukung</h2></div>
        @forelse ($client->files as $file)
            <div class="d-flex flex-wrap align-items-center justify-content-between p-3 border-bottom">
                <div class="mr-3 mb-2 mb-sm-0">
                    <div class="font-weight-bold">{{ $file->label }}</div>
                    <div class="small text-muted">{{ $file->original_name }}</div>
                </div>
                @php($isPdf = strtolower(pathinfo($file->original_name, PATHINFO_EXTENSION)) === 'pdf')
                <a class="btn btn-sm btn-outline-primary" href="{{ route($workspaceRoute . '.files.preview', $file) }}" target="_blank" rel="noopener">
                    <i class="fas {{ $isPdf ? 'fa-file-pdf' : 'fa-file-word' }} mr-1" aria-hidden="true"></i> {{ $isPdf ? 'Lihat PDF' : 'Unduh Word' }}
                </a>
            </div>
        @empty
            <p class="text-muted p-4 mb-0">Tidak ada berkas pendukung.</p>
        @endforelse
    </section>
@endsection

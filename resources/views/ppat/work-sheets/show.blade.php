@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Detail Lembar Kerja')

@section('content')
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-4">
        <div>
            <a href="{{ route('ppat.work-sheets.index') }}" class="small text-primary"><i class="fas fa-arrow-left mr-1" aria-hidden="true"></i>Kembali ke Lembar Kerja</a>
            <h1 class="h3 text-gray-800 mt-2 mb-1">{{ $sheet->code }}</h1>
            <p class="text-muted mb-0">{{ $sheet->order?->client_name_snapshot }} · {{ $sheet->order?->title }}</p>
        </div>
        <a href="{{ route('ppat.work-sheets.qr', $sheet->code) }}" download="{{ $sheet->code }}-QR.jpg" class="btn btn-outline-primary mt-3 mt-md-0"><i class="fas fa-download mr-1" aria-hidden="true"></i>Unduh QR JPG</a>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <section class="bg-white border rounded shadow-sm p-4 mb-4">
                <h2 class="h5 font-weight-bold text-gray-800 mb-3">Data Akta</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Tanggal</dt><dd class="col-sm-8">{{ $sheet->deed_date?->format('d/m/Y') ?? '-' }}</dd>
                    <dt class="col-sm-4">Nomor akta</dt><dd class="col-sm-8">{{ $sheet->deed_number ?: '-' }}</dd>
                    <dt class="col-sm-4">Akta</dt><dd class="col-sm-8">{{ $sheet->order?->service_name_snapshot ?: $sheet->deed_name ?: '-' }}</dd>
                </dl>
            </section>

            @foreach ([['Pihak 1', $sheet->party_one], ['Pihak 2', $sheet->party_two]] as [$title, $party])
                <section class="bg-white border rounded shadow-sm p-4 mb-4">
                    <h2 class="h5 font-weight-bold text-gray-800 mb-3">{{ $title }}</h2>
                    <dl class="row mb-0">
                        <dt class="col-sm-4">Jenis pihak</dt><dd class="col-sm-8">{{ ['perorangan' => 'Perorangan', 'badan_usaha' => 'Badan usaha / badan hukum', 'lembaga_non_ahu' => 'Lembaga / badan / perkumpulan non AHU'][data_get($party, 'type')] ?? '-' }}</dd>
                        <dt class="col-sm-4">Nama</dt><dd class="col-sm-8">{{ data_get($party, 'name') ?: '-' }}</dd>
                        <dt class="col-sm-4">Lahir / pendirian</dt><dd class="col-sm-8">{{ data_get($party, 'birth_info') ?: '-' }}</dd>
                        <dt class="col-sm-4">Identitas</dt><dd class="col-sm-8">{{ data_get($party, 'identity') ?: '-' }}</dd>
                        <dt class="col-sm-4">Alamat</dt><dd class="col-sm-8">{!! data_get($party, 'address') ? nl2br(e(data_get($party, 'address'))) : '-' !!}</dd>
                    </dl>
                </section>
            @endforeach

            <section class="bg-white border rounded shadow-sm p-4 mb-4">
                <h2 class="h5 font-weight-bold text-gray-800 mb-3">Sertipikat dan Pajak</h2>
                <dl class="row mb-0">
                    <dt class="col-sm-4">Data sertipikat</dt><dd class="col-sm-8">{!! $sheet->certificate_details ? nl2br(e($sheet->certificate_details)) : '-' !!}</dd>
                    <dt class="col-sm-4">Nilai transaksi</dt><dd class="col-sm-8">{{ $sheet->transaction_value === null ? '-' : 'Rp '.number_format($sheet->transaction_value, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4">PBB</dt><dd class="col-sm-8">{{ $sheet->pbb_amount === null ? '-' : 'Rp '.number_format($sheet->pbb_amount, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4">BPHTB</dt><dd class="col-sm-8">{{ $sheet->bphtb_amount === null ? '-' : 'Rp '.number_format($sheet->bphtb_amount, 0, ',', '.') }}</dd>
                    <dt class="col-sm-4">PPh</dt><dd class="col-sm-8">{{ $sheet->pph_amount === null ? '-' : 'Rp '.number_format($sheet->pph_amount, 0, ',', '.') }}</dd>
                </dl>
            </section>

            <section class="bg-white border rounded shadow-sm p-4 mb-4">
                <h2 class="h5 font-weight-bold text-gray-800 mb-3">Berkas pendukung</h2>
                @forelse ($sheet->files as $file)
                    <a href="{{ route('ppat.work-sheets.files.preview', $file) }}" target="_blank" rel="noopener" class="d-flex flex-wrap justify-content-between border rounded p-3 mb-2 text-decoration-none">
                        <span><strong class="d-block">{{ $file->label }}</strong><small class="text-muted">{{ $file->original_name }}</small></span>
                        <span class="badge badge-light align-self-center">{{ strtoupper($file->category) }}</span>
                    </a>
                @empty
                    <p class="text-muted mb-0">Belum ada berkas pendukung.</p>
                @endforelse
            </section>
        </div>

        <div class="col-lg-4">
            <section class="bg-white border rounded shadow-sm p-4 text-center mb-4">
                <h2 class="h5 font-weight-bold text-gray-800 mb-3">QR Lembar Kerja</h2>
                <img src="{{ route('ppat.work-sheets.qr', $sheet->code) }}" alt="QR menuju detail lembar kerja {{ $sheet->code }}" class="img-fluid mb-3" width="260" height="260">
                <p class="small text-muted mb-0">Memindai QR akan membuka halaman ini setelah login role PPAT.</p>
            </section>
            <section class="bg-white border rounded shadow-sm p-4">
                <h2 class="h6 font-weight-bold text-gray-800 mb-3">Asal Pemesanan</h2>
                <div class="mb-2"><span class="small text-muted d-block">Pemohon</span>{{ $sheet->order?->client_name_snapshot ?: '-' }}</div>
                <div class="mb-2"><span class="small text-muted d-block">Layanan</span>{{ $sheet->order?->service_name_snapshot ?: $sheet->order?->other_service ?: '-' }}</div>
                <div><span class="small text-muted d-block">Dibuat</span>{{ $sheet->order?->created_at?->format('d/m/Y H:i') ?: '-' }}</div>
            </section>
        </div>
    </div>
@endsection

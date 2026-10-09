<div class="d-flex justify-content-center align-items-center" style="gap:.35rem">
    <a href="{{ route('ppat.work-sheets.show', $sheet->code) }}" class="btn btn-sm btn-outline-secondary" aria-label="Lihat lembar kerja {{ $sheet->code }}" title="Lihat detail">
        <i class="fas fa-eye" aria-hidden="true"></i>
    </a>
    <button type="button" class="btn btn-sm btn-outline-primary js-edit-work-sheet" data-id="{{ $sheet->id }}" aria-label="Lengkapi lembar kerja {{ $sheet->code }}" title="Lengkapi data">
        <i class="fas fa-pen" aria-hidden="true"></i>
    </button>
    <a href="{{ route('ppat.work-sheets.qr', $sheet->code) }}" download="{{ $sheet->code }}-QR.jpg" class="btn btn-sm btn-outline-success" aria-label="Unduh QR JPG {{ $sheet->code }}" title="Unduh QR JPG">
        <i class="fas fa-download" aria-hidden="true"></i>
    </a>
</div>

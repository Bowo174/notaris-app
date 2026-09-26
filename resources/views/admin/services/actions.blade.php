<div class="btn-group" role="group" aria-label="Aksi layanan">
    <button type="button" class="btn btn-sm btn-outline-primary js-edit-service" data-id="{{ $service->id }}" title="Ubah layanan" aria-label="Ubah layanan">
        <i class="fas fa-pen" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger js-delete-service" data-id="{{ $service->id }}" data-name="{{ $service->name }}" title="Hapus layanan" aria-label="Hapus layanan">
        <i class="fas fa-trash" aria-hidden="true"></i>
    </button>
</div>

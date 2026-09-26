<div class="client-actions" role="group" aria-label="Aksi client">
    <button type="button" class="btn btn-sm btn-outline-success js-view-client" data-id="{{ $client->id }}" title="Lihat data client" aria-label="Lihat data client">
        <i class="fas fa-eye" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-primary js-edit-client" data-id="{{ $client->id }}" title="Ubah data client" aria-label="Ubah data client">
        <i class="fas fa-pen" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger js-delete-client" data-id="{{ $client->id }}" data-name="{{ $client->deed_title }}" title="Hapus data client" aria-label="Hapus data client">
        <i class="fas fa-trash" aria-hidden="true"></i>
    </button>
</div>

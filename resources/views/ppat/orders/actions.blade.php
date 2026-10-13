<div class="order-actions">
    <a href="{{ route('ppat.orders.show', $order) }}" class="btn btn-sm btn-outline-primary" aria-label="Lihat pemesanan {{ $order->title }}" title="Lihat detail">
        <i class="fas fa-eye" aria-hidden="true"></i>
    </a>
    <button type="button" class="btn btn-sm btn-outline-secondary js-edit-order" data-id="{{ $order->id }}" aria-label="Edit pemesanan {{ $order->title }}" title="Edit pemesanan">
        <i class="fas fa-pen" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-success js-download-receipt" data-url="{{ route('ppat.orders.receipt', $order) }}" aria-label="Unduh tanda terima {{ $order->title }}" title="Unduh tanda terima Word">
        <i class="fas fa-file-word" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-sm btn-outline-danger js-delete-order" data-id="{{ $order->id }}" data-title="{{ $order->title }}" aria-label="Hapus pemesanan {{ $order->title }}" title="Hapus pemesanan">
        <i class="fas fa-trash" aria-hidden="true"></i>
    </button>
</div>

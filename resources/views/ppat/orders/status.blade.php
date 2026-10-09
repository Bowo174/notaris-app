@php($statusClass = ['Konsultasi' => 'badge-info', 'Dalam proses' => 'badge-primary', 'Selesai' => 'badge-success'][$order->status] ?? 'badge-secondary')
<div class="order-status-control">
    <span class="badge {{ $statusClass }} mb-2">{{ $order->status }}</span>
    <form method="POST" action="{{ route('ppat.orders.status.update', $order) }}" class="d-flex align-items-center">
        @csrf
        @method('PATCH')
        <label class="sr-only" for="status-{{ $order->id }}">Ubah status {{ $order->title }}</label>
        <select id="status-{{ $order->id }}" name="status" class="form-control form-control-sm mr-1">
            @foreach (\App\Models\Order::STATUSES as $status)
                <option value="{{ $status }}" @selected($order->status === $status)>{{ $status }}</option>
            @endforeach
        </select>
        <button class="btn btn-sm btn-outline-primary" type="submit" aria-label="Simpan status" title="Simpan status"><i class="fas fa-save" aria-hidden="true"></i></button>
    </form>
</div>

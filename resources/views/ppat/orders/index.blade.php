@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Pemesanan')

@push('styles')
    <link href="{{ asset('template_dashboard/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        #orders-table { width: 100% !important; }
        .order-status-control { min-width: 185px; }
        .order-actions { white-space: nowrap; }
    </style>
@endpush

@section('content')
    <div class="d-flex flex-wrap align-items-center justify-content-between mb-4">
        <div><h1 class="h3 mb-1 text-gray-800">Pemesanan</h1><p class="mb-0 text-gray-600">Daftar pekerjaan layanan PPAT.</p></div>
        <a href="{{ route('ppat.orders.create') }}" class="btn btn-primary mt-3 mt-md-0"><i class="fas fa-plus mr-2" aria-hidden="true"></i>Tambah Pemesanan</a>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    @endif
    @if ($errors->has('status'))
        <div class="alert alert-danger" role="alert">{{ $errors->first('status') }}</div>
    @endif

    <div class="table-responsive bg-white border rounded shadow-sm p-3">
        <table id="orders-table" class="table table-hover mb-0">
            <thead class="thead-light"><tr><th>Pemohon</th><th>Pemesanan</th><th>Layanan</th><th>Status</th><th>Berkas</th><th>Tanggal</th><th>Aksi</th><th>NIK</th></tr></thead>
        </table>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('template_dashboard/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('template_dashboard/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script>
        $(function () {
            $('#orders-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: @json(route('ppat.orders.index')),
                order: [[5, 'desc']],
                columns: [
                    {
                        data: 'client_name_snapshot', name: 'client_name_snapshot',
                        render: function (value, type, row) {
                            if (type !== 'display') return value;
                            const name = $('<div>').text(value || '').html();
                            const nik = $('<div>').text(row.client_nik_snapshot || 'Identitas belum dicatat').html();
                            return `${name}<div class="small text-muted">${nik}</div>`;
                        }
                    },
                    { data: 'title', name: 'title' },
                    { data: 'service_name_snapshot', name: 'service_name_snapshot' },
                    { data: 'status_control', name: 'status', orderable: false, searchable: false },
                    { data: 'files_count', name: 'files_count', searchable: false, className: 'text-center', render: value => `${value} berkas` },
                    { data: 'created_at', name: 'created_at' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' },
                    { data: 'client_nik_snapshot', name: 'client_nik_snapshot', visible: false, orderable: false }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                language: {
                    emptyTable: 'Belum ada pemesanan',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ pemesanan',
                    infoEmpty: 'Tidak ada pemesanan untuk ditampilkan',
                    infoFiltered: '(disaring dari _MAX_ pemesanan)',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    loadingRecords: 'Memuat data...',
                    processing: 'Memproses...',
                    search: 'Cari:',
                    zeroRecords: 'Pemesanan tidak ditemukan',
                    paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
                }
            });
        });
    </script>
@endpush

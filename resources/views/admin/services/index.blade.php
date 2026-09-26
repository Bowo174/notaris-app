@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Layanan')

@push('styles')
    <link href="{{ asset('template_dashboard/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        #services-table { width: 100% !important; }
        #service-modal .modal-dialog { max-width: 560px; }
        .dataTables_wrapper .dataTables_processing { z-index: 2; }
    </style>
@endpush

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Layanan</h1>
            <p class="mb-0 text-gray-600">Kelola daftar layanan dan harga dasar.</p>
        </div>
        <button type="button" class="btn btn-primary mt-3 mt-sm-0" id="add-service">
            <i class="fas fa-plus fa-sm mr-1" aria-hidden="true"></i> Tambah Layanan
        </button>
    </div>

    <section class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="services-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Kode</th>
                            <th>Nama Layanan</th>
                            <th>Harga Dasar (Rp)</th>
                            <th>Estimasi Hari</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

    <div class="modal fade" id="service-modal" tabindex="-1" role="dialog" aria-labelledby="service-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content">
                <form id="service-form">
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="service-modal-title">Tambah Layanan</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-group">
                            <label for="service-code">Kode</label>
                            <input class="form-control" id="service-code" name="code" maxlength="50" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label for="service-name">Nama Layanan</label>
                            <input class="form-control" id="service-name" name="name" maxlength="255" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group">
                            <label for="service-price">Harga Dasar (Rp)</label>
                            <input class="form-control" id="service-price" name="base_price" type="number" min="0" step="1" inputmode="numeric" required>
                            <div class="invalid-feedback"></div>
                        </div>
                        <div class="form-group mb-0">
                            <label for="service-days">Estimasi Hari</label>
                            <input class="form-control" id="service-days" name="estimated_days" type="number" min="1" step="1" inputmode="numeric" required>
                            <div class="invalid-feedback"></div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="save-service">
                            <i class="fas fa-save mr-1" aria-hidden="true"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('template_dashboard/vendor/datatables/jquery.dataTables.min.js') }}"></script>
    <script src="{{ asset('template_dashboard/vendor/datatables/dataTables.bootstrap4.min.js') }}"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(function () {
            const endpoint = @json(route('admin.services.index'));
            const csrf = $('meta[name="csrf-token"]').attr('content');
            let editingId = null;

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            });

            const table = $('#services-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: endpoint,
                order: [[1, 'asc']],
                columns: [
                    { data: 'code', name: 'code' },
                    { data: 'name', name: 'name' },
                    {
                        data: 'base_price', name: 'base_price', className: 'text-right',
                        render: function (value, type) {
                            if (type !== 'display') return value;
                            return 'Rp ' + new Intl.NumberFormat('id-ID').format(value);
                        }
                    },
                    { data: 'estimated_days', name: 'estimated_days', className: 'text-center', render: value => value + ' hari' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                language: {
                    emptyTable: 'Belum ada data layanan',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ layanan',
                    infoEmpty: 'Tidak ada layanan untuk ditampilkan',
                    infoFiltered: '(disaring dari _MAX_ layanan)',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    loadingRecords: 'Memuat data...',
                    processing: 'Memproses...',
                    search: 'Cari:',
                    zeroRecords: 'Layanan tidak ditemukan',
                    paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
                }
            });

            function resetForm() {
                editingId = null;
                $('#service-form')[0].reset();
                $('#service-form .is-invalid').removeClass('is-invalid');
                $('#service-modal-title').text('Tambah Layanan');
                $('#save-service').html('<i class="fas fa-save mr-1" aria-hidden="true"></i> Simpan');
            }

            $('#add-service').on('click', function () {
                resetForm();
                $('#service-modal').modal('show');
            });

            $('#service-modal').on('hidden.bs.modal', resetForm);

            $('#services-table').on('click', '.js-edit-service', function () {
                const id = $(this).data('id');
                const row = table.row($(this).closest('tr')).data();
                editingId = id;
                $('#service-code').val(row.code);
                $('#service-name').val(row.name);
                $('#service-price').val(row.base_price);
                $('#service-days').val(row.estimated_days);
                $('#service-modal-title').text('Ubah Layanan');
                $('#save-service').html('<i class="fas fa-save mr-1" aria-hidden="true"></i> Perbarui');
                $('#service-modal').modal('show');
            });

            $('#service-form').on('submit', function (event) {
                event.preventDefault();
                const isEditing = editingId !== null;
                const url = isEditing ? endpoint + '/' + editingId : endpoint;
                const method = isEditing ? 'PUT' : 'POST';
                const button = $('#save-service').prop('disabled', true);

                $('#service-form .is-invalid').removeClass('is-invalid');
                $.ajax({ url: url, method: method, data: $(this).serialize() })
                    .done(function (response) {
                        $('#service-modal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1800, showConfirmButton: false });
                    })
                    .fail(function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            Object.entries(xhr.responseJSON.errors).forEach(function ([field, messages]) {
                                const input = $('[name="' + field + '"]');
                                input.addClass('is-invalid').siblings('.invalid-feedback').text(messages[0]);
                            });
                            return;
                        }
                        Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Layanan gagal disimpan.' });
                    })
                    .always(function () { button.prop('disabled', false); });
            });

            $('#services-table').on('click', '.js-delete-service', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus layanan?',
                    text: 'Layanan "' + name + '" akan dihapus.',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#c0392b'
                }).then(function (result) {
                    if (!result.isConfirmed) return;

                    $.ajax({ url: endpoint + '/' + id, method: 'DELETE' })
                        .done(function (response) {
                            table.ajax.reload(null, false);
                            Swal.fire({ icon: 'success', title: 'Terhapus', text: response.message, timer: 1600, showConfirmButton: false });
                        })
                        .fail(function (xhr) {
                            Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Layanan gagal dihapus.' });
                        });
                });
            });
        });
    </script>
@endpush

@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Data Client')

@push('styles')
    <link href="{{ asset('template_dashboard/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        #clients-table { width: 100% !important; }
        .client-actions { display: inline-flex; align-items: center; gap: .4rem; white-space: nowrap; }
        #client-modal .modal-dialog {
            width: calc(100vw - 3rem);
            max-width: 1180px;
            height: calc(100vh - 2rem);
            margin: 1rem auto;
        }
        #client-view-modal .modal-dialog { width: calc(100vw - 3rem); max-width: 1180px; max-height: calc(100vh - 3.5rem); }
        #client-view-modal .modal-content { max-height: calc(100vh - 3.5rem); overflow: hidden; }
        #client-view-modal .modal-header,
        #client-view-modal .modal-footer { flex-shrink: 0; }
        #client-view-modal .modal-body { flex: 1 1 auto; min-height: 0; max-height: calc(100vh - 12rem); overflow-y: auto; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; }
        #client-file-preview { width: 100%; height: min(68vh, 720px); border: 1px solid #e3e6f0; border-radius: .25rem; background: #f8f9fc; }
        #client-file-list .list-group-item { overflow-wrap: anywhere; }
        #client-modal .modal-content { height: 100%; max-height: 100%; overflow: hidden; }
        #client-form { display: flex; flex-direction: column; height: 100%; min-height: 0; }
        #client-form .modal-header,
        #client-form .modal-footer { flex-shrink: 0; }
        #client-form .modal-body { flex: 1 1 auto; min-height: 0; overflow-y: auto; overscroll-behavior-y: contain; -webkit-overflow-scrolling: touch; touch-action: pan-y; }
        #upload-list .upload-row { border-bottom: 1px solid #e3e6f0; padding-bottom: 1rem; margin-bottom: 1rem; }
        @media (max-width: 575.98px) {
            #client-modal .modal-dialog { width: calc(100vw - 1rem); height: calc(100vh - 1rem); margin: .5rem auto; }
            #client-view-modal .modal-dialog { width: calc(100vw - 1rem); height: calc(100vh - 1rem); height: calc(100dvh - 1rem); max-height: none; margin: .5rem auto; }
            #client-view-modal .modal-content { height: 100%; max-height: 100%; }
            #client-view-modal .modal-body { max-height: none; touch-action: pan-y; }
            #client-file-preview { display: none !important; }
        }
    </style>
@endpush

@section('content')
    <div class="d-sm-flex align-items-center justify-content-between mb-4">
        <div>
            <h1 class="h3 mb-1 text-gray-800">Data Client</h1>
            <p class="mb-0 text-gray-600">Kelola data client, layanan, dan berkas pendukung.</p>
        </div>
        <button type="button" class="btn btn-primary mt-3 mt-sm-0" id="add-client">
            <i class="fas fa-plus fa-sm mr-1" aria-hidden="true"></i> Tambah Client
        </button>
    </div>

    <section class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered table-hover mb-0" id="clients-table">
                    <thead class="thead-light">
                        <tr>
                            <th>Judul Akta</th>
                            {{-- <th>NIK KTP</th> --}}
                            <th>Jenis Layanan</th>
                            <th>Tipe Client</th>
                            <th>No. WA</th>
                            <th>Email</th>
                            <th>Berkas</th>
                            <th class="text-center">Aksi</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </section>

    <div class="modal fade" id="client-modal" tabindex="-1" role="dialog" aria-labelledby="client-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <form id="client-form" novalidate>
                    <div class="modal-header">
                        <h2 class="modal-title h5" id="client-modal-title">Tambah Client</h2>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                    </div>
                    <div class="modal-body">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="client-service">Jenis Layanan</label>
                                <select class="form-control" id="client-service" name="service_id">
                                    <option value="">Pilih layanan</option>
                                    @foreach ($services as $service)
                                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6" id="other-service-group">
                                <label for="other-service">Layanan lainnya</label>
                                <input class="form-control" id="other-service" name="other_service" maxlength="255" placeholder="Isi jika layanan belum tersedia">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="deed-title">Judul Akta</label>
                            <input class="form-control" id="deed-title" name="deed_title" maxlength="255" required>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="client-nik">NIK KTP</label>
                                <input class="form-control" id="client-nik" name="nik" type="text" inputmode="numeric" pattern="[0-9]{16}" minlength="16" maxlength="16" required>
                                <small class="form-text text-muted">Masukkan 16 digit angka.</small>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="client-type">Tipe Client</label>
                                <select class="form-control" id="client-type" name="client_type" required>
                                    <option value="">Pilih tipe client</option>
                                    <option value="individu">Individu</option>
                                    <option value="badan_usaha">Badan usaha/badan hukum</option>
                                    <option value="kuasa">Kuasa</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="client-phone">No. WA</label>
                                <input class="form-control" id="client-phone" name="phone" type="tel" maxlength="24" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="client-email">Email</label>
                                <input class="form-control" id="client-email" name="email" type="email" maxlength="255">
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="client-address">Alamat</label>
                            <textarea class="form-control" id="client-address" name="address" rows="3" maxlength="5000" required></textarea>
                        </div>

                        <hr>
                        <div class="d-flex align-items-center justify-content-between mb-3">
                            <div>
                                <h3 class="h6 font-weight-bold mb-1">Berkas pendukung</h3>
                                <p class="small text-muted mb-0">Format PDF, maksimal 10 MB per berkas.</p>
                            </div>
                            <button type="button" class="btn btn-sm btn-outline-primary" id="add-upload-row">
                                <i class="fas fa-plus mr-1" aria-hidden="true"></i> Tambah Berkas
                            </button>
                        </div>
                        <div id="existing-files" class="mb-3" aria-live="polite"></div>
                        <div id="upload-list"></div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary" id="save-client">
                            <i class="fas fa-save mr-1" aria-hidden="true"></i> Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="client-view-modal" tabindex="-1" role="dialog" aria-labelledby="client-view-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h2 class="modal-title h5 mb-1" id="client-view-title">Detail Client</h2>
                        <span class="badge badge-light" id="view-client-type"></span>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div class="row">
                        <section class="col-lg-5 mb-4 mb-lg-0" aria-label="Informasi client">
                            <h3 class="h6 font-weight-bold text-gray-800 mb-3">Informasi Client</h3>
                            <dl class="row mb-0">
                                <dt class="col-sm-5 text-gray-600">Judul Akta</dt><dd class="col-sm-7" id="view-deed-title"></dd>
                                <dt class="col-sm-5 text-gray-600">Jenis Layanan</dt><dd class="col-sm-7" id="view-service"></dd>
                                <dt class="col-sm-5 text-gray-600">NIK KTP</dt><dd class="col-sm-7" id="view-nik"></dd>
                                <dt class="col-sm-5 text-gray-600">No. WA</dt><dd class="col-sm-7" id="view-phone"></dd>
                                <dt class="col-sm-5 text-gray-600">Email</dt><dd class="col-sm-7" id="view-email"></dd>
                                <dt class="col-sm-5 text-gray-600">Alamat</dt><dd class="col-sm-7 text-break" id="view-address"></dd>
                            </dl>
                        </section>
                        <section class="col-lg-7" aria-label="Berkas client">
                            <h3 class="h6 font-weight-bold text-gray-800 mb-3">Berkas Pendukung</h3>
                            <div id="client-file-list" class="list-group mb-3"></div>
                            <a id="client-pdf-new-tab" class="btn btn-sm btn-outline-primary mb-3 d-none" href="#" target="_blank" rel="noopener">
                                <i class="fas fa-external-link-alt mr-1" aria-hidden="true"></i> Buka PDF di tab baru
                            </a>
                            <div id="client-file-empty" class="border rounded text-center text-muted py-5">Belum ada berkas untuk ditampilkan.</div>
                            <iframe id="client-file-preview" class="d-none" title="Pratinjau berkas PDF"></iframe>
                        </section>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-dismiss="modal">Tutup</button>
                </div>
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
            const endpoint = @json(route('admin.clients.index'));
            const filesEndpoint = @json(url('/admin/data-client/berkas'));
            const csrf = $('meta[name="csrf-token"]').attr('content');
            let editingId = null;

            $.ajaxSetup({
                headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
            });

            const table = $('#clients-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: endpoint,
                order: [[0, 'asc']],
                columns: [
                    { data: 'deed_title', name: 'deed_title' },
                    // { data: 'nik', name: 'nik' },
                    { data: 'service_name', name: 'service_name', orderable: false, searchable: false },
                    {
                        data: 'client_type', name: 'client_type',
                        render: function (value, type) {
                            if (type !== 'display') return value;
                            return { individu: 'Individu', badan_usaha: 'Badan usaha/badan hukum', kuasa: 'Kuasa' }[value] || value;
                        }
                    },
                    { data: 'phone', name: 'phone' },
                    { data: 'email', name: 'email', defaultContent: '-' },
                    { data: 'files_count', name: 'files_count', searchable: false, className: 'text-center', render: value => value + ' berkas' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                pageLength: 10,
                lengthMenu: [[10, 25, 50], [10, 25, 50]],
                language: {
                    emptyTable: 'Belum ada data client',
                    info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ client',
                    infoEmpty: 'Tidak ada client untuk ditampilkan',
                    infoFiltered: '(disaring dari _MAX_ client)',
                    lengthMenu: 'Tampilkan _MENU_ data',
                    loadingRecords: 'Memuat data...',
                    processing: 'Memproses...',
                    search: 'Cari:',
                    zeroRecords: 'Data client tidak ditemukan',
                    paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
                }
            });

            function addUploadRow() {
                const row = $('<div class="upload-row"></div>');
                const fields = $('<div class="form-row align-items-end"></div>');
                const labelGroup = $('<div class="form-group col-md-5 mb-md-0"></div>');
                labelGroup.append('<label>Nama file</label>');
                labelGroup.append($('<input class="form-control file-label" maxlength="255" required>').attr('aria-label', 'Nama file'));
                const fileGroup = $('<div class="form-group col-md-6 mb-md-0"></div>');
                fileGroup.append('<label>Upload PDF</label>');
                fileGroup.append($('<input class="form-control-file file-input" type="file" accept="application/pdf,.pdf" required>').attr('aria-label', 'Pilih berkas PDF'));
                const removeGroup = $('<div class="form-group col-md-1 mb-md-0 text-right"></div>');
                removeGroup.append('<button type="button" class="btn btn-sm btn-outline-danger remove-upload-row" aria-label="Hapus baris berkas" title="Hapus baris"><i class="fas fa-times" aria-hidden="true"></i></button>');
                fields.append(labelGroup, fileGroup, removeGroup);
                row.append(fields);
                $('#upload-list').append(row);
            }

            function resetForm() {
                editingId = null;
                $('#client-form')[0].reset();
                $('#existing-files, #upload-list').empty();
                $('#client-modal-title').text('Tambah Client');
                $('#save-client').html('<i class="fas fa-save mr-1" aria-hidden="true"></i> Simpan');
                updateOtherServiceField();
            }

            function updateOtherServiceField() {
                const useOther = !$('#client-service').val();
                $('#other-service-group').toggle(useOther);
                $('#other-service').prop('required', useOther);
                if (!useOther) $('#other-service').val('');
            }

            function displayExistingFiles(files) {
                const container = $('#existing-files').empty();
                if (!files.length) return;
                container.append('<div class="small font-weight-bold mb-2">Berkas tersimpan</div>');

                files.forEach(function (file) {
                    const row = $('<div class="d-flex align-items-center justify-content-between border rounded p-2 mb-2"></div>');
                    const info = $('<div class="mr-2"></div>');
                    const link = $('<a target="_blank" rel="noopener"></a>')
                        .attr('href', filesEndpoint + '/' + encodeURIComponent(file.id))
                        .text(file.label);
                    info.append(link, $('<div class="small text-muted"></div>').text(file.original_name));
                    const remove = $('<button type="button" class="btn btn-sm btn-outline-danger" aria-label="Hapus berkas" title="Hapus berkas"><i class="fas fa-trash" aria-hidden="true"></i></button>')
                        .on('click', function () {
                            Swal.fire({
                                icon: 'warning',
                                title: 'Hapus berkas?',
                                text: 'Berkas "' + file.label + '" akan dihapus.',
                                showCancelButton: true,
                                confirmButtonText: 'Ya, hapus',
                                cancelButtonText: 'Batal',
                                confirmButtonColor: '#c0392b'
                            }).then(function (result) {
                                if (!result.isConfirmed) return;
                                $.ajax({ url: filesEndpoint + '/' + file.id, method: 'DELETE' })
                                    .done(function () {
                                        row.remove();
                                        table.ajax.reload(null, false);
                                        Swal.fire({ icon: 'success', title: 'Terhapus', text: 'Berkas berhasil dihapus.', timer: 1400, showConfirmButton: false });
                                    })
                                    .fail(function () { Swal.fire({ icon: 'error', title: 'Gagal', text: 'Berkas gagal dihapus.' }); });
                            });
                        });
                    row.append(info, remove);
                    container.append(row);
                });
            }

            $('#client-service').on('change', updateOtherServiceField);
            $('#add-upload-row').on('click', addUploadRow);
            $('#upload-list').on('click', '.remove-upload-row', function () { $(this).closest('.upload-row').remove(); });
            $('#add-client').on('click', function () {
                resetForm();
                $('#client-modal').modal('show');
            });
            $('#client-modal').on('hidden.bs.modal', resetForm);

            $('#client-view-modal').on('hidden.bs.modal', function () {
                $('#client-file-preview').attr('src', 'about:blank').addClass('d-none');
                $('#client-pdf-new-tab').attr('href', '#').addClass('d-none');
                $('#client-file-list').empty();
                $('#client-file-empty').removeClass('d-none');
            });

            $('#clients-table').on('click', '.js-view-client', function () {
                const id = $(this).data('id');

                $.get(endpoint + '/' + id).done(function (client) {
                    const typeLabels = {
                        individu: 'Individu',
                        badan_usaha: 'Badan usaha/badan hukum',
                        kuasa: 'Kuasa'
                    };

                    $('#view-client-title').text(client.deed_title || 'Detail Client');
                    $('#view-client-type').text(typeLabels[client.client_type] || client.client_type);
                    $('#view-deed-title').text(client.deed_title || '-');
                    $('#view-service').text(client.service?.name || client.other_service || 'Layanan tidak tersedia');
                    $('#view-nik').text(client.nik || '-');
                    $('#view-phone').text(client.phone || '-');
                    $('#view-email').text(client.email || '-');
                    $('#view-address').text(client.address || '-');

                    const fileList = $('#client-file-list').empty();
                    const files = client.files || [];
                    $('#client-file-empty').toggleClass('d-none', files.length > 0);
                    $('#client-file-preview').attr('src', 'about:blank').toggleClass('d-none', files.length === 0);

                    files.forEach(function (file, index) {
                        const button = $('<button type="button" class="list-group-item list-group-item-action"></button>')
                            .append($('<span class="d-block font-weight-bold"></span>').text(file.label))
                            .append($('<span class="d-block small text-muted"></span>').text(file.original_name))
                            .on('click', function () {
                                fileList.find('.active').removeClass('active');
                                button.addClass('active');
                                const fileUrl = filesEndpoint + '/' + encodeURIComponent(file.id);
                                $('#client-file-preview')
                                    .attr('src', fileUrl)
                                    .removeClass('d-none');
                                $('#client-pdf-new-tab').attr('href', fileUrl).removeClass('d-none');
                            });

                        fileList.append(button);
                        if (index === 0) button.trigger('click');
                    });

                    $('#client-view-modal').modal('show');
                }).fail(function () {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Detail client gagal dimuat.' });
                });
            });

            $('#clients-table').on('click', '.js-edit-client', function () {
                const id = $(this).data('id');
                $.get(endpoint + '/' + id).done(function (client) {
                    resetForm();
                    editingId = client.id;
                    $('#client-modal-title').text('Ubah Data Client');
                    $('#save-client').html('<i class="fas fa-save mr-1" aria-hidden="true"></i> Perbarui');
                    $('#client-service').val(client.service_id || '');
                    $('#other-service').val(client.other_service || '');
                    updateOtherServiceField();
                    $('#deed-title').val(client.deed_title);
                    $('#client-nik').val(client.nik);
                    $('#client-type').val(client.client_type);
                    $('#client-phone').val(client.phone);
                    $('#client-email').val(client.email || '');
                    $('#client-address').val(client.address);
                    displayExistingFiles(client.files || []);
                    $('#client-modal').modal('show');
                }).fail(function () {
                    Swal.fire({ icon: 'error', title: 'Gagal', text: 'Data client gagal dimuat.' });
                });
            });

            $('#client-form').on('submit', function (event) {
                event.preventDefault();
                if (!this.reportValidity()) return;

                const formData = new FormData(this);
                $('#upload-list .upload-row').each(function (index) {
                    formData.append('files[' + index + '][label]', $(this).find('.file-label').val());
                    formData.append('files[' + index + '][file]', $(this).find('.file-input')[0].files[0]);
                });

                const isEditing = editingId !== null;
                const url = isEditing ? endpoint + '/' + editingId : endpoint;
                if (isEditing) formData.append('_method', 'PUT');
                const button = $('#save-client').prop('disabled', true);

                $.ajax({ url: url, method: 'POST', data: formData, processData: false, contentType: false })
                    .done(function (response) {
                        $('#client-modal').modal('hide');
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: 'Berhasil', text: response.message, timer: 1800, showConfirmButton: false });
                    })
                    .fail(function (xhr) {
                        if (xhr.status === 422 && xhr.responseJSON.errors) {
                            const messages = Object.values(xhr.responseJSON.errors).flat();
                            Swal.fire({ icon: 'error', title: 'Periksa kembali data', text: messages[0] });
                            return;
                        }
                        Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Data client gagal disimpan.' });
                    })
                    .always(function () { button.prop('disabled', false); });
            });

            $('#clients-table').on('click', '.js-delete-client', function () {
                const id = $(this).data('id');
                const name = $(this).data('name');

                Swal.fire({
                    icon: 'warning',
                    title: 'Hapus data client?',
                    text: 'Data "' + name + '" dan seluruh berkasnya akan dihapus.',
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
                            Swal.fire({ icon: 'error', title: 'Gagal', text: xhr.responseJSON?.message || 'Data client gagal dihapus.' });
                        });
                });
            });
        });
    </script>
@endpush

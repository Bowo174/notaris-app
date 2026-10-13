@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Pemesanan')

@push('styles')
    <link href="{{ asset('template_dashboard/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        #orders-table { width: 100% !important; }
        .order-status-control { min-width: 185px; }
        .order-actions { white-space: nowrap; display: flex; justify-content: center; gap: .35rem; }
        .edit-order-modal .modal-dialog { max-width: 980px; }
        .edit-order-modal .modal-body { max-height: calc(100vh - 200px); overflow-y: auto; }
        .order-service-options { max-height: 180px; overflow-y: auto; }
        @media (max-width: 575.98px) {
            .edit-order-modal .modal-dialog { margin: .5rem; }
            .edit-order-modal .modal-body { max-height: calc(100vh - 150px); padding: 1rem; }
        }
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
            <thead class="thead-light"><tr><th>Pemohon</th><th>Judul Akta</th><th>Jenis Akta</th><th>Status</th><th>Berkas</th><th>Tanggal</th><th>Aksi</th><th>NIK</th></tr></thead>
        </table>
    </div>

    <div class="modal fade edit-order-modal" id="editOrderModal" tabindex="-1" role="dialog" aria-labelledby="editOrderTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <form id="editOrderForm" class="modal-content" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-header"><div><h2 class="modal-title h5 mb-1" id="editOrderTitle">Edit Pemesanan</h2><div id="editOrderReference" class="small text-muted"></div></div><button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
                <div class="modal-body">
                    <div id="editOrderErrors" class="alert alert-danger d-none" role="alert"></div>
                    <h3 class="h6 font-weight-bold border-bottom pb-2 mb-3">Client / Pemohon</h3>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label for="edit_client_name">Nama</label><input id="edit_client_name" name="client[name]" class="form-control" maxlength="255" required></div>
                        <div class="form-group col-md-6"><label for="edit_client_type">Tipe client</label><select id="edit_client_type" name="client[client_type]" class="form-control" required><option value="perorangan">Perorangan</option><option value="badan_usaha">Badan usaha / badan hukum</option><option value="lembaga_non_ahu">Lembaga / badan / perkumpulan non AHU</option><option value="individu">Individu (data lama)</option><option value="kuasa">Kuasa (data lama)</option></select></div>
                        <div class="form-group col-md-6"><label for="edit_client_nik">NIK / nomor identitas</label><input id="edit_client_nik" name="client[nik]" class="form-control" maxlength="16" inputmode="numeric" autocomplete="off"><small class="form-text text-muted">Masukkan tepat 16 digit angka.</small></div>
                        <div class="form-group col-md-6"><label for="edit_represented">Nama pihak yang diwakili</label><input id="edit_represented" name="client[represented_party_name]" class="form-control" maxlength="255"></div>
                        <div class="form-group col-md-6"><label for="edit_client_phone">No. WhatsApp</label><input id="edit_client_phone" name="client[phone]" class="form-control" maxlength="24" required></div>
                        <div class="form-group col-md-6"><label for="edit_client_email">Email</label><input id="edit_client_email" name="client[email]" type="email" class="form-control" maxlength="255"></div>
                        <div class="form-group col-12"><label for="edit_client_address">Alamat</label><textarea id="edit_client_address" name="client[address]" class="form-control" rows="2" required></textarea></div>
                    </div>

                    <h3 class="h6 font-weight-bold border-bottom pb-2 mb-3">Detail Pemesanan</h3>
                    <div class="form-group"><label>Jenis Akta</label><div class="order-service-options border rounded p-2">
                        @foreach ($services as $service)
                            <label class="custom-control custom-checkbox py-1 mb-1"><input class="custom-control-input edit-order-service" type="checkbox" name="services[]" value="{{ $service->id }}"><span class="custom-control-label">{{ $service->code }} - {{ $service->name }}</span></label>
                        @endforeach
                    </div><small class="form-text text-muted">Pilih satu atau lebih jenis akta.</small></div>
                    <div class="form-group"><label for="edit_other_service">Jenis Akta lainnya <span class="text-muted font-weight-normal">(opsional)</span></label><input id="edit_other_service" name="other_service" class="form-control" maxlength="255"></div>
                    <div class="form-group"><label for="edit_order_title">Judul Akta</label><input id="edit_order_title" name="title" class="form-control" maxlength="255" required></div>
                    <div class="form-group"><label for="edit_object_location">Informasi objek / lokasi</label><textarea id="edit_object_location" name="object_location" class="form-control" rows="2"></textarea></div>
                    <div class="form-group"><label for="edit_related_parties">Pihak lain yang terlibat</label><textarea id="edit_related_parties" name="related_parties" class="form-control" rows="2"></textarea></div>
                    <div class="form-group"><label for="edit_internal_notes">Catatan internal</label><textarea id="edit_internal_notes" name="internal_notes" class="form-control" rows="2"></textarea></div>

                    <h3 class="h6 font-weight-bold border-bottom pb-2 mb-3">Berkas Pendukung</h3>
                    <div id="editExistingFiles" class="list-group mb-3"></div>
                    <div id="editNewFiles"></div>
                    <button type="button" class="btn btn-sm btn-outline-primary" id="addEditOrderFile"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah berkas</button>
                    <small class="form-text text-muted">PDF, DOC, atau DOCX; maksimal 10 MB per berkas. Berkas baru akan ditambahkan ke pemesanan.</small>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="saveEditOrder"><i class="fas fa-save mr-1" aria-hidden="true"></i>Simpan Perubahan</button></div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
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

            const table = $('#orders-table').DataTable();
            const modal = $('#editOrderModal');
            const form = document.getElementById('editOrderForm');
            let editFileIndex = 0;

            form.elements['client[nik]'].addEventListener('input', function () {
                this.value = this.value.replace(/\D/g, '').slice(0, 16);
            });

            function setValue(name, value) {
                const field = form.elements[name];
                if (field) field.value = value ?? '';
            }
            function escaped(value) { return $('<div>').text(value ?? '').html(); }

            $('#orders-table').on('click', '.js-edit-order', function () {
                const row = table.row($(this).closest('tr')).data();
                if (!row) return;
                form.action = @json(url('/ppat/pemesanan')) + '/' + row.id;
                form.reset();
                $('#editOrderReference').text(`Pemesanan #${row.id} · ${row.title || ''}`);
                $('#editOrderErrors').addClass('d-none').empty();
                $('#editNewFiles').empty();
                editFileIndex = 0;
                setValue('client[name]', row.client_name_snapshot);
                setValue('client[client_type]', row.client_type_snapshot);
                setValue('client[nik]', row.client_nik_snapshot);
                setValue('client[represented_party_name]', row.represented_party_snapshot);
                setValue('client[phone]', row.client_phone_snapshot);
                setValue('client[email]', row.client_email_snapshot);
                setValue('client[address]', row.client_address_snapshot);
                setValue('other_service', row.other_service);
                setValue('title', row.title);
                setValue('object_location', row.object_location);
                setValue('related_parties', row.related_parties);
                setValue('internal_notes', row.internal_notes);
                const serviceIds = (row.service_ids || []).map(String);
                form.querySelectorAll('.edit-order-service').forEach(input => { input.checked = serviceIds.includes(input.value); });
                const files = row.order_files || [];
                $('#editExistingFiles').html(files.map(file => `<a class="list-group-item list-group-item-action" href="${@json(url('/ppat/berkas-pemesanan'))}/${file.id}" target="_blank" rel="noopener"><strong>${escaped(file.label)}</strong><span class="small text-muted d-block">${escaped(file.original_name)}</span></a>`).join('') || '<div class="small text-muted">Belum ada berkas.</div>');
                modal.modal('show');
            });

            $('#orders-table').on('click', '.js-delete-order', async function () {
                const button = this;
                const confirmation = await Swal.fire({
                    icon: 'warning',
                    title: 'Hapus pemesanan?',
                    text: `Pemesanan "${button.dataset.title}" beserta lembar kerja dan berkasnya akan dihapus permanen.`,
                    showCancelButton: true,
                    confirmButtonText: 'Ya, hapus',
                    cancelButtonText: 'Batal',
                    confirmButtonColor: '#c0392b',
                });
                if (!confirmation.isConfirmed) return;

                button.disabled = true;
                try {
                    const response = await fetch(`${@json(url('/ppat/pemesanan'))}/${button.dataset.id}`, {
                        method: 'DELETE',
                        headers: {
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        },
                    });
                    const result = await response.json();
                    if (!response.ok) throw new Error(result.message || 'Pemesanan gagal dihapus.');

                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Terhapus', text: result.message, timer: 1800, showConfirmButton: false });
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Gagal menghapus', text: error.message });
                    button.disabled = false;
                }
            });

            $('#orders-table').on('click', '.js-download-receipt', async function () {
                const button = this;
                button.disabled = true;
                try {
                    const response = await fetch(button.dataset.url, {
                        headers: { 'Accept': 'application/vnd.openxmlformats-officedocument.wordprocessingml.document' },
                    });
                    const contentType = response.headers.get('Content-Type') || '';
                    if (!response.ok || !contentType.includes('wordprocessingml.document')) {
                        throw new Error('Tanda terima gagal diunduh. Pastikan sesi login masih aktif.');
                    }

                    const disposition = response.headers.get('Content-Disposition') || '';
                    const filename = disposition.match(/filename="?([^";]+)"?/i)?.[1] || 'Tanda-Terima.docx';
                    const fileUrl = URL.createObjectURL(await response.blob());
                    const downloadLink = document.createElement('a');
                    downloadLink.href = fileUrl;
                    downloadLink.download = filename;
                    document.body.appendChild(downloadLink);
                    downloadLink.click();
                    downloadLink.remove();
                    window.setTimeout(() => URL.revokeObjectURL(fileUrl), 1000);
                } catch (error) {
                    Swal.fire({ icon: 'error', title: 'Gagal mengunduh', text: error.message });
                } finally {
                    button.disabled = false;
                }
            });

            $('#addEditOrderFile').on('click', function () {
                const row = document.createElement('div');
                row.className = 'border rounded p-3 mb-2';
                row.innerHTML = `<div class="form-row align-items-end"><div class="form-group col-md-4"><label>Nama / jenis berkas</label><input name="files[${editFileIndex}][label]" class="form-control" maxlength="255" required></div><div class="form-group col-md-7"><label>File PDF, DOC, atau DOCX</label><input name="files[${editFileIndex}][file]" type="file" class="form-control-file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required></div><div class="form-group col-md-1 mb-3"><button type="button" class="btn btn-outline-danger remove-edit-file" aria-label="Hapus berkas"><i class="fas fa-trash" aria-hidden="true"></i></button></div></div>`;
                row.querySelector('.remove-edit-file').addEventListener('click', () => row.remove());
                $('#editNewFiles').append(row);
                editFileIndex++;
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const button = document.getElementById('saveEditOrder');
                const errorBox = document.getElementById('editOrderErrors');
                button.disabled = true;
                errorBox.classList.add('d-none');
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: new FormData(form)
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        const messages = Object.values(result.errors || {}).flat();
                        throw new Error(messages.join(' · ') || result.message || 'Pemesanan gagal diperbarui.');
                    }
                    modal.modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1800, showConfirmButton: false });
                } catch (error) {
                    errorBox.textContent = error.message;
                    errorBox.classList.remove('d-none');
                    Swal.fire({ icon: 'error', title: 'Gagal menyimpan', text: error.message });
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
@endpush

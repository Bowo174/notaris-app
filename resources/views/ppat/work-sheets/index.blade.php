@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Lembar Kerja')

@push('styles')
    <link href="{{ asset('template_dashboard/vendor/datatables/dataTables.bootstrap4.min.css') }}" rel="stylesheet">
    <style>
        #work-sheets-table { min-width: 1180px; }
        #work-sheets-table th, #work-sheets-table td { vertical-align: middle; }
        .worksheet-code-cell { display: flex; flex-direction: column; align-items: center; gap: .25rem; min-width: 74px; }
        .worksheet-code-cell img { display: block; object-fit: contain; }
        .worksheet-modal .modal-dialog { max-width: 980px; }
        .worksheet-modal .modal-body { max-height: calc(100vh - 210px); overflow-y: auto; }
        .worksheet-section-title { border-bottom: 1px solid #e3e6f0; padding-bottom: .55rem; margin: .25rem 0 1rem; }
        @media (max-width: 575.98px) {
            .worksheet-modal .modal-dialog { margin: .5rem; }
            .worksheet-modal .modal-body { max-height: calc(100vh - 150px); padding: 1rem; }
            .worksheet-modal .modal-footer { padding: .75rem 1rem; }
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <h1 class="h3 mb-1 text-gray-800">Lembar Kerja</h1>
        <p class="mb-0 text-gray-600">Lengkapi data akta, para pihak, sertipikat, dan kewajiban pajak dari pemesanan PPAT.</p>
    </div>

    @if (session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">{{ session('success') }}<button type="button" class="close" data-dismiss="alert" aria-label="Tutup"><span aria-hidden="true">&times;</span></button></div>
    @endif

    <div class="table-responsive bg-white border rounded shadow-sm p-3">
        <table id="work-sheets-table" class="table table-hover table-bordered mb-0">
            <thead class="thead-light">
                <tr>
                    <th>No.</th><th>Kode</th><th>Tanggal</th><th>Nomor Akta</th><th>Akta</th>
                    <th>Pihak 1</th><th>Pihak 2</th><th>Sertipikat</th><th>Nilai Transaksi</th>
                    <th>PPh</th><th>BPHTB</th><th>Aksi</th>
                </tr>
            </thead>
        </table>
    </div>

    <div class="modal fade worksheet-modal" id="workSheetModal" tabindex="-1" role="dialog" aria-labelledby="workSheetModalTitle" aria-hidden="true">
        <div class="modal-dialog modal-dialog-scrollable" role="document">
            <form id="workSheetForm" class="modal-content" enctype="multipart/form-data">
                @csrf
                @method('PATCH')
                <div class="modal-header">
                    <div><h2 class="modal-title h5 mb-1" id="workSheetModalTitle">Lengkapi Lembar Kerja</h2><div id="workSheetOrderTitle" class="small text-muted"></div></div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Tutup"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body">
                    <div id="workSheetErrors" class="alert alert-danger d-none" role="alert"></div>
                    <h3 class="h6 font-weight-bold worksheet-section-title">Data Akta</h3>
                    <div class="form-row">
                        <div class="form-group col-md-6"><label for="deed_date">Tanggal</label><input id="deed_date" name="deed_date" type="date" class="form-control"></div>
                        <div class="form-group col-md-6"><label for="deed_number">Nomor akta</label><input id="deed_number" name="deed_number" class="form-control" maxlength="100"></div>
                    </div>

                    @foreach (['party_one' => 'Pihak 1', 'party_two' => 'Pihak 2'] as $key => $label)
                        <h3 class="h6 font-weight-bold worksheet-section-title">{{ $label }}</h3>
                        <div class="form-row">
                            <div class="form-group col-md-4"><label for="{{ $key }}_type">Jenis pihak</label><select id="{{ $key }}_type" name="{{ $key }}[type]" class="form-control"><option value="">Pilih jenis</option><option value="perorangan">Perorangan</option><option value="badan_usaha">Badan usaha / badan hukum</option><option value="lembaga_non_ahu">Lembaga / badan / perkumpulan non AHU</option></select></div>
                            <div class="form-group col-md-8"><label for="{{ $key }}_name">Nama</label><input id="{{ $key }}_name" name="{{ $key }}[name]" class="form-control" maxlength="255"></div>
                            <div class="form-group col-md-6"><label for="{{ $key }}_birth">Tempat / tanggal lahir atau data pendirian</label><input id="{{ $key }}_birth" name="{{ $key }}[birth_info]" class="form-control" maxlength="255"></div>
                            <div class="form-group col-md-6"><label for="{{ $key }}_identity">NIK / nomor identitas</label><input id="{{ $key }}_identity" name="{{ $key }}[identity]" class="form-control" maxlength="100"></div>
                            <div class="form-group col-12"><label for="{{ $key }}_address">Alamat</label><textarea id="{{ $key }}_address" name="{{ $key }}[address]" class="form-control" rows="2"></textarea></div>
                        </div>
                    @endforeach

                    <h3 class="h6 font-weight-bold worksheet-section-title">Sertipikat dan Nilai</h3>
                    <div class="form-group"><label for="certificate_details">Data sertipikat</label><textarea id="certificate_details" name="certificate_details" class="form-control" rows="3" placeholder="Nomor, jenis hak, lokasi, luas, dan keterangan lain"></textarea></div>
                    <div class="form-group"><label for="transaction_value">Nilai transaksi (Rp)</label><input id="transaction_value" name="transaction_value" type="number" min="0" step="1" class="form-control"></div>

                    <h3 class="h6 font-weight-bold worksheet-section-title">Pajak</h3>
                    <div class="form-row">
                        <div class="form-group col-md-4"><label for="pbb_amount">PBB (Rp)</label><input id="pbb_amount" name="pbb_amount" type="number" min="0" step="1" class="form-control"></div>
                        <div class="form-group col-md-4"><label for="bphtb_amount">BPHTB (Rp)</label><input id="bphtb_amount" name="bphtb_amount" type="number" min="0" step="1" class="form-control"></div>
                        <div class="form-group col-md-4"><label for="pph_amount">PPh (Rp)</label><input id="pph_amount" name="pph_amount" type="number" min="0" step="1" class="form-control"></div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-between align-items-center worksheet-section-title">
                        <h3 class="h6 font-weight-bold mb-2 mb-sm-0">Berkas pendukung</h3>
                        <button type="button" class="btn btn-sm btn-outline-primary" id="addWorksheetFile"><i class="fas fa-plus mr-1" aria-hidden="true"></i>Tambah berkas</button>
                    </div>
                    <p class="small text-muted">PDF, DOC, atau DOCX; maksimal 10 MB per berkas.</p>
                    <div id="existingWorksheetFiles" class="list-group mb-3"></div>
                    <div id="newWorksheetFiles"></div>
                </div>
                <div class="modal-footer"><button type="button" class="btn btn-light" data-dismiss="modal">Batal</button><button type="submit" class="btn btn-primary" id="saveWorkSheet"><i class="fas fa-save mr-1" aria-hidden="true"></i>Simpan</button></div>
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
            const table = $('#work-sheets-table').DataTable({
                processing: true,
                serverSide: true,
                scrollX: true,
                ajax: @json(route('ppat.work-sheets.index')),
                order: [[0, 'desc']],
                columns: [
                    { data: 'id', name: 'id' },
                    { data: 'code', name: 'code' },
                    { data: 'deed_date', name: 'deed_date', searchable: false },
                    { data: 'deed_number', name: 'deed_number', defaultContent: '-' },
                    { data: 'deed_name', name: 'deed_name', defaultContent: '-', render: (v, t, row) => t === 'display' && !v ? $('<div>').text(row.order_title || '-').html() : v },
                    { data: 'party_one_name', name: 'party_one', searchable: false, orderable: false },
                    { data: 'party_two_name', name: 'party_two', searchable: false, orderable: false },
                    { data: 'certificate_status', name: 'certificate_details', searchable: false, orderable: false },
                    { data: 'transaction_value', name: 'transaction_value', render: value => value === null ? '-' : 'Rp ' + new Intl.NumberFormat('id-ID').format(value) },
                    { data: 'tax_status', name: 'pph_amount', searchable: false, orderable: false, render: v => v.pph ? '<span class="text-success">Terisi</span>' : '<span class="text-muted">Belum</span>' },
                    { data: 'tax_status', name: 'bphtb_amount', searchable: false, orderable: false, render: v => v.bphtb ? '<span class="text-success">Terisi</span>' : '<span class="text-muted">Belum</span>' },
                    { data: 'actions', name: 'actions', orderable: false, searchable: false, className: 'text-center' }
                ],
                pageLength: 10,
                language: {
                    emptyTable: 'Belum ada lembar kerja', info: 'Menampilkan _START_ sampai _END_ dari _TOTAL_ lembar kerja',
                    infoEmpty: 'Tidak ada data untuk ditampilkan', infoFiltered: '(disaring dari _MAX_ data)',
                    lengthMenu: 'Tampilkan _MENU_ data', loadingRecords: 'Memuat data...', processing: 'Memproses...',
                    search: 'Cari:', zeroRecords: 'Lembar kerja tidak ditemukan',
                    paginate: { first: 'Awal', last: 'Akhir', next: 'Berikutnya', previous: 'Sebelumnya' }
                }
            });

            const modal = $('#workSheetModal');
            const form = document.getElementById('workSheetForm');
            let fileIndex = 0;

            function setValue(name, value) { const field = form.elements[name]; if (field) field.value = value ?? ''; }
            function esc(value) { return $('<div>').text(value ?? '').html(); }

            $('#work-sheets-table').on('click', '.js-edit-work-sheet', function () {
                const row = table.row($(this).closest('tr')).data();
                if (!row) return;
                form.action = @json(url('/ppat/lembar-kerja')) + '/' + row.id;
                form.reset();
                $('#workSheetOrderTitle').text(`${row.code_label} · ${row.client_name || ''} · ${row.order_title || ''}`);
                $('#workSheetErrors').addClass('d-none').empty();
                $('#newWorksheetFiles').empty();
                fileIndex = 0;
                setValue('deed_date', row.deed_date ? String(row.deed_date).slice(0, 10) : '');
                setValue('deed_number', row.deed_number);
                setValue('certificate_details', row.certificate_details);
                ['transaction_value', 'pbb_amount', 'bphtb_amount', 'pph_amount'].forEach(key => setValue(key, row[key]));
                ['party_one', 'party_two'].forEach((key) => {
                    const party = row[key] || {};
                    setValue(`${key}[type]`, party.type);
                    setValue(`${key}[name]`, party.name);
                    setValue(`${key}[birth_info]`, party.birth_info);
                    setValue(`${key}[identity]`, party.identity);
                    setValue(`${key}[address]`, party.address);
                });
                const files = row.files || [];
                $('#existingWorksheetFiles').html(files.map(file => `<a class="list-group-item list-group-item-action" href="${@json(url('/ppat/lembar-kerja/berkas'))}/${file.id}" target="_blank" rel="noopener"><strong>${esc(file.label)}</strong><span class="small text-muted d-block">${esc(file.original_name)}</span></a>`).join('') || '<div class="small text-muted">Belum ada berkas terunggah.</div>');
                modal.modal('show');
            });

            $('#addWorksheetFile').on('click', function () {
                const row = document.createElement('div');
                row.className = 'border rounded p-3 mb-2';
                row.innerHTML = `<div class="form-row align-items-end"><div class="form-group col-md-3"><label>Kategori</label><select class="form-control" name="documents[${fileIndex}][category]" required><option value="other">Lainnya</option><option value="akta">Akta</option><option value="party_one">Pihak 1</option><option value="party_two">Pihak 2</option><option value="certificate">Sertipikat</option><option value="pbb">PBB</option><option value="bphtb">BPHTB</option><option value="pph">PPh</option></select></div><div class="form-group col-md-3"><label>Nama berkas</label><input class="form-control" name="documents[${fileIndex}][label]" maxlength="255" required></div><div class="form-group col-md-5"><label>File</label><input type="file" class="form-control-file" name="documents[${fileIndex}][file]" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required></div><div class="form-group col-md-1 mb-3"><button class="btn btn-outline-danger remove-worksheet-file" type="button" aria-label="Hapus baris"><i class="fas fa-trash" aria-hidden="true"></i></button></div></div>`;
                row.querySelector('.remove-worksheet-file').addEventListener('click', () => row.remove());
                $('#newWorksheetFiles').append(row);
                fileIndex++;
            });

            form.addEventListener('submit', async function (event) {
                event.preventDefault();
                const button = document.getElementById('saveWorkSheet');
                const errors = document.getElementById('workSheetErrors');
                button.disabled = true;
                errors.classList.add('d-none');
                try {
                    const response = await fetch(form.action, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content },
                        body: new FormData(form)
                    });
                    const result = await response.json();
                    if (!response.ok) {
                        const messages = Object.values(result.errors || {}).flat();
                        throw new Error(messages.join(' · ') || result.message || 'Lembar kerja gagal disimpan.');
                    }
                    modal.modal('hide');
                    table.ajax.reload(null, false);
                    Swal.fire({ icon: 'success', title: 'Berhasil', text: result.message, timer: 1800, showConfirmButton: false });
                } catch (error) {
                    errors.textContent = error.message;
                    errors.classList.remove('d-none');
                    Swal.fire({ icon: 'error', title: 'Gagal menyimpan', text: error.message });
                } finally {
                    button.disabled = false;
                }
            });
        });
    </script>
@endpush

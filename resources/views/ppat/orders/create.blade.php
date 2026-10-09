@extends('layouts.dashboard', ['role' => $role, 'dashboardRoute' => $dashboardRoute])

@section('title', 'Tambah Pemesanan')

@push('styles')
    <style>
        .section-toggle .fa-chevron-up {
            transition: transform .2s ease;
        }

        .section-toggle[aria-expanded="false"] .fa-chevron-up {
            transform: rotate(180deg);
        }
    </style>
@endpush

@section('content')
    <div class="mb-4">
        <a href="{{ route('ppat.orders.index') }}" class="small text-primary"><i class="fas fa-arrow-left mr-1"></i>Kembali ke
            pemesanan</a>
        <h1 class="h3 mt-2 mb-1 text-gray-800">Tambah Pemesanan</h1>
        <p class="mb-0 text-gray-600">Catat permintaan layanan dan data pemohonnya.</p>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <div class="font-weight-bold mb-1">Periksa kembali data berikut:</div>
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('ppat.orders.store') }}" enctype="multipart/form-data">
        @csrf
        <section class="bg-white border rounded shadow-sm p-3 p-md-4 mb-4">
            <h2 class="h5 mb-3"><button class="section-toggle btn btn-link p-0 text-gray-800 font-weight-bold"
                    type="button" data-toggle="collapse" data-target="#client-section-fields" aria-expanded="true"
                    aria-controls="client-section-fields">Client / Pemohon <i class="fas fa-chevron-up ml-1"
                        aria-hidden="true"></i></button></h2>
            <div id="client-section-fields" class="collapse show">
                <div class="form-group">
                    <label for="client-mode">Data pemohon</label>
                    <select id="client-mode" name="client_mode" class="form-control" required>
                        <option value="new" {{ old('client_mode', 'new') === 'new' ? 'selected' : '' }}>Client baru
                        </option>
                        <option value="existing" {{ old('client_mode') === 'existing' ? 'selected' : '' }}>Pilih client
                            terdaftar</option>
                    </select>
                </div>
                <div class="form-group" id="existing-client-fields" hidden>
                    <label for="client-search">Cari nama, NIK, atau nomor WhatsApp</label>
                    <input id="client-search" type="search" class="form-control" autocomplete="off"
                        placeholder="Ketik minimal 2 karakter">
                    <div id="client-search-results" class="list-group mt-2" role="listbox"></div>
                    <input id="client_profile_id" type="hidden" name="client_profile_id"
                        value="{{ old('client_profile_id') }}">
                    <div id="selected-client" class="small text-success mt-2" aria-live="polite">
                        @if ($selectedClient)
                            Terpilih:
                            {{ $selectedClient->name }}{{ $selectedClient->nik ? ' · ' . $selectedClient->nik : '' }}
                        @endif
                    </div>
                </div>
                <div id="new-client-fields" class="row">
                    <div class="form-group col-md-6"><label for="client_name">Nama client / badan usaha</label><input
                            id="client_name" name="client[name]" value="{{ old('client.name') }}" class="form-control"
                            maxlength="255"></div>
                    <div class="form-group col-md-6"><label for="client_type">Tipe client</label><select id="client_type"
                            name="client[client_type]" class="form-control">
                            <option value="">Pilih tipe client</option>
                            <option value="perorangan" @selected(old('client.client_type') === 'perorangan')>Perorangan</option>
                            <option value="badan_usaha" @selected(old('client.client_type') === 'badan_usaha')>Badan usaha / badan hukum</option>
                            <option value="lembaga_non_ahu" @selected(old('client.client_type') === 'lembaga_non_ahu')>Lembaga / badan / perkumpulan non AHU</option>
                        </select>
                    </div>
                    <div class="form-group col-md-6"><label for="client_nik">NIK / nomor identitas</label>
                        <input
                            id="client_nik" name="client[nik]" value="{{ old('client.nik') }}" class="form-control"
                            maxlength="32" inputmode="numeric"></div>
                    <div class="form-group col-md-6"><label for="represented_party_name">Nama pihak yang diwakili <span
                                class="text-muted font-weight-normal">(opsional)</span></label><input
                            id="represented_party_name" name="client[represented_party_name]"
                            value="{{ old('client.represented_party_name') }}" class="form-control" maxlength="255"></div>
                    <div class="form-group col-md-6"><label for="client_phone">No. WhatsApp</label><input id="client_phone"
                            name="client[phone]" value="{{ old('client.phone') }}" class="form-control" maxlength="24">
                    </div>
                    <div class="form-group col-md-6"><label for="client_email">Email</label><input id="client_email"
                            name="client[email]" type="email" value="{{ old('client.email') }}" class="form-control"
                            maxlength="255"></div>
                    <div class="form-group col-12"><label for="client_address">Alamat</label>
                        <textarea id="client_address" name="client[address]" class="form-control" rows="3">{{ old('client.address') }}</textarea>
                    </div>
                </div>
            </div>
        </section>

        <section class="bg-white border rounded shadow-sm p-3 p-md-4 mb-4">
            <h2 class="h5 mb-3"><button class="section-toggle btn btn-link p-0 text-gray-800 font-weight-bold"
                    type="button" data-toggle="collapse" data-target="#order-section-fields" aria-expanded="true"
                    aria-controls="order-section-fields">Detail Pemesanan <i class="fas fa-chevron-up ml-1"
                        aria-hidden="true"></i></button></h2>
            <div id="order-section-fields" class="collapse show">
                <div class="form-group"><label for="service_id">Layanan PPAT</label><select id="service_id"
                        name="service_id" class="form-control" required>
                        <option value="">Pilih layanan</option>
                        @foreach ($services as $service)
                            <option value="{{ $service->id }}" @selected((string) old('service_id') === (string) $service->id)>{{ $service->code }} -
                                {{ $service->name }}</option>
                        @endforeach
                        <option value="other" @selected(old('service_id') === 'other')>Layanan Lainnya</option>
                    </select>
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group" id="other-service-group" hidden><label for="other_service">Nama layanan
                        lainnya</label><input id="other_service" name="other_service" class="form-control"
                        maxlength="255" value="{{ old('other_service') }}">
                    <div class="invalid-feedback"></div>
                </div>
                <div class="form-group"><label for="title">keperluan pemesanan</label><input id="title"
                        name="title" value="{{ old('title') }}" class="form-control" maxlength="255" required></div>
                <div class="form-group"><label for="description">Ringkasan permintaan</label>
                    <textarea id="description" name="description" class="form-control" rows="3">{{ old('description') }}</textarea>
                </div>
                <div class="form-group"><label for="object_location">Informasi objek / lokasi</label>
                    <textarea id="object_location" name="object_location" class="form-control" rows="2">{{ old('object_location') }}</textarea>
                </div>
                <div class="form-group"><label for="related_parties">Pihak lain yang terlibat <span
                            class="text-muted font-weight-normal">(opsional)</span></label>
                    <textarea id="related_parties" name="related_parties" class="form-control" rows="2"
                        placeholder="Nama dan peran pihak terkait">{{ old('related_parties') }}</textarea>
                </div>
                <div class="form-group mb-0"><label for="internal_notes">Catatan internal</label>
                    <textarea id="internal_notes" name="internal_notes" class="form-control" rows="2">{{ old('internal_notes') }}</textarea>
                </div>
            </div>
        </section>

        <section class="bg-white border rounded shadow-sm p-3 p-md-4 mb-4">
            <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
                <div>
                    <h2 class="h5 font-weight-bold text-gray-800 mb-1">Berkas Pendukung</h2>
                    <div class="small text-muted">PDF, DOC, atau DOCX; maksimal 10 MB per berkas.</div>
                </div><button type="button" class="btn btn-sm btn-outline-primary mt-2 mt-sm-0" id="add-order-file"><i
                        class="fas fa-plus mr-1"></i>Tambah Berkas</button>
            </div>
            <div id="order-files"></div>
        </section>

        <div class="d-flex justify-content-end mb-4"><a href="{{ route('ppat.orders.index') }}"
                class="btn btn-light mr-2">Batal</a><button class="btn btn-primary" type="submit"><i
                    class="fas fa-save mr-1"></i>Simpan Pemesanan</button></div>
    </form>
@endsection

@push('scripts')
    <script>
        (function() {
            const mode = document.getElementById('client-mode');
            const existing = document.getElementById('existing-client-fields');
            const fresh = document.getElementById('new-client-fields');
            const requiredFields = ['client_name', 'client_type', 'client_phone', 'client_address'];
            const serviceSelect = document.getElementById('service_id');
            const otherServiceGroup = document.getElementById('other-service-group');
            const otherServiceInput = document.getElementById('other_service');

            function updateClientMode() {
                const isExisting = mode.value === 'existing';
                if (existing) existing.hidden = !isExisting;
                fresh.hidden = isExisting;
                const selector = document.getElementById('client_profile_id');
                selector.required = isExisting;
                requiredFields.forEach((id) => {
                    document.getElementById(id).required = !isExisting;
                });
            }
            mode.addEventListener('change', updateClientMode);
            updateClientMode();

            function updateOtherServiceField() {
                const useOther = serviceSelect.value === 'other';
                otherServiceGroup.hidden = !useOther;
                otherServiceInput.required = useOther;
                if (!useOther) otherServiceInput.value = '';
            }
            serviceSelect.addEventListener('change', updateOtherServiceField);
            updateOtherServiceField();

            const search = document.getElementById('client-search');
            const results = document.getElementById('client-search-results');
            const selectedId = document.getElementById('client_profile_id');
            const selectedLabel = document.getElementById('selected-client');
            let searchTimer;
            search.addEventListener('input', function() {
                window.clearTimeout(searchTimer);
                selectedId.value = '';
                selectedLabel.textContent = '';
                const term = search.value.trim();
                if (term.length < 2) {
                    results.replaceChildren();
                    return;
                }
                searchTimer = window.setTimeout(async function() {
                    try {
                        const response = await fetch(
                            `{{ route('ppat.orders.clients.search') }}?q=${encodeURIComponent(term)}`, {
                                headers: {
                                    Accept: 'application/json'
                                }
                            });
                        if (!response.ok) throw new Error('Pencarian client gagal.');
                        const clients = await response.json();
                        results.replaceChildren();
                        if (!clients.length) {
                            const empty = document.createElement('div');
                            empty.className = 'list-group-item small text-muted';
                            empty.textContent =
                                'Client tidak ditemukan. Pilih Client baru untuk mendaftarkannya.';
                            results.appendChild(empty);
                            return;
                        }
                        clients.forEach(function(client) {
                            const option = document.createElement('button');
                            option.type = 'button';
                            option.className =
                                'list-group-item list-group-item-action text-left';
                            option.textContent =
                                `${client.name} · ${client.nik || 'Tanpa NIK'} · ${client.phone}`;
                            option.addEventListener('click', function() {
                                selectedId.value = client.id;
                                selectedLabel.textContent =
                                    `Terpilih: ${client.name}${client.nik ? ` · ${client.nik}` : ''}`;
                                search.value = client.name;
                                results.replaceChildren();
                            });
                            results.appendChild(option);
                        });
                    } catch (error) {
                        results.replaceChildren();
                        const failure = document.createElement('div');
                        failure.className = 'list-group-item small text-danger';
                        failure.textContent = error.message;
                        results.appendChild(failure);
                    }
                }, 250);
            });

            let fileIndex = 0;
            document.getElementById('add-order-file').addEventListener('click', function() {
                const row = document.createElement('div');
                row.className = 'row align-items-end border-top pt-3 mt-3';
                row.innerHTML =
                    `<div class="form-group col-md-4"><label>Nama / jenis berkas</label><input name="files[${fileIndex}][label]" class="form-control" maxlength="255" required></div><div class="form-group col-md-6"><label>File PDF, DOC, atau DOCX</label><input name="files[${fileIndex}][file]" type="file" class="form-control-file" accept=".pdf,.doc,.docx,application/pdf,application/msword,application/vnd.openxmlformats-officedocument.wordprocessingml.document" required></div><div class="form-group col-md-2"><button type="button" class="btn btn-outline-danger btn-block remove-order-file" aria-label="Hapus berkas"><i class="fas fa-trash"></i></button></div>`;
                row.querySelector('.remove-order-file').addEventListener('click', () => row.remove());
                document.getElementById('order-files').appendChild(row);
                fileIndex++;
            });
        })();
    </script>
@endpush

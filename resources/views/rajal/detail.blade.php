@extends('layouts.base')

@push('title')
    Rajal Detail
@endpush

@push('additional_styles')
    <style>
        /* --- E-Resep: Grid 3 kolom (Obat | Qty | Aksi) --- */
        #frmResep .rx-item {
            display: grid;
            grid-template-columns: 1fr 140px 120px;
            /* ubah angka sesuai selera */
            gap: .5rem .75rem;
            align-items: start;
        }

        @media (max-width: 767.98px) {
            #frmResep .rx-item {
                grid-template-columns: 1fr;
            }
        }

        #frmResep .rx-price {
            min-height: 1.25rem;
        }

        /* jaga tinggi teks harga konsisten */

        /* --- dropdown autocomplete --- */
        #frmResep .rx-med {
            position: relative;
        }

        #frmResep .rx-med .ac-menu {
            position: absolute;
            left: 0;
            right: 0;
            top: calc(100% + 2px);
            background: #fff;
            border: 1px solid #dee2e6;
            border-radius: .375rem;
            box-shadow: 0 .25rem .5rem rgba(0, 0, 0, .05);
            z-index: 1056;
        }

        #frmResep .rx-med .ac-menu>div {
            padding: .375rem .5rem;
            cursor: pointer;
        }

        #frmResep .rx-med .ac-menu>div:hover {
            background: #f8f9fa;
        }
    </style>
@endpush

@section('content')
    <div class="row">
        <div class="col-12 mb-3">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex gap-2">
                        <a href="{{ route('rajal.index') }}" class="btn btn-warning btn-sm">Kembali</a>
                    </div>
                    <div>
                        <span class="badge bg-primary">{{ strtoupper($row->status) }}</span>
                    </div>
                </div>

                {{-- KARTU INFO PASIEN (minimalis) --}}
                <div class="card-body py-3">
                    <div class="d-flex flex-wrap align-items-start gap-4">
                        <div>
                            <div class="fs-6 fw-semibold">
                                {{ $row->pasien->panggilan ? $row->pasien->panggilan . ' ' : '' }}{{ $row->pasien->nama }}
                            </div>
                            <div class="small text-muted">No. RM: <span class="fw-semibold">{{ $row->pasien->no_rm }}</span></div>
                        </div>
                        <div class="small">
                            <div>JK: {{ $row->pasien->jenis_kelamin === 'male' ? 'Laki-laki' : ($row->pasien->jenis_kelamin === 'female' ? 'Perempuan' : '-') }}</div>
                            <div>Tgl Lahir: {{ optional($row->pasien->tanggal_lahir)->format('d/m/Y') ?? '-' }}</div>
                        </div>
                        <div class="small">
                            <div>Masuk: {{ optional($row->tanggal_masuk)->format('d/m/Y H:i') }}</div>
                            <div>Telp/HP: {{ $row->pasien->nohp ?? ($row->pasien->telepon ?? '-') }}</div>
                        </div>
                        <div class="small flex-grow-1">
                            <div class="text-truncate" title="{{ $row->pasien->alamat }}">Alamat: {{ $row->pasien->alamat }}</div>
                        </div>
                    </div>
                </div>

                {{-- TABS --}}
                <div class="card-body pt-0">
                    <ul class="nav nav-tabs" id="rajalTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="asesmen-tab" data-bs-toggle="tab" data-bs-target="#asesmen" type="button" role="tab">Asesmen</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="dx-tindakan-tab" data-bs-toggle="tab" data-bs-target="#dx-tindakan" type="button" role="tab">Diagnosa & Tindakan</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="eresep-tab" data-bs-toggle="tab" data-bs-target="#eresep" type="button" role="tab">E-Resep</button>
                        </li>
                    </ul>

                    <div class="tab-content pt-3" id="rajalTabsContent">
                        {{-- TAB ASESMEN (tetap load saat halaman dibuka) --}}
                        <div class="tab-pane fade show active" id="asesmen" role="tabpanel" aria-labelledby="asesmen-tab">
                            <form id="formAsesmen" enctype="multipart/form-data" class="mt-3">
                                @csrf
                                <input type="hidden" name="pasien_id" value="{{ $row->pasien_id }}">
                                <input type="hidden" name="rawat_pasien_id" value="{{ $row->id }}">

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label">Berkas Asesmen (pdf/jpg/png, maks 3MB)</label>
                                        <input type="file" name="file_asesmen" class="form-control" accept=".pdf,.jpg,.jpeg,.png">
                                        <div class="small mt-1" id="asesmenFileLink"></div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Level Nyeri (0-10)</label>
                                        <div class="d-flex align-items-center gap-2">
                                            <input type="range" min="0" max="10" value="0" class="form-range" id="rngNyeri"
                                                oninput="document.getElementById('nyeriVal').value=this.value">
                                            <input type="number" min="0" max="10" value="0" id="nyeriVal" name="level_nyeri" class="form-control"
                                                style="width:90px">
                                        </div>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Penurunan Berat Badan</label>
                                        <select class="form-select" name="penurunan_berat_badan">
                                            <option value="">-</option>
                                            <option value="0">Tidak</option>
                                            <option value="1">Ya</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Kurang Nafsu Makan</label>
                                        <select class="form-select" name="kurang_nafsu_makan">
                                            <option value="">-</option>
                                            <option value="0">Tidak</option>
                                            <option value="1">Ya</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Kondisi Gizi</label>
                                        <select class="form-select" name="kondisi_gizi">
                                            <option value="">-</option>
                                            <option value="0">Baik</option>
                                            <option value="1">Lebih</option>
                                            <option value="2">Kurang</option>
                                            <option value="3">Buruk</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label">Tanda Vital (Sistole/Diastole)</label>
                                        <input type="text" name="tanda_vital_td" class="form-control" placeholder="120/80">
                                    </div>

                                    <div class="col-md-3"><label class="form-label">Nadi</label><input type="number" name="tanda_vital_nadi" class="form-control" min="20"
                                            max="250"></div>
                                    <div class="col-md-3"><label class="form-label">RR</label><input type="number" name="tanda_vital_rr" class="form-control" min="5"
                                            max="80"></div>
                                    <div class="col-md-3"><label class="form-label">SpO2</label><input type="number" name="tanda_vital_spo2" class="form-control" min="50"
                                            max="100"></div>
                                    <div class="col-md-3"><label class="form-label">Suhu (°C)</label><input type="number" step="0.1" name="suhu" class="form-control"
                                            min="30" max="45"></div>

                                    <div class="col-md-3"><label class="form-label">Tinggi (cm)</label><input type="number" name="tinggi_badan" class="form-control"
                                            min="0" max="300"></div>
                                    <div class="col-md-3"><label class="form-label">Berat (kg)</label><input type="number" step="0.1" name="berat_badan" class="form-control"
                                            min="0" max="500"></div>

                                    <div class="col-md-12"><label class="form-label">Keluhan Utama</label>
                                        <textarea name="keluhan_utama" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Riwayat Penyakit Sekarang</label>
                                        <textarea name="riwayat_penyakit_sekarang" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Riwayat Penyakit Dahulu</label>
                                        <textarea name="riwayat_penyakit_dahulu" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Riwayat Penyakit Keluarga</label>
                                        <textarea name="riwayat_penyakit_keluarga" class="form-control" rows="2"></textarea>
                                    </div>

                                    <div class="col-md-12"><label class="form-label">Alergi Makanan</label>
                                        <textarea name="alergi_makanan" class="form-control" rows="2"></textarea>
                                    </div>
                                    <div class="col-md-12"><label class="form-label">Alergi Obat</label>
                                        <textarea name="alergi_obat" class="form-control" rows="2"></textarea>
                                    </div>

                                    <div class="col-md-12">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" value="1" id="chkSusah" name="susah_jalan">
                                            <label class="form-check-label" for="chkSusah">Susah Jalan</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" value="1" id="chkBantu" name="alat_bantu_jalan">
                                            <label class="form-check-label" for="chkBantu">Alat Bantu Jalan</label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="checkbox" value="1" id="chkDuduk" name="menopang_duduk">
                                            <label class="form-check-label" for="chkDuduk">Menopang Duduk</label>
                                        </div>
                                    </div>
                                </div>

                                <div class="mt-3 d-flex gap-2">
                                    <button type="submit" class="btn btn-primary btn-sm">Simpan Asesmen</button>
                                    <div id="asesmenErr" class="alert alert-danger d-none m-0 py-1 px-2 small"></div>
                                    <div id="asesmenOk" class="alert alert-success d-none m-0 py-1 px-2 small"></div>
                                </div>
                            </form>
                        </div>

                        {{-- TAB DIAGNOSA & TINDAKAN (lazy-load pada klik tab) --}}
                        <div class="tab-pane fade" id="dx-tindakan" role="tabpanel" aria-labelledby="dx-tindakan-tab">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header py-2 fw-semibold">Diagnosa (ICD10)</div>
                                        <div class="card-body">
                                            <form id="frmDx" class="row g-2">
                                                @csrf
                                                <input type="hidden" name="rawat_pasien_id" value="{{ $row->id }}">
                                                <input type="hidden" name="pasien_id" value="{{ $row->pasien_id }}">

                                                <div class="col-12 fw-semibold">Diagnosis Utama</div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Kode (ICD-10)</label>
                                                    <select id="icd_utama_kode" class="form-select" style="width:100%"></select>
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">Nama Diagnosis</label>
                                                    <select id="icd_utama_nama" class="form-select" style="width:100%"></select>
                                                </div>
                                                <input type="hidden" name="kode" id="dx_kode">
                                                <input type="hidden" name="diagnosis" id="dx_nama">

                                                <div class="col-12 fw-semibold mt-3">Diagnosis Penyerta (opsional)</div>
                                                <div class="col-md-4">
                                                    <label class="form-label">Kode (ICD-10)</label>
                                                    <select id="icd_penyerta_kode" class="form-select" style="width:100%"></select>
                                                </div>
                                                <div class="col-md-8">
                                                    <label class="form-label">Nama Diagnosis</label>
                                                    <select id="icd_penyerta_nama" class="form-select" style="width:100%"></select>
                                                </div>
                                                <input type="hidden" name="kode_penyerta" id="dx_kode_p">
                                                <input type="hidden" name="diagnosis_penyerta" id="dx_nama_p">

                                                <div class="col-12">
                                                    <label class="form-label">Keterangan (opsional)</label>
                                                    <input type="text" name="keterangan" class="form-control">
                                                </div>

                                                <div class="col-12 d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary">Tambah Diagnosa</button>
                                                    <div id="dxMsg" class="text-success small d-none"></div>
                                                </div>
                                            </form>

                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm table-striped" id="tblDx">
                                                    <thead>
                                                        <tr>
                                                            <th>Kode</th>
                                                            <th>Nama</th>
                                                            <th>Kode Penyerta</th>
                                                            <th>Diagnosis Penyerta</th>
                                                            <th>Keterangan</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-6">
                                    <div class="card border-0 shadow-sm h-100">
                                        <div class="card-header py-2 fw-semibold">Tindakan</div>
                                        <div class="card-body">
                                            <form id="frmTindakan" class="row g-2">
                                                @csrf
                                                <input type="hidden" name="rawat_pasien_id" value="{{ $row->id }}">
                                                <div class="col-9">
                                                    <label class="form-label">Cari Tindakan</label>
                                                    <select id="tindakan_id" name="tindakan_id" class="form-select" style="width:100%"></select>
                                                </div>
                                                <div class="col-3">
                                                    <label class="form-label">Jumlah</label>
                                                    <input type="number" name="qty" class="form-control" value="1" min="1">
                                                </div>
                                                <div class="col-12 d-flex gap-2">
                                                    <button type="submit" class="btn btn-primary">Tambah</button>
                                                    <div id="tindakanMsg" class="text-success small d-none"></div>
                                                </div>
                                            </form>

                                            <div class="table-responsive mt-3">
                                                <table class="table table-sm table-striped" id="tblTindakan">
                                                    <thead>
                                                        <tr>
                                                            <th>Kode</th>
                                                            <th>Nama</th>
                                                            <th>Qty</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody></tbody>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                            </div>
                        </div>

                        {{-- TAB E-RESEP (lazy-load pada klik tab) --}}
                        <div class="tab-pane fade" id="eresep" role="tabpanel" aria-labelledby="eresep-tab">
                            <form id="frmResep" class="border rounded p-3">
                                @csrf
                                <input type="hidden" name="rawat_pasien_id" value="{{ $row->id }}">
                                <input type="hidden" id="examAt" value="{{ optional($row->tanggal_masuk)->toIso8601String() }}">

                                <div class="rx-item small text-muted fw-semibold mb-1 d-none d-md-grid">
                                    <div>Obat</div>
                                    <div>Qty</div>
                                    <div>Aksi</div>
                                </div>

                                <div id="items" class="mb-3"></div>

                                <template id="rowTpl">
                                    <div class="rx-item item-row mb-4">
                                        <div class="rx-med">
                                            <input type="text" class="form-control med-name" placeholder="Ketik minimal 3 huruf…">
                                            <input type="hidden" class="med-id">
                                            <div class="form-text rx-price med-price">Harga berlaku: —</div>

                                            <div class="row g-1 mt-2">
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control form-control-sm dosis" placeholder="Dosis (cth: 500 mg)">
                                                </div>
                                                <div class="col-md-6">
                                                    <input type="text" class="form-control form-control-sm aturan" placeholder="Aturan pakai (cth: 3×1 sesudah makan)">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="rx-qty">
                                            <input type="number" class="form-control qty" min="1" value="1">
                                        </div>

                                        <div class="rx-act d-grid">
                                            <button type="button" class="btn btn-outline-danger rm-row">Hapus</button>
                                        </div>
                                    </div>
                                    <hr>
                                </template>

                                <div class="d-flex gap-2 align-items-center">
                                    <button type="button" id="addItem" class="btn btn-outline-primary">Tambah Obat</button>
                                    <button type="submit" class="btn btn-primary" id="btnSaveDraft">Simpan DRAFT</button>
                                    <button type="button" class="btn btn-success" id="btnSubmitRx">
                                        Kirim ke Apoteker
                                    </button>
                                    <div id="resepMsg" class="text-success small d-none ms-2"></div>

                                    <span class="ms-auto badge bg-secondary" id="rxStatus">Status: -</span>
                                </div>
                            </form>
                        </div>
                    </div>
                </div> {{-- /card-body --}}
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const RAWAT_UUID = @json($row->uuid);
            const RAWAT_ID = @json($row->id);
            const PASIEN_ID = @json($row->pasien_id);

            // --- flags lazy-load ---
            const LOADED = {
                dxInit: false,
                dx: false,
                tindakan: false,
                resep: false
            };

            // ===== Helpers =====
            function showMsg($el, text) {
                $el.text(text).removeClass('d-none');
                setTimeout(() => $el.addClass('d-none'), 2000);
            }

            // ===== ASESMEN (autoload on page load) =====
            function prefillAsesmen(d) {
                if (!d) return;
                $('[name=keluhan_utama]').val(d.keluhan_utama || '');
                $('[name=riwayat_penyakit_sekarang]').val(d.riwayat_penyakit_sekarang || '');
                $('[name=riwayat_penyakit_dahulu]').val(d.riwayat_penyakit_dahulu || '');
                $('[name=riwayat_penyakit_keluarga]').val(d.riwayat_penyakit_keluarga || '');
                $('[name=tanda_vital_td]').val(d.tanda_vital_td || '');
                $('[name=tanda_vital_nadi]').val(d.tanda_vital_nadi || '');
                $('[name=tanda_vital_rr]').val(d.tanda_vital_rr || '');
                $('[name=tanda_vital_spo2]').val(d.tanda_vital_spo2 || '');
                $('[name=suhu]').val(d.suhu || '');
                $('[name=tinggi_badan]').val(d.tinggi_badan || '');
                $('[name=berat_badan]').val(d.berat_badan || '');
                $('[name=penurunan_berat_badan]').val(d.penurunan_berat_badan ?? '');
                $('[name=kurang_nafsu_makan]').val(d.kurang_nafsu_makan ?? '');
                $('[name=kondisi_gizi]').val(d.kondisi_gizi ?? '');
                const lv = (d.level_nyeri ?? 0);
                $('#rngNyeri').val(lv);
                $('#nyeriVal').val(lv);
                $('#chkSusah').prop('checked', !!d.susah_jalan);
                $('#chkBantu').prop('checked', !!d.alat_bantu_jalan);
                $('#chkDuduk').prop('checked', !!d.menopang_duduk);
                $('[name=alergi_makanan]').val(d.alergi_makanan || '');
                $('[name=alergi_obat]').val(d.alergi_obat || '');
                const $link = $('#asesmenFileLink').empty();
                if (d.file_url) {
                    $link.html(`<a href="${d.file_url}" target="_blank">Lihat berkas asesmen saat ini</a>`);
                }
            }
            $.get("{{ route('rajal.asesmen.get') }}", {
                    rawat_pasien_id: RAWAT_ID,
                    pasien_id: PASIEN_ID
                })
                .done(res => prefillAsesmen(res?.data)).fail(() => {});

            $('#formAsesmen').on('submit', function(e) {
                e.preventDefault();
                const $f = $(this),
                    fd = new FormData(this),
                    $ok = $('#asesmenOk'),
                    $er = $('#asesmenErr');
                $.ajax({
                    url: "{{ route('rajal.asesmen.save') }}",
                    method: 'POST',
                    data: fd,
                    processData: false,
                    contentType: false,
                    headers: {
                        'X-CSRF-TOKEN': $('input[name=_token]', $f).val()
                    }
                }).done(function(res) {
                    $er.addClass('d-none').text('');
                    $ok.removeClass('d-none').text(res.message || 'Tersimpan.');
                    if (res.file_url) {
                        $('#asesmenFileLink').html(`<a href="${res.file_url}" target="_blank">Lihat berkas asesmen saat ini</a>`);
                    }
                }).fail(function(xhr) {
                    let msg = 'Terjadi kesalahan.';
                    if (xhr.status === 422) {
                        const errs = xhr.responseJSON?.errors || {};
                        msg = Object.values(errs).map(a => a.join(' ')).join(' ');
                    } else if (xhr.responseJSON?.message) {
                        msg = xhr.responseJSON.message;
                    }
                    $ok.addClass('d-none').text('');
                    $er.removeClass('d-none').text(msg);
                });
            });
            $('#rngNyeri').on('input', function() {
                $('#nyeriVal').val(this.value);
            });
            $('#nyeriVal').on('input', function() {
                $('#rngNyeri').val(this.value);
            });

            // ===== DIAGNOSA & TINDAKAN (fungsi & lazy init) =====
            function initIcd($el, mode) {
                return $el.select2({
                    placeholder: mode === 'code' ? 'Ketik kode ICD-10…' : 'Ketik nama ICD-10…',
                    allowClear: true,
                    minimumInputLength: 2,
                    ajax: {
                        url: "{{ route('rajal.icd.search') }}",
                        dataType: 'json',
                        delay: 250,
                        data: params => ({
                            q: params.term || '',
                            page: params.page || 1
                        }),
                        processResults: d => {
                            const results = (d.results || []).map(it => ({
                                id: it.id,
                                text: mode === 'code' ? it.id : it.name,
                                code: it.id,
                                name: it.name
                            }));
                            return {
                                results,
                                pagination: d.pagination || {
                                    more: false
                                }
                            };
                        }
                    },
                    templateResult: data => !data.id ? data.text : (mode === 'code' ? $(`<div><strong>${data.code}</strong></div>`) : $(`<div>${data.name}</div>`)),
                    templateSelection: data => mode === 'code' ? (data.code || data.id || '') : (data.name || data.text || ''),
                    escapeMarkup: m => m
                });
            }

            function initDxTabOnce() {
                if (LOADED.dxInit) return;
                LOADED.dxInit = true;

                // pasangan ICD utama
                const $uKode = initIcd($('#icd_utama_kode'), 'code');
                const $uNama = initIcd($('#icd_utama_nama'), 'name');
                let syncing = false;

                function syncPair($dst, hidCode, hidName, data, dstMode) {
                    if (syncing) return;
                    syncing = true;
                    hidCode.val(data.code || data.id || '');
                    hidName.val(data.name || data.text || '');
                    const optText = dstMode === 'code' ? (data.code || data.id || '') : (data.name || data.text || '');
                    const opt = new Option(optText, data.code, true, true);
                    opt.dataset.code = data.code;
                    opt.dataset.name = data.name || data.text;
                    $dst.find('option').remove();
                    $dst.append(opt).trigger('change.select2');
                    syncing = false;
                }
                $('#icd_utama_kode').on('select2:select', e => syncPair($('#icd_utama_nama'), $('#dx_kode'), $('#dx_nama'), e.params.data, 'name'))
                    .on('select2:clear', () => {
                        if (syncing) return;
                        $('#dx_kode,#dx_nama').val('');
                        $('#icd_utama_nama').val(null).trigger('change');
                    });
                $('#icd_utama_nama').on('select2:select', e => syncPair($('#icd_utama_kode'), $('#dx_kode'), $('#dx_nama'), e.params.data, 'code'))
                    .on('select2:clear', () => {
                        if (syncing) return;
                        $('#dx_kode,#dx_nama').val('');
                        $('#icd_utama_kode').val(null).trigger('change');
                    });

                // pasangan ICD penyerta
                const $pKode = initIcd($('#icd_penyerta_kode'), 'code');
                const $pNama = initIcd($('#icd_penyerta_nama'), 'name');
                $('#icd_penyerta_kode').on('select2:select', e => syncPair($('#icd_penyerta_nama'), $('#dx_kode_p'), $('#dx_nama_p'), e.params.data, 'name'))
                    .on('select2:clear', () => {
                        if (syncing) return;
                        $('#dx_kode_p,#dx_nama_p').val('');
                        $('#icd_penyerta_nama').val(null).trigger('change');
                    });
                $('#icd_penyerta_nama').on('select2:select', e => syncPair($('#icd_penyerta_kode'), $('#dx_kode_p'), $('#dx_nama_p'), e.params.data, 'code'))
                    .on('select2:clear', () => {
                        if (syncing) return;
                        $('#dx_kode_p,#dx_nama_p').val('');
                        $('#icd_penyerta_kode').val(null).trigger('change');
                    });

                // validasi minimal
                $('#frmDx').on('submit', function(e) {
                    if (!$('#dx_kode').val() || !$('#dx_nama').val()) {
                        e.preventDefault();
                        alert('Pilih diagnosis utama (kode & nama).');
                    }
                });

                // Select2 tindakan + submit
                window.$tindakSel = $('#tindakan_id').select2({
                    placeholder: 'Ketik nama/kode tindakan…',
                    minimumInputLength: 2,
                    ajax: {
                        url: "{{ route('rajal.tindakan.search') }}",
                        delay: 250,
                        dataType: 'json',
                        data: p => ({
                            q: p.term,
                            page: p.page || 1
                        }),
                        processResults: d => d
                    },
                    dropdownParent: $('#dx-tindakan')
                });
                $('#frmTindakan').on('submit', function(e) {
                    e.preventDefault();
                    const $btn = $(this).find('button[type=submit]').prop('disabled', true);
                    $.post("{{ route('rajal.tindakan.add') }}", $(this).serialize())
                        .done(() => {
                            loadTindakan();
                            showMsg($('#tindakanMsg'), 'Tindakan ditambah.');
                            toastShow('Tindakan ditambah.');
                            window.$tindakSel.val(null).trigger('change');
                        })
                        .fail(xhr => alert(xhr.responseJSON?.message || 'Gagal menambah tindakan'))
                        .always(() => $btn.prop('disabled', false));
                });

                // submit diagnosa
                $('#frmDx').on('submit', function(e) {
                    e.preventDefault();
                    const $btn = $(this).find('button[type=submit]').prop('disabled', true);
                    $.post("{{ route('rajal.diagnosis.add') }}", $(this).serialize())
                        .done(() => {
                            loadDx();
                            showMsg($('#dxMsg'), 'Diagnosa ditambah.');
                            toastShow('Diagnosa ditambah.');
                            this.reset();
                        })
                        .fail(xhr => alert(xhr.responseJSON?.message || 'Gagal menambah diagnosa'))
                        .always(() => $btn.prop('disabled', false));
                });
            }

            function loadDx() {
                $.get("{{ route('rajal.diagnosis.list') }}", {
                        uuid: RAWAT_UUID
                    })
                    .done(res => {
                        const $tb = $('#tblDx tbody').empty();
                        (res.data || []).forEach(row => {
                            $('<tr/>').append(`<td>${row.kode||'-'}</td>`)
                                .append(`<td>${row.diagnosis||'-'}</td>`)
                                .append(`<td>${row.kode_penyerta||'-'}</td>`)
                                .append(`<td>${row.diagnosis_penyerta||'-'}</td>`)
                                .append(`<td>${row.keterangan||''}</td>`).appendTo($tb);
                        });
                    });
            }

            function loadTindakan() {
                $.get("{{ route('rajal.tindakan.list') }}", {
                        uuid: RAWAT_UUID
                    })
                    .done(res => {
                        const $tb = $('#tblTindakan tbody').empty();
                        (res.data || []).forEach(r => {
                            $('<tr/>').append(`<td>${r.kode_internal||'-'}</td>`)
                                .append(`<td>${r.nama_tindakan||'-'}</td>`)
                                .append(`<td>${r.qty||1}</td>`).appendTo($tb);
                        });
                    });
            }

            // ===== E-RESEP (lazy-load) =====
            const $items = $('#items'),
                $tpl = $('#rowTpl');

            function appendRow(data = {}) {
                const $row = $($tpl.html());
                if (data.medicine_name) $row.find('.med-name').val(data.medicine_name);
                if (data.medicine_id) $row.find('.med-id').val(data.medicine_id);
                if (data.qty) $row.find('.qty').val(data.qty);
                if (data.dosis) $row.find('.dosis').val(data.dosis);
                if (data.aturan_pakai) $row.find('.aturan').val(data.aturan_pakai);
                $items.append($row);
                if (data.medicine_id) previewPrice($row);
            }
            async function loadResep() {
                $items.empty();
                const $spin = $('<div class="text-muted small mb-2" id="rxSpin">Memuat resep…</div>');
                $items.before($spin);
                try {
                    const res = await $.get("{{ route('rajal.eresep.list') }}", {
                        rawat_pasien_id: RAWAT_ID
                    });
                    const arr = res?.data || [];
                    const meta = res?.meta || {};
                    $('#rxStatus').text('Status: ' + (meta.status || '-'));

                    // render rows
                    if (arr.length) {
                        arr.forEach(it => appendRow({
                            medicine_id: it.medicine_id,
                            medicine_name: it.medicine_name,
                            qty: it.qty,
                            dosis: it.dosis || '',
                            aturan_pakai: it.aturan_pakai || ''
                        }));
                    } else {
                        appendRow();
                    }

                    // kunci UI jika tidak editable
                    toggleRxEditable(!!meta.editable);

                    // tombol submit enable kalau boleh
                    $('#btnSubmitRx').prop('disabled', !meta.can_submit).toggleClass('disabled', !meta.can_submit);

                } catch (e) {
                    appendRow();
                    toggleRxEditable(true);
                    $('#btnSubmitRx').prop('disabled', true);
                } finally {
                    $('#rxSpin').remove();
                }
            }

            function toggleRxEditable(editable) {
                // input & tombol row
                $('#frmResep .item-row :input').prop('disabled', !editable);
                // tombol form
                $('#addItem').prop('disabled', !editable);
                $('#btnSaveDraft').prop('disabled', !editable);
                // tombol hapus di tiap baris
                if (!editable) {
                    $('#frmResep .rm-row').addClass('disabled').prop('disabled', true);
                }
            }

            // tambah/hapus baris
            $('#addItem').on('click', () => appendRow());
            $items.on('click', '.rm-row', function() {
                $(this).closest('.item-row').remove();
            });

            $('#btnSubmitRx').on('click', function() {
                const $btn = $(this);

                Swal.fire({
                    title: 'Kirim ke Apoteker?',
                    text: 'Setelah dikirim, resep tidak bisa diedit oleh dokter.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, kirim',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => {
                        $btn.prop('disabled', true);
                        return $.post("{{ route('rajal.eresep.submit') }}", {
                                _token: $('meta[name="csrf-token"]').attr('content'),
                                rawat_pasien_id: RAWAT_ID
                            })
                            .then(res => res)
                            .catch(xhr => {
                                const msg = xhr?.responseJSON?.message || 'Gagal mengirim resep.';
                                Swal.showValidationMessage(msg);
                            })
                            .always(() => {
                                $btn.prop('disabled', false);
                            });
                    }
                }).then((result) => {
                    if (result.isConfirmed && result.value) {
                        Swal.fire({
                            icon: 'success',
                            title: 'Terkirim',
                            text: result.value.message || 'Resep dikirim ke Apoteker.'
                        }).then(() => {
                            loadResep();
                        });
                    }
                });
            });

            // autocomplete obat
            let acTimer = null;
            $items.on('input', '.med-name', function() {
                const $txt = $(this),
                    val = $txt.val();
                if (acTimer) clearTimeout(acTimer);
                if ((val || '').length < 3) return;
                acTimer = setTimeout(async () => {
                    const res = await fetch("{{ route('api.medicines') }}?q=" + encodeURIComponent(val));
                    const list = await res.json();
                    const menu = $('<div class="ac-menu"></div>');
                    list.forEach(it => {
                        $('<div></div>').text(it.name).appendTo(menu).on('click', () => {
                            $txt.val(it.name).siblings('.med-id').val(it.id);
                            previewPrice($txt.closest('.item-row'));
                            menu.remove();
                        });
                    });
                    $txt.siblings('.ac-menu').remove();
                    $txt.after(menu);
                    setTimeout(() => $(document).one('click', () => menu.remove()), 10);
                }, 300);
            });

            function previewPrice($row) {
                const mid = $row.find('.med-id').val();
                if (!mid) return;
                const exam = $('#examAt').val();
                fetch("{{ route('api.medicine.priceAt', ['id' => '__ID__']) }}".replace('__ID__', mid) + '?date=' + encodeURIComponent(exam))
                    .then(r => r.ok ? r.json() : null)
                    .then(js => {
                        if (js && js.unit_price) {
                            $row.find('.med-price').text('Harga berlaku: Rp ' + new Intl.NumberFormat('id-ID').format(js.unit_price));
                        } else {
                            $row.find('.med-price').text('Harga berlaku: —');
                        }
                    }).catch(() => $row.find('.med-price').text('Harga berlaku: —'));
            }
            $items.on('change', '.med-id', function() {
                previewPrice($(this).closest('.item-row'));
            });

            $('#frmResep').on('submit', function(e) {
                e.preventDefault();
                const payload = [];
                $items.find('.item-row').each(function() {
                    const id = $(this).find('.med-id').val();
                    const nam = $(this).find('.med-name').val();
                    const qty = parseInt($(this).find('.qty').val() || '0', 10);
                    const dos = ($(this).find('.dosis').val() || '').trim();
                    const atr = ($(this).find('.aturan').val() || '').trim();
                    if (id && qty > 0) payload.push({
                        medicine_id: id,
                        medicine_name: nam,
                        qty: qty,
                        dosis: dos,
                        aturan_pakai: atr
                    });
                });
                if (payload.length === 0) {
                    alert('Tambahkan minimal 1 obat.');
                    return;
                }

                const $btn = $(this).find('[type=submit]').prop('disabled', true);
                $.ajax({
                        url: "{{ route('rajal.eresep.save') }}",
                        method: 'POST',
                        data: {
                            _token: $('meta[name="csrf-token"]').attr('content'),
                            rawat_pasien_id: RAWAT_ID,
                            items: JSON.stringify(payload)
                        }
                    }).done(() => {
                        showMsg($('#resepMsg'), 'Resep disimpan (DRAFT).');
                        toastShow('Resep disimpan (DRAFT).');
                        if (LOADED.resep) loadResep(); // refresh dari server
                    }).fail(xhr => alert(xhr.responseJSON?.message || 'Gagal menyimpan resep'))
                    .always(() => $btn.prop('disabled', false));
            });

            // ===== LAZY-LOAD PER TAB =====
            $('button[data-bs-toggle="tab"]').on('shown.bs.tab', function(e) {
                const target = $(e.target).attr('data-bs-target');
                if (target === '#dx-tindakan') {
                    initDxTabOnce();
                    if (!LOADED.dx) {
                        LOADED.dx = true;
                        loadDx();
                    }
                    if (!LOADED.tindakan) {
                        LOADED.tindakan = true;
                        loadTindakan();
                    }
                }
                if (target === '#eresep') {
                    if (!LOADED.resep) {
                        LOADED.resep = true;
                        loadResep();
                    }
                }
            });
        });
    </script>
@endpush

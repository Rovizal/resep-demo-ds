@extends('layouts.base')

@push('title')
    Rajal
@endpush

@section('content')
    <div class="row">
        <div class="col-md-12 col-lg-12 col-xl-12 col-xxl-3 mb-4">
            <div class="card">
                <div class="card-header">
                    <div class="d-flex justify-content-start">
                        <button data-bs-toggle="modal" data-bs-target="#modalAddRajal" class="btn btn-primary btn-sm">
                            Daftar Pasien
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped" id="tblRajal">
                            <thead>
                                <tr>
                                    <th>No RM</th>
                                    <th>Nama</th>
                                    <th>Jenis Kelamin</th>
                                    <th>Tanggal Lahir</th>
                                    <th>Tanggal Daftar</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL --}}
    <div class="modal fade" id="modalAddRajal" tabindex="-1" aria-labelledby="modalAddRajalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <form id="formAddRajal" class="modal-content">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title" id="modalAddRajalLabel">Daftar Rawat Jalan</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Pilih Pasien</label>
                        <select id="pasien_id" name="pasien_id" class="form-select" style="width:100%"></select>
                        <div class="form-text">Hanya menampilkan pasien yang belum punya kunjungan aktif.</div>
                    </div>

                    {{-- Kartu info minimalis pasien (muncul setelah dipilih) --}}
                    <div id="miniPasien" class="card border shadow-sm d-none">
                        <div class="card-body py-2">
                            <div class="fw-semibold" id="mp_nama">-</div>
                            <div class="small text-muted" id="mp_no_rm">-</div>
                            <div class="small" id="mp_meta">-</div>
                            <div class="small" id="mp_kontak">-</div>
                            <div class="small text-truncate" id="mp_alamat" title=""></div>
                        </div>
                    </div>

                    <div class="alert alert-danger d-none mt-3" id="errBox"></div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Daftarkan</button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#tblRajal').DataTable({
                processing: true,
                serverSide: true,
                ajax: {
                    url: "{{ route('rajal.datatable') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'no_rm',
                        name: 'no_rm'
                    },
                    {
                        data: 'nama',
                        name: 'nama'
                    },
                    {
                        data: 'jk',
                        name: 'jk',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'dob',
                        name: 'dob'
                    },
                    {
                        data: 'tgl_daftar',
                        name: 'tgl_daftar'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false
                    }
                ],
                order: [
                    [4, 'desc']
                ],
                pageLength: 10
            });
        });

        $(function() {
            const $modal = $('#modalAddRajal');
            const $mini = $('#miniPasien');

            function renderMini(d) {
                if (!d) {
                    $mini.addClass('d-none');
                    return;
                }
                $('#mp_nama').text(d.nama || d.text || '-');
                $('#mp_no_rm').text(d.no_rm ? `No. RM: ${d.no_rm}` : '');
                const jk = d.jk || '-';
                const dob = d.tanggal_lahir || '-';
                const daftar = d.tanggal_daftar || '-';
                $('#mp_meta').text(`JK: ${jk} • Lahir: ${dob} • Daftar: ${daftar}`);
                $('#mp_kontak').text(d.nohp ? `Kontak: ${d.nohp}` : '');
                $('#mp_alamat').text(d.alamat || '').attr('title', d.alamat || '');
                $mini.removeClass('d-none');
            }

            const $select = $('#pasien_id').select2({
                dropdownParent: $modal,
                placeholder: 'Cari pasien (nama / RM / NIK / No HP)…',
                minimumInputLength: 2,
                allowClear: true,
                ajax: {
                    url: "{{ route('rajal.pasien.search') }}",
                    dataType: 'json',
                    delay: 250,
                    data: params => ({
                        term: params.term || '',
                        page: params.page || 1
                    }),
                    processResults: data => data
                }
            });

            $select.on('select2:select', function(e) {
                const d = e.params.data;
                renderMini(d);
            });

            $select.on('select2:clear', function() {
                renderMini(null);
            });

            // Submit AJAX
            $('#formAddRajal').on('submit', function(e) {
                e.preventDefault();
                const $btn = $(this).find('button[type=submit]').prop('disabled', true);
                $('#errBox').addClass('d-none').empty();

                $.ajax({
                    url: "{{ route('rajal.daftar') }}",
                    method: 'POST',
                    data: $(this).serialize(),
                    headers: {
                        'X-CSRF-TOKEN': $('input[name=_token]', this).val()
                    },
                    success: function(res) {
                        $modal.modal('hide');
                        window.location.href = "{{ route('rajal.detail') }}" + "?uuid=" + encodeURIComponent(res.data.uuid);
                    },
                    error: function(xhr) {
                        let msg = 'Terjadi kesalahan.';
                        if (xhr.status === 409 && xhr.responseJSON?.message) {
                            msg = xhr.responseJSON.message;
                        } else if (xhr.status === 422) {
                            const errs = xhr.responseJSON?.errors || {};
                            msg = Object.values(errs).map(a => a.join(' ')).join('<br>');
                        }
                        $('#errBox').removeClass('d-none').html(msg);
                    },
                    complete: function() {
                        $btn.prop('disabled', false);
                    }
                });
            });

            // Reset saat modal dibuka
            $modal.on('shown.bs.modal', function() {
                $('#errBox').addClass('d-none').empty();
                $select.val(null).trigger('change');
                renderMini(null);
            });
        });
    </script>
@endpush

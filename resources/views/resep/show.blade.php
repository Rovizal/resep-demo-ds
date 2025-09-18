@extends('layouts.base')

@push('title')
    Detail E-Resep
@endpush

@section('content')
    <div class="row">
        <div class="col-lg-12 col-md-12">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-2">
                        <a href="{{ route('resep.index') }}" class="btn btn-sm btn-outline-secondary">
                            <i class="fa fa-arrow-left"></i> Kembali
                        </a>
                        <h5 class="mb-0">Detail Resep</h5>
                    </div>
                </div>

                <div class="card-body">
                    <dl class="row mb-0">
                        <dt class="col-sm-3">No. RM</dt>
                        <dd class="col-sm-9">{{ $erx->rawat?->pasien?->no_rm ?? '-' }}</dd>

                        <dt class="col-sm-3">Pasien</dt>
                        <dd class="col-sm-9">{{ $erx->rawat?->pasien?->nama ?? '-' }}</dd>

                        <dt class="col-sm-3">Dokter</dt>
                        <dd class="col-sm-9">{{ $erx->dokter?->name ?? '-' }}</dd>

                        <dt class="col-sm-3">Status</dt>
                        <dd class="col-sm-9">
                            <span class="badge bg-{{ $erx->status === 'SERVED' ? 'success' : ($erx->status === 'SUBMITTED' ? 'warning' : 'secondary') }}">
                                {{ $erx->status }}
                            </span>
                        </dd>

                        <dt class="col-sm-3">Total</dt>
                        <dd class="col-sm-9">Rp {{ number_format((int) $erx->total_amount, 0, ',', '.') }}</dd>
                    </dl>

                    <hr>

                    <div class="table-responsive">
                        <table class="table table-sm table-bordered">
                            <thead>
                                <tr>
                                    <th style="width:40px;">#</th>
                                    <th>Nama Obat</th>
                                    <th class="text-center" style="width:80px;">Qty</th>
                                    <th>Dosis</th>
                                    <th>Aturan Pakai</th>
                                    <th class="text-end" style="width:120px;">Harga Satuan</th>
                                    <th class="text-end" style="width:120px;">Subtotal</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($erx->items->sortBy('urutan') as $i => $it)
                                    <tr>
                                        <td class="text-center">{{ $i + 1 }}</td>
                                        <td>{{ $it->medicine_name ?? '-' }}</td>
                                        <td class="text-center">{{ (int) $it->qty }}</td>
                                        <td>{{ $it->dosis ?? '-' }}</td>
                                        <td>{{ $it->aturan_pakai ?? '-' }}</td>
                                        <td class="text-end">Rp {{ number_format((int) $it->harga_satuan, 0, ',', '.') }}</td>
                                        <td class="text-end">Rp {{ number_format((int) $it->subtotal, 0, ',', '.') }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    <div class="d-flex gap-2 mt-3">
                        <button id="btn-serve" class="btn btn-success" @if ($erx->status === 'SERVED') disabled @endif>
                            <i class="fa fa-check"></i> Selesaikan
                        </button>
                        <a href="{{ route('resep.print', $erx->uuid) }}" target="_blank" class="btn btn-secondary">
                            <i class="fa fa-print"></i> Cetak
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#btn-serve').on('click', function() {
                const $btn = $(this);

                Swal.fire({
                    title: 'Selesaikan resep?',
                    text: 'Tandai resep ini sebagai SUDAH DILAYANI (SERVED).',
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonText: 'Ya, selesaikan',
                    cancelButtonText: 'Batal',
                    reverseButtons: true,
                    showLoaderOnConfirm: true,
                    allowOutsideClick: () => !Swal.isLoading(),
                    preConfirm: () => {
                        $btn.prop('disabled', true);
                        return $.post("{{ route('resep.serve', $erx->uuid) }}", {
                                _token: '{{ csrf_token() }}'
                            })
                            .then(res => res)
                            .catch(xhr => {
                                const msg = xhr?.responseJSON?.message || 'Gagal menyelesaikan resep.';
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
                            title: 'Berhasil',
                            text: result.value.message || 'Resep ditandai SERVED.'
                        }).then(() => {
                            location.reload();
                        });
                    }
                });
            });
        });
    </script>
@endpush

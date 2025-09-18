@extends('layouts.base')

@push('title')
    E-Resep
@endpush

@section('content')
    <div class="row">
        <div class="col-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="mb-0">Daftar E-Resep (Masuk)</h5>
                </div>
                <div class="card-body">
                    <table id="rx-table" class="table table-striped table-bordered w-100">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>No. RM</th>
                                <th>Pasien</th>
                                <th>Dokter</th>
                                <th>Total</th>
                                <th>Status</th>
                                <th style="width:110px;">Aksi</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            const table = $('#rx-table').DataTable({
                processing: true,
                serverSide: true,
                ajax: '{{ route('resep.datatable') }}',
                order: [
                    [0, 'desc']
                ],
                columns: [{
                        data: 'tanggal',
                        name: 'created_at',
                        searchable: false
                    },
                    {
                        data: 'no_rm',
                        name: 'rawat.pasien.no_rm'
                    },
                    {
                        data: 'pasien',
                        name: 'rawat.pasien.nama'
                    },
                    {
                        data: 'dokter',
                        name: 'dokter.name'
                    },
                    {
                        data: 'total',
                        name: 'total_amount',
                        searchable: false,
                        className: 'text-end'
                    },
                    {
                        data: 'status',
                        name: 'status',
                        className: 'text-center'
                    },
                    {
                        data: 'action',
                        name: 'action',
                        orderable: false,
                        searchable: false,
                        className: 'text-center'
                    },
                ]
            });
        });
    </script>
@endpush

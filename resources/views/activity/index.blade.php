@extends('layouts.base')

@push('title')
    Activity Logs
@endpush

@section('content')
    <div class="row">
        <div class="col-12 mb-4">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">Activity Logs</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table id="tblActivity" class="table table-sm table-striped table-bordered w-100">
                            <thead>
                                <tr>
                                    <th>Waktu</th>
                                    <th>User</th>
                                    <th>Aksi</th>
                                    <th>Subject</th>
                                    <th>Changes</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        $(function() {
            $('#tblActivity').DataTable({
                processing: true,
                serverSide: true,
                searching: true,
                lengthChange: true,
                order: [
                    [0, 'desc']
                ],
                ajax: {
                    url: "{{ route('activity.datatable') }}",
                    type: 'GET'
                },
                columns: [{
                        data: 'when',
                        name: 'created_at'
                    },
                    {
                        data: 'user_name',
                        name: 'user.name',
                        defaultContent: '—'
                    },
                    {
                        data: 'action',
                        name: 'action'
                    },
                    {
                        data: 'subject',
                        name: 'subject_type',
                        orderable: false,
                        searchable: false
                    },
                    {
                        data: 'changes_text',
                        name: 'changes',
                        orderable: false,
                        searchable: false
                    }
                ],
                pageLength: 10,
                language: {
                    processing: "Memuat...",
                    search: "Cari:",
                    lengthMenu: "Tampil _MENU_",
                    infoEmpty: "No data",
                    zeroRecords: "Tidak ada data",
                }
            });
        });
    </script>
@endpush

@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .pagination {
        display: flex;
        justify-content: flex-end;
        padding-left: 0;
        list-style: none;
        margin-top: 1rem;
    }
    .dataTables_wrapper .dataTables_paginate .page-item {
        margin: 0 2px;
    }
    .dataTables_wrapper .dataTables_paginate .page-link {
        border-radius: 4px;
        padding: 6px 12px;
        border: 1px solid #dee2e6;
        color: #3b7ddd;
        text-decoration: none;
        background-color: #fff;
    }
    .dataTables_wrapper .dataTables_paginate .page-item.active .page-link {
        background-color: #3b7ddd;
        border-color: #3b7ddd;
        color: #fff;
    }
    .dataTables_wrapper .dataTables_paginate .page-item.disabled .page-link {
        color: #6c757d;
        pointer-events: none;
        background-color: #fff;
        border-color: #dee2e6;
    }
</style>
@endpush

@section('content')
<h1 class="h3 mb-3"><strong>Shukrani</strong> Management</h1>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Orodha ya Shukrani</h5>
                <div class="float-end mt-n4">
                    <a href="{{ route('shukranis.create') }}" class="btn btn-primary">Ongeza Shukrani</a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('shukranis.index') }}" method="GET" class="row mb-3">
                    <div class="col-md-4">
                        <select name="jumuiya_id" class="form-select" onchange="this.form.submit()">
                            <option value="">-- Chujio kwa Jumuiya --</option>
                            @foreach($jumuiyas as $j)
                                <option value="{{ $j->id }}" {{ request('jumuiya_id') == $j->id ? 'selected' : '' }}>
                                    {{ $j->jina_la_jumuiya }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(request()->filled('jumuiya_id'))
                    <div class="col-md-2">
                        <a href="{{ route('shukranis.index') }}" class="btn btn-secondary">Ondoa Chujio</a>
                    </div>
                    @endif
                </form>
                <table id="shukraniTable" class="table table-hover my-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mtoto</th>
                            <th>Jumuiya</th>
                            <th>Kiasi</th>
                            <th>Mode</th>
                            <th>Hali</th>
                            <th>Tarehe</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($shukranis as $row)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $row->mtoto?->jina_la_mtoto }}</td>
                                <td>{{ $row->mtoto?->jumuiya?->jina_la_jumuiya }}</td>
                                <td>{{ number_format($row->kiasi) }}</td>
                                <td>{{ $row->mode_ya_malipo ?? '-' }}</td>
                                <td>{{ $row->hali_ya_malipo ?? '-' }}</td>
                                <td data-sort="{{ optional($row->paid_at)?->timestamp }}">{{ optional($row->paid_at)?->format('Y-m-d') }}</td>
                                <td>
                                    <a href="{{ route('shukranis.edit', $row->id) }}" class="btn btn-sm btn-info">Hariri</a>
                                    <form action="{{ route('shukranis.destroy', $row->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('are you sure to delete this data?')">Futa</button>
                                    </form>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#shukraniTable').DataTable({
            order: [[6, 'desc']], // sort by Tarehe
            pageLength: 50,
            lengthMenu: [[10, 20, 50, 100, -1], [10, 20, 50, 100, 'All']],
            language: {
                search: 'Tafuta:',
                lengthMenu: 'Onyesha _MENU_ rekodi',
                info: 'Inaonyesha _START_ hadi _END_ ya _TOTAL_ rekodi',
                paginate: {
                    first: 'Kwanza',
                    last: 'Mwisho',
                    next: 'Ijayo',
                    previous: 'Iliyopita'
                },
                zeroRecords: 'Hakuna rekodi zilizopatikana',
                infoEmpty: 'Hakuna rekodi',
                infoFiltered: '(imuchujwa kutoka jumla ya rekodi _MAX_)'
            },
            columnDefs: [
                { orderable: false, targets: [0, 7] }
            ]
        });
    });
</script>
@endpush

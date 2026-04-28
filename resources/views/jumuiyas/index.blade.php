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
<h1 class="h3 mb-3"><strong>Jumuiya</strong> Management</h1>

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
                <h5 class="card-title mb-0">Orodha ya Jumuiya</h5>
                <a href="{{ route('jumuiyas.create') }}" class="btn btn-primary float-end mt-n4">Ongeza Jumuiya</a>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('jumuiyas.index') }}" id="kandaFilterForm" class="d-flex align-items-center gap-2">
                            <label for="kanda_id" class="mb-0">Chuja kwa Kanda:</label>
                            <select name="kanda_id" id="kanda_id" class="form-select" style="max-width: 260px;">
                                <option value="">Zote</option>
                                @foreach($kandas as $kanda)
                                    <option value="{{ $kanda->id }}" {{ request('kanda_id') == $kanda->id ? 'selected' : '' }}>
                                        {{ $kanda->jina_la_kanda }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-secondary">Chuja</button>
                            @if(request()->filled('kanda_id'))
                                <a href="{{ route('jumuiyas.index') }}" class="btn btn-outline-secondary">Ondoa</a>
                            @endif
                        </form>
                    </div>
                </div>
                <table id="jumuiyaTable" class="table table-hover my-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jina la Jumuiya</th>
                            <th>Kanda</th>
                            <th>Wanajumuiya Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($jumuiyas as $jumuiya)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $jumuiya->jina_la_jumuiya }}</td>
                                <td>{{ $jumuiya->kanda->jina_la_kanda }}</td>
                                <td><span class="badge bg-primary">{{ $jumuiya->wanajumuiya_count }}</span></td>
                                <td>
                                    <a href="{{ route('jumuiyas.edit', $jumuiya->id) }}" class="btn btn-sm btn-info">Hariri</a>
                                    <a href="{{ route('mwanajumuiya.create', ['jumuiya_id' => $jumuiya->id]) }}" class="btn btn-sm btn-primary">Ongeza Mwanajumuiya</a>
                                    <form action="{{ route('jumuiyas.destroy', $jumuiya->id) }}" method="POST" class="d-inline">
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
        $('#jumuiyaTable').DataTable({
            order: [[1, 'asc']],
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
                { orderable: false, targets: [0, 4] }
            ]
        });
        $('#kanda_id').on('change', function() {
            $('#kandaFilterForm').submit();
        });
    });
</script>
@endpush

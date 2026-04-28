@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
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
        color: white;
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
<h1 class="h3 mb-3"><strong>Mwanajumuiya</strong> Management</h1>

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
                <h5 class="card-title mb-0">Orodha ya Wanajumuiya</h5>
                <div class="float-end mt-n4">
                    <a href="{{ route('mwanajumuiya.import.form') }}" class="btn btn-outline-primary">Import Excel</a>
                    <a href="{{ route('mwanajumuiya.sample') }}" class="btn btn-outline-secondary">Pakua Template</a>
                    <a href="{{ route('mwanajumuiya.export') }}" class="btn btn-outline-success">Pakua Orodha</a>
                    <a href="{{ route('mwanajumuiya.create') }}" class="btn btn-primary">Ongeza Mwanajumuiya</a>
                </div>
            </div>
            <div class="card-body">
                <div class="row mb-3">
                    <div class="col-md-8">
                        <form method="GET" action="{{ route('mwanajumuiya.index') }}" id="jumuiyaFilterForm" class="d-flex align-items-center gap-2">
                            <label for="jumuiya_id" class="mb-0">Chuja kwa Jumuiya:</label>
                            <select name="jumuiya_id" id="jumuiya_id" class="selectpicker" data-live-search="true" data-width="260px">
                                <option value="">Zote</option>
                                @foreach($jumuiyas as $jumuiya)
                                    <option value="{{ $jumuiya->id }}" {{ request('jumuiya_id') == $jumuiya->id ? 'selected' : '' }}>
                                        {{ $jumuiya->jina_la_jumuiya }}
                                    </option>
                                @endforeach
                            </select>
                            <button type="submit" class="btn btn-outline-secondary">Chuja</button>
                            @if(request()->filled('jumuiya_id'))
                                <a href="{{ route('mwanajumuiya.index') }}" class="btn btn-outline-secondary">Ondoa</a>
                            @endif
                        </form>
                    </div>
                </div>
                <table id="mwanajumuiyaTable" class="table table-hover my-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jina la Mwanajumuiya</th>
                            <th>Kadi Namba</th>
                            <th>Namba ya Simu</th>
                            <th>Jumuiya</th>
                            <th>Zaka Count</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($wanajumuiya as $mwana)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $mwana->jina_la_mwanajumuiya }}</td>
                                <td>{{ $mwana->kadi_namba }}</td>
                                <td>{{ $mwana->namba_ya_simu }}</td>
                                <td>{{ $mwana->jumuiya->jina_la_jumuiya }}</td>
                                <td><a href="{{ route('zakas.index', ['mwanajumuiya_id' => $mwana->id]) }}"><span class="badge bg-primary">{{ $mwana->zakas_count }}</span></a></td>
                                <td>
                                    <a href="{{ route('mwanajumuiya.edit', $mwana->id) }}" class="btn btn-sm btn-info">Hariri</a>
                                    <a href="{{ route('zakas.index', ['mwanajumuiya_id' => $mwana->id]) }}" class="btn btn-sm btn-success">Angalia Zaka</a>
                                    <a href="{{ route('zakas.create', ['mwanajumuiya_id' => $mwana->id]) }}" class="btn btn-sm btn-primary">Ongeza Zaka</a>
                                    @if(auth()->user()->role === 'admin')
                                        <form action="{{ route('mwanajumuiya.destroy', $mwana->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('are you sure to delete this data?')">Futa</button>
                                        </form>
                                    @endif
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
    $(document).ready(function() {
        $('#mwanajumuiyaTable').DataTable({
            "order": [[ 0, "asc" ]], // Sort by # (index 0) ascending by default
            "pageLength": 50,
            "lengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],
            "language": {
                "search": "Tafuta:",
                "lengthMenu": "Onyesha _MENU_ rekodi",
                "info": "Inaonyesha _START_ hadi _END_ ya _TOTAL_ rekodi",
                "paginate": {
                    "first": "Kwanza",
                    "last": "Mwisho",
                    "next": "Ijayo",
                    "previous": "Iliyopita"
                },
                "zeroRecords": "Hakuna rekodi zilizopatikana",
                "infoEmpty": "Hakuna rekodi",
                "infoFiltered": "(imuchujwa kutoka jumla ya rekodi _MAX_)"
            }
        });
        $('.selectpicker').selectpicker();
        $('#jumuiya_id').on('changed.bs.select', function() {
            $('#jumuiyaFilterForm').submit();
        });
    });
</script>
@endpush

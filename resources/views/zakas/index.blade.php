@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
<style>
    /* Custom styles to fix pagination if Bootstrap 5 integration is partial */
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
<h1 class="h3 mb-3"><strong>Zaka</strong> Management</h1>

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
                <h5 class="card-title mb-0">Orodha ya Zaka</h5>
                <div class="float-end mt-n4">
                    <a href="{{ route('zakas.import.form') }}" class="btn btn-outline-primary">Import Excel</a>
                    <a href="{{ route('zakas.sample') }}" class="btn btn-outline-secondary">Pakua Template</a>
                    <a href="{{ route('zakas.create') }}" class="btn btn-primary">Rekodi Zaka</a>
                </div>
            </div>
            <div class="card-body">
                <form id="zakaFilterForm" method="GET" action="{{ route('zakas.index') }}" class="row g-3 mb-3">
                    <div class="col-md-4">
                        <select name="jumuiya_id" id="jumuiya_id" class="selectpicker" data-live-search="true" data-width="100%">
                            <option value="">Filter kwa Jumuiya (Zote)</option>
                            @foreach($jumuiyas as $jumuiya)
                                <option value="{{ $jumuiya->id }}" {{ request('jumuiya_id') == $jumuiya->id ? 'selected' : '' }}>
                                    {{ $jumuiya->jina_la_jumuiya }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    @if(request('jumuiya_id') || request('mwanajumuiya_id'))
                        <div class="col-md-4 d-flex align-items-center">
                            @if(request('mwanajumuiya_id'))
                                @php
                                    $filteredMwanajumuiya = \App\Models\Mwanajumuiya::find(request('mwanajumuiya_id'));
                                @endphp
                                <span class="me-2 text-muted">Zaka za: <strong>{{ $filteredMwanajumuiya->jina_la_mwanajumuiya ?? 'Mwanajumuiya' }}</strong></span>
                            @endif
                            <a href="{{ route('zakas.index') }}" class="btn btn-secondary">Ondoa Filter</a>
                        </div>
                    @endif
                </form>

                <table id="zakaTable" class="table table-hover my-0 w-100">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Mwanajumuiya</th>
                            <th>Jumuiya</th>
                            <th>Kiasi</th>
                            <th>Risiti Namba</th>
                            <th>Mode ya Malipo</th>
                            <th>Hali ya Malipo</th>
                            <th>SMS</th>
                            <th>Muda wa Malipo</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($zakas as $zaka)
                            <tr>
                                <td>{{ $loop->iteration }}</td>
                                <td>{{ $zaka->mwanajumuiya->jina_la_mwanajumuiya }}</td>
                                <td>{{ $zaka->mwanajumuiya->jumuiya->jina_la_jumuiya }}</td>
                                <td>{{ number_format($zaka->kiasi, 2) }}</td>
                                <td>{{ $zaka->risiti_namba }}</td>
                                <td>{{ $zaka->mode_ya_malipo }}</td>
                                <td>{{ $zaka->hali_ya_malipo ?? '-' }}</td>
                                <td>
                                    @if($zaka->sms_sent)
                                        <span class="badge bg-success">Imetumwa</span>
                                    @else
                                        <div class="d-flex align-items-center">
                                            <span class="badge bg-secondary me-1">Bado</span>
                                            <form action="{{ route('zakas.resend-sms', $zaka->id) }}" method="POST" class="d-inline">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-link p-0 text-primary" title="Tuma tena SMS">
                                                    <i class="align-middle" data-feather="send"></i> Tuma Tena
                                                </button>
                                            </form>
                                        </div>
                                    @endif
                                </td>
                                <td data-sort="{{ optional($zaka->paid_at)->timestamp }}">{{ optional($zaka->paid_at)->format('Y-m-d H:i') }}</td>
                                <td>
                                    <a href="{{ route('zakas.edit', $zaka->id) }}" class="btn btn-sm btn-info">Hariri</a>
                                    @if(auth()->user()->role === 'admin')
                                        <form action="{{ route('zakas.destroy', $zaka->id) }}" method="POST" class="d-inline">
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
        $('.selectpicker').selectpicker();
        $('#jumuiya_id').on('changed.bs.select', function() {
            $('#zakaFilterForm').submit();
        });
        $('#zakaTable').DataTable({
            "order": [[ 8, "desc" ]], // Sort by Muda wa Malipo (index 8) descending by default
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
    });
</script>
@endpush

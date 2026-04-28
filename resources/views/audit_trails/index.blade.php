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
<div class="container-fluid p-0">
    <h1 class="h3 mb-3">Audit Logs</h1>

    <div class="card mb-3">
        <div class="card-header">
            <h5 class="card-title mb-0">Filter by Date</h5>
        </div>
        <div class="card-body">
            <form method="GET" action="{{ route('audit_trails.index') }}" class="row g-3">
                <div class="col-md-4">
                    <label for="start_date" class="form-label">Start Date</label>
                    <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate ?? '' }}">
                </div>
                <div class="col-md-4">
                    <label for="end_date" class="form-label">End Date</label>
                    <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate ?? '' }}">
                </div>
                <div class="col-md-4 d-flex align-items-end gap-2">
                    <button type="submit" class="btn btn-primary w-100">Filter</button>
                    @if(request()->filled('start_date') || request()->filled('end_date'))
                        <a href="{{ route('audit_trails.index') }}" class="btn btn-outline-secondary w-100">Clear</a>
                    @endif
                </div>
            </form>
        </div>
    </div>

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table id="auditTable" class="table table-sm table-striped table-hover w-100">
                    <thead>
                        <tr>
                            <th>User</th>
                            <th>Action</th>
                            <th>Model</th>
                            <th>ID</th>
                            <th>IP Address</th>
                            <th>Time</th>
                            <th>Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($logs as $log)
                        <tr>
                            <td>{{ $log->user ? $log->user->name : 'System/Unknown' }}</td>
                            <td>{{ $log->action }}</td>
                            <td>{{ class_basename($log->auditable_type) }}</td>
                            <td>{{ $log->auditable_id }}</td>
                            <td>{{ $log->ip_address }}</td>
                            <td data-sort="{{ $log->created_at->timestamp }}">{{ $log->created_at->format('Y-m-d H:i:s') }}</td>
                            <td>
                                <button type="button" class="btn btn-sm btn-info" data-bs-toggle="modal" data-bs-target="#logModal{{ $log->id }}">
                                    View
                                </button>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modals outside the table -->
@foreach($logs as $log)
<div class="modal fade" id="logModal{{ $log->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Log Details #{{ $log->id }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p><strong>User Agent:</strong> {{ $log->user_agent }}</p>
                <p><strong>Route:</strong> {{ $log->route }}</p>
                <p><strong>Method:</strong> {{ $log->method }}</p>
                <hr>
                <h6>Changes:</h6>
                <pre>{{ json_encode($log->changes, JSON_PRETTY_PRINT) }}</pre>
            </div>
        </div>
    </div>
</div>
@endforeach
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(document).ready(function() {
        $('#auditTable').DataTable({
            "order": [[ 5, "desc" ]], // Sort by Time column (index 5) descending
            "pageLength": 20,
            "lengthMenu": [[10, 20, 50, 100, -1], [10, 20, 50, 100, "All"]],
            "language": {
                "search": "Search logs:",
                "lengthMenu": "Show _MENU_ entries",
                "info": "Showing _START_ to _END_ of _TOTAL_ entries",
                "paginate": {
                    "first": "First",
                    "last": "Last",
                    "next": "Next",
                    "previous": "Previous"
                }
            }
        });
    });
</script>
@endpush

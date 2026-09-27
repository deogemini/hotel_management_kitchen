@extends('layouts.admin')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>.dataTables_wrapper .dataTables_paginate .pagination{justify-content:flex-end;margin-top:1rem}.dataTables_wrapper .dataTables_paginate .page-item{margin:0 2px}.dataTables_wrapper .dataTables_length,.dataTables_wrapper .dataTables_filter{margin-bottom:1rem}</style>
@endpush
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Purchase Management</h1><a href="{{ route('purchases.create') }}" class="btn btn-primary">Record Purchase</a></div>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 align-items-end mb-4"><div class="col-md-3"><label class="form-label">From Date</label><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div><div class="col-md-3"><label class="form-label">To Date</label><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div><div class="col-auto"><button class="btn btn-primary">Filter</button> <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Clear</a></div></form>
<div class="table-responsive"><table class="table table-hover"><thead><tr><th>Date</th><th>Timeline</th><th>Item</th><th>Quantity</th><th>Unit Cost</th><th>Total Cost</th><th>Supplier</th><th>Actions</th></tr></thead><tbody>
@forelse($purchases as $purchase)<tr><td>{{ $purchase->purchased_at->format('d M Y') }}</td><td>{{ $purchase->created_at?->format('Y-m-d H:i') }}</td><td>{{ $purchase->menuItem->name }}</td><td>{{ $purchase->quantity }}</td><td>{{ number_format($purchase->unit_cost, 2) }}</td><td>{{ number_format($purchase->total_cost, 2) }}</td><td>{{ $purchase->supplier ?: '-' }}</td><td>@if(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner')<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete this purchase? Stock will be reduced.');">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>@endif</td></tr>@empty<tr><td colspan="8" class="text-muted">No purchases recorded.</td></tr>@endforelse
@if($purchases->isNotEmpty())<tr class="fw-bold table-light"><td colspan="5" class="text-end">Total Purchase Cost:</td><td>{{ number_format($totalCost, 2) }}</td><td colspan="2"></td></tr>@endif
</tbody></table></div></div></div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('.table.table-hover').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        order: [[0, 'desc'], [1, 'desc']],
        columnDefs: [{ orderable: false, targets: [7] }],
        language: { search: 'Search purchases:', lengthMenu: 'Show _MENU_ entries', info: 'Showing _START_ to _END_ of _TOTAL_ purchases' }
    });
});
</script>
@endpush

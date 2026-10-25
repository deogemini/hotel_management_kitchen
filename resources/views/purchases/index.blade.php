@extends('layouts.admin')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    #purchasesTable_wrapper .dataTables_length,
    #purchasesTable_wrapper .dataTables_filter {
        margin-bottom: 0;
    }

    #purchasesTable_wrapper .dataTables_filter {
        text-align: left;
    }

    #purchasesTable_wrapper .dataTables_filter input {
        margin-left: .5rem;
    }

    #purchasesTable_wrapper .dataTables_info {
        padding-top: 0;
    }

    #purchasesTable_wrapper .dataTables_paginate {
        display: flex;
        justify-content: flex-end;
    }

    /* Keep pagination readable even when the DataTables CDN stylesheet is unavailable. */
    #purchasesTable_wrapper .dataTables_paginate ul.pagination {
        display: flex !important;
        flex-direction: row;
        flex-wrap: wrap;
        align-items: center;
        justify-content: flex-end;
        gap: .25rem;
        list-style: none;
        margin: 0;
        padding: 0;
    }

    #purchasesTable_wrapper .dataTables_paginate li.page-item {
        display: block;
        margin: 0;
    }

    #purchasesTable_wrapper .dataTables_paginate .page-link {
        display: block;
        padding: .375rem .75rem;
        white-space: nowrap;
    }

    #purchasesTable th,
    #purchasesTable td {
        vertical-align: middle;
    }

    #purchasesTable td:last-child {
        white-space: nowrap;
    }

    #purchasesTable td:last-child form {
        display: inline-block;
        margin: 0;
    }

    #purchasesTable th:nth-child(1),
    #purchasesTable td:nth-child(1),
    #purchasesTable th:nth-child(2),
    #purchasesTable td:nth-child(2),
    #purchasesTable th:nth-child(4),
    #purchasesTable td:nth-child(4),
    #purchasesTable th:nth-child(5),
    #purchasesTable td:nth-child(5),
    #purchasesTable th:nth-child(6),
    #purchasesTable td:nth-child(6) {
        white-space: nowrap;
    }

    #purchasesTable th:nth-child(4),
    #purchasesTable td:nth-child(4),
    #purchasesTable th:nth-child(5),
    #purchasesTable td:nth-child(5),
    #purchasesTable th:nth-child(6),
    #purchasesTable td:nth-child(6) {
        text-align: right;
    }

    @media (max-width: 767.98px) {
        #purchasesTable_wrapper .dataTables_paginate {
            justify-content: flex-start;
        }
    }
</style>
@endpush
@if(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner')
@push('scripts')
<script>
document.querySelectorAll('form[action*="/purchases/"]').forEach(function (form) {
    const id = form.action.split('/').pop();
    const link = document.createElement('a');
    link.href = '{{ url('purchases') }}/' + id + '/edit';
    link.className = 'btn btn-sm btn-info me-1';
    link.textContent = 'Edit';
    form.parentElement.insertBefore(link, form);
});
</script>
@endpush
@endif
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Purchase Management</h1><a href="{{ route('purchases.create') }}" class="btn btn-primary">Record Purchase</a></div>
<div class="card"><div class="card-body">
<form method="GET" class="row g-2 align-items-end mb-4"><div class="col-md-3"><label class="form-label">From Date</label><input type="date" name="from_date" class="form-control" value="{{ request('from_date') }}"></div><div class="col-md-3"><label class="form-label">To Date</label><input type="date" name="to_date" class="form-control" value="{{ request('to_date') }}"></div><div class="col-auto"><button class="btn btn-primary">Filter</button> <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Clear</a></div><div class="col-auto ms-auto"><a target="_blank" href="{{ route('purchases.export.pdf', request()->only('from_date','to_date')) }}" class="btn btn-outline-danger">Print / PDF</a> <a href="{{ route('purchases.export.excel', request()->only('from_date','to_date')) }}" class="btn btn-outline-success">Excel</a></div></form>
<table id="purchasesTable" class="table table-hover w-100"><thead><tr><th>Date</th><th>Timeline</th><th>Item</th><th>Quantity</th><th>Unit Cost</th><th>Total Cost</th><th>Supplier</th><th>Last action by</th><th>Actions</th></tr></thead><tbody>
@foreach($purchases as $purchase)<tr><td>{{ $purchase->purchased_at->format('d M Y') }}</td><td>{{ $purchase->created_at?->format('Y-m-d H:i') }}</td><td>{{ $purchase->menuItem->name }}</td><td>{{ $purchase->quantity }}</td><td>{{ number_format($purchase->unit_cost, 2) }}</td><td>{{ number_format($purchase->total_cost, 2) }}</td><td>{{ $purchase->supplier ?: '-' }}</td>@include('partials.performed_by_cell', ['record' => $purchase])<td>@if(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner')<form method="POST" action="{{ route('purchases.destroy', $purchase) }}" onsubmit="return confirm('Delete this purchase? Stock will be reduced.');">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>@endif</td></tr>@endforeach
@if($purchases->isNotEmpty())
</tbody><tfoot><tr class="fw-bold table-light"><td></td><td></td><td></td><td></td><td class="text-end">Total Purchase Cost:</td><td>{{ number_format($totalCost, 2) }}</td><td></td><td></td><td></td></tr></tfoot>
@else
</tbody>
@endif</table></div></div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
$(function () {
    $('#purchasesTable').DataTable({
        pageLength: 10,
        lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
        order: [[0, 'desc'], [1, 'desc']],
        columnDefs: [{ orderable: false, targets: [7, 8] }],
        dom: '<"row g-3 align-items-center mb-3"<"col-12 col-md-6"l><"col-12 col-md-6"f>>' +
            '<"table-responsive"rt>' +
            '<"row g-3 align-items-center mt-3"<"col-12 col-md-6"i><"col-12 col-md-6"p>>',
        language: {
            search: 'Search purchases:',
            lengthMenu: 'Show _MENU_ entries',
            info: 'Showing _START_ to _END_ of _TOTAL_ purchases',
            emptyTable: 'No purchases recorded.',
            zeroRecords: 'No matching purchases found.'
        }
    });
});
</script>
@endpush

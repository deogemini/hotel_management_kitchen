@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .pagination {
        justify-content: flex-end;
        margin-top: 1rem;
    }
    .dataTables_wrapper .dataTables_paginate .page-item {
        margin: 0 2px;
    }
    .dataTables_wrapper .dataTables_paginate .page-link {
        border-radius: 4px;
        padding: 6px 12px;
    }
</style>
@endpush

@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><strong>Stock</strong> Management{{ !empty($isDrinksPage) ? ' — Drinks' : '' }}</h1>
    @if(empty($isDrinksPage))
        <a href="{{ route('stocks.drinks') }}" class="btn btn-primary">Drinks Stock</a>
    @else
        <a href="{{ route('stocks.index') }}" class="btn btn-outline-secondary">All Stock</a>
    @endif
</div>
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">{{ !empty($isDrinksPage) ? 'Drinks Stock' : 'Restaurant Item Stock' }}</h5></div>
    <div class="card-body">
        <div class="table-responsive">
        <table id="stockTable" class="table table-hover w-100">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Item</th>
                    <th>Category</th>
                    <th>Current Stock</th>
                    <th>Low Alert</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                @forelse($menuItems as $item)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $item->name }}</td>
                        <td>{{ $item->category }}</td>
                        <td>{{ $item->stock_quantity }}</td>
                        <td>{{ $item->low_stock_quantity }}</td>
                        <td>
                            @if($item->stock_quantity <= 0)
                                <span class="badge bg-danger">Out of stock</span>
                            @elseif($item->stock_quantity <= $item->low_stock_quantity)
                                <span class="badge bg-warning">Low stock</span>
                            @else
                                <span class="badge bg-success">In stock</span>
                            @endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="text-muted">No menu items found.</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        $('#stockTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            order: [[1, 'asc']],
            columnDefs: [{ orderable: false, targets: [5, 6] }],
            language: {
                search: 'Search stock:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ items',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            }
        });
    });
</script>
@endpush

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
<h1 class="h3 mb-3"><strong>Stock</strong> Management</h1>
<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Restaurant Item Stock</h5></div>
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
                    <th>Update</th>
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
                        <td>
                            <form method="POST" action="{{ route('stocks.update', $item) }}" class="row g-2 align-items-center">
                                @csrf
                                @method('PATCH')
                                <div class="col-auto"><input type="number" min="0" name="stock_quantity" class="form-control form-control-sm" value="{{ $item->stock_quantity }}" style="width: 110px;"></div>
                                <div class="col-auto"><input type="number" min="0" name="low_stock_quantity" class="form-control form-control-sm" value="{{ $item->low_stock_quantity }}" style="width: 110px;"></div>
                                <div class="col-auto"><button class="btn btn-sm btn-primary">Save</button></div>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="text-muted">No menu items found.</td></tr>
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

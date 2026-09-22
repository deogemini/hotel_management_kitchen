@extends('layouts.admin')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>.dataTables_wrapper .dataTables_paginate .pagination{justify-content:flex-end;margin-top:1rem}.dataTables_wrapper .dataTables_paginate .page-item{margin:0 2px}</style>
@endpush
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0"><strong>Menu</strong> Items</h1><a class="btn btn-primary" href="{{ route('menu-items.create') }}">Add Item</a></div>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Food and Drinks</h5></div><div class="card-body">
<form method="GET" class="row g-2 mb-3 align-items-end"><div class="col-md-4"><label class="form-label">Filter by Category</label><select name="category" class="form-select"><option value="">All Categories</option><option value="Food" @selected(request('category') === 'Food')>Food</option><option value="Drinks" @selected(request('category') === 'Drinks')>Drinks</option></select></div><div class="col-auto"><button class="btn btn-primary">Filter</button> <a href="{{ route('menu-items.index') }}" class="btn btn-outline-secondary">Clear</a></div></form>
<div class="table-responsive"><table id="menuItemsTable" class="table table-hover w-100"><thead><tr><th>#</th><th>Name</th><th>Category</th><th>Buying Price</th><th>Selling Price</th><th>Stock</th><th>Available</th><th>Actions</th></tr></thead><tbody>
@forelse($menuItems as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->name }}</td><td>{{ $item->category }}</td><td>{{ number_format($item->buying_price, 2) }}</td><td>{{ number_format($item->selling_price ?? $item->price, 2) }}</td><td>{{ $item->stock_quantity }}</td><td>{{ $item->is_available ? 'Yes' : 'No' }}</td><td><a class="btn btn-sm btn-info" href="{{ route('menu-items.edit', $item) }}">Edit</a></td></tr>@empty<tr><td></td><td>No menu items found.</td><td></td><td></td><td></td><td></td><td></td><td></td></tr>@endforelse
</tbody></table></div></div></div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script><script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script><script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>$(function(){ $('#menuItemsTable').DataTable({pageLength:10,lengthMenu:[[10,25,50,-1],[10,25,50,'All']],order:[[1,'asc']],columnDefs:[{orderable:false,targets:[7]}],language:{search:'Search items:',lengthMenu:'Show _MENU_ entries',info:'Showing _START_ to _END_ of _TOTAL_ items',paginate:{first:'First',last:'Last',next:'Next',previous:'Previous'}}}); });</script>
@endpush

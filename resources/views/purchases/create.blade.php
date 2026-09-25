@extends('layouts.admin')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush
@section('content')
<h1 class="h3 mb-3">Purchase Management</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('purchases.store') }}">@csrf
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label" for="menu_item_id">Existing Stock Item</label><select id="menu_item_id" name="menu_item_id" class="selectpicker" data-live-search="true" data-width="100%" data-size="10" title="Search or select an item" required>@foreach($menuItems as $item)<option value="{{ $item->id }}" data-buying-price="{{ $item->buying_price }}" @selected(old('menu_item_id') == $item->id)>{{ $item->name }} — Current stock: {{ $item->stock_quantity }}</option>@endforeach</select></div>
        <div class="col-md-3 mb-3"><label class="form-label">Quantity Purchased</label><input type="number" name="quantity" min="1" class="form-control" required></div>
        <div class="col-md-3 mb-3"><label class="form-label" for="unit_cost">Unit Cost</label><input id="unit_cost" type="number" name="unit_cost" min="0" step="0.01" class="form-control" value="{{ old('unit_cost') }}" required></div>
        <div class="col-md-4 mb-3"><label class="form-label" for="supplier">Supplier</label><select id="supplier" name="supplier" class="selectpicker" data-live-search="true" data-width="100%" title="Search or select a supplier"><option value="">No supplier selected</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->name }}" @selected(old('supplier') === $supplier->name)>{{ $supplier->name }}{{ $supplier->phone ? ' — '.$supplier->phone : '' }}</option>@endforeach</select>@if(auth()->user()?->hasRole('hotel_manager')) @if($suppliers->isEmpty())<small class="text-muted d-block mt-1">No suppliers added yet. <a href="{{ route('suppliers.create') }}">Add a supplier</a>.</small>@else<a href="{{ route('suppliers.index') }}" class="small">Manage suppliers</a>@endif @endif</div>
        <div class="col-md-4 mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchased_at" value="{{ now()->format('Y-m-d') }}" class="form-control" required></div>
        <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
    </div>
    <button class="btn btn-primary">Record Purchase &amp; Update Stock</button>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Purchase History</a>
</form>
</div></div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
    $(function () {
        $('.selectpicker').selectpicker();
    });
    document.getElementById('menu_item_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('unit_cost').value = selected.dataset.buyingPrice ?? '';
    });
</script>
@endpush

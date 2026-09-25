@extends('layouts.admin')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush
@section('content')
<h1 class="h3 mb-3">Purchase Management</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('purchases.store') }}">@csrf
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label" for="menu_item_id">Existing Stock Item</label><select id="menu_item_id" name="menu_item_id" class="selectpicker" data-live-search="true" data-width="100%" data-size="10" title="Search or select an item" required>@foreach($menuItems as $item)<option value="{{ $item->id }}" data-buying-price="{{ $item->buying_price }}" data-stock="{{ $item->stock_quantity }}" @selected(old('menu_item_id') == $item->id)>{{ $item->name }} — Current stock: {{ $item->stock_quantity }}</option>@endforeach</select></div>
        <div class="col-md-3 mb-3"><label class="form-label">Quantity Purchased</label><input type="number" name="quantity" min="0" class="form-control" value="{{ old('quantity') }}" required></div>
        <div class="col-md-3 mb-3"><label class="form-label" for="unit_cost">Unit Cost</label><input id="unit_cost" type="number" name="unit_cost" min="0" step="0.01" class="form-control" value="{{ old('unit_cost') }}" required><label class="form-label mt-2" for="total_cost_preview">Total Cost</label><input id="total_cost_preview" type="text" class="form-control" value="0.00" readonly></div>
        <div class="col-md-4 mb-3"><label class="form-label" for="supplier">Supplier</label><select id="supplier" name="supplier" class="selectpicker" data-live-search="true" data-width="100%" title="Search or select a supplier"><option value="">No supplier selected</option>@foreach($suppliers as $supplier)<option value="{{ $supplier->name }}" @selected(old('supplier') === $supplier->name)>{{ $supplier->name }}{{ $supplier->phone ? ' — '.$supplier->phone : '' }}</option>@endforeach</select>@if(auth()->user()?->hasRole('hotel_manager')) @if($suppliers->isEmpty())<small class="text-muted d-block mt-1">No suppliers added yet. <a href="{{ route('suppliers.create') }}">Add a supplier</a>.</small>@else<a href="{{ route('suppliers.index') }}" class="small">Manage suppliers</a>@endif @endif</div>
        <div class="col-md-4 mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchased_at" value="{{ now()->format('Y-m-d') }}" class="form-control" required></div>
        <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
        <div class="col-md-12 mb-3"><div class="form-check"><input type="hidden" name="affects_stock" value="0"><input type="checkbox" name="affects_stock" value="1" id="affects_stock" class="form-check-input" checked><label class="form-check-label" for="affects_stock">Add quantity to current stock</label></div><small class="text-muted">Uncheck this when the item is already included in stock.</small></div>
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
    function updateTotal() {
        const quantity = Number(document.querySelector('[name="quantity"]').value || 0);
        const unitCost = Number(document.getElementById('unit_cost').value || 0);
        document.getElementById('total_cost_preview').value = (quantity * unitCost).toFixed(2);
    }
    function updateQuantityForStockMode() {
        const selected = document.getElementById('menu_item_id').options[document.getElementById('menu_item_id').selectedIndex];
        if (!document.getElementById('affects_stock').checked && selected) {
            document.querySelector('[name="quantity"]').value = selected.dataset.stock || 0;
        }
        updateTotal();
    }
    document.getElementById('menu_item_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('unit_cost').value = selected.dataset.buyingPrice ?? '';
        updateQuantityForStockMode();
    });
    document.getElementById('affects_stock').addEventListener('change', updateQuantityForStockMode);
    document.querySelector('[name="quantity"]').addEventListener('input', updateTotal);
    document.getElementById('unit_cost').addEventListener('input', updateTotal);
    updateTotal();
</script>
@endpush

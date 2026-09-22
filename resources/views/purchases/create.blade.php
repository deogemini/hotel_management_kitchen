@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Purchase Management</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('purchases.store') }}">@csrf
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label" for="menu_item_id">Existing Stock Item</label><select id="menu_item_id" name="menu_item_id" class="form-select" required><option value="">Select item</option>@foreach($menuItems as $item)<option value="{{ $item->id }}" data-buying-price="{{ $item->buying_price }}">{{ $item->name }} — Current stock: {{ $item->stock_quantity }}</option>@endforeach</select></div>
        <div class="col-md-3 mb-3"><label class="form-label">Quantity Purchased</label><input type="number" name="quantity" min="1" class="form-control" required></div>
        <div class="col-md-3 mb-3"><label class="form-label" for="unit_cost">Unit Cost</label><input id="unit_cost" type="number" name="unit_cost" min="0" step="0.01" class="form-control" value="{{ old('unit_cost') }}" required></div>
        <div class="col-md-4 mb-3"><label class="form-label">Supplier</label><input name="supplier" class="form-control"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchased_at" value="{{ now()->format('Y-m-d') }}" class="form-control" required></div>
        <div class="col-md-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control" rows="2"></textarea></div>
    </div>
    <button class="btn btn-primary">Record Purchase &amp; Update Stock</button>
    <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Purchase History</a>
</form>
</div></div>
@endsection
@push('scripts')
<script>
    document.getElementById('menu_item_id').addEventListener('change', function () {
        const selected = this.options[this.selectedIndex];
        document.getElementById('unit_cost').value = selected.dataset.buyingPrice ?? '';
    });
</script>
@endpush

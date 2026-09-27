@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Edit Purchase</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('purchases.update', $purchase) }}">@csrf @method('PUT')
<p><strong>Item:</strong> {{ $purchase->menuItem?->name }}</p>
<div class="row"><div class="col-md-3 mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" min="0" class="form-control" value="{{ old('quantity', $purchase->quantity) }}" required></div><div class="col-md-3 mb-3"><label class="form-label">Unit Cost</label><input type="number" name="unit_cost" min="0" step="0.01" class="form-control" value="{{ old('unit_cost', $purchase->unit_cost) }}" required></div><div class="col-md-3 mb-3"><label class="form-label">Supplier</label><input name="supplier" class="form-control" value="{{ old('supplier', $purchase->supplier) }}"></div><div class="col-md-3 mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchased_at" class="form-control" value="{{ old('purchased_at', $purchase->purchased_at?->format('Y-m-d')) }}" required></div></div>
<div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control">{{ old('notes', $purchase->notes) }}</textarea></div><div class="form-check mb-3"><input type="hidden" name="affects_stock" value="0"><input type="checkbox" name="affects_stock" value="1" class="form-check-input" id="affects_stock" @checked(old('affects_stock', $purchase->affects_stock))><label for="affects_stock" class="form-check-label">Add quantity to current stock</label></div>
<button class="btn btn-primary">Update Purchase</button> <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Cancel</a></form></div></div>
@endsection

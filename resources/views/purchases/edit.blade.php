@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Edit Purchase</h1>
<div class="card"><div class="card-body">
@if($errors->any())<div class="alert alert-danger"><ul class="mb-0">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
<form method="POST" action="{{ route('purchases.update', $purchase) }}">@csrf @method('PUT')
<p><strong>Item:</strong> {{ $purchase->menuItem?->name }}</p>
<div class="alert alert-info py-2">
    <strong>Current stock:</strong> {{ number_format((int) ($purchase->menuItem?->stock_quantity ?? 0)) }}.
    When checked, this edit applies only the quantity difference from the existing purchase, so saving the same quantity will not add those units a second time.
    Uncheck it to set current stock to the quantity entered above.
</div>
<div class="row"><div class="col-md-3 mb-3"><label class="form-label">Quantity</label><input type="number" name="quantity" min="0" class="form-control" value="{{ old('quantity', $purchase->quantity) }}" required></div><div class="col-md-3 mb-3"><label class="form-label">Unit Cost</label><input type="number" name="unit_cost" min="0" step="0.01" class="form-control" value="{{ old('unit_cost', $purchase->unit_cost) }}" required></div><div class="col-md-3 mb-3"><label class="form-label">Supplier</label><input name="supplier" class="form-control" value="{{ old('supplier', $purchase->supplier) }}"></div><div class="col-md-3 mb-3"><label class="form-label">Purchase Date</label><input type="date" name="purchased_at" class="form-control" value="{{ old('purchased_at', $purchase->purchased_at?->format('Y-m-d')) }}" required></div></div>
<div class="mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control">{{ old('notes', $purchase->notes) }}</textarea></div><div class="form-check"><input type="hidden" name="affects_stock" value="0"><input type="checkbox" name="affects_stock" value="1" class="form-check-input" id="affects_stock" @checked(old('affects_stock', $purchase->affects_stock))><label for="affects_stock" class="form-check-label">Apply purchase quantity to stock</label></div><small class="text-muted d-block mb-3">When unchecked, the item’s current stock will be set to the quantity entered above.</small>
<button class="btn btn-primary">Update Purchase</button> <a href="{{ route('purchases.index') }}" class="btn btn-outline-secondary">Cancel</a></form></div></div>
@endsection

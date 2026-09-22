@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Purchase Management</h1><a href="{{ route('purchases.create') }}" class="btn btn-primary">Record Purchase</a></div>
<div class="card"><div class="card-body"><table class="table table-hover"><thead><tr><th>Date</th><th>Item</th><th>Quantity</th><th>Unit Cost</th><th>Total Cost</th><th>Supplier</th></tr></thead><tbody>
@forelse($purchases as $purchase)<tr><td>{{ $purchase->purchased_at->format('d M Y') }}</td><td>{{ $purchase->menuItem->name }}</td><td>{{ $purchase->quantity }}</td><td>{{ number_format($purchase->unit_cost, 2) }}</td><td>{{ number_format($purchase->total_cost, 2) }}</td><td>{{ $purchase->supplier ?: '—' }}</td></tr>@empty<tr><td colspan="6" class="text-muted">No purchases recorded.</td></tr>@endforelse
</tbody></table></div></div>
@endsection

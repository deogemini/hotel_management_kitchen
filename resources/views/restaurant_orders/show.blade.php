@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">{{ $restaurantOrder->order_number }}</h1>
<div class="card"><div class="card-body">
<p><strong>Customer:</strong> {{ $restaurantOrder->guest?->full_name ?? $restaurantOrder->walk_in_customer_name ?? 'Walk-in' }}</p>
<p><strong>Status:</strong> {{ $restaurantOrder->status }}</p>
<table class="table"><thead><tr><th>Item</th><th>Qty</th><th>Price</th><th>Total</th></tr></thead><tbody>@foreach($restaurantOrder->items as $item)<tr><td>{{ $item->menuItem->name }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td>{{ number_format($item->total_price, 2) }}</td></tr>@endforeach</tbody></table>
<p><strong>Total:</strong> {{ number_format($restaurantOrder->subtotal, 2) }} | <strong>Balance:</strong> {{ number_format($restaurantOrder->balance_amount, 2) }}</p>
@if($restaurantOrder->balance_amount > 0 && $restaurantOrder->payment_method !== 'Room charge')<a href="{{ route('payments.create', ['target_type' => 'restaurant_order', 'target_id' => $restaurantOrder->id]) }}" class="btn btn-primary">Receive Payment</a>@endif
</div></div>
@endsection

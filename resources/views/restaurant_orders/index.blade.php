@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Restaurant</strong> Orders</h1>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Orders</h5><a href="{{ route('restaurant-orders.create') }}" class="btn btn-primary float-end mt-n4">New Order</a></div><div class="card-body">
<table class="table table-hover"><thead><tr><th>#</th><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th>Total</th><th>Balance</th><th>Actions</th></tr></thead><tbody>
@foreach($restaurantOrders as $order)<tr><td>{{ $loop->iteration }}</td><td>{{ $order->order_number }}</td><td>{{ $order->guest?->full_name ?? $order->walk_in_customer_name ?? 'Walk-in' }}</td><td>{{ $order->status }}</td><td>{{ $order->payment_status }}</td><td>{{ number_format($order->subtotal, 2) }}</td><td>{{ number_format($order->balance_amount, 2) }}</td><td><a href="{{ route('restaurant-orders.show', $order) }}" class="btn btn-sm btn-secondary">View</a></td></tr>@endforeach
</tbody></table></div></div>
@endsection

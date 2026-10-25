@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Restaurant</strong> Orders</h1>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Orders</h5><a href="{{ route('restaurant-orders.create') }}" class="btn btn-primary float-end mt-n4">New Order</a></div><div class="card-body">
<form method="GET" action="{{ route('restaurant-orders.index') }}" class="row g-2 align-items-end mb-3">
    @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
    <div class="col-md-6"><label for="order-search" class="form-label">Search orders or items</label><input id="order-search" type="search" name="search" value="{{ $search }}" class="form-control" placeholder="Order number, customer, or item name"></div>
    <div class="col-auto"><button type="submit" class="btn btn-primary">Search</button></div>
    @if($search !== '')<div class="col-auto"><a href="{{ route('restaurant-orders.index', request()->only('status')) }}" class="btn btn-outline-secondary">Clear</a></div>@endif
</form>
@php
    $ordersTotal = $restaurantOrders->sum('subtotal');
    $ordersBalance = $restaurantOrders->sum('balance_amount');
@endphp
<table class="table table-hover"><thead><tr><th>#</th><th>Order</th><th>Customer</th><th>Status</th><th>Payment</th><th>Total</th><th>Balance</th><th>Order date</th><th>Last action by</th><th>Actions</th></tr></thead><tbody>
@foreach($restaurantOrders as $order)<tr><td>{{ $loop->iteration }}</td><td>{{ $order->order_number }}</td><td>{{ $order->guest?->full_name ?? $order->walk_in_customer_name ?? 'Walk-in' }}</td><td>{{ $order->status }}</td><td>{{ $order->payment_status }}</td><td>{{ number_format($order->subtotal, 2) }}</td><td>{{ number_format($order->balance_amount, 2) }}</td><td>{{ $order->order_date?->format('Y-m-d') }}</td>@include('partials.performed_by_cell', ['record' => $order])<td><a href="{{ route('restaurant-orders.show', $order) }}" class="btn btn-sm btn-secondary">View</a> @if(auth()->user()?->hasRole('hotel_manager'))<a href="{{ route('restaurant-orders.edit', $order) }}" class="btn btn-sm btn-info">Edit Items</a>@endif @if(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner')<form method="POST" action="{{ route('restaurant-orders.destroy', $order) }}" class="d-inline" onsubmit="return confirm('Delete this order and its payment records? Sold stock will be restored.');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>@endif</td></tr>@endforeach
</tbody><tfoot><tr class="table-light fw-bold"><th colspan="5" class="text-end">TOTAL</th><td>{{ number_format($ordersTotal, 2) }}</td><td>{{ number_format($ordersBalance, 2) }}</td><td colspan="3"></td></tr></tfoot></table></div></div>
@if(auth()->user()?->hasPermission('payments.manage'))
<script>
document.querySelectorAll('table tbody tr').forEach(function (row) {
    const balance = Number((row.cells[6]?.textContent || '0').replace(/,/g, ''));
    const view = row.querySelector('a[href*="/restaurant-orders/"]');
    if (balance > 0 && view) {
        const payment = document.createElement('a');
        payment.href = '{{ url('payments/create') }}?target_type=restaurant_order&target_id=' + view.href.split('/').pop();
        payment.className = 'btn btn-sm btn-primary ms-1';
        payment.textContent = 'Process Payment';
        view.parentElement.insertBefore(payment, view.nextSibling);
    }
});
</script>
@endif
@endsection

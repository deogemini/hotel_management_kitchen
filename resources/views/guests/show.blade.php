@extends('layouts.admin')
@section('content')
@php
    $bookingTotal = $guest->bookings->sum('room_total');
    $bookingBalance = $guest->bookings->sum('balance_amount');
    $foodTotal = $guest->restaurantOrders->sum('subtotal');
    $foodBalance = $guest->restaurantOrders->sum('balance_amount');
    $serviceTotal = $guest->otherCharges->sum('amount');
    $serviceBalance = $guest->otherCharges->sum('balance_amount');
    $paidTotal = $guest->payments->whereIn('status', ['Paid', 'Partial'])->sum('amount');
@endphp
<h1 class="h3 mb-3">{{ $guest->full_name }}</h1>
<div class="row">
    <div class="col-lg-4">
        <div class="card"><div class="card-body">
            <p><strong>Phone:</strong> {{ $guest->phone_number }}</p>
            <p><strong>Email:</strong> {{ $guest->email }}</p>
            <p><strong>ID:</strong> {{ $guest->id_type }} {{ $guest->id_number }}</p>
            <p><strong>Nationality:</strong> {{ $guest->nationality }}</p>
            <div class="d-flex flex-wrap gap-2">
                <a href="{{ route('bookings.create', ['guest_id' => $guest->id]) }}" class="btn btn-primary">Create Booking</a>
                <a href="{{ route('restaurant-orders.create', ['guest_id' => $guest->id]) }}" class="btn btn-secondary">Food / Drinks Order</a>
                <a href="{{ route('service-charges.create', ['guest_id' => $guest->id]) }}" class="btn btn-info">Add Service</a>
            </div>
        </div></div>
    </div>
    <div class="col-lg-8">
        <div class="row">
            <div class="col-md-3 mb-3"><div class="card"><div class="card-body"><h6>Total Bills</h6><h4>{{ number_format($bookingTotal + $foodTotal + $serviceTotal, 2) }}</h4></div></div></div>
            <div class="col-md-3 mb-3"><div class="card"><div class="card-body"><h6>Paid</h6><h4>{{ number_format($paidTotal, 2) }}</h4></div></div></div>
            <div class="col-md-3 mb-3"><div class="card"><div class="card-body"><h6>Balance</h6><h4>{{ number_format($bookingBalance + $foodBalance + $serviceBalance, 2) }}</h4></div></div></div>
            <div class="col-md-3 mb-3"><div class="card"><div class="card-body"><h6>Services</h6><h4>{{ number_format($serviceTotal, 2) }}</h4></div></div></div>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Booking History</h5></div>
    <div class="card-body">
        <table class="table"><thead><tr><th>Booking</th><th>Room</th><th>Dates</th><th>Status</th><th>Total</th><th>Balance</th><th>Action</th></tr></thead><tbody>
        @forelse($guest->bookings as $booking)
            <tr><td>{{ $booking->booking_number }}</td><td>{{ $booking->room?->room_number }}</td><td>{{ $booking->check_in_date?->format('Y-m-d') }} to {{ $booking->check_out_date?->format('Y-m-d') }}</td><td>{{ $booking->status }}</td><td>{{ number_format($booking->room_total, 2) }}</td><td>{{ number_format($booking->balance_amount, 2) }}</td><td><a class="btn btn-sm btn-secondary" href="{{ route('bookings.invoice', $booking) }}">Print Invoice</a></td></tr>
        @empty
            <tr><td colspan="7" class="text-muted">No bookings found.</td></tr>
        @endforelse
        </tbody></table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Food and Drinks Bills</h5></div>
    <div class="card-body">
        <table class="table"><thead><tr><th>Order</th><th>Items</th><th>Status</th><th>Total</th><th>Balance</th><th>Action</th></tr></thead><tbody>
        @forelse($guest->restaurantOrders as $order)
            <tr><td>{{ $order->order_number }}</td><td>@foreach($order->items as $item)<div>{{ $item->quantity }} x {{ $item->menuItem?->name }}</div>@endforeach</td><td>{{ $order->payment_status }}</td><td>{{ number_format($order->subtotal, 2) }}</td><td>{{ number_format($order->balance_amount, 2) }}</td><td><a class="btn btn-sm btn-secondary" href="{{ route('restaurant-orders.show', $order) }}">View</a></td></tr>
        @empty
            <tr><td colspan="6" class="text-muted">No food or drinks orders found.</td></tr>
        @endforelse
        </tbody></table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Laundry, Ironing, and Other Services</h5></div>
    <div class="card-body">
        <table class="table"><thead><tr><th>Service</th><th>Booking</th><th>Description</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead><tbody>
        @forelse($guest->otherCharges as $charge)
            <tr><td>{{ $charge->service_type }}</td><td>{{ $charge->booking?->booking_number }}</td><td>{{ $charge->description }}</td><td>{{ number_format($charge->amount, 2) }}</td><td>{{ number_format($charge->paid_amount, 2) }}</td><td>{{ number_format($charge->balance_amount, 2) }}</td><td>{{ $charge->payment_status }}</td><td><a class="btn btn-sm btn-secondary" href="{{ route('service-charges.show', $charge) }}">View</a></td></tr>
        @empty
            <tr><td colspan="8" class="text-muted">No guest services found.</td></tr>
        @endforelse
        </tbody></table>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Payments</h5></div>
    <div class="card-body">
        <table class="table"><thead><tr><th>Receipt</th><th>For</th><th>Method</th><th>Amount</th><th>Date</th></tr></thead><tbody>
        @forelse($guest->payments as $payment)
            <tr><td>{{ $payment->payment_number }}</td><td>{{ $payment->purposeLabel() }}</td><td>{{ $payment->payment_method }}</td><td>{{ number_format($payment->amount, 2) }}</td><td>{{ $payment->paid_at?->format('Y-m-d H:i') }}</td></tr>
        @empty
            <tr><td colspan="5" class="text-muted">No payments recorded.</td></tr>
        @endforelse
        </tbody></table>
    </div>
</div>
@endsection

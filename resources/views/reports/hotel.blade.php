@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">{{ $title }}</h1>
<div class="card"><div class="card-body"><table class="table table-hover"><thead><tr>
@if($type === 'payments')<th>Receipt</th><th>Guest</th><th>Method</th><th>Amount</th><th>Date</th>@endif
@if($type === 'bookings')<th>Booking</th><th>Guest</th><th>Room</th><th>Status</th><th>Total</th><th>Balance</th>@endif
@if($type === 'rooms')<th>Room</th><th>Type</th><th>Status</th><th>Price</th>@endif
@if($type === 'guests')<th>Name</th><th>Phone</th><th>Email</th><th>Bookings</th>@endif
@if($type === 'orders')<th>Order</th><th>Customer</th><th>Status</th><th>Total</th><th>Payment</th>@endif
</tr></thead><tbody>
@foreach($rows as $row)<tr>
@if($type === 'payments')<td>{{ $row->payment_number }}</td><td>{{ $row->guest?->full_name }}</td><td>{{ $row->payment_method }}</td><td>{{ number_format($row->amount, 2) }}</td><td>{{ $row->paid_at?->format('Y-m-d') }}</td>@endif
@if($type === 'bookings')<td>{{ $row->booking_number }}</td><td>{{ $row->guest?->full_name }}</td><td>{{ $row->room?->room_number }}</td><td>{{ $row->status }}</td><td>{{ number_format($row->room_total, 2) }}</td><td>{{ number_format($row->balance_amount, 2) }}</td>@endif
@if($type === 'rooms')<td>{{ $row->room_number }}</td><td>{{ $row->room_type }}</td><td>{{ $row->status }}</td><td>{{ number_format($row->price_per_night, 2) }}</td>@endif
@if($type === 'guests')<td>{{ $row->full_name }}</td><td>{{ $row->phone_number }}</td><td>{{ $row->email }}</td><td>{{ $row->bookings_count }}</td>@endif
@if($type === 'orders')<td>{{ $row->order_number }}</td><td>{{ $row->guest?->full_name ?? $row->walk_in_customer_name }}</td><td>{{ $row->status }}</td><td>{{ number_format($row->subtotal, 2) }}</td><td>{{ $row->payment_status }}</td>@endif
</tr>@endforeach
</tbody></table><button onclick="window.print()" class="btn btn-secondary">Print</button></div></div>
@endsection

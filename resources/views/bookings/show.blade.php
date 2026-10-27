@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">{{ $booking->booking_number }}</h1>
<div class="card"><div class="card-header">@if($booking->public_request_token)<span class="badge bg-primary">Website self-service request</span>@else<span class="badge bg-light text-dark">Staff booking</span>@endif</div><div class="card-body">
<div class="row">
    <div class="col-md-6">
        <p><strong>Guest:</strong> {{ $booking->guest->full_name }}</p>
        <p><strong>Email:</strong> <a href="mailto:{{ $booking->guest->email }}">{{ $booking->guest->email }}</a></p>
        <p><strong>Phone:</strong> {{ $booking->guest->phone_number ?: 'Not provided' }}</p>
        <p><strong>Room:</strong> {{ $booking->room?->room_number ? 'Room '.$booking->room->room_number.' · ' : 'Not assigned · ' }}{{ $booking->room_type ?: 'Not specified' }}</p>
        <p><strong>Dates:</strong> {{ $booking->check_in_date?->format('Y-m-d') }} to {{ $booking->check_out_date?->format('Y-m-d') }}</p>
        @if($booking->public_request_token)
            <p><strong>Guests:</strong> {{ $booking->number_of_adults }} adult(s), {{ $booking->number_of_children }} child(ren)</p>
        @else
            <p><strong>Guests:</strong> {{ $booking->number_of_guests }}</p>
        @endif
        @if($booking->special_requests)<p><strong>Guest requests:</strong><br>{{ $booking->special_requests }}</p>@endif
    </div>
    <div class="col-md-6">
        <p><strong>Status:</strong> {{ $booking->status }}</p>
        <p><strong>Rate per night:</strong> {{ number_format($booking->room_rate, 2) }}</p>
        <p><strong>Total:</strong> {{ number_format($booking->room_total, 2) }}</p>
        <p><strong>Balance:</strong> {{ number_format($booking->balance_amount, 2) }}</p>
        <p><strong>Received:</strong> {{ $booking->created_at?->format('Y-m-d H:i') }}</p>
        <p><strong>Last action by:</strong> {{ $booking->performedBy?->name ?? 'Not recorded' }}</p>
    </div>
</div>
<div class="d-flex gap-2">
    @if(auth()->user()?->hasPermission('checkin.manage') || auth()->user()?->hasPermission('bookings.manage'))
        @if(in_array($booking->status, ['Pending', 'Confirmed'], true) && ! $booking->check_in_date?->isFuture())<form method="POST" action="{{ route('bookings.check-in', $booking) }}">@csrf<button class="btn btn-success">Check In</button></form>@endif
        @if($booking->status === 'Checked In')<form method="POST" action="{{ route('bookings.check-out', $booking) }}">@csrf<button class="btn btn-warning">Check Out</button></form>@endif
    @endif
    @if(auth()->user()?->hasPermission('bookings.manage'))
        @if(in_array($booking->status, ['Pending', 'Confirmed'], true) && $booking->check_in_date?->isFuture())<form method="POST" action="{{ route('bookings.cancel', $booking) }}" onsubmit="return confirm('Cancel this booking and refund any paid amount?')">@csrf<button class="btn btn-danger">Cancel Booking</button></form>@endif
        @if($booking->status !== 'Cancelled' && $booking->balance_amount > 0 && auth()->user()?->hasPermission('payments.manage'))<a href="{{ route('payments.create', ['target_type' => 'booking', 'target_id' => $booking->id]) }}" class="btn btn-primary">Receive Payment</a>@endif
        <a href="{{ route('bookings.receipt', $booking) }}" class="btn btn-secondary">Print Receipt</a>
    @endif
</div>
</div></div>
@endsection

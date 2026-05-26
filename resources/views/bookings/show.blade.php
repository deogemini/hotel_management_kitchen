@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">{{ $booking->booking_number }}</h1>
<div class="card"><div class="card-body">
<div class="row"><div class="col-md-6"><p><strong>Guest:</strong> {{ $booking->guest->full_name }}</p><p><strong>Room:</strong> {{ $booking->room?->room_number }} {{ $booking->room_type }}</p><p><strong>Dates:</strong> {{ $booking->check_in_date?->format('Y-m-d') }} to {{ $booking->check_out_date?->format('Y-m-d') }}</p></div><div class="col-md-6"><p><strong>Status:</strong> {{ $booking->status }}</p><p><strong>Total:</strong> {{ number_format($booking->room_total, 2) }}</p><p><strong>Balance:</strong> {{ number_format($booking->balance_amount, 2) }}</p></div></div>
<div class="d-flex gap-2">
    @if(in_array($booking->status, ['Pending', 'Confirmed'], true) && ! $booking->check_in_date?->isFuture())<form method="POST" action="{{ route('bookings.check-in', $booking) }}">@csrf<button class="btn btn-success">Check In</button></form>@endif
    @if($booking->status === 'Checked In')<form method="POST" action="{{ route('bookings.check-out', $booking) }}">@csrf<button class="btn btn-warning">Check Out</button></form>@endif
    @if($booking->balance_amount > 0)<a href="{{ route('payments.create', ['target_type' => 'booking', 'target_id' => $booking->id]) }}" class="btn btn-primary">Receive Payment</a>@endif
    <a href="{{ route('bookings.receipt', $booking) }}" class="btn btn-secondary">Print Receipt</a>
</div>
</div></div>
@endsection

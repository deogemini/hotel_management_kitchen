@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Booking Receipt</h1>
<div class="card"><div class="card-body">
<h4>{{ $booking->booking_number }}</h4><p>Guest: {{ $booking->guest->full_name }}</p><p>Room: {{ $booking->room?->room_number }}</p><p>Total: {{ number_format($booking->room_total, 2) }}</p><p>Paid: {{ number_format($booking->payments->sum('amount'), 2) }}</p><p>Balance: {{ number_format($booking->balance_amount, 2) }}</p>
<button onclick="window.print()" class="btn btn-primary">Print</button>
</div></div>
@endsection

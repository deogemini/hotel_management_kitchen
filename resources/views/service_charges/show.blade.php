@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">{{ $serviceCharge->service_type }} Service</h1>
<div class="card"><div class="card-body">
    <p><strong>Guest:</strong> {{ $serviceCharge->guest?->full_name }}</p>
    <p><strong>Booking:</strong> {{ $serviceCharge->booking?->booking_number }} - Room {{ $serviceCharge->booking?->room?->room_number }}</p>
    <p><strong>Description:</strong> {{ $serviceCharge->description }}</p>
    <p><strong>Total:</strong> {{ number_format($serviceCharge->amount, 2) }}</p>
    <p><strong>Paid:</strong> {{ number_format($serviceCharge->paid_amount, 2) }}</p>
    <p><strong>Balance:</strong> {{ number_format($serviceCharge->balance_amount, 2) }}</p>
    <p><strong>Status:</strong> {{ $serviceCharge->payment_status }}</p>
    @if($serviceCharge->balance_amount > 0)
        <a href="{{ route('payments.create', ['target_type' => 'service_charge', 'target_id' => $serviceCharge->id]) }}" class="btn btn-primary">Receive Payment</a>
    @endif
    <a href="{{ route('guests.show', $serviceCharge->guest_id) }}" class="btn btn-secondary">Customer Bills</a>
</div></div>
@endsection

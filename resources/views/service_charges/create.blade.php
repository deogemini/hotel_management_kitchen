@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Add Guest Service</h1>
<div class="card"><div class="card-body">
    <form method="POST" action="{{ route('service-charges.store') }}">
        @csrf
        <div class="row">
            <div class="col-md-4 mb-3">
                <label class="form-label">Guest</label>
                <select name="guest_id" class="form-select" required>
                    <option value="">Select guest</option>
                    @foreach($guests as $guest)
                        <option value="{{ $guest->id }}" @selected((int) old('guest_id', $guestId) === $guest->id)>{{ $guest->full_name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Booking</label>
                <select name="booking_id" class="form-select" required>
                    <option value="">Select booking</option>
                    @foreach($bookings as $booking)
                        <option value="{{ $booking->id }}" @selected((int) old('booking_id') === $booking->id)>{{ $booking->booking_number }} - Room {{ $booking->room?->room_number }} - {{ $booking->status }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Service</label>
                <select name="service_type" class="form-select">
                    @foreach(['Laundry', 'Ironing', 'Transport', 'Room service', 'Other'] as $type)
                        <option value="{{ $type }}" @selected(old('service_type') === $type)>{{ $type }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-6 mb-3">
                <label class="form-label">Description</label>
                <input name="description" class="form-control" value="{{ old('description') }}" placeholder="Laundry, ironing clothes, etc." required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Amount</label>
                <input type="number" step="0.01" min="0.01" name="amount" class="form-control" value="{{ old('amount') }}" required>
            </div>
            <div class="col-md-3 mb-3">
                <label class="form-label">Paid Amount</label>
                <input type="number" step="0.01" min="0" name="paid_amount" class="form-control" value="{{ old('paid_amount', 0) }}">
            </div>
            <div class="col-md-4 mb-3">
                <label class="form-label">Payment Method</label>
                <select name="payment_method" class="form-select">
                    <option>Cash</option>
                    <option>Mobile money</option>
                    <option>Card</option>
                    <option>Room charge</option>
                </select>
            </div>
        </div>
        <button class="btn btn-primary">Save Service</button>
        <a href="{{ route('service-charges.index') }}" class="btn btn-secondary">Back</a>
    </form>
</div></div>
@endsection

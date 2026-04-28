@csrf
<div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Guest</label><select name="guest_id" class="form-select" required>@foreach($guests as $guest)<option value="{{ $guest->id }}" @selected((int)old('guest_id', request('guest_id', $booking->guest_id))===$guest->id)>{{ $guest->full_name }} - {{ $guest->phone_number }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-3"><label class="form-label">Room</label><select name="room_id" class="form-select">@foreach($rooms as $room)<option value="{{ $room->id }}" @selected((int)old('room_id', $booking->room_id)===$room->id)>Room {{ $room->room_number }} - {{ $room->room_type }} - {{ number_format($room->price_per_night, 2) }}</option>@endforeach</select></div>
    <div class="col-md-3 mb-3"><label class="form-label">Check-In Date</label><input type="date" name="check_in_date" class="form-control" value="{{ old('check_in_date', optional($booking->check_in_date)->format('Y-m-d')) }}" required></div>
    <div class="col-md-3 mb-3"><label class="form-label">Check-Out Date</label><input type="date" name="check_out_date" class="form-control" value="{{ old('check_out_date', optional($booking->check_out_date)->format('Y-m-d')) }}" required></div>
    <div class="col-md-3 mb-3"><label class="form-label">Guests</label><input type="number" min="1" name="number_of_guests" class="form-control" value="{{ old('number_of_guests', $booking->number_of_guests ?: 1) }}" required></div>
    <div class="col-md-3 mb-3"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(\App\Models\Booking::STATUSES as $status)<option value="{{ $status }}" @selected(old('status', $booking->status ?: 'Pending')===$status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-3 mb-3"><label class="form-label">Deposit</label><input type="number" step="0.01" name="deposit_amount" class="form-control" value="{{ old('deposit_amount', $booking->deposit_amount ?: 0) }}"></div>
    <div class="col-md-3 mb-3"><label class="form-label">Deposit Method</label><select name="payment_method" class="form-select"><option>Cash</option><option>Mobile money</option><option>Card</option></select></div>
</div>
<button class="btn btn-primary">Save Booking</button>
<a href="{{ route('bookings.index') }}" class="btn btn-secondary">Back</a>

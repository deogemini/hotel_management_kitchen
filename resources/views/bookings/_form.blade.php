@csrf
<div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Guest</label><select name="guest_id" class="form-select" required>@foreach($guests as $guest)<option value="{{ $guest->id }}" @selected((int)old('guest_id', request('guest_id', $booking->guest_id))===$guest->id)>{{ $guest->full_name }} - {{ $guest->phone_number }}</option>@endforeach</select></div>
    <div class="col-md-6 mb-3"><label class="form-label">Room</label><select name="room_id" class="form-select" id="room_id">@foreach($rooms as $room)<option value="{{ $room->id }}" data-rate="{{ $room->price_per_night }}" @selected((int)old('room_id', $booking->room_id)===$room->id)>Room {{ $room->room_number }} - {{ $room->room_type }} - {{ number_format($room->price_per_night, 2) }}</option>@endforeach</select></div>
    <div class="col-md-3 mb-3"><label class="form-label">Check-In Date</label><input type="date" name="check_in_date" class="form-control" value="{{ old('check_in_date', optional($booking->check_in_date)->format('Y-m-d')) }}" required></div>
    <div class="col-md-3 mb-3"><label class="form-label">Check-Out Date</label><input type="date" name="check_out_date" class="form-control" value="{{ old('check_out_date', optional($booking->check_out_date)->format('Y-m-d')) }}" required></div>
    <div class="col-md-3 mb-3"><label class="form-label">Guests</label><input type="number" min="1" name="number_of_guests" class="form-control" value="{{ old('number_of_guests', $booking->number_of_guests ?: 1) }}" required></div>
    <div class="col-md-3 mb-3"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(\App\Models\Booking::STATUSES as $status)<option value="{{ $status }}" @selected(old('status', $booking->status ?: 'Pending')===$status)>{{ $status }}</option>@endforeach</select></div>
    <div class="col-md-3 mb-3"><label class="form-label">Deposit</label><input type="number" step="0.01" min="0" name="deposit_amount" id="deposit_amount" class="form-control" value="{{ old('deposit_amount', $booking->deposit_amount ?: 0) }}"><small class="text-muted" id="deposit_limit"></small></div>
    <div class="col-md-3 mb-3"><label class="form-label">Deposit Method</label><select name="payment_method" class="form-select"><option>Cash</option><option>Mobile money</option><option>Card</option></select></div>
</div>
<button class="btn btn-primary">Save Booking</button>
<a href="{{ route('bookings.index') }}" class="btn btn-secondary">Back</a>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const room = document.getElementById('room_id');
    const checkIn = document.querySelector('[name="check_in_date"]');
    const checkOut = document.querySelector('[name="check_out_date"]');
    const deposit = document.getElementById('deposit_amount');
    const limit = document.getElementById('deposit_limit');

    function nightsBetween(start, end) {
        if (! start || ! end) {
            return 1;
        }

        const startDate = new Date(start);
        const endDate = new Date(end);
        const days = Math.ceil((endDate - startDate) / 86400000);

        return Math.max(1, days);
    }

    function updateDepositLimit() {
        const selectedRoom = room.options[room.selectedIndex];
        const rate = Number(selectedRoom?.dataset.rate || 0);
        const total = rate * nightsBetween(checkIn.value, checkOut.value);

        deposit.max = total.toFixed(2);
        limit.textContent = 'Maximum deposit: ' + total.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });

        if (Number(deposit.value || 0) > total) {
            deposit.value = total.toFixed(2);
        }
    }

    [room, checkIn, checkOut].forEach((field) => field?.addEventListener('change', updateDepositLimit));
    updateDepositLimit();
});
</script>

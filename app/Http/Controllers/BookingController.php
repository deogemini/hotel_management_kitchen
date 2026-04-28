<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use App\Services\AuditService;
use Illuminate\Http\Request;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with('guest', 'room')
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        return view('bookings.create', [
            'booking' => new Booking(),
            'guests' => Guest::orderBy('full_name')->get(),
            'rooms' => Room::whereIn('status', ['Available', 'Reserved'])->orderBy('room_number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $room = Room::find($data['room_id']);
        $nights = max(1, now()->parse($data['check_in_date'])->diffInDays(now()->parse($data['check_out_date'])));
        $rate = $room?->price_per_night ?? 0;
        $total = $nights * $rate;
        $deposit = (float) ($data['deposit_amount'] ?? 0);

        $booking = Booking::create($data + [
            'booking_number' => $this->number('BK'),
            'room_type' => $room?->room_type,
            'number_of_nights' => $nights,
            'room_rate' => $rate,
            'room_total' => $total,
            'balance_amount' => max(0, $total - $deposit),
            'created_by' => auth()->id(),
        ]);

        if ($room && in_array($booking->status, ['Pending', 'Confirmed'], true)) {
            $room->update(['status' => 'Reserved']);
        }

        if ($deposit > 0) {
            Payment::create([
                'payment_number' => $this->number('PAY'),
                'payable_type' => Booking::class,
                'payable_id' => $booking->id,
                'guest_id' => $booking->guest_id,
                'booking_id' => $booking->id,
                'amount' => $deposit,
                'payment_method' => $request->input('payment_method', 'Cash'),
                'status' => $deposit >= $total ? 'Paid' : 'Partial',
                'received_by' => auth()->id(),
                'paid_at' => now(),
            ]);
        }

        AuditService::log('booking.create', $booking, $booking->getAttributes());

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking created successfully.');
    }

    public function show(Booking $booking)
    {
        $booking->load('guest', 'room', 'payments', 'restaurantOrders.items.menuItem', 'invoice.items');

        return view('bookings.show', compact('booking'));
    }

    public function edit(Booking $booking)
    {
        return view('bookings.edit', [
            'booking' => $booking,
            'guests' => Guest::orderBy('full_name')->get(),
            'rooms' => Room::orderBy('room_number')->get(),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $this->validated($request);
        $room = Room::find($data['room_id']);
        $nights = max(1, now()->parse($data['check_in_date'])->diffInDays(now()->parse($data['check_out_date'])));
        $rate = $room?->price_per_night ?? 0;
        $total = $nights * $rate;
        $deposit = (float) ($data['deposit_amount'] ?? 0);
        $original = $booking->getOriginal();

        $booking->update($data + [
            'room_type' => $room?->room_type,
            'number_of_nights' => $nights,
            'room_rate' => $rate,
            'room_total' => $total,
            'balance_amount' => max(0, $total - $deposit - $booking->payments()->sum('amount')),
        ]);

        AuditService::log('booking.update', $booking, ['from' => $original, 'to' => $booking->getAttributes()]);

        return redirect()->route('bookings.show', $booking)->with('success', 'Booking updated successfully.');
    }

    public function destroy(Booking $booking)
    {
        if ($booking->status === 'Checked In') {
            return back()->withErrors(['booking' => 'Cannot delete a checked-in booking.']);
        }

        $booking->room?->update(['status' => 'Available']);
        $booking->delete();

        return redirect()->route('bookings.index')->with('success', 'Booking deleted successfully.');
    }

    public function receipt(Booking $booking)
    {
        $booking->load('guest', 'room', 'payments');

        return view('bookings.receipt', compact('booking'));
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'guest_id' => ['required', 'exists:guests,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:'.implode(',', Booking::STATUSES)],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
        ]);
    }

    private function number(string $prefix): string
    {
        return $prefix.'-'.now()->format('YmdHis').'-'.random_int(100, 999);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\Room;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BookingController extends Controller
{
    public function index(Request $request)
    {
        $bookings = Booking::with('guest', 'room')
            ->when(! $this->canSeeAllLodges(), fn ($query) => $query->where('lodge_id', auth()->user()?->lodge_id))
            ->when($this->canSeeAllLodges() && $request->lodge_id, fn ($query, $lodgeId) => $query->where('lodge_id', $lodgeId))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('bookings.index', compact('bookings'));
    }

    public function create()
    {
        return view('bookings.create', [
            'booking' => new Booking(),
            'guests' => $this->lodgeQuery(Guest::query())->orderBy('full_name')->get(),
            'rooms' => $this->lodgeQuery(Room::whereIn('status', ['Available', 'Reserved']))->orderBy('room_number')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $room = $this->lodgeQuery(Room::query())->find($data['room_id']);
        $nights = max(1, now()->parse($data['check_in_date'])->diffInDays(now()->parse($data['check_out_date'])));
        $rate = $room?->price_per_night ?? 0;
        $total = $nights * $rate;
        $deposit = (float) ($data['deposit_amount'] ?? 0);

        $booking = Booking::create($data + [
            'booking_number' => $this->number('BK'),
            'lodge_id' => $room?->lodge_id ?: auth()->user()?->lodge_id,
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
                'lodge_id' => $booking->lodge_id,
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
            'guests' => $this->lodgeQuery(Guest::query())->orderBy('full_name')->get(),
            'rooms' => $this->lodgeQuery(Room::query())->orderBy('room_number')->get(),
        ]);
    }

    public function update(Request $request, Booking $booking)
    {
        $data = $this->validated($request);
        $room = $this->lodgeQuery(Room::query())->find($data['room_id']);
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
            'balance_amount' => max(0, $total - $deposit - $booking->payments()->whereIn('status', ['Paid', 'Partial'])->sum('amount')),
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

    public function cancel(Booking $booking)
    {
        if (! in_array($booking->status, ['Pending', 'Confirmed'], true)) {
            return back()->withErrors(['booking' => 'Only pending or confirmed bookings can be cancelled.']);
        }

        if (! $booking->check_in_date?->isFuture()) {
            return back()->withErrors(['booking' => 'This booking can only be cancelled before the check-in date.']);
        }

        DB::transaction(function () use ($booking): void {
            $refundedAmount = $booking->payments()
                ->whereIn('status', ['Paid', 'Partial'])
                ->sum('amount');

            $booking->payments()
                ->whereIn('status', ['Paid', 'Partial'])
                ->update([
                    'status' => 'Refunded',
                    'notes' => 'Refunded after booking cancellation.',
                ]);

            $booking->update([
                'status' => 'Cancelled',
                'deposit_amount' => 0,
                'balance_amount' => 0,
            ]);

            $booking->room?->update(['status' => 'Available']);
            AuditService::log('booking.cancel', $booking, ['refunded_amount' => $refundedAmount]);
        });

        return back()->with('success', 'Booking cancelled successfully. Any paid amount was refunded.');
    }

    public function receipt(Booking $booking)
    {
        $booking->load('guest', 'room', 'payments');

        return view('bookings.receipt', compact('booking'));
    }

    public function invoice(Booking $booking)
    {
        $booking->load('guest', 'room', 'payments');

        return view('bookings.invoice', compact('booking'));
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'guest_id' => ['required', 'exists:guests,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'check_in_date' => ['required', 'date'],
            'check_out_date' => ['required', 'date', 'after:check_in_date'],
            'number_of_guests' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:'.implode(',', Booking::STATUSES)],
            'deposit_amount' => ['nullable', 'numeric', 'min:0'],
        ]);

        $total = $this->roomTotal($data);
        $deposit = (float) ($data['deposit_amount'] ?? 0);

        if ($deposit > $total) {
            throw ValidationException::withMessages([
                'deposit_amount' => 'Deposit cannot exceed the selected room total of '.number_format($total, 2).'.',
            ]);
        }

        return $data;
    }

    private function roomTotal(array $data): float
    {
        $room = $this->lodgeQuery(Room::query())->find($data['room_id'] ?? null);
        $nights = max(1, now()->parse($data['check_in_date'])->diffInDays(now()->parse($data['check_out_date'])));

        return $nights * (float) ($room?->price_per_night ?? 0);
    }

    private function number(string $prefix): string
    {
        return $prefix.'-'.now()->format('YmdHis').'-'.random_int(100, 999);
    }

    private function canSeeAllLodges(): bool
    {
        return auth()->user()?->hasRole('hotel_manager') ?? false;
    }

    private function lodgeQuery($query)
    {
        if (! $this->canSeeAllLodges()) {
            $query->where('lodge_id', auth()->user()?->lodge_id);
        }

        return $query;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CheckInOutController extends Controller
{
    public function checkIn(Booking $booking)
    {
        if (! in_array($booking->status, ['Pending', 'Confirmed'], true)) {
            return back()->withErrors(['booking' => 'Only pending or confirmed bookings can be checked in.']);
        }

        if ($booking->check_in_date?->isFuture()) {
            return back()->withErrors(['booking' => 'This booking cannot be checked in before the check-in date.']);
        }

        DB::transaction(fn () => $this->markCheckedIn($booking));

        return back()->with('success', 'Guest checked in successfully.');
    }

    public function bulkCheckIn(Request $request)
    {
        $bookingIds = $this->validatedBookingIds($request);

        DB::transaction(function () use ($bookingIds): void {
            $bookings = $this->selectedBookings($bookingIds);

            if ($bookings->contains(fn (Booking $booking) => ! in_array($booking->status, ['Pending', 'Confirmed'], true))) {
                throw ValidationException::withMessages(['booking_ids' => 'Select pending or confirmed bookings for check-in.']);
            }

            if ($bookings->contains(fn (Booking $booking) => $booking->check_in_date?->isFuture())) {
                throw ValidationException::withMessages(['booking_ids' => 'One or more selected bookings have a future check-in date.']);
            }

            foreach ($bookings as $booking) {
                $this->markCheckedIn($booking);
            }
        });

        return back()->with('success', count($bookingIds).' bookings checked in successfully.');
    }

    public function bulkCheckOut(Request $request)
    {
        $bookingIds = $this->validatedBookingIds($request);

        DB::transaction(function () use ($bookingIds): void {
            $bookings = $this->selectedBookings($bookingIds);

            if ($bookings->contains(fn (Booking $booking) => $booking->status !== 'Checked In')) {
                throw ValidationException::withMessages(['booking_ids' => 'Select checked-in bookings for check-out.']);
            }

            foreach ($bookings as $booking) {
                $this->completeCheckOut($booking);
            }
        });

        return back()->with('success', count($bookingIds).' bookings checked out successfully. Invoices have been generated.');
    }

    public function checkOut(Booking $booking)
    {
        if ($booking->status !== 'Checked In') {
            return back()->withErrors(['booking' => 'Only checked-in bookings can be checked out.']);
        }

        DB::transaction(fn () => $this->completeCheckOut($booking));

        return redirect()->route('bookings.index')->with('success', 'Guest checked out successfully. Invoice generated.');
    }

    private function markCheckedIn(Booking $booking): void
    {
        $booking->update(['status' => 'Checked In', 'checked_in_at' => now()]);
        $booking->room?->update(['status' => 'Occupied']);
        AuditService::log('booking.check_in', $booking, ['status' => 'Checked In']);
    }

    private function completeCheckOut(Booking $booking): Invoice
    {
        $booking->load('guest', 'room', 'restaurantOrders.items.menuItem', 'otherCharges', 'payments');
        $restaurantTotal = $booking->restaurantOrders()
            ->where('payment_method', 'Room charge')
            ->where('status', '!=', 'Cancelled')
            ->sum('balance_amount');
        $otherCharges = $booking->otherCharges()->sum('amount');
        $subtotal = $booking->room_total + $restaurantTotal + $otherCharges;
        $paid = $booking->payments()->whereIn('status', ['Paid', 'Partial'])->sum('amount');

        $invoice = $booking->invoice ?: Invoice::create([
            'invoice_number' => $this->nextInvoiceNumber(),
            'lodge_id' => $booking->lodge_id,
            'guest_id' => $booking->guest_id,
            'booking_id' => $booking->id,
            'subtotal' => $subtotal,
            'paid_amount' => $paid,
            'balance_amount' => max(0, $subtotal - $paid),
            'status' => $paid >= $subtotal ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
            'issued_by' => auth()->id(),
            'issued_at' => now(),
            'issuer_details' => \App\Models\InvoiceSetting::details(),
            'bill_to' => ['name' => $booking->guest?->full_name, 'phone' => $booking->guest?->phone_number,
                'email' => $booking->guest?->email, 'address' => $booking->guest?->address],
            'due_date' => now()->addDays(7),
        ]);

        if (! $invoice->items()->exists()) {
            InvoiceItem::create([
                'invoice_id' => $invoice->id,
                'item_type' => 'room',
                'description' => 'Room '.$booking->room?->room_number.' charges',
                'quantity' => $booking->number_of_nights,
                'unit_price' => $booking->room_rate,
                'total_price' => $booking->room_total,
            ]);

            if ($restaurantTotal > 0) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'restaurant',
                    'description' => 'Restaurant room charges',
                    'quantity' => 1,
                    'unit_price' => $restaurantTotal,
                    'total_price' => $restaurantTotal,
                ]);
            }

            foreach ($booking->otherCharges as $charge) {
                InvoiceItem::create([
                    'invoice_id' => $invoice->id,
                    'item_type' => 'other',
                    'description' => $charge->description,
                    'quantity' => 1,
                    'unit_price' => $charge->amount,
                    'total_price' => $charge->amount,
                ]);
            }
        }

        $booking->update([
            'status' => 'Checked Out',
            'checked_out_at' => now(),
            'balance_amount' => max(0, $subtotal - $paid),
        ]);
        $booking->room?->update(['status' => 'Available']);
        AuditService::log('booking.check_out', $booking, ['invoice_id' => $invoice->id]);

        return $invoice;
    }

    private function validatedBookingIds(Request $request): array
    {
        return $request->validate([
            'booking_ids' => ['required', 'array', 'min:1', 'max:500'],
            'booking_ids.*' => ['required', 'integer', 'distinct'],
        ])['booking_ids'];
    }

    private function selectedBookings(array $bookingIds)
    {
        $user = auth()->user();
        $query = Booking::query()
            ->whereKey($bookingIds)
            ->with('guest', 'room')
            ->lockForUpdate();

        if (! $user?->hasRole('hotel_manager')) {
            $query->where('lodge_id', $user?->lodge_id);
        }

        $bookings = $query->get();

        if ($bookings->count() !== count($bookingIds)) {
            throw ValidationException::withMessages(['booking_ids' => 'One or more selected bookings are unavailable.']);
        }

        return $bookings;
    }

    private function nextInvoiceNumber(): string
    {
        do {
            $invoiceNumber = 'INV-'.now()->format('YmdHis').'-'.random_int(100, 999);
        } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }
}

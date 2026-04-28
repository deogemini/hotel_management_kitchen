<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Services\AuditService;

class CheckInOutController extends Controller
{
    public function checkIn(Booking $booking)
    {
        if (! in_array($booking->status, ['Pending', 'Confirmed'], true)) {
            return back()->withErrors(['booking' => 'Only pending or confirmed bookings can be checked in.']);
        }

        $booking->update(['status' => 'Checked In', 'checked_in_at' => now()]);
        $booking->room?->update(['status' => 'Occupied']);
        AuditService::log('booking.check_in', $booking, ['status' => 'Checked In']);

        return back()->with('success', 'Guest checked in successfully.');
    }

    public function checkOut(Booking $booking)
    {
        if ($booking->status !== 'Checked In') {
            return back()->withErrors(['booking' => 'Only checked-in bookings can be checked out.']);
        }

        $booking->load('restaurantOrders.items.menuItem', 'otherCharges', 'payments');
        $restaurantTotal = $booking->restaurantOrders()
            ->where('payment_method', 'Room charge')
            ->where('status', '!=', 'Cancelled')
            ->sum('balance_amount');
        $otherCharges = $booking->otherCharges()->sum('amount');
        $subtotal = $booking->room_total + $restaurantTotal + $otherCharges;
        $paid = $booking->payments()->sum('amount');

        $invoice = $booking->invoice ?: Invoice::create([
            'invoice_number' => 'INV-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'guest_id' => $booking->guest_id,
            'booking_id' => $booking->id,
            'subtotal' => $subtotal,
            'paid_amount' => $paid,
            'balance_amount' => max(0, $subtotal - $paid),
            'status' => $paid >= $subtotal ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
            'issued_by' => auth()->id(),
            'issued_at' => now(),
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

        return redirect()->route('invoices.print', $invoice)->with('success', 'Guest checked out successfully.');
    }
}

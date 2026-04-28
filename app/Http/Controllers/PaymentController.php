<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('guest', 'booking', 'restaurantOrder', 'invoice')->latest('paid_at')->get();

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        return view('payments.create', [
            'bookings' => Booking::with('guest', 'room')->whereIn('status', ['Pending', 'Confirmed', 'Checked In', 'Checked Out'])->get(),
            'restaurantOrders' => RestaurantOrder::with('guest')->whereIn('payment_status', ['Unpaid', 'Partial'])->get(),
            'invoices' => Invoice::with('guest')->whereIn('status', ['Unpaid', 'Partial'])->get(),
            'targetType' => $request->input('target_type'),
            'targetId' => $request->input('target_id'),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'target_type' => ['required', 'in:booking,restaurant_order,invoice'],
            'target_id' => ['required', 'integer'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'payment_method' => ['required', 'in:Cash,Mobile money,Card,Room charge'],
            'reference_number' => ['nullable', 'string', 'max:255'],
            'notes' => ['nullable', 'string'],
        ]);

        [$target, $guestId, $bookingId, $restaurantOrderId, $invoiceId] = $this->resolveTarget($data['target_type'], $data['target_id']);

        $payment = Payment::create([
            'payment_number' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'payable_type' => $target::class,
            'payable_id' => $target->id,
            'guest_id' => $guestId,
            'booking_id' => $bookingId,
            'restaurant_order_id' => $restaurantOrderId,
            'invoice_id' => $invoiceId,
            'amount' => $data['amount'],
            'payment_method' => $data['payment_method'],
            'status' => 'Paid',
            'reference_number' => $data['reference_number'] ?? null,
            'notes' => $data['notes'] ?? null,
            'received_by' => auth()->id(),
            'paid_at' => now(),
        ]);

        $this->refreshBalances($target);

        return redirect()->route('payments.receipt', $payment)->with('success', 'Payment received successfully.');
    }

    public function show(Payment $payment)
    {
        return $this->receipt($payment);
    }

    public function receipt(Payment $payment)
    {
        $payment->load('guest', 'booking.room', 'restaurantOrder', 'invoice');

        return view('payments.receipt', compact('payment'));
    }

    private function resolveTarget(string $type, int $id): array
    {
        if ($type === 'booking') {
            $target = Booking::findOrFail($id);
            return [$target, $target->guest_id, $target->id, null, null];
        }

        if ($type === 'restaurant_order') {
            $target = RestaurantOrder::findOrFail($id);
            return [$target, $target->guest_id, $target->booking_id, $target->id, null];
        }

        $target = Invoice::findOrFail($id);
        return [$target, $target->guest_id, $target->booking_id, null, $target->id];
    }

    private function refreshBalances(object $target): void
    {
        if ($target instanceof Booking) {
            $paid = $target->payments()->sum('amount');
            $target->update(['balance_amount' => max(0, $target->room_total - $paid), 'deposit_amount' => $paid]);
        }

        if ($target instanceof RestaurantOrder) {
            $paid = $target->payments()->sum('amount');
            $target->update([
                'paid_amount' => $paid,
                'balance_amount' => max(0, $target->subtotal - $paid),
                'payment_status' => $paid >= $target->subtotal ? 'Paid' : 'Partial',
            ]);
        }

        if ($target instanceof Invoice) {
            $paid = $target->payments()->sum('amount');
            $target->update([
                'paid_amount' => $paid,
                'balance_amount' => max(0, $target->subtotal - $paid),
                'status' => $paid >= $target->subtotal ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
            ]);
        }
    }
}

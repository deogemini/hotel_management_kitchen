<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class PaymentController extends Controller
{
    public function index()
    {
        $payments = Payment::with('guest', 'booking.room', 'restaurantOrder.guest', 'restaurantOrder.room', 'invoice')->latest('paid_at')->get();

        return view('payments.index', compact('payments'));
    }

    public function create(Request $request)
    {
        $selectedTarget = null;
        $targetType = $request->input('target_type');
        $targetId = $request->input('target_id');

        if ($targetType && $targetId) {
            $selectedTarget = $this->findTarget($targetType, (int) $targetId);
        }

        return view('payments.create', [
            'bookings' => Booking::with('guest', 'room')->whereIn('status', ['Pending', 'Confirmed', 'Checked In', 'Checked Out'])->where('balance_amount', '>', 0)->get(),
            'restaurantOrders' => RestaurantOrder::with('guest')->whereIn('payment_status', ['Unpaid', 'Partial'])->get(),
            'invoices' => Invoice::with('guest')->whereIn('status', ['Unpaid', 'Partial'])->get(),
            'targetType' => $targetType,
            'targetId' => $targetId,
            'selectedTarget' => $selectedTarget,
            'targetLabel' => $selectedTarget ? $this->targetLabel($selectedTarget) : null,
            'remainingBalance' => $selectedTarget ? $this->remainingBalance($selectedTarget) : null,
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
        $remainingBalance = $this->remainingBalance($target);
        $amount = (float) $data['amount'];

        if ($remainingBalance <= 0) {
            throw ValidationException::withMessages([
                'amount' => 'This customer has no remaining balance to pay.',
            ]);
        }

        if ($amount > $remainingBalance) {
            throw ValidationException::withMessages([
                'amount' => 'Payment cannot exceed the remaining balance of '.number_format($remainingBalance, 2).'.',
            ]);
        }

        $payment = Payment::create([
            'payment_number' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
            'payable_type' => $target::class,
            'payable_id' => $target->id,
            'guest_id' => $guestId,
            'booking_id' => $bookingId,
            'restaurant_order_id' => $restaurantOrderId,
            'invoice_id' => $invoiceId,
            'amount' => $amount,
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
        $payment->load('guest', 'booking.room', 'restaurantOrder.guest', 'restaurantOrder.room', 'invoice');

        return view('payments.receipt', compact('payment'));
    }

    private function resolveTarget(string $type, int $id): array
    {
        $target = $this->findTarget($type, $id);

        if ($type === 'booking') {
            return [$target, $target->guest_id, $target->id, null, null];
        }

        if ($type === 'restaurant_order') {
            return [$target, $target->guest_id, $target->booking_id, $target->id, null];
        }

        return [$target, $target->guest_id, $target->booking_id, null, $target->id];
    }

    private function findTarget(string $type, int $id): object
    {
        if ($type === 'booking') {
            return Booking::with('guest', 'room')->findOrFail($id);
        }

        if ($type === 'restaurant_order') {
            return RestaurantOrder::with('guest', 'room')->findOrFail($id);
        }

        if ($type === 'invoice') {
            return Invoice::with('guest')->findOrFail($id);
        }

        abort(404);
    }

    private function remainingBalance(object $target): float
    {
        return (float) ($target->balance_amount ?? 0);
    }

    private function targetLabel(object $target): string
    {
        if ($target instanceof Booking) {
            return $target->booking_number.' - '.$target->guest?->full_name.' - Room '.$target->room?->room_number;
        }

        if ($target instanceof RestaurantOrder) {
            $customer = $target->guest?->full_name ?? $target->walk_in_customer_name ?? 'Walk-in customer';

            return $target->order_number.' - Food - '.$customer;
        }

        if ($target instanceof Invoice) {
            return $target->invoice_number.' - '.$target->guest?->full_name;
        }

        return '-';
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

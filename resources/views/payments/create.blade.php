@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Receive Payment</h1>
<div class="card">
    <div class="card-body">
        @if($selectedTarget && $remainingBalance <= 0)
            <div class="alert alert-info mb-0">This customer has no remaining balance to pay.</div>
        @else
            <form method="POST" action="{{ route('payments.store') }}">
                @csrf
                <div class="row">
                    @if($selectedTarget)
                        <input type="hidden" name="target_type" value="{{ $targetType }}">
                        <input type="hidden" name="target_id" value="{{ $targetId }}">
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Customer / Bill</label>
                            <input class="form-control" value="{{ $targetLabel }}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Remaining Balance</label>
                            <input class="form-control" value="{{ number_format($remainingBalance, 2) }}" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="0.01" max="{{ $remainingBalance }}" name="amount" class="form-control" value="{{ $remainingBalance }}" readonly required>
                        </div>
                    @else
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Target Type</label>
                            <select name="target_type" id="target_type" class="form-select">
                                <option value="booking" @selected(($targetType ?? 'booking')==='booking')>Booking</option>
                                <option value="restaurant_order" @selected($targetType==='restaurant_order')>Restaurant Order</option>
                                <option value="invoice" @selected($targetType==='invoice')>Invoice</option>
                                <option value="service_charge" @selected($targetType==='service_charge')>Guest Service</option>
                            </select>
                        </div>
                        <div class="col-md-8 mb-3">
                            <label class="form-label">Pending Payment</label>
                            <select name="target_id" id="target_id" class="form-select" required>
                                <option value="">Select pending payment</option>
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Remaining Balance</label>
                            <input id="remaining_balance" class="form-control" value="0.00" readonly>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Amount</label>
                            <input type="number" step="0.01" min="0.01" name="amount" id="amount" class="form-control" value="{{ old('amount') }}" readonly required>
                        </div>
                    @endif
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Method</label>
                        <select name="payment_method" class="form-select">
                            <option>Cash</option>
                            <option>Mobile money</option>
                            <option>Card</option>
                            <option>Room charge</option>
                        </select>
                    </div>
                    <div class="col-md-4 mb-3">
                        <label class="form-label">Reference</label>
                        <input name="reference_number" class="form-control" value="{{ old('reference_number') }}">
                    </div>
                    <div class="col-12 mb-3">
                        <label class="form-label">Notes</label>
                        <textarea name="notes" class="form-control">{{ old('notes') }}</textarea>
                    </div>
                </div>
                <button class="btn btn-primary">Receive Payment</button>
            </form>
        @endif
    </div>
</div>
@if(! $selectedTarget)
<script>
document.addEventListener('DOMContentLoaded', function () {
    const pendingPayments = {
        booking: [
            @foreach($bookings as $booking)
                {
                    id: '{{ $booking->id }}',
                    label: @json($booking->booking_number.' - '.$booking->guest?->full_name.' - Room '.$booking->room?->room_number.' - Balance '.number_format($booking->balance_amount, 2)),
                    balance: {{ (float) $booking->balance_amount }},
                },
            @endforeach
        ],
        restaurant_order: [
            @foreach($restaurantOrders as $order)
                {
                    id: '{{ $order->id }}',
                    label: @json($order->order_number.' - Food - '.($order->guest?->full_name ?? $order->walk_in_customer_name ?? 'Walk-in customer').' - Balance '.number_format($order->balance_amount, 2)),
                    balance: {{ (float) $order->balance_amount }},
                },
            @endforeach
        ],
        invoice: [
            @foreach($invoices as $invoice)
                {
                    id: '{{ $invoice->id }}',
                    label: @json($invoice->invoice_number.' - '.$invoice->guest?->full_name.' - Balance '.number_format($invoice->balance_amount, 2)),
                    balance: {{ (float) $invoice->balance_amount }},
                },
            @endforeach
        ],
        service_charge: [
            @foreach($serviceCharges as $charge)
                {
                    id: '{{ $charge->id }}',
                    label: @json($charge->service_type.' - '.$charge->guest?->full_name.' - Balance '.number_format($charge->balance_amount, 2)),
                    balance: {{ (float) $charge->balance_amount }},
                },
            @endforeach
        ],
    };
    const targetType = document.getElementById('target_type');
    const targetId = document.getElementById('target_id');
    const amount = document.getElementById('amount');
    const remainingBalance = document.getElementById('remaining_balance');
    const oldTargetId = @json(old('target_id', $targetId));

    function formatAmount(value) {
        return Number(value || 0).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function selectedPendingPayment() {
        const option = targetId.options[targetId.selectedIndex];

        return {
            balance: Number(option?.dataset.balance || 0),
        };
    }

    function updateAmount() {
        const selected = selectedPendingPayment();

        remainingBalance.value = formatAmount(selected.balance);
        amount.value = selected.balance > 0 ? selected.balance.toFixed(2) : '';
        amount.max = selected.balance.toFixed(2);
    }

    function updateTargetOptions() {
        const rows = pendingPayments[targetType.value] || [];

        targetId.innerHTML = '<option value="">Select pending payment</option>';

        rows.forEach(function (row) {
            const option = document.createElement('option');
            option.value = row.id;
            option.textContent = row.label;
            option.dataset.balance = row.balance;
            option.selected = oldTargetId && String(oldTargetId) === row.id;
            targetId.appendChild(option);
        });

        updateAmount();
    }

    targetType.addEventListener('change', updateTargetOptions);
    targetId.addEventListener('change', updateAmount);
    updateTargetOptions();
});
</script>
@endif
@endsection

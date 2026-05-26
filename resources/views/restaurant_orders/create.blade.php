@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">New Restaurant Order</h1>
<div class="card">
    <div class="card-body">
        <form method="POST" action="{{ route('restaurant-orders.store') }}">
            @csrf
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Customer Type</label>
                    <select name="customer_type" class="form-select">
                        <option>Room guest</option>
                        <option>Walk-in customer</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Active Room Booking</label>
                    <select name="booking_id" class="form-select">
                        <option value="">Walk-in / none</option>
                        @foreach($bookings as $booking)
                            <option value="{{ $booking->id }}">{{ $booking->guest->full_name }} - Room {{ $booking->room?->room_number }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Walk-in Name</label>
                    <input name="walk_in_customer_name" class="form-control">
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
                <div class="col-md-4 mb-3">
                    <label class="form-label">Paid Amount</label>
                    <input type="number" step="0.01" name="paid_amount" class="form-control" value="0">
                </div>
            </div>
            <h5>Items</h5>
            @for($i = 0; $i < 5; $i++)
                <div class="row g-2 mb-2 order-item-row">
                    <div class="col-md-7">
                        <select name="menu_item_id[]" class="form-select menu-item-select">
                            <option value="" data-price="0">Select item</option>
                            @foreach($menuItems as $item)
                                <option value="{{ $item->id }}" data-price="{{ $item->price }}">{{ $item->name }} - {{ number_format($item->price, 2) }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2">
                        <input type="number" min="1" name="quantity[]" class="form-control quantity-input" value="{{ $i === 0 ? 1 : '' }}">
                    </div>
                    <div class="col-md-3">
                        <input class="form-control line-total" value="0.00" readonly>
                    </div>
                </div>
            @endfor
            <div class="row mt-3">
                <div class="col-md-4 ms-auto">
                    <label class="form-label">Total Amount</label>
                    <input id="order_total" class="form-control fw-bold" value="0.00" readonly>
                </div>
            </div>
            <button class="btn btn-primary mt-3">Send Order</button>
        </form>
    </div>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const rows = document.querySelectorAll('.order-item-row');
    const orderTotal = document.getElementById('order_total');

    function formatAmount(amount) {
        return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateTotal() {
        let total = 0;

        rows.forEach(function (row) {
            const item = row.querySelector('.menu-item-select');
            const quantity = row.querySelector('.quantity-input');
            const lineTotal = row.querySelector('.line-total');
            const price = Number(item.options[item.selectedIndex]?.dataset.price || 0);
            const qty = Number(quantity.value || 0);
            const amount = price * qty;

            lineTotal.value = formatAmount(amount);
            total += amount;
        });

        orderTotal.value = formatAmount(total);
    }

    rows.forEach(function (row) {
        row.querySelector('.menu-item-select').addEventListener('change', updateTotal);
        row.querySelector('.quantity-input').addEventListener('input', updateTotal);
    });

    updateTotal();
});
</script>
@endsection

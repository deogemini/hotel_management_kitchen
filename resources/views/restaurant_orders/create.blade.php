@extends('layouts.admin')
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush
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
                    <label class="form-label">Guest</label>
                    <select name="guest_id" class="form-select">
                        <option value="">Walk-in / none</option>
                        @foreach($guests as $guest)
                            <option value="{{ $guest->id }}" @selected((int) old('guest_id', $guestId ?? null) === $guest->id)>{{ $guest->full_name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Payment Method</label>
                    <select name="payment_method" class="form-select">
                        <option>Cash</option>
                        <option>LIPA NAMBA</option>
                        <option>Card</option>
                        <option>Room charge</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Paid Amount</label>
                    <input type="number" step="0.01" name="paid_amount" class="form-control" value="0" readonly>
                    <small class="text-muted">The selected payment method marks the order as fully paid. Room charge remains unpaid.</small>
                </div>
                <div class="col-md-4 mb-3 d-flex align-items-end"><div class="form-check mb-2"><input type="hidden" name="not_paid" value="0"><input type="checkbox" name="not_paid" value="1" id="not_paid" class="form-check-input"><label for="not_paid" class="form-check-label">Not paid</label></div></div>
            </div>
            <h5>Items</h5>
            <div id="order-items">
                @for($i = 0; $i < 5; $i++)
                    @include('restaurant_orders.partials.item-row', ['quantity' => $i === 0 ? 1 : ''])
                @endfor
            </div>
            <template id="order-item-template">
                @include('restaurant_orders.partials.item-row', ['quantity' => 1])
            </template>
            <button type="button" id="add-order-item" class="btn btn-outline-primary mt-2">
                <span aria-hidden="true">+</span> Add item
            </button>
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
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>$(function () { $('.selectpicker').selectpicker(); });</script>
@endpush
<script>
window.addEventListener('load', function () {
    const items = document.getElementById('order-items');
    const itemTemplate = document.getElementById('order-item-template');
    const orderTotal = document.getElementById('order_total');

    function formatAmount(amount) {
        return amount.toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateTotal() {
        let total = 0;

        items.querySelectorAll('.order-item-row').forEach(function (row) {
            const item = row.querySelector('.menu-item-select');
            const quantity = row.querySelector('.quantity-input');
            const lineTotal = row.querySelector('.line-total');
            const selectedOption = item.querySelector('option:checked') || item.selectedOptions[0];
            const price = Number(selectedOption?.dataset.price || 0);
            const stock = Number(selectedOption?.dataset.stock || 0);
            const isFood = selectedOption?.dataset.category === 'Food';
            const qty = Number(quantity.value || 0);
            const amount = price * qty;

            if (!isFood && stock > 0) {
                quantity.max = stock;
            } else {
                quantity.removeAttribute('max');
            }

            lineTotal.value = formatAmount(amount);
            total += amount;
        });

        orderTotal.value = formatAmount(total);
    }

    function bindRow(row) {
        row.querySelector('.menu-item-select').addEventListener('change', updateTotal);
        $(row.querySelector('.menu-item-select')).on('changed.bs.select', updateTotal);
        row.querySelector('.quantity-input').addEventListener('input', updateTotal);
    }

    items.querySelectorAll('.order-item-row').forEach(bindRow);

    document.getElementById('add-order-item').addEventListener('click', function () {
        const row = itemTemplate.content.firstElementChild.cloneNode(true);
        items.appendChild(row);
        bindRow(row);
        $(row.querySelector('.menu-item-select')).selectpicker();
        updateTotal();
        row.querySelector('.bootstrap-select button, .menu-item-select').focus();
    });

    updateTotal();
    setTimeout(updateTotal, 100);
});
</script>
@endsection

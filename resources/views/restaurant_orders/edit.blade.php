@extends('layouts.admin')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush

@section('content')
<h1 class="h3 mb-3">Edit Restaurant Order</h1>

<div class="card">
    <div class="card-body">
        <p class="text-muted">Order: <strong>{{ $restaurantOrder->order_number }}</strong></p>
        <p class="text-muted">Payments received: <strong>{{ number_format($restaurantOrder->paid_amount, 2) }}</strong>. The balance and payment status will be recalculated when you save.</p>

        <form method="POST" action="{{ route('restaurant-orders.update', $restaurantOrder) }}">
            @csrf
            @method('PUT')

            <div class="mb-4">
                <label for="status" class="form-label">Order status</label>
                <select id="status" name="status" class="form-select" required>
                    @foreach(\App\Models\RestaurantOrder::STATUSES as $status)
                        <option value="{{ $status }}" @selected(old('status', $restaurantOrder->status) === $status)>{{ $status }}</option>
                    @endforeach
                </select>
            </div>

            <div class="d-flex justify-content-between align-items-center mb-2">
                <h2 class="h5 mb-0">Order items</h2>
                <button type="button" id="add-order-item" class="btn btn-outline-primary">+ Add item</button>
            </div>

            @error('menu_item_id')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
            @error('quantity')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror
            @error('unit_price')<div class="alert alert-danger py-2">{{ $message }}</div>@enderror

            @php
                $menuItemIds = old('menu_item_id', $restaurantOrder->items->pluck('menu_item_id')->all());
                $quantities = old('quantity', $restaurantOrder->items->pluck('quantity')->all());
                $unitPrices = old('unit_price', $restaurantOrder->items->pluck('unit_price')->all());
                $orderItemIds = old('order_item_id', $restaurantOrder->items->pluck('id')->all());
                $orderQuantities = $restaurantOrder->items->groupBy('menu_item_id')->map(fn ($items) => $items->sum('quantity'));
                $rowCount = max(count($menuItemIds), 1);
            @endphp

            <div class="row text-muted small fw-semibold d-none d-md-flex mb-1">
                <div class="col-md-5">Item</div>
                <div class="col-md-2">Quantity</div>
                <div class="col-md-2">Unit price</div>
                <div class="col-md-2">Line total</div>
                <div class="col-md-1">Remove</div>
            </div>

            <div id="order-items">
                @for($i = 0; $i < $rowCount; $i++)
                    @php
                        $selectedMenuItemId = (int) ($menuItemIds[$i] ?? 0);
                        $selectedOrderItemId = (int) ($orderItemIds[$i] ?? 0);
                        $existingLine = $restaurantOrder->items->firstWhere('id', $selectedOrderItemId);
                    @endphp
                    <div class="row g-2 align-items-center mb-2 order-item-row">
                        <input type="hidden" name="order_item_id[]" value="{{ $selectedOrderItemId ?: '' }}">
                        <div class="col-md-5">
                            <select name="menu_item_id[]" class="selectpicker menu-item-select" data-live-search="true" data-size="8" data-width="100%" title="Search or select item" required>
                                <option value="">Select item</option>
                                @foreach($menuItems as $item)
                                    @php
                                        $linePrice = $existingLine && (int) $existingLine->menu_item_id === (int) $item->id
                                            ? $existingLine->unit_price
                                            : $item->price;
                                        $availableStock = (int) $item->stock_quantity + (int) ($orderQuantities[$item->id] ?? 0);
                                    @endphp
                                    <option value="{{ $item->id }}" data-price="{{ $linePrice }}" data-stock="{{ $availableStock }}" data-category="{{ $item->category }}" @selected($selectedMenuItemId === $item->id)>
                                        {{ $item->name }} - {{ number_format($item->price, 2) }}{{ $item->category === 'Food' ? '' : ' (stock '.$availableStock.')' }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-2">
                            <input type="number" min="1" name="quantity[]" class="form-control quantity-input" value="{{ $quantities[$i] ?? '' }}" required>
                        </div>
                        <div class="col-md-2">
                            <input type="number" min="0" step="0.01" name="unit_price[]" class="form-control unit-price" value="{{ number_format((float) ($unitPrices[$i] ?? 0), 2, '.', '') }}" required aria-label="Unit price">
                        </div>
                        <div class="col-md-2">
                            <input class="form-control line-total" value="0.00" readonly aria-label="Line total">
                        </div>
                        <div class="col-md-1">
                            <button type="button" class="btn btn-outline-danger remove-order-item" aria-label="Remove item">&times;</button>
                        </div>
                    </div>
                @endfor
            </div>

            <template id="order-item-template">
                <div class="row g-2 align-items-center mb-2 order-item-row">
                    <input type="hidden" name="order_item_id[]" value="">
                    <div class="col-md-5">
                        <select name="menu_item_id[]" class="selectpicker menu-item-select" data-live-search="true" data-size="8" data-width="100%" title="Search or select item" required>
                            <option value="">Select item</option>
                            @foreach($menuItems as $item)
                                @php $availableStock = (int) $item->stock_quantity + (int) ($orderQuantities[$item->id] ?? 0); @endphp
                                <option value="{{ $item->id }}" data-price="{{ $item->price }}" data-stock="{{ $availableStock }}" data-category="{{ $item->category }}">
                                    {{ $item->name }} - {{ number_format($item->price, 2) }}{{ $item->category === 'Food' ? '' : ' (stock '.$availableStock.')' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2"><input type="number" min="1" name="quantity[]" class="form-control quantity-input" value="1" required></div>
                    <div class="col-md-2"><input type="number" min="0" step="0.01" name="unit_price[]" class="form-control unit-price" value="0.00" required aria-label="Unit price"></div>
                    <div class="col-md-2"><input class="form-control line-total" value="0.00" readonly aria-label="Line total"></div>
                    <div class="col-md-1"><button type="button" class="btn btn-outline-danger remove-order-item" aria-label="Remove item">&times;</button></div>
                </div>
            </template>

            <div class="row mt-3">
                <div class="col-md-4 ms-auto">
                    <label for="order_total" class="form-label">New order total</label>
                    <input id="order_total" class="form-control fw-bold" value="0.00" readonly>
                </div>
            </div>

            <div class="mt-4">
                <button type="submit" class="btn btn-primary">Save order</button>
                <a href="{{ route('restaurant-orders.show', $restaurantOrder) }}" class="btn btn-outline-secondary">Cancel</a>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
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
            const unitPrice = row.querySelector('.unit-price');
            const lineTotal = row.querySelector('.line-total');
            const selectedOption = item.selectedOptions[0];
            const price = Number(unitPrice.value || 0);
            const stock = Number(selectedOption?.dataset.stock || 0);
            const isFood = selectedOption?.dataset.category === 'Food';
            const qty = Number(quantity.value || 0);

            lineTotal.value = formatAmount(price * qty);
            total += price * qty;

            if (!isFood && stock > 0) {
                quantity.max = stock;
            } else {
                quantity.removeAttribute('max');
            }
        });

        orderTotal.value = formatAmount(total);
    }

    function bindRow(row) {
        const select = row.querySelector('.menu-item-select');
        const unitPrice = row.querySelector('.unit-price');
        function selectPrice() {
            unitPrice.value = Number(select.selectedOptions[0]?.dataset.price || 0).toFixed(2);
            updateTotal();
        }
        select.addEventListener('change', selectPrice);
        $(select).on('changed.bs.select', selectPrice);
        unitPrice.addEventListener('input', updateTotal);
        row.querySelector('.quantity-input').addEventListener('input', updateTotal);
        row.querySelector('.remove-order-item').addEventListener('click', function () {
            if (items.querySelectorAll('.order-item-row').length === 1) {
                row.querySelector('.menu-item-select').value = '';
                row.querySelector('.quantity-input').value = '';
                row.querySelector('.unit-price').value = '0.00';
                row.querySelector('input[name="order_item_id[]"]').value = '';
                $(row.querySelector('.menu-item-select')).selectpicker('refresh');
            } else {
                row.remove();
            }
            updateTotal();
        });
    }

    items.querySelectorAll('.order-item-row').forEach(function (row) {
        $(row.querySelector('.menu-item-select')).selectpicker();
        bindRow(row);
    });

    document.getElementById('add-order-item').addEventListener('click', function () {
        const row = itemTemplate.content.firstElementChild.cloneNode(true);
        items.appendChild(row);
        $(row.querySelector('.menu-item-select')).selectpicker();
        bindRow(row);
        updateTotal();
    });

    updateTotal();
});
</script>
@endpush

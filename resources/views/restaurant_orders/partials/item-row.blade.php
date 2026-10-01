<div class="row g-2 mb-2 order-item-row">
    <div class="col-md-7">
        <select name="menu_item_id[]" class="selectpicker menu-item-select" data-live-search="true" data-size="8" data-width="100%" title="Search or select item">
            <option value="" data-price="0">Select item</option>
            @foreach($menuItems as $item)
                <option value="{{ $item->id }}" data-price="{{ $item->price }}" data-stock="{{ $item->stock_quantity }}" data-category="{{ $item->category }}">{{ $item->name }} - {{ number_format($item->price, 2) }} - {{ $item->category === 'Food' ? 'Food item' : 'Stock '.$item->stock_quantity }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-2">
        <input type="number" min="1" name="quantity[]" class="form-control quantity-input" value="{{ $quantity }}">
    </div>
    <div class="col-md-3">
        <input class="form-control line-total" value="0.00" readonly>
    </div>
</div>

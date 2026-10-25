<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Lodge;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestaurantOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = trim((string) $request->input('search', ''));
        $restaurantOrders = RestaurantOrder::with('guest', 'room', 'items.menuItem', 'performedBy')
            ->when(! $this->canSeeAllLodges(), fn ($query) => $query->where('lodge_id', auth()->user()?->lodge_id))
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->when($search !== '', function ($query) use ($search) {
                $like = '%' . $search . '%';
                $query->where(function ($matches) use ($like) {
                    $matches->where('order_number', 'like', $like)
                        ->orWhere('walk_in_customer_name', 'like', $like)
                        ->orWhere('status', 'like', $like)
                        ->orWhere('payment_status', 'like', $like)
                        ->orWhereHas('guest', fn ($guest) => $guest->where('full_name', 'like', $like))
                        ->orWhereHas('items.menuItem', fn ($item) => $item->where('name', 'like', $like));
                });
            })
            ->latest('order_date')->latest()
            ->get();

        return view('restaurant_orders.index', compact('restaurantOrders', 'search'));
    }

    public function create(Request $request)
    {
        $guestId = $request->integer('guest_id') ?: null;

        return view('restaurant_orders.create', [
            'restaurantOrder' => new RestaurantOrder(),
            'guests' => $this->lodgeQuery(Guest::query())->orderBy('full_name')->get(),
            'menuItems' => $this->lodgeQuery(MenuItem::where('is_available', true)->where(fn ($query) => $query->where('category', 'Food')->orWhere('stock_quantity', '>', 0)))->orderBy('category')->orderBy('name')->get(),
            'guestId' => $guestId,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_type' => ['required', 'in:Room guest,Walk-in customer'],
            'order_date' => ['required', 'date_format:Y-m-d'],
            'guest_id' => ['nullable', 'exists:guests,id'],
            'payment_method' => ['required', 'in:Cash,LIPA NAMBA,Mobile money,Card,Room charge'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
            'not_paid' => ['nullable', 'boolean'],
            'menu_item_id' => ['required', 'array'],
            'menu_item_id.*' => ['nullable', 'exists:menu_items,id'],
            'quantity' => ['required', 'array'],
            'quantity.*' => ['nullable', 'integer', 'min:1'],
        ]);

        $requestedItems = [];

        foreach ($data['menu_item_id'] as $index => $menuItemId) {
            if (empty($menuItemId) || empty($data['quantity'][$index])) {
                continue;
            }

            $requestedItems[(int) $menuItemId] = ($requestedItems[(int) $menuItemId] ?? 0) + (int) $data['quantity'][$index];
        }

        if (empty($requestedItems)) {
            throw ValidationException::withMessages([
                'menu_item_id' => 'Select at least one item for the order.',
            ]);
        }

        $order = DB::transaction(function () use ($data, $requestedItems) {
            $guest = ! empty($data['guest_id']) ? $this->lodgeQuery(Guest::query())->find($data['guest_id']) : null;
            $booking = null;
            if ($data['customer_type'] === 'Room guest' && $guest) {
                $booking = $this->lodgeQuery(Booking::with('guest', 'room')->where('status', 'Checked In'))
                    ->where('guest_id', $guest->id)
                    ->latest('checked_in_at')
                    ->lockForUpdate()
                    ->first();
            }

            if (($data['customer_type'] === 'Room guest' || $data['payment_method'] === 'Room charge') && ! $booking) {
                throw ValidationException::withMessages([
                    'guest_id' => 'Select a guest with an active room booking for room guest orders or room charges.',
                ]);
            }

            $guestId = $booking?->guest_id ?: ($data['guest_id'] ?? null);
            $lodgeId = $booking?->lodge_id ?: $guest?->lodge_id ?: auth()->user()?->lodge_id ?: Lodge::query()->orderBy('id')->value('id');
            $subtotal = 0;
            $menuItems = $this->lodgeQuery(MenuItem::whereKey(array_keys($requestedItems))->where(fn ($query) => $query->where('lodge_id', $lodgeId)->orWhereNull('lodge_id')))->lockForUpdate()->get()->keyBy('id');

            foreach ($requestedItems as $menuItemId => $quantity) {
                $menuItem = $menuItems->get($menuItemId);

                if (! $menuItem || ! $menuItem->is_available) {
                    throw ValidationException::withMessages([
                        'menu_item_id' => 'One of the selected items is not available.',
                    ]);
                }

                if ($menuItem->category !== 'Food' && $quantity > $menuItem->stock_quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => $menuItem->name.' has only '.$menuItem->stock_quantity.' item(s) in stock.',
                    ]);
                }
            }

            $order = RestaurantOrder::create([
                'order_number' => 'ORD-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'order_date' => $data['order_date'],
                'lodge_id' => $lodgeId,
                'customer_type' => $data['customer_type'],
                'guest_id' => $guestId,
                'booking_id' => $booking?->id,
                'room_id' => $booking?->room_id,
                'payment_method' => $data['payment_method'],
                'status' => 'Pending',
                'created_by' => auth()->id(),
            ]);

            foreach ($data['menu_item_id'] as $index => $menuItemId) {
                if (empty($menuItemId) || empty($data['quantity'][$index])) {
                    continue;
                }

                $menuItem = $menuItems->get((int) $menuItemId);
                $quantity = (int) $data['quantity'][$index];
                $lineTotal = $quantity * $menuItem->price;
                $subtotal += $lineTotal;
                $stockBefore = $menuItem->stock_quantity;
                if ($menuItem->category !== 'Food') {
                    $menuItem->decrement('stock_quantity', $quantity);
                    StockMovement::create([
                    'lodge_id' => $order->lodge_id, 'menu_item_id' => $menuItem->id, 'type' => 'sale',
                    'quantity' => $quantity, 'stock_before' => $stockBefore,
                    'stock_after' => $stockBefore - $quantity, 'unit_price' => $menuItem->price,
                    'reference_type' => RestaurantOrder::class, 'reference_id' => $order->id,
                    'movement_date' => today(), 'created_by' => auth()->id(),
                    ]);
                }

                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'lodge_id' => $order->lodge_id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $menuItem->price,
                    'total_price' => $lineTotal,
                ]);
            }

            // Selecting a payment method means the order was paid in full.
            // Room charge is the exception because it is billed to the room/guest account.
            $paid = ! empty($data['not_paid']) || $data['payment_method'] === 'Room charge' ? 0 : $subtotal;

            $order->update([
                'subtotal' => $subtotal,
                'paid_amount' => $paid,
                'balance_amount' => max(0, $subtotal - $paid),
                'payment_status' => $paid >= $subtotal ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
            ]);

            if ($paid > 0) {
                Payment::create([
                    'payment_number' => 'PAY-'.now()->format('YmdHis').'-'.random_int(100, 999),
                    'payable_type' => RestaurantOrder::class,
                    'payable_id' => $order->id,
                    'guest_id' => $order->guest_id,
                    'booking_id' => $order->booking_id,
                    'restaurant_order_id' => $order->id,
                    'amount' => $paid,
                    'payment_method' => $data['payment_method'],
                    'status' => $paid >= $subtotal ? 'Paid' : 'Partial',
                    'received_by' => auth()->id(),
                    'paid_at' => now(),
                ]);
            }

            return $order;
        });

        return redirect()->route('restaurant-orders.show', $order)->with('success', 'Restaurant order created successfully.');
    }

    public function show(RestaurantOrder $restaurantOrder)
    {
        $restaurantOrder->load('guest', 'booking.room', 'items.menuItem', 'performedBy');

        return view('restaurant_orders.show', compact('restaurantOrder'));
    }

    public function edit(RestaurantOrder $restaurantOrder)
    {
        abort_unless(auth()->user()?->hasRole('hotel_manager'), 403);

        $restaurantOrder->load('items.menuItem', 'payments');
        $existingMenuItemIds = $restaurantOrder->items->pluck('menu_item_id')->all();
        $menuItems = $this->lodgeQuery(MenuItem::query())
            ->where(fn ($query) => $query
                ->whereIn('id', $existingMenuItemIds ?: [0])
                ->orWhere(fn ($available) => $available->where('is_available', true)
                    ->where(fn ($stock) => $stock->where('category', 'Food')->orWhere('stock_quantity', '>', 0))))
            ->where(fn ($query) => $query->where('lodge_id', $restaurantOrder->lodge_id)->orWhereNull('lodge_id'))
            ->orderBy('category')->orderBy('name')->get();

        return view('restaurant_orders.edit', compact('restaurantOrder', 'menuItems'));
    }

    public function update(Request $request, RestaurantOrder $restaurantOrder)
    {
        abort_unless(auth()->user()?->hasRole('hotel_manager'), 403);

        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', RestaurantOrder::STATUSES)],
            'menu_item_id' => ['required', 'array', 'min:1'],
            'menu_item_id.*' => ['nullable', 'integer', 'exists:menu_items,id'],
            'quantity' => ['required', 'array'],
            'quantity.*' => ['nullable', 'integer', 'min:1'],
            'unit_price' => ['required', 'array'],
            'unit_price.*' => ['nullable', 'numeric', 'min:0', 'max:9999999999.99'],
            'order_item_id' => ['nullable', 'array'],
            'order_item_id.*' => ['nullable', 'integer', 'distinct', 'exists:restaurant_order_items,id'],
        ]);

        $lines = [];
        foreach ($data['menu_item_id'] as $index => $menuItemId) {
            $quantity = $data['quantity'][$index] ?? null;
            $unitPrice = $data['unit_price'][$index] ?? null;
            $orderItemId = $data['order_item_id'][$index] ?? null;

            if (empty($menuItemId) && empty($quantity) && empty($orderItemId)) {
                continue;
            }

            if (empty($menuItemId) || empty($quantity) || $unitPrice === null || $unitPrice === '') {
                throw ValidationException::withMessages([
                    'menu_item_id' => 'Choose an item and enter a quantity for every order line.',
                ]);
            }

            $lines[] = [
                'menu_item_id' => (int) $menuItemId,
                'quantity' => (int) $quantity,
                'unit_price' => (float) $unitPrice,
                'order_item_id' => $orderItemId ? (int) $orderItemId : null,
            ];
        }

        if ($lines === []) {
            throw ValidationException::withMessages([
                'menu_item_id' => 'Keep at least one item in the order.',
            ]);
        }

        DB::transaction(function () use ($restaurantOrder, $data, $lines) {
            $order = RestaurantOrder::query()->whereKey($restaurantOrder->id)->lockForUpdate()->firstOrFail();
            $oldItems = $order->items()->with('menuItem')->lockForUpdate()->get();
            $oldItemIds = $oldItems->pluck('id')->all();

            foreach ($lines as $line) {
                if ($line['order_item_id'] && ! in_array($line['order_item_id'], $oldItemIds, true)) {
                    throw ValidationException::withMessages([
                        'menu_item_id' => 'An order line does not belong to this order. Refresh and try again.',
                    ]);
                }
            }

            $itemIds = array_values(array_unique(array_merge(
                $oldItems->pluck('menu_item_id')->all(),
                array_column($lines, 'menu_item_id'),
            )));
            $menuItems = MenuItem::query()->whereKey($itemIds)->lockForUpdate()->get()->keyBy('id');
            $saleMovements = StockMovement::query()
                ->where('reference_type', RestaurantOrder::class)
                ->where('reference_id', $order->id)
                ->where('type', 'sale')
                ->lockForUpdate()->get();

            // Restore exactly the stock this order previously consumed. If a legacy order
            // has incomplete movement history, restore its non-food lines that have no movement.
            $restoredMenuItemIds = [];
            foreach ($saleMovements->groupBy('menu_item_id') as $menuItemId => $movements) {
                $menuItem = $menuItems->get((int) $menuItemId);
                if ($menuItem) {
                    $menuItem->stock_quantity += (int) $movements->sum('quantity');
                    $menuItem->save();
                    $restoredMenuItemIds[] = (int) $menuItemId;
                }
            }
            foreach ($oldItems->groupBy('menu_item_id') as $menuItemId => $items) {
                $menuItem = $menuItems->get((int) $menuItemId);
                if ($menuItem && $menuItem->category !== 'Food' && ! in_array((int) $menuItemId, $restoredMenuItemIds, true)) {
                    $menuItem->stock_quantity += (int) $items->sum('quantity');
                    $menuItem->save();
                }
            }

            if ($oldItemIds !== []) {
                RestaurantOrderItem::query()->whereIn('id', $oldItemIds)->delete();
            }
            StockMovement::query()
                ->where('reference_type', RestaurantOrder::class)
                ->where('reference_id', $order->id)
                ->where('type', 'sale')
                ->delete();

            $newSubtotal = 0;
            foreach ($lines as $line) {
                $menuItem = $menuItems->get($line['menu_item_id']);
                if (! $menuItem
                    || ($menuItem->lodge_id !== null && (int) $menuItem->lodge_id !== (int) $order->lodge_id)
                    || (! $menuItem->is_available && ! in_array($menuItem->id, $oldItems->pluck('menu_item_id')->all(), true))) {
                    throw ValidationException::withMessages([
                        'menu_item_id' => 'One of the selected items is unavailable for this lodge.',
                    ]);
                }

                $unitPrice = $line['unit_price'];
                $lineTotal = round($unitPrice * $line['quantity'], 2);
                if ($lineTotal > 9999999999.99 || $newSubtotal + $lineTotal > 9999999999.99) {
                    throw ValidationException::withMessages([
                        'unit_price' => 'The updated order total exceeds the maximum supported amount.',
                    ]);
                }
                $newSubtotal = round($newSubtotal + $lineTotal, 2);

                if ($menuItem->category !== 'Food') {
                    if ($line['quantity'] > $menuItem->stock_quantity) {
                        throw ValidationException::withMessages([
                            'quantity' => $menuItem->name.' has only '.$menuItem->stock_quantity.' item(s) in stock.',
                        ]);
                    }

                    $stockBefore = $menuItem->stock_quantity;
                    $menuItem->stock_quantity -= $line['quantity'];
                    $menuItem->save();
                    StockMovement::create([
                        'lodge_id' => $order->lodge_id,
                        'menu_item_id' => $menuItem->id,
                        'type' => 'sale',
                        'quantity' => $line['quantity'],
                        'stock_before' => $stockBefore,
                        'stock_after' => $menuItem->stock_quantity,
                        'unit_price' => $unitPrice,
                        'reference_type' => RestaurantOrder::class,
                        'reference_id' => $order->id,
                        'movement_date' => today(),
                        'created_by' => auth()->id(),
                        'notes' => 'Updated restaurant order '.$order->order_number,
                    ]);
                }

                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $line['quantity'],
                    'unit_price' => $unitPrice,
                    'total_price' => $lineTotal,
                ]);
            }

            $paymentRows = $order->payments()->lockForUpdate()->get();
            $paid = round($paymentRows->isNotEmpty()
                ? (float) $paymentRows->whereIn('status', ['Paid', 'Partial'])->sum('amount')
                : (float) $order->paid_amount, 2);

            if ($paid > $newSubtotal) {
                throw ValidationException::withMessages([
                    'menu_item_id' => 'The new order total cannot be less than payments already received ('.number_format($paid, 2).').',
                ]);
            }

            $order->update([
                'status' => $data['status'],
                'subtotal' => $newSubtotal,
                'paid_amount' => $paid,
                'balance_amount' => round(max(0, $newSubtotal - $paid), 2),
                'payment_status' => $paid >= $newSubtotal ? 'Paid' : ($paid > 0 ? 'Partial' : 'Unpaid'),
            ]);
        });

        return redirect()->route('restaurant-orders.index')->with('success', 'Restaurant order updated successfully.');
    }

    public function destroy(RestaurantOrder $restaurantOrder)
    {
        abort_unless(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner', 403);

        DB::transaction(function () use ($restaurantOrder) {
            $restaurantOrder->load('items');
            foreach ($restaurantOrder->items as $item) {
                $menuItem = MenuItem::whereKey($item->menu_item_id)->lockForUpdate()->first();
                if ($menuItem && $menuItem->category !== 'Food') {
                    $menuItem->increment('stock_quantity', $item->quantity);
                }
            }
            Payment::query()
                ->where(function ($query) use ($restaurantOrder) {
                    $query->where('restaurant_order_id', $restaurantOrder->id)
                        ->orWhere(function ($query) use ($restaurantOrder) {
                            $query->where('payable_type', RestaurantOrder::class)
                                ->where('payable_id', $restaurantOrder->id);
                        });
                })
                ->delete();
            StockMovement::where('reference_type', RestaurantOrder::class)
                ->where('reference_id', $restaurantOrder->id)
                ->delete();
            $restaurantOrder->delete();
        });

        return redirect()->route('restaurant-orders.index')->with('success', 'Restaurant order deleted and stock restored successfully.');
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

<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\MenuItem;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RestaurantOrderController extends Controller
{
    public function index(Request $request)
    {
        $restaurantOrders = RestaurantOrder::with('guest', 'room', 'items.menuItem')
            ->when($request->status, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->get();

        return view('restaurant_orders.index', compact('restaurantOrders'));
    }

    public function create(Request $request)
    {
        $guestId = $request->integer('guest_id') ?: null;

        return view('restaurant_orders.create', [
            'restaurantOrder' => new RestaurantOrder(),
            'bookings' => Booking::with('guest', 'room')->where('status', 'Checked In')->get(),
            'guests' => Guest::orderBy('full_name')->get(),
            'menuItems' => MenuItem::where('is_available', true)->where('stock_quantity', '>', 0)->orderBy('category')->orderBy('name')->get(),
            'guestId' => $guestId,
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'customer_type' => ['required', 'in:Room guest,Walk-in customer'],
            'booking_id' => ['nullable', 'exists:bookings,id'],
            'guest_id' => ['nullable', 'exists:guests,id'],
            'walk_in_customer_name' => ['nullable', 'string', 'max:255'],
            'payment_method' => ['required', 'in:Cash,Mobile money,Card,Room charge'],
            'paid_amount' => ['nullable', 'numeric', 'min:0'],
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
            $booking = ! empty($data['booking_id']) ? Booking::with('guest', 'room')->find($data['booking_id']) : null;
            $guestId = $booking?->guest_id ?: ($data['guest_id'] ?? null);
            $subtotal = 0;
            $menuItems = MenuItem::whereKey(array_keys($requestedItems))->lockForUpdate()->get()->keyBy('id');

            foreach ($requestedItems as $menuItemId => $quantity) {
                $menuItem = $menuItems->get($menuItemId);

                if (! $menuItem || ! $menuItem->is_available) {
                    throw ValidationException::withMessages([
                        'menu_item_id' => 'One of the selected items is not available.',
                    ]);
                }

                if ($quantity > $menuItem->stock_quantity) {
                    throw ValidationException::withMessages([
                        'quantity' => $menuItem->name.' has only '.$menuItem->stock_quantity.' item(s) in stock.',
                    ]);
                }
            }

            $order = RestaurantOrder::create([
                'order_number' => 'ORD-'.now()->format('YmdHis').'-'.random_int(100, 999),
                'customer_type' => $data['customer_type'],
                'guest_id' => $guestId,
                'booking_id' => $booking?->id,
                'room_id' => $booking?->room_id,
                'walk_in_customer_name' => $data['walk_in_customer_name'] ?? null,
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
                $menuItem->decrement('stock_quantity', $quantity);

                RestaurantOrderItem::create([
                    'restaurant_order_id' => $order->id,
                    'menu_item_id' => $menuItem->id,
                    'quantity' => $quantity,
                    'unit_price' => $menuItem->price,
                    'total_price' => $lineTotal,
                ]);
            }

            $paid = $data['payment_method'] === 'Room charge' ? 0 : (float) ($data['paid_amount'] ?? 0);

            if ($paid > $subtotal) {
                throw ValidationException::withMessages([
                    'paid_amount' => 'Paid amount cannot exceed the order total of '.number_format($subtotal, 2).'.',
                ]);
            }

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
        $restaurantOrder->load('guest', 'booking.room', 'items.menuItem');

        return view('restaurant_orders.show', compact('restaurantOrder'));
    }

    public function edit(RestaurantOrder $restaurantOrder)
    {
        return view('restaurant_orders.edit', compact('restaurantOrder'));
    }

    public function update(Request $request, RestaurantOrder $restaurantOrder)
    {
        $data = $request->validate([
            'status' => ['required', 'in:'.implode(',', RestaurantOrder::STATUSES)],
            'payment_status' => ['required', 'in:Unpaid,Partial,Paid'],
        ]);

        $restaurantOrder->update($data);

        return redirect()->route('restaurant-orders.index')->with('success', 'Restaurant order updated successfully.');
    }

    public function destroy(RestaurantOrder $restaurantOrder)
    {
        $restaurantOrder->delete();

        return redirect()->route('restaurant-orders.index')->with('success', 'Restaurant order deleted successfully.');
    }
}

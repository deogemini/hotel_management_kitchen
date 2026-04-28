<?php

namespace App\Http\Controllers;

use App\Models\RestaurantOrder;
use Illuminate\Http\Request;

class KitchenOrderController extends Controller
{
    public function index()
    {
        $restaurantOrders = RestaurantOrder::with('items.menuItem', 'room')
            ->whereIn('status', ['Pending', 'Preparing', 'Ready'])
            ->latest()
            ->get();

        return view('kitchen_orders.index', compact('restaurantOrders'));
    }

    public function updateStatus(Request $request, RestaurantOrder $restaurantOrder)
    {
        $data = $request->validate([
            'status' => ['required', 'in:Pending,Preparing,Ready,Served,Cancelled'],
        ]);

        $restaurantOrder->update($data + ['chef_id' => auth()->id()]);

        return back()->with('success', 'Kitchen order status updated.');
    }
}

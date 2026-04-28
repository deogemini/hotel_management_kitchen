<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\Room;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'totalRooms' => Room::count(),
            'availableRooms' => Room::where('status', 'Available')->count(),
            'occupiedRooms' => Room::where('status', 'Occupied')->count(),
            'reservedRooms' => Room::where('status', 'Reserved')->count(),
            'maintenanceRooms' => Room::where('status', 'Maintenance')->count(),
            'currentGuests' => Booking::where('status', 'Checked In')->count(),
            'todayBookings' => Booking::whereDate('created_at', today())->count(),
            'todayRestaurantOrders' => RestaurantOrder::whereDate('created_at', today())->count(),
            'totalCollections' => Payment::whereIn('status', ['Paid', 'Partial'])->sum('amount'),
            'totalRoomRevenue' => Payment::whereNotNull('booking_id')->whereIn('status', ['Paid', 'Partial'])->sum('amount'),
            'totalRestaurantRevenue' => Payment::whereNotNull('restaurant_order_id')->whereIn('status', ['Paid', 'Partial'])->sum('amount'),
            'pendingPayments' => Invoice::whereIn('status', ['Unpaid', 'Partial'])->sum('balance_amount') + Booking::where('balance_amount', '>', 0)->sum('balance_amount'),
        ];

        $todayCheckIns = Booking::with('guest', 'room')->whereDate('check_in_date', today())->latest()->limit(8)->get();
        $todayCheckOuts = Booking::with('guest', 'room')->whereDate('check_out_date', today())->latest()->limit(8)->get();
        $recentPayments = Payment::with('guest')->latest('paid_at')->limit(8)->get();
        $pendingKitchenOrders = RestaurantOrder::with('items.menuItem', 'room')->whereIn('status', ['Pending', 'Preparing'])->latest()->limit(8)->get();

        return view('dashboard', compact('stats', 'todayCheckIns', 'todayCheckOuts', 'recentPayments', 'pendingKitchenOrders'));
    }
}

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
        $rooms = $this->lodgeQuery(Room::query());
        $bookings = $this->lodgeQuery(Booking::query());
        $orders = $this->lodgeQuery(RestaurantOrder::query());
        $payments = $this->lodgeQuery(Payment::query());
        $invoices = $this->lodgeQuery(Invoice::query());

        $stats = [
            'totalRooms' => (clone $rooms)->count(),
            'availableRooms' => (clone $rooms)->where('status', 'Available')->count(),
            'occupiedRooms' => (clone $rooms)->where('status', 'Occupied')->count(),
            'reservedRooms' => (clone $rooms)->where('status', 'Reserved')->count(),
            'maintenanceRooms' => (clone $rooms)->where('status', 'Maintenance')->count(),
            'currentGuests' => (clone $bookings)->where('status', 'Checked In')->count(),
            'todayBookings' => (clone $bookings)->whereDate('created_at', today())->count(),
            'todayRestaurantOrders' => (clone $orders)->whereDate('created_at', today())->count(),
            'totalCollections' => (clone $payments)->whereIn('status', ['Paid', 'Partial'])->sum('amount'),
            'totalRoomRevenue' => (clone $payments)->whereNotNull('booking_id')->whereIn('status', ['Paid', 'Partial'])->sum('amount'),
            'totalRestaurantRevenue' => (clone $payments)->whereNotNull('restaurant_order_id')->whereIn('status', ['Paid', 'Partial'])->sum('amount'),
            'pendingPayments' => (clone $invoices)->whereIn('status', ['Unpaid', 'Partial'])->sum('balance_amount') + (clone $bookings)->where('balance_amount', '>', 0)->sum('balance_amount'),
        ];

        $todayCheckIns = $this->lodgeQuery(Booking::with('guest', 'room'))->whereDate('check_in_date', today())->latest()->limit(8)->get();
        $todayCheckOuts = $this->lodgeQuery(Booking::with('guest', 'room'))->whereDate('check_out_date', today())->latest()->limit(8)->get();
        $recentPayments = $this->lodgeQuery(Payment::with('guest'))->latest('paid_at')->limit(8)->get();
        $pendingKitchenOrders = $this->lodgeQuery(RestaurantOrder::with('items.menuItem', 'room'))->whereIn('status', ['Pending', 'Preparing'])->latest()->limit(8)->get();

        return view('dashboard', compact('stats', 'todayCheckIns', 'todayCheckOuts', 'recentPayments', 'pendingKitchenOrders'));
    }

    private function lodgeQuery($query)
    {
        if (! (auth()->user()?->hasRole('hotel_manager') ?? false)) {
            $query->where('lodge_id', auth()->user()?->lodge_id);
        }

        return $query;
    }
}

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
        $user = $request->user();

        if (! $user?->hasPermission('dashboard.view')) {
            foreach ([
                ['kitchen_orders.view', 'kitchen-orders.index'],
                ['kitchen_orders.update_status', 'kitchen-orders.index'],
                ['bookings.manage', 'bookings.index'],
                ['checkin.manage', 'bookings.index'],
                ['rooms.manage', 'rooms.index'],
                ['guests.manage', 'guests.index'],
                ['companies.manage', 'companies.index'],
                ['restaurant_orders.manage', 'restaurant-orders.index'],
                ['payments.manage', 'payments.index'],
                ['service_charges.manage', 'service-charges.index'],
                ['stocks.manage', 'stocks.index'],
                ['purchases.manage', 'purchases.index'],
                ['expenses.manage', 'expenses.index'],
                ['suppliers.manage', 'suppliers.index'],
                ['menu_items.manage', 'menu-items.index'],
                ['reports.view', 'reports.index'],
                ['lodges.manage', 'lodges.index'],
                ['users.manage', 'users.index'],
                ['settings.sms.manage', 'settings.sms.index'],
                ['settings.invoice.manage', 'settings.invoice.edit'],
                ['audit_trails.view', 'audit_trails.index'],
            ] as [$permission, $route]) {
                if ($user->hasPermission($permission)) {
                    return redirect()->route($route);
                }
            }

            return redirect()->route('profile.edit');
        }

        $rooms = $this->lodgeQuery(Room::query());
        $bookings = $this->lodgeQuery(Booking::query());
        $orders = $this->lodgeQuery(RestaurantOrder::query());
        $payments = $this->lodgeQuery(Payment::query());
        $invoices = $this->lodgeQuery(Invoice::query());
        $occupiedRoomIds = (clone $bookings)->where('status', 'Checked In')->whereNotNull('room_id')->pluck('room_id');
        $reservedRoomIds = (clone $bookings)
            ->whereIn('status', ['Pending', 'Confirmed'])
            ->whereDate('check_out_date', '>=', today())
            ->whereNotNull('room_id')
            ->pluck('room_id');
        $unavailableRoomIds = $occupiedRoomIds->merge($reservedRoomIds)->unique();

        $stats = [
            'totalRooms' => (clone $rooms)->count(),
            'availableRooms' => (clone $rooms)->where('status', '!=', 'Maintenance')->whereNotIn('id', $unavailableRoomIds)->count(),
            'occupiedRooms' => $occupiedRoomIds->count(),
            'reservedRooms' => $reservedRoomIds->diff($occupiedRoomIds)->count(),
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
        $pendingKitchenOrders = $this->lodgeQuery(RestaurantOrder::with('items.menuItem', 'room'))
            ->whereHas('items.menuItem', fn ($query) => $query->where('category', 'Food'))
            ->whereIn('status', ['Pending', 'Preparing'])
            ->latest()
            ->limit(8)
            ->get();

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

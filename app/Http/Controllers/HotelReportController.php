<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\Room;
use Illuminate\Http\Request;

class HotelReportController extends Controller
{
    public function dailyCollections(Request $request)
    {
        $date = $request->input('date', today()->toDateString());
        $rows = Payment::with('guest')->whereDate('paid_at', $date)->latest('paid_at')->get();

        return view('reports.hotel', ['title' => 'Daily Collection Report', 'rows' => $rows, 'type' => 'payments']);
    }

    public function roomBookings()
    {
        return view('reports.hotel', ['title' => 'Room Booking Report', 'rows' => Booking::with('guest', 'room')->latest()->get(), 'type' => 'bookings']);
    }

    public function occupiedRooms()
    {
        return view('reports.hotel', ['title' => 'Occupied Room Report', 'rows' => Room::where('status', 'Occupied')->get(), 'type' => 'rooms']);
    }

    public function availableRooms()
    {
        return view('reports.hotel', ['title' => 'Available Room Report', 'rows' => Room::where('status', 'Available')->get(), 'type' => 'rooms']);
    }

    public function guests()
    {
        return view('reports.hotel', ['title' => 'Guest Report', 'rows' => Guest::withCount('bookings')->latest()->get(), 'type' => 'guests']);
    }

    public function restaurantSales()
    {
        return view('reports.hotel', ['title' => 'Restaurant Sales Report', 'rows' => RestaurantOrder::with('guest')->latest()->get(), 'type' => 'orders']);
    }

    public function payments()
    {
        return view('reports.hotel', ['title' => 'Payment Report', 'rows' => Payment::with('guest')->latest('paid_at')->get(), 'type' => 'payments']);
    }

    public function unpaidBills()
    {
        return view('reports.hotel', ['title' => 'Unpaid Bills Report', 'rows' => Booking::with('guest', 'room')->where('balance_amount', '>', 0)->latest()->get(), 'type' => 'bookings']);
    }
}

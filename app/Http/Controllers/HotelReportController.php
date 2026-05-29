<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Lodge;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HotelReportController extends Controller
{
    public function dailyCollections(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request, today()->toDateString(), today()->toDateString());
        $rows = $this->lodgeQuery(Payment::with('guest'), $request)
            ->whereIn('status', ['Paid', 'Partial'])
            ->whereBetween('paid_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->latest('paid_at')
            ->get();

        return $this->reportView($request, 'Daily Collection Report', $rows, 'payments', $startDate, $endDate);
    }

    public function roomBookings(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(Booking::with('guest', 'room'), $request)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->latest()
            ->get();

        return $this->reportView($request, 'Room Booking Report', $rows, 'bookings', $startDate, $endDate);
    }

    public function occupiedRooms(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request, today()->toDateString(), today()->toDateString());
        $rows = $this->lodgeQuery(Room::query(), $request)
            ->whereHas('bookings', function ($query) use ($startDate, $endDate) {
                $query->whereIn('status', ['Pending', 'Confirmed', 'Checked In', 'Checked Out'])
                    ->whereDate('check_in_date', '<=', $endDate)
                    ->whereDate('check_out_date', '>=', $startDate);
            })
            ->orderBy('room_number')
            ->get();

        return $this->reportView($request, 'Occupied Room Report', $rows, 'rooms', $startDate, $endDate);
    }

    public function availableRooms(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request, today()->toDateString(), today()->toDateString());
        $rows = $this->lodgeQuery(Room::where('status', '!=', 'Maintenance'), $request)
            ->whereDoesntHave('bookings', function ($query) use ($startDate, $endDate) {
                $query->whereIn('status', ['Pending', 'Confirmed', 'Checked In', 'Checked Out'])
                    ->whereDate('check_in_date', '<=', $endDate)
                    ->whereDate('check_out_date', '>=', $startDate);
            })
            ->orderBy('room_number')
            ->get();

        return $this->reportView($request, 'Available Room Report', $rows, 'rooms', $startDate, $endDate);
    }

    public function guests(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(Guest::withCount('bookings'), $request)
            ->whereBetween('created_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->latest()
            ->get();

        return $this->reportView($request, 'Guest Report', $rows, 'guests', $startDate, $endDate);
    }

    public function restaurantSales(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(RestaurantOrder::with('guest'), $request)
            ->whereBetween('created_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->latest()
            ->get();

        return $this->reportView($request, 'Restaurant Sales Report', $rows, 'orders', $startDate, $endDate);
    }

    public function payments(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(Payment::with('guest'), $request)
            ->whereIn('status', ['Paid', 'Partial'])
            ->whereBetween('paid_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->latest('paid_at')
            ->get();

        return $this->reportView($request, 'Payment Report', $rows, 'payments', $startDate, $endDate);
    }

    public function unpaidBills(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(Booking::with('guest', 'room'), $request)
            ->where('balance_amount', '>', 0)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->latest()
            ->get();

        return $this->reportView($request, 'Unpaid Bills Report', $rows, 'bookings', $startDate, $endDate);
    }

    private function dateRange(Request $request, ?string $defaultStart = null, ?string $defaultEnd = null): array
    {
        $request->validate([
            'start_date' => ['nullable', 'date'],
            'end_date' => ['nullable', 'date', 'after_or_equal:start_date'],
            'lodge_id' => ['nullable', 'exists:lodges,id'],
        ]);

        $startDate = $request->input('start_date', $defaultStart ?? now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', $defaultEnd ?? today()->toDateString());

        return [$startDate, $endDate];
    }

    private function reportView(Request $request, string $title, $rows, string $type, string $startDate, string $endDate)
    {
        return view('reports.hotel', [
            'title' => $title,
            'rows' => $rows,
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'lodges' => Lodge::orderBy('name')->get(),
            'selectedLodgeId' => $request->input('lodge_id'),
        ]);
    }

    private function lodgeQuery($query, Request $request)
    {
        if (auth()->user()?->hasRole('hotel_manager') && $request->filled('lodge_id')) {
            return $query->where('lodge_id', $request->input('lodge_id'));
        }

        if (! (auth()->user()?->hasRole('hotel_manager') ?? false)) {
            return $query->where('lodge_id', auth()->user()?->lodge_id);
        }

        return $query;
    }

    private function startOfDay(string $date): Carbon
    {
        return Carbon::parse($date)->startOfDay();
    }

    private function endOfDay(string $date): Carbon
    {
        return Carbon::parse($date)->endOfDay();
    }
}

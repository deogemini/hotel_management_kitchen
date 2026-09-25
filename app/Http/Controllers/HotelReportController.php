<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Lodge;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\StockMovement;
use App\Models\Expense;
use App\Models\OtherCharge;
use App\Models\Purchase;
use App\Models\MenuItem;
use App\Models\Room;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;

class HotelReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function dailyCollections(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request, today()->toDateString(), today()->toDateString());
        $rooms = $this->lodgeQuery(Room::with('lodge'), $request)
            ->orderBy('room_number')
            ->get();
        $payments = $this->lodgeQuery(Payment::with('booking.room'), $request)
            ->whereIn('status', ['Paid', 'Partial'])
            ->whereBetween('paid_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->get();
        $balances = $this->lodgeQuery(Booking::with('room'), $request)
            ->where('balance_amount', '>', 0)
            ->whereDate('check_in_date', '<=', $endDate)
            ->whereDate('check_out_date', '>=', $startDate)
            ->get();

        $cashMovement = $this->cashMovementRows($rooms, $payments, $balances);

        return $this->reportView($request, 'Cash Movement Report', $rooms, 'cash_movement', $startDate, $endDate, [
            'cashMovement' => $cashMovement,
        ]);
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

    public function stockMovements(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(StockMovement::with('menuItem'), $request)
            ->whereBetween('movement_date', [$startDate, $endDate])->latest('movement_date')->latest()->get();
        return $this->reportView($request, 'Stock Movement Report', $rows, 'movements', $startDate, $endDate);
    }

    public function purchases(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(Purchase::with('menuItem'), $request)
            ->whereBetween('purchased_at', [$startDate, $endDate])->latest('purchased_at')->get();
        return $this->reportView($request, 'Purchase Report', $rows, 'purchases', $startDate, $endDate);
    }

    public function foodSales(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(StockMovement::with('menuItem'), $request)
            ->where('type', 'sale')->whereBetween('movement_date', [$startDate, $endDate])
            ->when($request->filled('category'), fn ($query) => $query->whereHas('menuItem', fn ($item) => $item->where('category', $request->category)))
            ->latest('movement_date')->get();
        return $this->reportView($request, 'Food & Drinks Sales Report', $rows, 'food_sales', $startDate, $endDate);
    }

    public function accounting(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rooms = $this->lodgeQuery(Booking::query(), $request)->whereBetween('check_in_date', [$startDate, $endDate])->sum('room_total');
        $restaurant = $this->lodgeQuery(RestaurantOrder::query(), $request)->whereBetween('created_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])->sum('subtotal');
        $services = $this->lodgeQuery(OtherCharge::query(), $request)->whereBetween('created_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])->sum('amount');
        $purchases = $this->lodgeQuery(Purchase::query(), $request)->whereBetween('purchased_at', [$startDate, $endDate])->sum('total_cost');
        $expenses = $this->lodgeQuery(Expense::query(), $request)->whereBetween('spent_at', [$startDate, $endDate])->sum('amount');
        $currentStockValue = $this->lodgeQuery(MenuItem::query(), $request)
            ->whereRaw('LOWER(category) != ?', ['food'])
            ->get()
            ->sum(fn ($item) => (float) $item->stock_quantity * (float) $item->buying_price);
        $revenue = $rooms + $restaurant + $services;
        return view('reports.accounting', compact('startDate','endDate','rooms','restaurant','services','purchases','expenses','revenue','currentStockValue'));
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

    private function reportView(Request $request, string $title, $rows, string $type, string $startDate, string $endDate, array $extra = [])
    {
        return view('reports.hotel', array_merge([
            'title' => $title,
            'rows' => $rows,
            'type' => $type,
            'startDate' => $startDate,
            'endDate' => $endDate,
            'lodges' => Lodge::orderBy('name')->get(),
            'selectedLodgeId' => $request->input('lodge_id'),
        ], $extra));
    }

    private function cashMovementRows($rooms, $payments, $balances): array
    {
        $paymentsByRoom = $payments
            ->filter(fn ($payment) => $payment->booking?->room_id)
            ->groupBy(fn ($payment) => $payment->booking->room_id);
        $balancesByRoom = $balances
            ->filter(fn ($booking) => $booking->room_id)
            ->groupBy('room_id');
        $collectionRows = [];
        $outstandingRows = [];

        foreach ($rooms as $room) {
            $roomPayments = $paymentsByRoom->get($room->id, collect());
            $roomBalances = $balancesByRoom->get($room->id, collect());
            $cash = (float) $roomPayments->where('payment_method', 'Cash')->sum('amount');
            $lipaNamba = (float) $roomPayments->whereIn('payment_method', ['LIPA NAMBA', 'Mobile money', 'Card'])->sum('amount');
            $debtor = (float) $roomBalances->sum('balance_amount');

            $collectionRows[] = [
                'room_number' => $room->room_number,
                'debtor' => $debtor,
                'cash' => $cash,
                'lipa_namba' => $lipaNamba,
                'amount' => $debtor + $cash + $lipaNamba,
            ];
            $outstandingRows[] = [
                'room_number' => $room->room_number,
                'amount' => $debtor,
            ];
        }

        return [
            'collectionRows' => $collectionRows,
            'outstandingRows' => $outstandingRows,
            'totalCollections' => collect($collectionRows)->sum('amount'),
            'totalOutstanding' => collect($outstandingRows)->sum('amount'),
            'totalIncome' => collect($collectionRows)->sum('amount'),
        ];
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

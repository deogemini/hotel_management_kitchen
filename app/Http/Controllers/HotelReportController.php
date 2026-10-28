<?php

namespace App\Http\Controllers;

use App\Models\Booking;
use App\Models\Guest;
use App\Models\Lodge;
use App\Models\Payment;
use App\Models\RestaurantOrder;
use App\Models\RestaurantOrderItem;
use App\Models\StockMovement;
use App\Models\Expense;
use App\Models\OtherCharge;
use App\Models\Purchase;
use App\Models\MenuItem;
use App\Models\Room;
use App\Exports\DrinksSalesReportExport;
use App\Exports\HotelReportExport;
use App\Exports\PurchaseReportExport;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Facades\Excel;

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
        $payments = $this->lodgeQuery(Payment::with('booking.room', 'invoice', 'performedBy'), $request)
            ->forRoomCollection()
            ->whereIn('status', ['Paid', 'Partial'])
            ->whereBetween('paid_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->get();
        $balances = $this->lodgeQuery(Booking::with('room', 'performedBy'), $request)
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
        $rows = $this->lodgeQuery(Booking::with('guest', 'room', 'performedBy'), $request)
            ->whereBetween('check_in_date', [$startDate, $endDate])
            ->latest()
            ->get();

        return $this->reportView($request, 'Room Booking Report', $rows, 'bookings', $startDate, $endDate);
    }

    public function occupiedRooms(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request, today()->toDateString(), today()->toDateString());
        $rows = $this->lodgeQuery(Room::with('performedBy'), $request)
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
        $rows = $this->lodgeQuery(Room::with('performedBy')->where('status', '!=', 'Maintenance'), $request)
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
        $rows = $this->lodgeQuery(Guest::with('performedBy')->withCount('bookings'), $request)
            ->whereBetween('created_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->latest()
            ->get();

        return $this->reportView($request, 'Guest Report', $rows, 'guests', $startDate, $endDate);
    }

    public function restaurantSales(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(RestaurantOrder::with('guest', 'performedBy'), $request)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->latest()
            ->get();

        return $this->reportView($request, 'Restaurant Sales Report', $rows, 'orders', $startDate, $endDate);
    }

    public function stockMovements(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(StockMovement::with('menuItem', 'performedBy'), $request)
            ->whereBetween('movement_date', [$startDate, $endDate])->latest('movement_date')->latest()->get();
        return $this->reportView($request, 'Stock Movement Report', $rows, 'movements', $startDate, $endDate);
    }

    public function purchases(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->purchaseRows($request, $startDate, $endDate)->get();
        $totalCost = $rows->sum('total_cost');

        return $this->reportView($request, 'Purchase Report', $rows, 'purchases', $startDate, $endDate, compact('totalCost'));
    }

    public function purchasesExcel(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $purchases = $this->purchaseRows($request, $startDate, $endDate)->get();
        $filename = "purchase-report-{$startDate}-to-{$endDate}.xlsx";

        return Excel::download(new PurchaseReportExport($purchases), $filename);
    }

    public function reportExcel(Request $request, string $report)
    {
        $view = match ($report) {
            'daily-collections' => $this->dailyCollections($request),
            'room-bookings' => $this->roomBookings($request),
            'occupied-rooms' => $this->occupiedRooms($request),
            'available-rooms' => $this->availableRooms($request),
            'guests' => $this->guests($request),
            'restaurant-sales' => $this->restaurantSales($request),
            'food-sales' => $this->foodSales($request),
            'stock-movements' => $this->stockMovements($request),
            'accounting' => $this->accounting($request),
            'payments' => $this->payments($request),
            'unpaid-bills' => $this->unpaidBills($request),
            default => abort(404),
        };

        $data = $view->getData();
        [$startDate, $endDate] = [$data['startDate'], $data['endDate']];
        $filename = "{$report}-report-{$startDate}-to-{$endDate}.xlsx";

        return Excel::download(
            new HotelReportExport($data['title'] ?? 'Accounting & Financial Statement', $startDate, $endDate, $this->reportExcelSections($report, $data)),
            $filename
        );
    }

    public function foodSales(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(StockMovement::with('menuItem', 'performedBy'), $request)
            ->where('type', 'sale')->whereBetween('movement_date', [$startDate, $endDate])
            ->when($request->filled('category'), fn ($query) => $query->whereHas('menuItem', fn ($item) => $item->where('category', $request->category)))
            ->latest('movement_date')->get();
        return $this->reportView($request, 'Food & Drinks Sales Report', $rows, 'food_sales', $startDate, $endDate);
    }

    public function drinksSales(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $report = $this->drinksSalesData($request, $startDate, $endDate);

        return view('reports.drinks_sales', array_merge($report, compact('startDate', 'endDate'), [
            'lodges' => Lodge::orderBy('name')->get(),
            'selectedLodgeId' => $request->input('lodge_id'),
        ]));
    }

    public function drinksSalesExcel(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $report = $this->drinksSalesData($request, $startDate, $endDate);
        $filename = "drinks-sales-report-{$startDate}-to-{$endDate}.xlsx";

        return Excel::download(
            new DrinksSalesReportExport($startDate, $endDate, $this->drinksSalesLodgeLabel($request), $report),
            $filename
        );
    }

    private function drinksSalesData(Request $request, string $startDate, string $endDate): array
    {
        $drinkPurchases = $this->purchaseRows($request, $startDate, $endDate)
            ->whereHas('menuItem', fn ($query) => $query->where('category', 'Drinks'))
            ->get();

        $drinkSales = RestaurantOrderItem::with('menuItem', 'order')
            ->whereHas('menuItem', fn ($query) => $query->where('category', 'Drinks'))
            ->whereHas('order', function ($query) use ($request, $startDate, $endDate) {
                $this->lodgeQuery($query, $request)
                    ->whereBetween('order_date', [$startDate, $endDate])
                    ->where('status', '!=', 'Cancelled');
            })
            ->get();

        $dailyTotals = [];
        foreach ($drinkPurchases as $purchase) {
            $date = $purchase->purchased_at->format('Y-m-d');
            $dailyTotals[$date] ??= ['date' => $date, 'purchase_cost' => 0, 'sales_total' => 0];
            $dailyTotals[$date]['purchase_cost'] += (float) $purchase->total_cost;
        }
        foreach ($drinkSales as $sale) {
            $date = $sale->order?->order_date?->format('Y-m-d');
            if (! $date) {
                continue;
            }
            $dailyTotals[$date] ??= ['date' => $date, 'purchase_cost' => 0, 'sales_total' => 0];
            $dailyTotals[$date]['sales_total'] += (float) $sale->total_price;
        }
        $dailyTotals = collect($dailyTotals)->sortKeys()->values();

        $averagePurchaseCosts = $this->lodgeQuery(Purchase::query(), $request)
            ->whereDate('purchased_at', '<=', $endDate)
            ->whereHas('menuItem', fn ($query) => $query->where('category', 'Drinks'))
            ->selectRaw('menu_item_id, SUM(quantity) as purchased_quantity, SUM(quantity * unit_cost) as purchase_value')
            ->groupBy('menu_item_id')
            ->get()
            ->mapWithKeys(function ($purchase) {
                $quantity = (int) $purchase->purchased_quantity;

                return [$purchase->menu_item_id => $quantity > 0
                    ? (float) $purchase->purchase_value / $quantity
                    : null];
            });

        $purchasesByItem = $drinkPurchases->groupBy('menu_item_id');
        $salesByItem = $drinkSales->groupBy('menu_item_id');
        $itemIds = $purchasesByItem->keys()->merge($salesByItem->keys())->unique();
        $menuItems = $this->lodgeQuery(MenuItem::query(), $request)
            ->whereIn('id', $itemIds)
            ->orderBy('name')
            ->get()
            ->keyBy('id');

        $drinkRows = $itemIds->map(function ($itemId) use ($menuItems, $purchasesByItem, $salesByItem, $averagePurchaseCosts) {
            $item = $menuItems->get($itemId);
            if (! $item) {
                return null;
            }

            $purchases = $purchasesByItem->get($itemId, collect());
            $sales = $salesByItem->get($itemId, collect());
            $quantityPurchased = (int) $purchases->sum('quantity');
            $purchaseCost = (float) $purchases->sum('total_cost');
            $quantitySold = (int) $sales->sum('quantity');
            $salesTotal = (float) $sales->sum('total_price');
            $averageUnitCost = $averagePurchaseCosts->get($itemId);
            $averageUnitCost = $averageUnitCost === null ? (float) $item->buying_price : (float) $averageUnitCost;
            $costOfSales = $quantitySold * $averageUnitCost;

            return [
                'item' => $item,
                'quantity_purchased' => $quantityPurchased,
                'purchase_cost' => $purchaseCost,
                'quantity_sold' => $quantitySold,
                'sales_total' => $salesTotal,
                'average_unit_cost' => $averageUnitCost,
                'cost_of_sales' => $costOfSales,
                'profit_loss' => $salesTotal - $costOfSales,
            ];
        })->filter()->sortBy(fn ($row) => $row['item']->name)->values();

        $totalPurchaseCost = (float) $drinkPurchases->sum('total_cost');
        $totalSales = (float) $drinkSales->sum('total_price');
        $totalCostOfSales = (float) $drinkRows->sum('cost_of_sales');
        $totalProfitLoss = $totalSales - $totalCostOfSales;

        return compact(
            'dailyTotals', 'drinkRows', 'totalPurchaseCost', 'totalSales',
            'totalCostOfSales', 'totalProfitLoss'
        );
    }

    private function drinksSalesLodgeLabel(Request $request): string
    {
        $user = auth()->user();

        if ($user?->hasRole('hotel_manager')) {
            return $request->filled('lodge_id')
                ? (Lodge::find($request->input('lodge_id'))?->name ?? 'All Lodges')
                : 'All Lodges';
        }

        return Lodge::find($user?->lodge_id)?->name ?? 'Assigned Lodge';
    }

    public function accounting(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rooms = $this->lodgeQuery(Booking::query(), $request)->whereBetween('check_in_date', [$startDate, $endDate])->sum('room_total');
        $restaurantOrderIds = $this->lodgeQuery(RestaurantOrder::query(), $request)
            ->whereBetween('order_date', [$startDate, $endDate])
            ->select('id');
        $foodRevenue = RestaurantOrderItem::query()
            ->whereIn('restaurant_order_id', $restaurantOrderIds)
            ->whereHas('menuItem', fn ($query) => $query->where('category', 'Food'))
            ->sum('total_price');
        $drinksRevenue = RestaurantOrderItem::query()
            ->whereIn('restaurant_order_id', $restaurantOrderIds)
            ->whereHas('menuItem', fn ($query) => $query->where('category', 'Drinks'))
            ->sum('total_price');
        $services = $this->lodgeQuery(OtherCharge::query(), $request)->whereBetween('created_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])->sum('amount');
        $invoicePaymentsQuery = Payment::query()
            ->whereNotNull('invoice_id')
            ->whereIn('status', ['Paid', 'Partial'])
            ->whereBetween('paid_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->whereHas('invoice', function ($query) use ($request) {
                $this->lodgeQuery($query, $request);
            });
        $invoicePaymentsReceived = (clone $invoicePaymentsQuery)->sum('amount');
        $standaloneInvoicePayments = (clone $invoicePaymentsQuery)
            ->whereHas('invoice', fn ($query) => $query->whereNull('booking_id'))
            ->sum('amount');
        $purchases = $this->lodgeQuery(Purchase::query(), $request)->whereBetween('purchased_at', [$startDate, $endDate])->sum('total_cost');
        $expenses = $this->lodgeQuery(Expense::query(), $request)->whereBetween('spent_at', [$startDate, $endDate])->sum('amount');
        $currentStockValue = $this->lodgeQuery(MenuItem::query(), $request)
            ->whereRaw('LOWER(category) != ?', ['food'])
            ->get()
            ->sum(fn ($item) => (float) $item->stock_quantity * (float) $item->buying_price);
        $revenue = $rooms + $foodRevenue + $drinksRevenue + $services + $standaloneInvoicePayments;
        return view('reports.accounting', compact('startDate','endDate','rooms','foodRevenue','drinksRevenue','services','invoicePaymentsReceived','standaloneInvoicePayments','purchases','expenses','revenue','currentStockValue'));
    }

    public function payments(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(Payment::with('guest', 'performedBy'), $request)
            ->whereIn('status', ['Paid', 'Partial'])
            ->whereBetween('paid_at', [$this->startOfDay($startDate), $this->endOfDay($endDate)])
            ->latest('paid_at')
            ->get();

        return $this->reportView($request, 'Payment Report', $rows, 'payments', $startDate, $endDate);
    }

    public function unpaidBills(Request $request)
    {
        [$startDate, $endDate] = $this->dateRange($request);
        $rows = $this->lodgeQuery(Booking::with('guest', 'room', 'performedBy'), $request)
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
        $routeName = $request->route()?->getName() ?? '';

        return view('reports.hotel', array_merge([
            'title' => $title,
            'rows' => $rows,
            'type' => $type,
            'exportReport' => str_starts_with($routeName, 'reports.') ? substr($routeName, strlen('reports.')) : '',
            'startDate' => $startDate,
            'endDate' => $endDate,
            'lodges' => Lodge::orderBy('name')->get(),
            'selectedLodgeId' => $request->input('lodge_id'),
        ], $extra));
    }

    private function reportExcelSections(string $report, array $data): array
    {
        $rows = $data['rows'] ?? collect();

        return match ($report) {
            'daily-collections' => [
                [
                    'title' => 'Collections',
                    'headings' => ['S/N', 'Room', 'Debtor', 'Cash', 'Lipa Namba', 'Amount', 'Collected By'],
                    'rows' => collect($data['cashMovement']['collectionRows'])->values()->map(fn ($row, $index) => [
                        $index + 1, $row['room_label'] ?? 'Room No. '.$row['room_number'],
                        (float) $row['debtor'], (float) $row['cash'], (float) $row['lipa_namba'],
                        (float) $row['amount'], $row['performed_by'],
                    ])->all(),
                    'total' => ['TOTAL COLLECTIONS', null, null, null, null, (float) $data['cashMovement']['totalCollections'], null],
                    'money_columns' => [3, 4, 5, 6],
                    'integer_columns' => [1],
                ],
                [
                    'title' => 'Outstanding Collections',
                    'headings' => ['S/N', 'Room', 'Outstanding Amount', 'Collected By'],
                    'rows' => collect($data['cashMovement']['outstandingRows'])->values()->map(fn ($row, $index) => [
                        count($data['cashMovement']['collectionRows']) + $index + 1,
                        'Room No. '.$row['room_number'], (float) $row['amount'], $row['performed_by'],
                    ])->all(),
                    'total' => ['TOTAL OUTSTANDING', null, (float) $data['cashMovement']['totalOutstanding'], null],
                    'money_columns' => [3],
                    'integer_columns' => [1],
                ],
                [
                    'title' => 'Income Summary',
                    'headings' => ['Metric', 'Amount'],
                    'rows' => [],
                    'total' => ['TOTAL INCOME COLLECTIONS', (float) $data['cashMovement']['totalIncome']],
                    'money_columns' => [2],
                    'integer_columns' => [],
                    'empty_message' => null,
                ],
            ],
            'room-bookings', 'unpaid-bills' => [[
                'title' => $report === 'unpaid-bills' ? 'Unpaid Bills' : 'Room Bookings',
                'headings' => ['Booking', 'Guest', 'Room', 'Status', 'Total', 'Balance', 'Last action by'],
                'rows' => $rows->map(fn ($row) => [
                    $row->booking_number, $row->guest?->full_name, $row->room?->room_number,
                    $row->status, (float) $row->room_total, (float) $row->balance_amount,
                    $row->performedBy?->name ?? 'Not recorded',
                ])->all(),
                'total' => [
                    'TOTAL', null, null, null, (float) $rows->sum('room_total'),
                    (float) $rows->sum('balance_amount'), null,
                ],
                'money_columns' => [5, 6],
                'integer_columns' => [],
            ]],
            'occupied-rooms', 'available-rooms' => [[
                'title' => $report === 'occupied-rooms' ? 'Occupied Rooms' : 'Available Rooms',
                'headings' => ['Room', 'Type', 'Status', 'Price', 'Last action by'],
                'rows' => $rows->map(fn ($row) => [
                    $row->room_number, $row->room_type, $row->status,
                    (float) $row->price_per_night, $row->performedBy?->name ?? 'Not recorded',
                ])->all(),
                'total' => ['TOTAL', null, null, (float) $rows->sum('price_per_night'), null],
                'money_columns' => [4],
                'integer_columns' => [],
            ]],
            'guests' => [[
                'title' => 'Guests',
                'headings' => ['Name', 'Phone', 'Email', 'Bookings', 'Last action by'],
                'rows' => $rows->map(fn ($row) => [
                    $row->full_name, $row->phone_number, $row->email,
                    (int) $row->bookings_count, $row->performedBy?->name ?? 'Not recorded',
                ])->all(),
                'money_columns' => [],
                'integer_columns' => [4],
            ]],
            'restaurant-sales' => [[
                'title' => 'Restaurant Sales',
                'headings' => ['Order', 'Customer', 'Status', 'Total', 'Payment', 'Last action by'],
                'rows' => $rows->map(fn ($row) => [
                    $row->order_number, $row->guest?->full_name ?? $row->walk_in_customer_name,
                    $row->status, (float) $row->subtotal, $row->payment_status,
                    $row->performedBy?->name ?? 'Not recorded',
                ])->all(),
                'total' => ['TOTAL', null, null, (float) $rows->sum('subtotal'), null, null],
                'money_columns' => [4],
                'integer_columns' => [],
            ]],
            'food-sales' => [[
                'title' => 'Food & Drinks Sales',
                'headings' => ['Date', 'Item', 'Category', 'Quantity Sold', 'Unit Price', 'Total Sales', 'Last action by'],
                'rows' => $rows->map(fn ($row) => [
                    $row->movement_date?->format('Y-m-d'), $row->menuItem?->name,
                    $row->menuItem?->category, (int) $row->quantity, (float) $row->unit_price,
                    (float) ($row->quantity * $row->unit_price), $row->performedBy?->name ?? 'Not recorded',
                ])->all(),
                'money_columns' => [5, 6],
                'integer_columns' => [4],
            ]],
            'stock-movements' => [[
                'title' => 'Stock Movements',
                'headings' => ['Date', 'Item', 'Type', 'Quantity', 'Before', 'After', 'Unit Price', 'Last action by'],
                'rows' => $rows->map(fn ($row) => [
                    $row->movement_date?->format('Y-m-d'), $row->menuItem?->name, ucfirst($row->type),
                    (int) $row->quantity, (int) $row->stock_before, (int) $row->stock_after,
                    $row->unit_price !== null ? (float) $row->unit_price : null,
                    $row->performedBy?->name ?? 'Not recorded',
                ])->all(),
                'money_columns' => [7],
                'integer_columns' => [4, 5, 6],
            ]],
            'payments' => [[
                'title' => 'Payments',
                'headings' => ['Receipt', 'Guest', 'Method', 'Amount', 'Date', 'Received by'],
                'rows' => $rows->map(fn ($row) => [
                    $row->payment_number, $row->guest?->full_name, $row->payment_method,
                    (float) $row->amount, $row->paid_at?->format('Y-m-d'),
                    $row->performedBy?->name ?? 'Not recorded',
                ])->all(),
                'total' => ['TOTAL', null, null, (float) $rows->sum('amount'), null, null],
                'money_columns' => [4],
                'integer_columns' => [],
            ]],
            'accounting' => $this->accountingExcelSections($data),
            default => abort(404),
        };
    }

    private function accountingExcelSections(array $data): array
    {
        return [
            [
                'title' => 'Profit & Loss',
                'headings' => ['Metric', 'Amount'],
                'rows' => [
                    ['Room Revenue', (float) $data['rooms']],
                    ['Food Revenue', (float) $data['foodRevenue']],
                    ['Drinks Revenue', (float) $data['drinksRevenue']],
                    ['Other Services', (float) $data['services']],
                    ['Standalone Invoice Payments', (float) $data['standaloneInvoicePayments']],
                    ['Total Revenue', (float) $data['revenue']],
                    ['Purchases / Cost of Stock', (float) $data['purchases']],
                    ['Other Expenses', (float) $data['expenses']],
                ],
                'total' => ['NET PROFIT / (LOSS)', (float) ($data['revenue'] - $data['purchases'] - $data['expenses'])],
                'money_columns' => [2],
                'integer_columns' => [],
            ],
            [
                'title' => 'Financial Statement Summary',
                'headings' => ['Metric', 'Amount'],
                'rows' => [
                    ['Total Income', (float) $data['revenue']],
                    ['Invoice Payments Received', (float) $data['invoicePaymentsReceived']],
                    ['Total Outflows', (float) ($data['purchases'] + $data['expenses'])],
                    ['Current Stock Value', (float) $data['currentStockValue']],
                ],
                'total' => ['Closing Operating Result', (float) ($data['revenue'] - $data['purchases'] - $data['expenses'])],
                'money_columns' => [2],
                'integer_columns' => [],
            ],
        ];
    }

    private function purchaseRows(Request $request, string $startDate, string $endDate)
    {
        return $this->lodgeQuery(Purchase::with('menuItem', 'performedBy'), $request)
            ->whereBetween('purchased_at', [$startDate, $endDate])
            ->latest('purchased_at');
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
                'room_label' => 'Room No. '.$room->room_number,
                'debtor' => $debtor,
                'cash' => $cash,
                'lipa_namba' => $lipaNamba,
                'amount' => $debtor + $cash + $lipaNamba,
                'performed_by' => $roomPayments->pluck('performedBy.name')->filter()->unique()->implode(', ') ?: 'Not recorded',
            ];
            $outstandingRows[] = [
                'room_number' => $room->room_number,
                'amount' => $debtor,
                'performed_by' => $roomBalances->pluck('performedBy.name')->filter()->unique()->implode(', ') ?: 'Not recorded',
            ];
        }

        $unassignedRoomPayments = $payments->filter(fn ($payment) => ! $payment->booking?->room_id);
        if ($unassignedRoomPayments->isNotEmpty()) {
            $cash = (float) $unassignedRoomPayments->where('payment_method', 'Cash')->sum('amount');
            $lipaNamba = (float) $unassignedRoomPayments->whereIn('payment_method', ['LIPA NAMBA', 'Mobile money', 'Card'])->sum('amount');
            $collectionRows[] = [
                'room_number' => null,
                'room_label' => 'Unassigned room payments',
                'debtor' => 0,
                'cash' => $cash,
                'lipa_namba' => $lipaNamba,
                'amount' => $cash + $lipaNamba,
                'performed_by' => $unassignedRoomPayments->pluck('performedBy.name')->filter()->unique()->implode(', ') ?: 'Not recorded',
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

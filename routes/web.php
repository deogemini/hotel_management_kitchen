<?php

use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckInOutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HotelReportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\LodgeController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\RestaurantOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ServiceChargeController;
use App\Http\Controllers\SmsSettingController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }

    return view('auth.login');
});

Route::get('/dashboard', [DashboardController::class, 'index'])->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    Route::middleware('permission:rooms.manage')->group(function () {
        Route::resource('rooms', RoomController::class);
    });

    Route::middleware('permission:guests.manage')->group(function () {
        Route::resource('guests', GuestController::class);
        Route::get('guests/{guest}/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
        Route::post('guests/{guest}/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
        Route::get('invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
        Route::get('invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
        Route::put('invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
        Route::delete('invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    });

    Route::middleware('permission:companies.manage')->group(function () {
        Route::resource('companies', \App\Http\Controllers\CompanyController::class)->except(['show', 'destroy']);
    });

    Route::middleware('permission:bookings.manage')->group(function () {
        Route::resource('bookings', BookingController::class)->except(['index', 'show']);
        Route::get('bookings/{booking}/receipt', [BookingController::class, 'receipt'])->name('bookings.receipt');
        Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
    });

    Route::middleware('permission:bookings.manage,checkin.manage')->group(function () {
        Route::get('bookings', [BookingController::class, 'index'])->name('bookings.index');
        Route::get('bookings/{booking}', [BookingController::class, 'show'])->name('bookings.show');
        Route::post('bookings/{booking}/check-in', [CheckInOutController::class, 'checkIn'])->name('bookings.check-in');
        Route::post('bookings/{booking}/check-out', [CheckInOutController::class, 'checkOut'])->name('bookings.check-out');
    });

    Route::middleware('permission:restaurant_orders.manage')->group(function () {
        Route::resource('restaurant-orders', RestaurantOrderController::class);
    });

    Route::middleware('permission:service_charges.manage')->group(function () {
        Route::resource('service-charges', ServiceChargeController::class)->parameters(['service-charges' => 'serviceCharge'])->only(['index', 'create', 'store', 'show']);
    });

    Route::middleware('permission:stocks.manage')->group(function () {
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::get('stocks/drinks', [StockController::class, 'drinks'])->name('stocks.drinks');
        Route::get('stocks/export/excel', [StockController::class, 'excel'])->name('stocks.export.excel');
        Route::get('stocks/export/pdf', [StockController::class, 'pdf'])->name('stocks.export.pdf');
    });

    Route::middleware('permission:purchases.manage')->group(function () {
        Route::resource('purchases', PurchaseController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
        Route::get('purchases/export/excel', [PurchaseController::class, 'excel'])->name('purchases.export.excel');
        Route::get('purchases/export/pdf', [PurchaseController::class, 'pdf'])->name('purchases.export.pdf');
    });

    Route::middleware('permission:expenses.manage')->group(function () {
        Route::resource('expenses', ExpenseController::class)->only(['index', 'create', 'store', 'edit', 'update', 'destroy']);
    });

    Route::middleware('permission:payments.manage')->group(function () {
        Route::resource('payments', PaymentController::class)->except(['edit', 'update', 'destroy']);
        Route::delete('payments/{payment}', [PaymentController::class, 'destroy'])->name('payments.destroy');
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
    });

    Route::middleware('permission:suppliers.manage')->group(function () {
        Route::resource('suppliers', SupplierController::class)->except(['show']);
    });

    Route::middleware('permission:menu_items.manage')->group(function () {
        Route::resource('menu-items', MenuItemController::class)->except(['show']);
    });

    Route::middleware('permission:lodges.manage')->group(function () {
        Route::resource('lodges', LodgeController::class)->except(['show']);
    });

    Route::middleware('permission:users.manage')->group(function () {
        Route::resource('users', UserController::class);
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::post('users/{user}/unlock-login-lock', [UserController::class, 'unlockLoginLock'])->name('users.unlock-login-lock');
    });

    Route::middleware('permission:audit_trails.view')->group(function () {
        Route::get('audit-trails', [App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit_trails.index');
    });

    Route::middleware('permission:settings.sms.manage')->group(function () {
        Route::get('settings/sms', [SmsSettingController::class, 'index'])->name('settings.sms.index');
        Route::put('settings/sms', [SmsSettingController::class, 'update'])->name('settings.sms.update');
    });

    Route::middleware('permission:settings.invoice.manage')->group(function () {
        Route::get('settings/invoice', [\App\Http\Controllers\InvoiceSettingController::class, 'edit'])->name('settings.invoice.edit');
        Route::put('settings/invoice', [\App\Http\Controllers\InvoiceSettingController::class, 'update'])->name('settings.invoice.update');
    });

    Route::middleware('permission:reports.view')->group(function () {
        Route::get('reports/daily-collections', [HotelReportController::class, 'dailyCollections'])->name('reports.daily-collections');
        Route::get('reports', [HotelReportController::class, 'index'])->name('reports.index');
        Route::get('reports/room-bookings', [HotelReportController::class, 'roomBookings'])->name('reports.room-bookings');
        Route::get('reports/occupied-rooms', [HotelReportController::class, 'occupiedRooms'])->name('reports.occupied-rooms');
        Route::get('reports/available-rooms', [HotelReportController::class, 'availableRooms'])->name('reports.available-rooms');
        Route::get('reports/guests', [HotelReportController::class, 'guests'])->name('reports.guests');
        Route::get('reports/restaurant-sales', [HotelReportController::class, 'restaurantSales'])->name('reports.restaurant-sales');
        Route::get('reports/stock-movements', [HotelReportController::class, 'stockMovements'])->name('reports.stock-movements');
        Route::get('reports/purchases', [HotelReportController::class, 'purchases'])->name('reports.purchases');
        Route::get('reports/food-sales', [HotelReportController::class, 'foodSales'])->name('reports.food-sales');
        Route::get('reports/accounting', [HotelReportController::class, 'accounting'])->name('reports.accounting');
        Route::get('reports/payments', [HotelReportController::class, 'payments'])->name('reports.payments');
        Route::get('reports/unpaid-bills', [HotelReportController::class, 'unpaidBills'])->name('reports.unpaid-bills');
    });

    Route::middleware('permission:kitchen_orders.view,kitchen_orders.update_status')->group(function () {
        Route::get('kitchen-orders', [KitchenOrderController::class, 'index'])->name('kitchen-orders.index');
        Route::get('kitchen-orders/notifications', [KitchenOrderController::class, 'notifications'])->name('kitchen-orders.notifications');
    });

    Route::middleware('permission:kitchen_orders.update_status')->group(function () {
        Route::patch('kitchen-orders/{restaurantOrder}/status', [KitchenOrderController::class, 'updateStatus'])->name('kitchen-orders.update-status');
    });
});

require __DIR__.'/auth.php';

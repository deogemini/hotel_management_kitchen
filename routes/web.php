<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BookingController;
use App\Http\Controllers\CheckInOutController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\GuestController;
use App\Http\Controllers\HotelReportController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\KitchenOrderController;
use App\Http\Controllers\MenuItemController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\RestaurantOrderController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\RoomController;
use App\Http\Controllers\ServiceChargeController;
use App\Http\Controllers\StockController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\SmsSettingController;
use App\Http\Middleware\AdminMiddleware;

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

    Route::middleware('role:hotel_manager,cashier')->group(function () {
        Route::resource('rooms', RoomController::class);
        Route::resource('guests', GuestController::class);
        Route::resource('bookings', BookingController::class);
        Route::get('bookings/{booking}/receipt', [BookingController::class, 'receipt'])->name('bookings.receipt');
        Route::get('bookings/{booking}/invoice', [BookingController::class, 'invoice'])->name('bookings.invoice');
        Route::post('bookings/{booking}/cancel', [BookingController::class, 'cancel'])->name('bookings.cancel');
        Route::post('bookings/{booking}/check-in', [CheckInOutController::class, 'checkIn'])->name('bookings.check-in');
        Route::post('bookings/{booking}/check-out', [CheckInOutController::class, 'checkOut'])->name('bookings.check-out');
        Route::resource('restaurant-orders', RestaurantOrderController::class);
        Route::resource('service-charges', ServiceChargeController::class)->parameters(['service-charges' => 'serviceCharge'])->only(['index', 'create', 'store', 'show']);
        Route::get('stocks', [StockController::class, 'index'])->name('stocks.index');
        Route::patch('stocks/{menuItem}', [StockController::class, 'update'])->name('stocks.update');
        Route::resource('payments', PaymentController::class)->except(['edit', 'update', 'destroy']);
        Route::get('payments/{payment}/receipt', [PaymentController::class, 'receipt'])->name('payments.receipt');
        Route::get('invoices/{invoice}/print', [InvoiceController::class, 'print'])->name('invoices.print');
    });

    Route::middleware('role:hotel_manager')->group(function () {
        Route::resource('menu-items', MenuItemController::class)->except(['show']);
        Route::resource('users', UserController::class);
        Route::get('roles', [RoleController::class, 'index'])->name('roles.index');
        Route::put('roles/{role}', [RoleController::class, 'update'])->name('roles.update');
        Route::post('users/{user}/unlock-login-lock', [UserController::class, 'unlockLoginLock'])->name('users.unlock-login-lock');
        Route::get('audit-trails', [App\Http\Controllers\AuditTrailController::class, 'index'])->name('audit_trails.index');
        Route::get('settings/sms', [SmsSettingController::class, 'index'])->name('settings.sms.index');
        Route::put('settings/sms', [SmsSettingController::class, 'update'])->name('settings.sms.update');

        Route::get('reports/daily-collections', [HotelReportController::class, 'dailyCollections'])->name('reports.daily-collections');
        Route::get('reports/room-bookings', [HotelReportController::class, 'roomBookings'])->name('reports.room-bookings');
        Route::get('reports/occupied-rooms', [HotelReportController::class, 'occupiedRooms'])->name('reports.occupied-rooms');
        Route::get('reports/available-rooms', [HotelReportController::class, 'availableRooms'])->name('reports.available-rooms');
        Route::get('reports/guests', [HotelReportController::class, 'guests'])->name('reports.guests');
        Route::get('reports/restaurant-sales', [HotelReportController::class, 'restaurantSales'])->name('reports.restaurant-sales');
        Route::get('reports/payments', [HotelReportController::class, 'payments'])->name('reports.payments');
        Route::get('reports/unpaid-bills', [HotelReportController::class, 'unpaidBills'])->name('reports.unpaid-bills');
    });

    Route::middleware('role:hotel_manager,chef')->group(function () {
        Route::get('kitchen-orders', [KitchenOrderController::class, 'index'])->name('kitchen-orders.index');
        Route::patch('kitchen-orders/{restaurantOrder}/status', [KitchenOrderController::class, 'updateStatus'])->name('kitchen-orders.update-status');
    });
});

require __DIR__.'/auth.php';

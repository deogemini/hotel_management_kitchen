@extends('layouts.admin')

@section('content')
<div class="card overflow-hidden mb-4">
    <div class="row g-0 align-items-stretch">
        <div class="col-lg-7">
            <div class="card-body p-4 p-xl-5">
                <p class="text-uppercase text-primary fw-semibold mb-2">Hotel Management System</p>
                <h1 class="h2 mb-2">Operations Dashboard</h1>
                <p class="text-muted mb-4">Track room availability, active guests, kitchen orders, payments, and today’s hotel activity from one workspace.</p>
                <div class="d-flex flex-wrap gap-2">
                    <a href="{{ route('bookings.create') }}" class="btn btn-primary">New Booking</a>
                    <a href="{{ route('restaurant-orders.create') }}" class="btn btn-outline-primary">New Restaurant Order</a>
                    <a href="{{ route('payments.create') }}" class="btn btn-outline-secondary">Receive Payment</a>
                </div>
            </div>
        </div>
        <div class="col-lg-5">
            <img src="{{ asset('img/rooms.jpg') }}" alt="Hotel rooms" class="w-100 h-100 object-fit-cover" style="min-height: 260px;">
        </div>
    </div>
</div>

<div class="row">
    @foreach([
        ['Total Rooms', $stats['totalRooms'], 'home', 'primary'],
        ['Available Rooms', $stats['availableRooms'], 'unlock', 'success'],
        ['Occupied Rooms', $stats['occupiedRooms'], 'lock', 'danger'],
        ['Reserved Rooms', $stats['reservedRooms'], 'calendar', 'warning'],
        ['Maintenance', $stats['maintenanceRooms'], 'tool', 'secondary'],
        ['Guests In-House', $stats['currentGuests'], 'users', 'info'],
        ['Bookings Today', $stats['todayBookings'], 'book-open', 'primary'],
        ['Orders Today', $stats['todayRestaurantOrders'], 'coffee', 'primary'],
        ['Collections', number_format($stats['totalCollections'], 2), 'dollar-sign', 'success'],
        ['Room Revenue', number_format($stats['totalRoomRevenue'], 2), 'credit-card', 'success'],
        ['Restaurant Revenue', number_format($stats['totalRestaurantRevenue'], 2), 'shopping-cart', 'success'],
        ['Pending Payments', number_format($stats['pendingPayments'], 2), 'alert-circle', 'warning'],
    ] as [$label, $value, $icon, $color])
    <div class="col-sm-6 col-xl-3">
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col mt-0"><h5 class="card-title">{{ $label }}</h5></div>
                    <div class="col-auto"><div class="stat text-{{ $color }}"><i class="align-middle" data-feather="{{ $icon }}"></i></div></div>
                </div>
                <h1 class="mt-1 mb-3">{{ $value }}</h1>
            </div>
        </div>
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Today Check-Ins</h5></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Guest</th><th>Room</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($todayCheckIns as $booking)
                        <tr><td>{{ $booking->guest->full_name }}</td><td>{{ $booking->room?->room_number }}</td><td>{{ $booking->status }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No check-ins today.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Kitchen Queue</h5></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Order</th><th>Room</th><th>Status</th></tr></thead>
                    <tbody>
                    @forelse($pendingKitchenOrders as $order)
                        <tr><td>{{ $order->order_number }}</td><td>{{ $order->room?->room_number ?? 'Walk-in' }}</td><td>{{ $order->status }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No pending kitchen orders.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Today Check-Outs</h5></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Guest</th><th>Room</th><th>Balance</th></tr></thead>
                    <tbody>
                    @forelse($todayCheckOuts as $booking)
                        <tr><td>{{ $booking->guest->full_name }}</td><td>{{ $booking->room?->room_number }}</td><td>{{ number_format($booking->balance_amount, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No check-outs today.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header"><h5 class="card-title mb-0">Recent Payments</h5></div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Receipt</th><th>Guest</th><th>Amount</th></tr></thead>
                    <tbody>
                    @forelse($recentPayments as $payment)
                        <tr><td>{{ $payment->payment_number }}</td><td>{{ $payment->guest?->full_name ?? '-' }}</td><td>{{ number_format($payment->amount, 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-muted">No payments recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

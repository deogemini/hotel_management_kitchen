@extends('layouts.admin')

@section('content')
@php
    $canViewBookings = auth()->user()?->hasPermission('bookings.manage') || auth()->user()?->hasPermission('checkin.manage');
    $canViewKitchen = auth()->user()?->hasPermission('kitchen_orders.view') || auth()->user()?->hasPermission('kitchen_orders.update_status');
    $canViewPayments = auth()->user()?->hasPermission('payments.manage');
@endphp
@push('styles')
<style>
    .dashboard-stat-link { color: inherit; text-decoration: none; }
    .dashboard-stat-link .card { height: 100%; transition: transform .15s ease, box-shadow .15s ease; }
    .dashboard-stat-link:hover .card { transform: translateY(-2px); box-shadow: 0 .5rem 1.25rem rgba(33, 37, 41, .12); }
    .dashboard-stat-link:focus-visible { outline: 3px solid #3b7ddd; outline-offset: 3px; border-radius: .375rem; }
</style>
@endpush
<div class="card overflow-hidden mb-4">
    <div class="row g-0 align-items-stretch">
        <div class="col-lg-7">
            <div class="card-body p-4 p-xl-5">
                <p class="text-uppercase text-primary fw-semibold mb-2">Hard Rock Executive Lodge</p>
                <h1 class="h2 mb-2">Operations Dashboard</h1>
                <p class="text-muted mb-4">Track room availability, active guests, kitchen orders, payments, and today’s hotel activity from one workspace.</p>
                <div class="d-flex flex-wrap gap-2">
                    @if(auth()->user()?->hasPermission('bookings.manage'))<a href="{{ route('bookings.create') }}" class="btn btn-primary">New Booking</a>@endif
                    @if(auth()->user()?->hasPermission('restaurant_orders.manage'))<a href="{{ route('restaurant-orders.create') }}" class="btn btn-outline-primary">New Restaurant Order</a>@endif
                    @if(auth()->user()?->hasPermission('payments.manage'))<a href="{{ route('payments.create') }}" class="btn btn-outline-secondary">Receive Payment</a>@endif
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
        ['Total Rooms', $stats['totalRooms'], 'home', 'primary', route('rooms.index'), ['rooms.manage']],
        ['Available Rooms', $stats['availableRooms'], 'unlock', 'success', route('rooms.index', ['status' => 'Available']), ['rooms.manage']],
        ['Occupied Rooms', $stats['occupiedRooms'], 'lock', 'danger', route('rooms.index', ['status' => 'Occupied']), ['rooms.manage']],
        ['Reserved Rooms', $stats['reservedRooms'], 'calendar', 'warning', route('rooms.index', ['status' => 'Reserved']), ['rooms.manage']],
        ['Maintenance', $stats['maintenanceRooms'], 'tool', 'secondary', route('rooms.index', ['status' => 'Maintenance']), ['rooms.manage']],
        ['Guests In-House', $stats['currentGuests'], 'users', 'info', route('bookings.index', ['status' => 'Checked In']), ['bookings.manage', 'checkin.manage']],
        ['Bookings Today', $stats['todayBookings'], 'book-open', 'primary', route('bookings.index'), ['bookings.manage', 'checkin.manage']],
        ['Orders Today', $stats['todayRestaurantOrders'], 'coffee', 'primary', route('restaurant-orders.index'), ['restaurant_orders.manage']],
        ['Collections', number_format($stats['totalCollections'], 2), 'dollar-sign', 'success', route('payments.index'), ['payments.manage']],
        ['Room Revenue', number_format($stats['totalRoomRevenue'], 2), 'credit-card', 'success', route('payments.index', ['type' => 'room']), ['payments.manage']],
        ['Restaurant Revenue', number_format($stats['totalRestaurantRevenue'], 2), 'shopping-cart', 'success', route('payments.index'), ['payments.manage']],
        ['Pending Payments', number_format($stats['pendingPayments'], 2), 'alert-circle', 'warning', route('payments.index'), ['payments.manage']],
    ] as [$label, $value, $icon, $color, $href, $permissions])
    @php $canOpenCard = collect($permissions)->contains(fn ($permission) => auth()->user()?->hasPermission($permission)); @endphp
    <div class="col-sm-6 col-xl-3">
        @if($canOpenCard)<a class="dashboard-stat-link d-block h-100" href="{{ $href }}" aria-label="Open {{ $label }} list">@endif
            <div class="card">
                <div class="card-body">
                    <div class="row">
                        <div class="col mt-0"><h5 class="card-title">{{ $label }}</h5></div>
                        <div class="col-auto"><div class="stat text-{{ $color }}"><i class="align-middle" data-feather="{{ $icon }}"></i></div></div>
                    </div>
                    <h1 class="mt-1 mb-3">{{ $value }}</h1>
                    @if($canOpenCard)<span class="small text-primary">View list &rarr;</span>@endif
                </div>
            </div>
        @if($canOpenCard)</a>@endif
    </div>
    @endforeach
</div>

<div class="row">
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Today Check-Ins</h5>
                @if($canViewBookings)<a href="{{ route('bookings.index') }}" class="small">View bookings</a>@endif
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Guest</th><th>Room</th><th>Status</th><th>Last action by</th></tr></thead>
                    <tbody>
                    @forelse($todayCheckIns as $booking)
                        <tr><td>{{ $booking->guest->full_name }}</td><td>{{ $booking->room?->room_number }}</td><td>{{ $booking->status }}</td>@include('partials.performed_by_cell', ['record' => $booking])</tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No check-ins today.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Kitchen Queue</h5>
                @if($canViewKitchen)<a href="{{ route('kitchen-orders.index') }}" class="small">View queue</a>@endif
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Order</th><th>Room</th><th>Status</th><th>Last action by</th></tr></thead>
                    <tbody>
                    @forelse($pendingKitchenOrders as $order)
                        <tr><td>{{ $order->order_number }}</td><td>{{ $order->room?->room_number ?? 'Walk-in' }}</td><td>{{ $order->status }}</td>@include('partials.performed_by_cell', ['record' => $order])</tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No pending kitchen orders.</td></tr>
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
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Today Check-Outs</h5>
                @if($canViewBookings)<a href="{{ route('bookings.index') }}" class="small">View bookings</a>@endif
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Guest</th><th>Room</th><th>Balance</th><th>Last action by</th></tr></thead>
                    <tbody>
                    @forelse($todayCheckOuts as $booking)
                        <tr><td>{{ $booking->guest->full_name }}</td><td>{{ $booking->room?->room_number }}</td><td>{{ number_format($booking->balance_amount, 2) }}</td>@include('partials.performed_by_cell', ['record' => $booking])</tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No check-outs today.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <div class="col-lg-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Recent Payments</h5>
                @if($canViewPayments)<a href="{{ route('payments.index') }}" class="small">View payments</a>@endif
            </div>
            <div class="card-body">
                <table class="table table-sm">
                    <thead><tr><th>Receipt</th><th>Guest</th><th>Amount</th><th>Last action by</th></tr></thead>
                    <tbody>
                    @forelse($recentPayments as $payment)
                        <tr><td>{{ $payment->payment_number }}</td><td>{{ $payment->guest?->full_name ?? '-' }}</td><td>{{ number_format($payment->amount, 2) }}</td>@include('partials.performed_by_cell', ['record' => $payment])</tr>
                    @empty
                        <tr><td colspan="4" class="text-muted">No payments recorded.</td></tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

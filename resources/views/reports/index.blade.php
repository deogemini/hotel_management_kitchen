@extends('layouts.admin')
@section('content')
<div class="mb-4"><h1 class="h3 mb-1">Reports</h1><p class="text-muted mb-0">Select a report to view detailed information.</p></div>
<div class="row g-3">
@php
$reports = [
 ['route'=>'reports.daily-collections','title'=>'Daily Collections','icon'=>'calendar','description'=>'Review daily payments and collections.'],
 ['route'=>'reports.room-bookings','title'=>'Room Bookings','icon'=>'book-open','description'=>'View room reservations and booking activity.'],
 ['route'=>'reports.occupied-rooms','title'=>'Occupied Rooms','icon'=>'home','description'=>'See currently occupied rooms.'],
 ['route'=>'reports.available-rooms','title'=>'Available Rooms','icon'=>'check-circle','description'=>'See rooms currently available.'],
 ['route'=>'reports.guests','title'=>'Guests','icon'=>'users','description'=>'Review guest records and activity.'],
 ['route'=>'reports.restaurant-sales','title'=>'Restaurant Sales','icon'=>'shopping-bag','description'=>'Analyze restaurant sales and revenue.'],
 ['route'=>'reports.food-sales','title'=>'Food & Drinks Sales','icon'=>'coffee','description'=>'Review food and drinks sales.'],
 ['route'=>'reports.purchases','title'=>'Purchases','icon'=>'shopping-cart','description'=>'Review purchases and stock costs.'],
 ['route'=>'reports.stock-movements','title'=>'Stock Movements','icon'=>'repeat','description'=>'Track stock additions and deductions.'],
 ['route'=>'reports.accounting','title'=>'Accounting','icon'=>'bar-chart-2','description'=>'View financial summaries and results.'],
 ['route'=>'reports.payments','title'=>'Payments','icon'=>'credit-card','description'=>'Review recorded payments.'],
 ['route'=>'reports.unpaid-bills','title'=>'Unpaid Bills','icon'=>'alert-circle','description'=>'Find outstanding guest balances.'],
];
@endphp
@foreach($reports as $report)
<div class="col-sm-6 col-xl-4"><a href="{{ route($report['route']) }}" class="card h-100 text-decoration-none shadow-sm"><div class="card-body d-flex align-items-start"><div class="bg-primary-subtle text-primary rounded p-3 me-3"><i data-feather="{{ $report['icon'] }}"></i></div><div><h5 class="card-title text-dark mb-1">{{ $report['title'] }}</h5><p class="text-muted mb-0">{{ $report['description'] }}</p></div></div></a></div>
@endforeach
</div>
@endsection

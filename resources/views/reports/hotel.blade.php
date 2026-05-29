@extends('layouts.admin')
@section('content')
@php
    $moneyTotals = [
        'payments' => ['amount' => $rows->sum('amount')],
        'bookings' => ['room_total' => $rows->sum('room_total'), 'balance_amount' => $rows->sum('balance_amount')],
        'rooms' => ['price_per_night' => $rows->sum('price_per_night')],
        'orders' => ['subtotal' => $rows->sum('subtotal')],
    ];
@endphp
@push('styles')
<style>
    .report-print-header {
        display: none;
    }

    @media print {
        .sidebar,
        .navbar,
        .footer,
        .btn,
        .alert,
        .report-filter,
        .report-screen-title {
            display: none !important;
        }

        .main,
        .content,
        .container-fluid,
        .card,
        .card-body {
            margin: 0 !important;
            padding: 0 !important;
            width: 100% !important;
            box-shadow: none !important;
            border: 0 !important;
        }

        .report-print-header {
            display: block;
            text-align: center;
            margin-bottom: 20px;
        }

        .report-print-header h1 {
            font-size: 22px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .report-print-header h2 {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 4px;
        }

        table {
            width: 100% !important;
        }
    }
</style>
@endpush
<div class="report-print-header">
    <h1>Hard Rock Executive Lodge</h1>
    <h2>{{ $title }}</h2>
    <div>Period: {{ $startDate }} to {{ $endDate }}</div>
    <div>Generated on {{ now()->format('Y-m-d H:i') }}</div>
</div>
<h1 class="h3 mb-3 report-screen-title">{{ $title }}</h1>
<div class="card report-filter"><div class="card-body">
    <form method="GET" class="row g-2 align-items-end">
        <div class="col-md-3">
            <label class="form-label">Start Date</label>
            <input type="date" name="start_date" class="form-control" value="{{ $startDate }}">
        </div>
        <div class="col-md-3">
            <label class="form-label">End Date</label>
            <input type="date" name="end_date" class="form-control" value="{{ $endDate }}">
        </div>
        @if(auth()->user()?->hasRole('hotel_manager'))
        <div class="col-md-3">
            <label class="form-label">Lodge</label>
            <select name="lodge_id" class="form-select">
                <option value="">All Lodges</option>
                @foreach($lodges as $lodge)
                    <option value="{{ $lodge->id }}" @selected((int) $selectedLodgeId === $lodge->id)>{{ $lodge->name }}</option>
                @endforeach
            </select>
        </div>
        @endif
        <div class="col-md-3">
            <button class="btn btn-primary">Pull Report</button>
            <a href="{{ url()->current() }}" class="btn btn-secondary">Reset</a>
        </div>
    </form>
</div></div>
<div class="card"><div class="card-body">
<p class="text-muted report-screen-title">Period: {{ $startDate }} to {{ $endDate }}</p>
<table class="table table-hover"><thead><tr>
@if($type === 'payments')<th>Receipt</th><th>Guest</th><th>Method</th><th>Amount</th><th>Date</th>@endif
@if($type === 'bookings')<th>Booking</th><th>Guest</th><th>Room</th><th>Status</th><th>Total</th><th>Balance</th>@endif
@if($type === 'rooms')<th>Room</th><th>Type</th><th>Status</th><th>Price</th>@endif
@if($type === 'guests')<th>Name</th><th>Phone</th><th>Email</th><th>Bookings</th>@endif
@if($type === 'orders')<th>Order</th><th>Customer</th><th>Status</th><th>Total</th><th>Payment</th>@endif
</tr></thead><tbody>
@foreach($rows as $row)<tr>
@if($type === 'payments')<td>{{ $row->payment_number }}</td><td>{{ $row->guest?->full_name }}</td><td>{{ $row->payment_method }}</td><td>{{ number_format($row->amount, 2) }}</td><td>{{ $row->paid_at?->format('Y-m-d') }}</td>@endif
@if($type === 'bookings')<td>{{ $row->booking_number }}</td><td>{{ $row->guest?->full_name }}</td><td>{{ $row->room?->room_number }}</td><td>{{ $row->status }}</td><td>{{ number_format($row->room_total, 2) }}</td><td>{{ number_format($row->balance_amount, 2) }}</td>@endif
@if($type === 'rooms')<td>{{ $row->room_number }}</td><td>{{ $row->room_type }}</td><td>{{ $row->status }}</td><td>{{ number_format($row->price_per_night, 2) }}</td>@endif
@if($type === 'guests')<td>{{ $row->full_name }}</td><td>{{ $row->phone_number }}</td><td>{{ $row->email }}</td><td>{{ $row->bookings_count }}</td>@endif
@if($type === 'orders')<td>{{ $row->order_number }}</td><td>{{ $row->guest?->full_name ?? $row->walk_in_customer_name }}</td><td>{{ $row->status }}</td><td>{{ number_format($row->subtotal, 2) }}</td><td>{{ $row->payment_status }}</td>@endif
</tr>@endforeach
</tbody>
@if(isset($moneyTotals[$type]))
<tfoot>
<tr class="fw-bold">
@if($type === 'payments')<td colspan="3">Total</td><td>{{ number_format($moneyTotals[$type]['amount'], 2) }}</td><td></td>@endif
@if($type === 'bookings')<td colspan="4">Total</td><td>{{ number_format($moneyTotals[$type]['room_total'], 2) }}</td><td>{{ number_format($moneyTotals[$type]['balance_amount'], 2) }}</td>@endif
@if($type === 'rooms')<td colspan="3">Total</td><td>{{ number_format($moneyTotals[$type]['price_per_night'], 2) }}</td>@endif
@if($type === 'orders')<td colspan="3">Total</td><td>{{ number_format($moneyTotals[$type]['subtotal'], 2) }}</td><td></td>@endif
</tr>
</tfoot>
@endif
</table><button onclick="window.print()" class="btn btn-secondary">Print</button></div></div>
@endsection

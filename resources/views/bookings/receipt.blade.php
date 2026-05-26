@extends('layouts.admin')
@section('content')
@php
    $paidAmount = $booking->payments->sum('amount');
@endphp
@push('styles')
<style>
    @media print {
        .sidebar,
        .navbar,
        .footer,
        .btn,
        .alert {
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

        body {
            background: #fff !important;
        }
    }
</style>
@endpush
<h1 class="h3 mb-3">Booking Receipt</h1>
<div class="card">
    <div class="card-body">
        <div class="text-center mb-4">
            <h2 class="h3 mb-1">Hard Rock Executive Lodge</h2>
            <div>Hotel Booking Receipt</div>
            <div>Issued: {{ now()->format('Y-m-d H:i') }}</div>
        </div>

        <div class="row mb-3">
            <div class="col-md-6">
                <p><strong>Receipt / Booking No:</strong> {{ $booking->booking_number }}</p>
                <p><strong>Guest Name:</strong> {{ $booking->guest->full_name }}</p>
                <p><strong>Status:</strong> {{ $booking->status }}</p>
            </div>
            <div class="col-md-6">
                <p><strong>Room:</strong> {{ $booking->room?->room_number }} {{ $booking->room_type }}</p>
                <p><strong>Check-In Date:</strong> {{ $booking->check_in_date?->format('Y-m-d') }}</p>
                <p><strong>Check-Out Date:</strong> {{ $booking->check_out_date?->format('Y-m-d') }}</p>
            </div>
        </div>

        <table class="table table-bordered">
            <tbody>
                <tr>
                    <th>Room Rate</th>
                    <td class="text-end">{{ number_format($booking->room_rate, 2) }}</td>
                </tr>
                <tr>
                    <th>Nights</th>
                    <td class="text-end">{{ $booking->number_of_nights }}</td>
                </tr>
                <tr>
                    <th>Total</th>
                    <td class="text-end">{{ number_format($booking->room_total, 2) }}</td>
                </tr>
                <tr>
                    <th>Paid</th>
                    <td class="text-end">{{ number_format($paidAmount, 2) }}</td>
                </tr>
                <tr>
                    <th>Balance</th>
                    <td class="text-end">{{ number_format($booking->balance_amount, 2) }}</td>
                </tr>
            </tbody>
        </table>

        <button onclick="window.print()" class="btn btn-primary">Save as PDF / Print</button>
    </div>
</div>
@endsection

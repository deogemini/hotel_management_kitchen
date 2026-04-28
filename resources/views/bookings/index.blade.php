@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Bookings</strong> / Reservations</h1>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Bookings</h5><a href="{{ route('bookings.create') }}" class="btn btn-primary float-end mt-n4">New Booking</a></div><div class="card-body">
<table class="table table-hover"><thead><tr><th>#</th><th>Booking</th><th>Guest</th><th>Room</th><th>Dates</th><th>Status</th><th>Total</th><th>Balance</th><th>Actions</th></tr></thead><tbody>
@foreach($bookings as $booking)<tr><td>{{ $loop->iteration }}</td><td>{{ $booking->booking_number }}</td><td>{{ $booking->guest->full_name }}</td><td>{{ $booking->room?->room_number }}</td><td>{{ $booking->check_in_date?->format('Y-m-d') }} to {{ $booking->check_out_date?->format('Y-m-d') }}</td><td><span class="badge bg-secondary">{{ $booking->status }}</span></td><td>{{ number_format($booking->room_total, 2) }}</td><td>{{ number_format($booking->balance_amount, 2) }}</td><td><a class="btn btn-sm btn-secondary" href="{{ route('bookings.show', $booking) }}">View</a> <a class="btn btn-sm btn-info" href="{{ route('bookings.edit', $booking) }}">Edit</a></td></tr>@endforeach
</tbody></table></div></div>
@endsection

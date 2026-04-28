@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">{{ $guest->full_name }}</h1>
<div class="row"><div class="col-lg-4"><div class="card"><div class="card-body">
<p><strong>Phone:</strong> {{ $guest->phone_number }}</p><p><strong>Email:</strong> {{ $guest->email }}</p><p><strong>ID:</strong> {{ $guest->id_type }} {{ $guest->id_number }}</p><p><strong>Nationality:</strong> {{ $guest->nationality }}</p>
<a href="{{ route('bookings.create', ['guest_id' => $guest->id]) }}" class="btn btn-primary">Create Booking</a>
</div></div></div><div class="col-lg-8"><div class="card"><div class="card-header"><h5 class="card-title mb-0">Booking History</h5></div><div class="card-body"><table class="table"><thead><tr><th>Booking</th><th>Room</th><th>Dates</th><th>Status</th><th>Total</th></tr></thead><tbody>
@foreach($guest->bookings as $booking)<tr><td>{{ $booking->booking_number }}</td><td>{{ $booking->room?->room_number }}</td><td>{{ $booking->check_in_date?->format('Y-m-d') }} to {{ $booking->check_out_date?->format('Y-m-d') }}</td><td>{{ $booking->status }}</td><td>{{ number_format($booking->room_total, 2) }}</td></tr>@endforeach
</tbody></table></div></div></div></div>
@endsection

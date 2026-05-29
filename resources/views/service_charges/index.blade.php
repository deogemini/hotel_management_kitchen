@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Guest</strong> Services</h1>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Laundry, Ironing, and Other Services</h5><a class="btn btn-primary float-end mt-n4" href="{{ route('service-charges.create') }}">Add Service</a></div><div class="card-body">
<table class="table table-hover"><thead><tr><th>#</th><th>Guest</th><th>Booking</th><th>Service</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Action</th></tr></thead><tbody>
@forelse($serviceCharges as $charge)
<tr><td>{{ $loop->iteration }}</td><td>{{ $charge->guest?->full_name }}</td><td>{{ $charge->booking?->booking_number }}</td><td>{{ $charge->service_type }}</td><td>{{ number_format($charge->amount, 2) }}</td><td>{{ number_format($charge->paid_amount, 2) }}</td><td>{{ number_format($charge->balance_amount, 2) }}</td><td>{{ $charge->payment_status }}</td><td><a class="btn btn-sm btn-secondary" href="{{ route('service-charges.show', $charge) }}">View</a></td></tr>
@empty
<tr><td colspan="9" class="text-muted">No guest services recorded.</td></tr>
@endforelse
</tbody></table>
</div></div>
@endsection

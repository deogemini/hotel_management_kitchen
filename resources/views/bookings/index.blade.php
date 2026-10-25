@extends('layouts.admin')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .pagination { justify-content: flex-end; margin-top: 1rem; }
    .dataTables_wrapper .dataTables_paginate .page-item { margin: 0 2px; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { margin-bottom: 1rem; }
</style>
@endpush
@section('content')
<h1 class="h3 mb-3"><strong>Bookings</strong> / Reservations</h1>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Bookings</h5>
        @if(auth()->user()?->hasPermission('bookings.manage'))
            <a href="{{ route('bookings.create') }}" class="btn btn-primary float-end mt-n4">New Booking</a>
        @endif
    </div>
    <div class="card-body">
        <div class="table-responsive">
            <table id="bookingsTable" class="table table-hover w-100">
                <thead>
                    <tr><th>#</th><th>Booking</th><th>Guest</th><th>Room</th><th>Dates</th><th>Status</th><th>Total</th><th>Balance</th><th>Actions</th></tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $booking->booking_number }}</td>
                            <td>{{ $booking->guest->full_name }}</td>
                            <td>{{ $booking->room?->room_number }}</td>
                            <td>{{ $booking->check_in_date?->format('Y-m-d') }} to {{ $booking->check_out_date?->format('Y-m-d') }}</td>
                            <td><span class="badge bg-secondary">{{ $booking->status }}</span></td>
                            <td>{{ number_format($booking->room_total, 2) }}</td>
                            <td>{{ number_format($booking->balance_amount, 2) }}</td>
                            <td>
                                <a class="btn btn-sm btn-secondary" href="{{ route('bookings.show', $booking) }}">View</a>
                                @if(auth()->user()?->hasPermission('bookings.manage'))
                                    <a class="btn btn-sm btn-info" href="{{ route('bookings.edit', $booking) }}">Edit</a>
                                    @if(auth()->user()?->hasRole('Owner', 'owner'))
                                        <form method="POST" action="{{ route('bookings.destroy', $booking) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this booking?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger">Delete</button>
                                        </form>
                                    @endif
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        $('#bookingsTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            order: [],
            columnDefs: [{ orderable: false, targets: [0, 8] }],
            language: {
                search: 'Search bookings:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ bookings',
                infoEmpty: 'No bookings to show',
                zeroRecords: 'No matching bookings found',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            drawCallback: function () {
                const page = this.api().page.info();
                this.api().column(0, { page: 'current' }).nodes().each(function (cell, index) {
                    cell.textContent = page.start + index + 1;
                });
            }
        });
    });
</script>
@endpush

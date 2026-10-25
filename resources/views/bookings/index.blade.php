@extends('layouts.admin')
@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap5.min.css">
<style>
    .dataTables_wrapper .dataTables_paginate .pagination { justify-content: flex-end; margin-top: 1rem; }
    .dataTables_wrapper .dataTables_paginate .page-item { margin: 0 2px; }
    .dataTables_wrapper .dataTables_length,
    .dataTables_wrapper .dataTables_filter { margin-bottom: 1rem; }
    .booking-select-control { width: 1rem; height: 1rem; cursor: pointer; }
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
        <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
            <button id="bulkCheckInButton" type="button" class="btn btn-success" disabled>
                Check In Selected (<span id="checkInSelectionCount">0</span>)
            </button>
            <button id="bulkCheckOutButton" type="button" class="btn btn-warning" disabled>
                Check Out Selected (<span id="checkOutSelectionCount">0</span>)
            </button>
            <span id="bookingSelectionSummary" class="text-muted small" aria-live="polite">Select eligible bookings to use a bulk action.</span>
        </div>

        <form id="bulkCheckInForm" method="POST" action="{{ route('bookings.bulk-check-in') }}" class="d-none">
            @csrf
            <div id="bulkCheckInIds"></div>
        </form>
        <form id="bulkCheckOutForm" method="POST" action="{{ route('bookings.bulk-check-out') }}" class="d-none">
            @csrf
            <div id="bulkCheckOutIds"></div>
        </form>

        <div class="table-responsive">
            <table id="bookingsTable" class="table table-hover w-100">
                <thead>
                    <tr>
                        <th><input id="selectBookingsPage" class="booking-select-control" type="checkbox" aria-label="Select eligible bookings on this page" title="Select eligible bookings on this page"></th>
                        <th>#</th><th>Booking</th><th>Guest</th><th>Room</th><th>Dates</th><th>Status</th><th>Total</th><th>Balance</th><th>Last action by</th><th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $booking)
                        <tr>
                            <td>
                                @if(in_array($booking->status, ['Pending', 'Confirmed'], true) && ! $booking->check_in_date?->isFuture())
                                    <input class="booking-select-control booking-select" type="checkbox" value="{{ $booking->id }}" data-action="check-in" aria-label="Select booking {{ $booking->booking_number }} for check-in">
                                @elseif($booking->status === 'Checked In')
                                    <input class="booking-select-control booking-select" type="checkbox" value="{{ $booking->id }}" data-action="check-out" aria-label="Select booking {{ $booking->booking_number }} for check-out">
                                @else
                                    <span class="text-muted" aria-hidden="true">—</span>
                                @endif
                            </td>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $booking->booking_number }}</td>
                            <td>{{ $booking->guest->full_name }}</td>
                            <td>{{ $booking->room?->room_number }}</td>
                            <td>{{ $booking->check_in_date?->format('Y-m-d') }} to {{ $booking->check_out_date?->format('Y-m-d') }}</td>
                            <td><span class="badge bg-secondary">{{ $booking->status }}</span></td>
                            <td data-total="{{ $booking->room_total }}">{{ number_format($booking->room_total, 2) }}</td>
                            <td data-balance="{{ $booking->balance_amount }}">{{ number_format($booking->balance_amount, 2) }}</td>
                            @include('partials.performed_by_cell', ['record' => $booking])
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
                <tfoot>
                    <tr class="table-light fw-bold">
                        <td colspan="7" class="text-end">TOTAL</td>
                        <td id="bookingTotalAmount" class="text-end">0.00</td>
                        <td id="bookingBalanceTotal" class="text-end">0.00</td>
                        <td colspan="2"></td>
                    </tr>
                </tfoot>
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
        const selectedBookings = new Map();
        const selectionAction = {
            checkIn: 'check-in',
            checkOut: 'check-out'
        };

        function refreshSelectionControls(api) {
            const checkInCount = Array.from(selectedBookings.values()).filter((action) => action === selectionAction.checkIn).length;
            const checkOutCount = Array.from(selectedBookings.values()).filter((action) => action === selectionAction.checkOut).length;

            $('#checkInSelectionCount').text(checkInCount);
            $('#checkOutSelectionCount').text(checkOutCount);
            $('#bulkCheckInButton').prop('disabled', checkInCount === 0);
            $('#bulkCheckOutButton').prop('disabled', checkOutCount === 0);
            $('#bookingSelectionSummary').text(`${selectedBookings.size} booking(s) selected`);

            if (!api) {
                return;
            }

            const currentPageCheckboxes = $(api.rows({ page: 'current' }).nodes()).find('.booking-select').toArray();
            const selectedOnPage = currentPageCheckboxes.filter((checkbox) => selectedBookings.has(checkbox.value)).length;
            const selectPage = document.getElementById('selectBookingsPage');

            selectPage.checked = currentPageCheckboxes.length > 0 && selectedOnPage === currentPageCheckboxes.length;
            selectPage.indeterminate = selectedOnPage > 0 && selectedOnPage < currentPageCheckboxes.length;
        }

        function updateTotals(api) {
            let total = 0;
            let balance = 0;

            $(api.rows({ search: 'applied' }).nodes()).each(function () {
                total += Number(this.cells[7]?.dataset.total || 0);
                balance += Number(this.cells[8]?.dataset.balance || 0);
            });

            const formatAmount = (amount) => amount.toLocaleString(undefined, {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            $('#bookingTotalAmount').text(formatAmount(total));
            $('#bookingBalanceTotal').text(formatAmount(balance));
        }

        const bookingsTable = $('#bookingsTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            order: [],
            columnDefs: [{ orderable: false, targets: [0, 1, 9] }],
            language: {
                search: 'Search bookings:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ bookings',
                infoEmpty: 'No bookings to show',
                zeroRecords: 'No matching bookings found',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            },
            drawCallback: function () {
                const api = this.api();
                const page = api.page.info();

                $(api.rows({ page: 'current' }).nodes()).find('.booking-select').each(function () {
                    this.checked = selectedBookings.has(this.value);
                });
                api.column(1, { page: 'current' }).nodes().each(function (cell, index) {
                    cell.textContent = page.start + index + 1;
                });

                updateTotals(api);
                refreshSelectionControls(api);
            }
        });

        $('#bookingsTable').on('change', '.booking-select', function () {
            if (this.checked) {
                selectedBookings.set(this.value, this.dataset.action);
            } else {
                selectedBookings.delete(this.value);
            }

            refreshSelectionControls(bookingsTable);
        });

        $('#selectBookingsPage').on('change', function () {
            $(bookingsTable.rows({ page: 'current' }).nodes()).find('.booking-select').each((_, checkbox) => {
                if (this.checked) {
                    selectedBookings.set(checkbox.value, checkbox.dataset.action);
                } else {
                    selectedBookings.delete(checkbox.value);
                }
            });

            refreshSelectionControls(bookingsTable);
        });

        function submitBulkAction(action) {
            const ids = Array.from(selectedBookings.entries())
                .filter(([, selectedAction]) => selectedAction === action)
                .map(([id]) => id);

            if (ids.length === 0) {
                return;
            }

            const isCheckIn = action === selectionAction.checkIn;
            const verb = isCheckIn ? 'check in' : 'check out';

            if (!window.confirm(`Are you sure you want to ${verb} ${ids.length} selected booking(s)?`)) {
                return;
            }

            const form = document.getElementById(isCheckIn ? 'bulkCheckInForm' : 'bulkCheckOutForm');
            const inputContainer = document.getElementById(isCheckIn ? 'bulkCheckInIds' : 'bulkCheckOutIds');
            inputContainer.replaceChildren(...ids.map((id) => {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'booking_ids[]';
                input.value = id;
                return input;
            }));
            form.requestSubmit();
        }

        $('#bulkCheckInButton').on('click', () => submitBulkAction(selectionAction.checkIn));
        $('#bulkCheckOutButton').on('click', () => submitBulkAction(selectionAction.checkOut));
    });
</script>
@endpush

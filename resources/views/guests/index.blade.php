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
<h1 class="h3 mb-3"><strong>Guest</strong> Registration</h1>
<a href="{{ route('companies.index') }}" class="btn btn-outline-primary mb-3">Companies / Organizations</a>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Guests</h5><a href="{{ route('guests.create') }}" class="btn btn-primary float-end mt-n4">Register Guest</a></div>
<div class="card-body"><div class="table-responsive"><table id="guestsTable" class="table table-hover w-100"><thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Nationality</th><th>Bookings</th><th>Time Registered</th><th>Actions</th></tr></thead><tbody>
@foreach($guests as $guest)<tr><td>{{ $loop->iteration }}</td><td>{{ $guest->full_name }}</td><td>{{ $guest->phone_number }}</td><td>{{ $guest->email }}</td><td>{{ $guest->nationality }}</td><td>{{ $guest->bookings_count }}</td><td>{{ $guest->created_at?->format('Y-m-d H:i') }}</td><td><a class="btn btn-sm btn-secondary" href="{{ route('guests.show', $guest) }}">History</a> <a class="btn btn-sm btn-info" href="{{ route('guests.edit', $guest) }}">Edit</a> <form method="POST" action="{{ route('guests.destroy', $guest) }}" class="d-inline" onsubmit="return confirm('Are you sure you want to delete this guest?');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">Delete</button></form></td></tr>@endforeach
</tbody></table></div></div></div>
@endsection
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script>
    $(function () {
        $('#guestsTable').DataTable({
            pageLength: 10,
            lengthMenu: [[10, 25, 50, -1], [10, 25, 50, 'All']],
            order: [[6, 'desc']],
            columnDefs: [{ orderable: false, targets: [0, 7] }],
            language: {
                search: 'Search guests:',
                lengthMenu: 'Show _MENU_ entries',
                info: 'Showing _START_ to _END_ of _TOTAL_ guests',
                paginate: { first: 'First', last: 'Last', next: 'Next', previous: 'Previous' }
            }
        });
    });
</script>
@endpush

@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Guest</strong> Registration</h1>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Guests</h5><a href="{{ route('guests.create') }}" class="btn btn-primary float-end mt-n4">Register Guest</a></div>
<div class="card-body"><table class="table table-hover"><thead><tr><th>#</th><th>Name</th><th>Phone</th><th>Email</th><th>Nationality</th><th>Bookings</th><th>Actions</th></tr></thead><tbody>
@foreach($guests as $guest)<tr><td>{{ $loop->iteration }}</td><td>{{ $guest->full_name }}</td><td>{{ $guest->phone_number }}</td><td>{{ $guest->email }}</td><td>{{ $guest->nationality }}</td><td>{{ $guest->bookings_count }}</td><td><a class="btn btn-sm btn-secondary" href="{{ route('guests.show', $guest) }}">History</a> <a class="btn btn-sm btn-info" href="{{ route('guests.edit', $guest) }}">Edit</a></td></tr>@endforeach
</tbody></table></div></div>
@endsection

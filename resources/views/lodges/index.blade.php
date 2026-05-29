@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Lodge</strong> Management</h1>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Lodges</h5><a class="btn btn-primary float-end mt-n4" href="{{ route('lodges.create') }}">Add Lodge</a></div><div class="card-body">
<table class="table table-hover"><thead><tr><th>#</th><th>Name</th><th>Location</th><th>Phone</th><th>Users</th><th>Rooms</th><th>Actions</th></tr></thead><tbody>
@foreach($lodges as $lodge)
<tr><td>{{ $loop->iteration }}</td><td>{{ $lodge->name }}</td><td>{{ $lodge->location }}</td><td>{{ $lodge->phone_number }}</td><td>{{ $lodge->users_count }}</td><td>{{ $lodge->rooms_count }}</td><td><a class="btn btn-sm btn-info" href="{{ route('lodges.edit', $lodge) }}">Edit</a></td></tr>
@endforeach
</tbody></table></div></div>
@endsection

@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3"><strong>Room</strong> Management</h1>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Rooms</h5>
        <a href="{{ route('rooms.create') }}" class="btn btn-primary float-end mt-n4">Add Room</a>
    </div>
    <div class="card-body">
        <form class="row g-2 mb-3">
            <div class="col-md-3"><select name="status" class="form-select"><option value="">All Statuses</option>@foreach(\App\Models\Room::STATUSES as $status)<option value="{{ $status }}" @selected(request('status')===$status)>{{ $status }}</option>@endforeach</select></div>
            <div class="col-md-3"><select name="room_type" class="form-select"><option value="">All Types</option>@foreach(\App\Models\Room::TYPES as $type)<option value="{{ $type }}" @selected(request('room_type')===$type)>{{ $type }}</option>@endforeach</select></div>
            <div class="col-md-2"><button class="btn btn-secondary">Filter</button></div>
        </form>
        <table class="table table-hover">
            <thead><tr><th>#</th><th>Room</th><th>Type</th><th>Price</th><th>Status</th><th>Features</th><th>Actions</th></tr></thead>
            <tbody>
            @foreach($rooms as $room)
                <tr>
                    <td>{{ $loop->iteration }}</td>
                    <td>{{ $room->room_number }}</td>
                    <td>{{ $room->room_type }}</td>
                    <td>{{ number_format($room->price_per_night, 2) }}</td>
                    <td><span class="badge bg-secondary">{{ $room->status }}</span></td>
                    <td>{{ implode(', ', $room->features ?? []) }}</td>
                    <td>
                        <a href="{{ route('rooms.show', $room) }}" class="btn btn-sm btn-secondary">View</a>
                        <a href="{{ route('rooms.edit', $room) }}" class="btn btn-sm btn-info">Edit</a>
                        <form action="{{ route('rooms.destroy', $room) }}" method="POST" class="d-inline">@csrf @method('DELETE')<button class="btn btn-sm btn-danger" onclick="return confirm('Delete this room?')">Delete</button></form>
                    </td>
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

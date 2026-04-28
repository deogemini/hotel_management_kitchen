@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Room {{ $room->room_number }}</h1>
<div class="card"><div class="card-body">
    <p><strong>Type:</strong> {{ $room->room_type }}</p>
    <p><strong>Price:</strong> {{ number_format($room->price_per_night, 2) }}</p>
    <p><strong>Status:</strong> {{ $room->status }}</p>
    <p><strong>Features:</strong> {{ implode(', ', $room->features ?? []) }}</p>
    <p>{{ $room->description }}</p>
    <a href="{{ route('rooms.edit', $room) }}" class="btn btn-primary">Edit</a>
    <a href="{{ route('rooms.index') }}" class="btn btn-secondary">Back</a>
</div></div>
@endsection

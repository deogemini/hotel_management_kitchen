@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Edit Room</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('rooms.update', $room) }}" enctype="multipart/form-data">@method('PUT')@include('rooms._form')</form></div></div>
@endsection

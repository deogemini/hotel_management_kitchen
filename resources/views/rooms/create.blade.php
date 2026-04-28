@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Add Room</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('rooms.store') }}" enctype="multipart/form-data">@include('rooms._form')</form></div></div>
@endsection

@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">New Booking</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('bookings.store') }}">@include('bookings._form')</form></div></div>
@endsection

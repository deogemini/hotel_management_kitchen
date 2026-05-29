@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Add Lodge</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('lodges.store') }}">@include('lodges._form')</form></div></div>
@endsection

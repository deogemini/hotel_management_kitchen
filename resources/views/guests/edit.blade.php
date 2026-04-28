@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Edit Guest</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('guests.update', $guest) }}">@method('PUT')@include('guests._form')</form></div></div>
@endsection

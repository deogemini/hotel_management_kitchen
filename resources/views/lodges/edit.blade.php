@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Edit Lodge</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('lodges.update', $lodge) }}">@method('PUT')@include('lodges._form')</form></div></div>
@endsection

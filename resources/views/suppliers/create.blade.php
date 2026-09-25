@extends('layouts.admin')
@section('content')<h1 class="h3 mb-3">Add Supplier</h1><div class="card"><div class="card-body">@include('suppliers._form', ['action' => route('suppliers.store'), 'method' => 'POST'])</div></div>@endsection

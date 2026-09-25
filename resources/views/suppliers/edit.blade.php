@extends('layouts.admin')
@section('content')<h1 class="h3 mb-3">Edit Supplier</h1><div class="card"><div class="card-body">@include('suppliers._form', ['action' => route('suppliers.update', $supplier), 'method' => 'PUT'])</div></div>@endsection

@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">{{ $company->exists ? 'Edit' : 'Register' }} Company / Organization</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ $company->exists ? route('companies.update', $company) : route('companies.store') }}">
@csrf @if($company->exists) @method('PUT') @endif
@foreach(['name' => 'Company / organization name', 'tin' => 'TIN number', 'phone' => 'Phone number', 'email' => 'Email', 'address' => 'Postal address / location'] as $field => $label)
<div class="mb-3"><label class="form-label" for="{{ $field }}">{{ $label }}</label><input id="{{ $field }}" class="form-control" name="{{ $field }}" type="{{ $field === 'email' ? 'email' : 'text' }}" value="{{ old($field, $company->$field) }}" {{ $field === 'name' ? 'required' : '' }} maxlength="{{ $field === 'address' ? 2000 : 255 }}"></div>
@endforeach
<button class="btn btn-primary">Save Company / Organization</button> <a class="btn btn-secondary" href="{{ route('companies.index') }}">Cancel</a>
</form></div></div>
@endsection

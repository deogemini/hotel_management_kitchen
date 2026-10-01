@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Invoice Settings</h1>
<p>These details appear on new invoices. Saved invoices retain their original details.</p>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('settings.invoice.update') }}">@csrf @method('PUT')
@foreach(['Hotel details' => ['name' => 'Hotel name', 'tin' => 'TIN number', 'phone' => 'Phone number', 'email' => 'Email', 'address' => 'Location / postal address', 'currency' => 'Currency (e.g. TZS)'], 'Merchant payment details' => ['payment_name' => 'Payment account name', 'merchant_number' => 'Merchant number', 'payment_provider' => 'Payment provider'], 'Bank details' => ['bank_name' => 'Bank name', 'account_name' => 'Account name', 'account_number' => 'Account number', 'swift_code' => 'SWIFT code'], 'Payment terms' => ['terms' => 'Terms shown below the invoice']] as $section => $fields)
<h4 class="mt-3">{{ $section }}</h4><div class="row">
@foreach($fields as $field => $label)
<div class="col-md-6 mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label><input class="form-control" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $details[$field]) }}" {{ in_array($field, ['name', 'currency']) ? 'required' : '' }}></div>
@endforeach</div>
@endforeach
<button class="btn btn-primary">Save Invoice Settings</button>
</form></div></div>
@endsection

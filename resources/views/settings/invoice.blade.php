@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Invoice Settings</h1>
<p>These details appear on new invoices. Saved invoices retain their original details.</p>
<div class="card"><div class="card-body"><form method="POST" enctype="multipart/form-data" action="{{ route('settings.invoice.update') }}">@csrf @method('PUT')
<h4>Hotel logo</h4>
@if($logoUrl = \App\Models\InvoiceSetting::logoUrl($details['logo_path'] ?? null))
<div class="mb-3"><img src="{{ $logoUrl }}" alt="Current hotel logo" style="max-width:180px;max-height:120px;object-fit:contain"></div>
<div class="form-check mb-3"><input class="form-check-input" type="checkbox" id="remove-logo" name="remove_logo" value="1" @checked(old('remove_logo'))><label class="form-check-label" for="remove-logo">Remove logo from future invoices</label></div>
@endif
<div class="mb-3"><label class="form-label" for="logo">Upload logo</label><input class="form-control" type="file" id="logo" name="logo" accept="image/png,image/jpeg,image/webp" aria-describedby="logo-help"><div id="logo-help" class="form-text">PNG, JPG or WebP. Maximum 2 MB and 4000 × 4000 pixels. The logo appears at the top of new invoices. Uploading a new image replaces the current logo.</div>@error('logo')<div class="text-danger">{{ $message }}</div>@enderror</div>
@foreach(['Hotel details' => ['name' => 'Hotel name', 'tin' => 'TIN number', 'phone' => 'Phone number', 'email' => 'Email', 'address' => 'Location / postal address', 'currency' => 'Currency (e.g. TZS)'], 'Merchant payment details' => ['payment_name' => 'Payment account name', 'merchant_number' => 'Merchant number', 'payment_provider' => 'Payment provider'], 'Bank details' => ['bank_name' => 'Bank name', 'account_name' => 'Account name', 'account_number' => 'Account number', 'swift_code' => 'SWIFT code'], 'Payment terms' => ['terms' => 'Terms shown below the invoice']] as $section => $fields)
<h4 class="mt-3">{{ $section }}</h4><div class="row">
@foreach($fields as $field => $label)
<div class="col-md-6 mb-3"><label for="{{ $field }}" class="form-label">{{ $label }}</label><input class="form-control" id="{{ $field }}" name="{{ $field }}" value="{{ old($field, $details[$field]) }}" {{ in_array($field, ['name', 'currency']) ? 'required' : '' }}></div>
@endforeach</div>
@endforeach
<button class="btn btn-primary">Save Invoice Settings</button>
</form></div></div>
@endsection

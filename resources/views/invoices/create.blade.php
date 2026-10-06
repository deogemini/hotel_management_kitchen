@extends('layouts.admin')
@section('content')
@php($editing = isset($invoice) && $invoice->exists)
<h1 class="h3 mb-3">{{ $editing ? 'Edit Invoice' : 'Create Invoice' }} - {{ $guest->full_name }}</h1>
<form method="POST" action="{{ $editing ? route('invoices.update', $invoice) : route('invoices.store', $guest) }}">@csrf @if($editing) @method('PUT') @endif
<div class="card"><div class="card-body"><h4>Bill to</h4>
<label for="billing-type" class="form-label">Use customer or company details</label>
<select id="billing-type" name="billing_type" class="form-select mb-3"><option value="guest" @selected(old('billing_type', ($invoice->company_id ?? null) ? 'company' : 'guest') !== 'company')>Customer details</option><option value="company" @selected(old('billing_type', ($invoice->company_id ?? null) ? 'company' : 'guest') === 'company')>Registered company / organization</option></select>
<div id="company-fields"><label for="company-id" class="form-label">Company / organization</label><select id="company-id" name="company_id" class="form-select"><option value="">Choose company / organization</option>@foreach($companies as $company)<option value="{{ $company->id }}" @selected(old('company_id', $invoice->company_id ?? null) == $company->id)>{{ $company->name }} — {{ $company->tin }}</option>@endforeach</select><p class="mt-2">The registered company details will appear on the invoice. @if(auth()->user()?->hasPermission('companies.manage'))<a href="{{ route('companies.create') }}" target="_blank" rel="noopener">Register a company</a>@endif (reload this page afterward).</p></div>
<div id="guest-fields" class="row">
@foreach(['name' => ['Customer name', $guest->full_name], 'tin' => ['TIN number', ''], 'phone' => ['Phone', $guest->phone_number], 'email' => ['Email', $guest->email], 'address' => ['Address / location', $guest->address]] as $field => [$label, $value])
<div class="col-md-6 mb-3"><label class="form-label" for="bill-{{ $field }}">{{ $label }}</label><input class="form-control" id="bill-{{ $field }}" name="bill_to[{{ $field }}]" value="{{ old('bill_to.'.$field, $editing ? ($invoice->bill_to[$field] ?? $value) : $value) }}" type="{{ $field === 'email' ? 'email' : 'text' }}"></div>
@endforeach</div>
<div class="row"><div class="col-md-6 mb-3"><label class="form-label" for="issued-at">Invoice date</label><input type="date" id="issued-at" name="issued_at" class="form-control" value="{{ old('issued_at', $editing ? $invoice->issued_at?->toDateString() : now()->toDateString()) }}" required></div><div class="col-md-6 mb-3"><label class="form-label" for="due-date">Due date</label><input type="date" id="due-date" name="due_date" class="form-control" value="{{ old('due_date', $editing ? $invoice->due_date?->toDateString() : now()->addDays(7)->toDateString()) }}"></div></div>
</div></div>
<div class="card"><div class="card-body"><h4>Invoice items</h4><p>Enter the charges for this invoice. Existing bookings and service bills are not automatically added.</p>
<div class="table-responsive"><table class="table"><thead><tr><th>Description</th><th>Price</th><th>Quantity</th><th>Amount</th><th></th></tr></thead><tbody id="invoice-items">
@foreach(old('items', $editing ? $invoice->items->toArray() : [['description' => '', 'unit_price' => '', 'quantity' => 1]]) as $item)
<tr><td><input aria-label="Description" class="form-control" name="items[{{ $loop->index }}][description]" value="{{ $item['description'] ?? '' }}" required maxlength="255"></td><td><input aria-label="Price" class="form-control price" type="number" min="0.01" max="999999999.99" step="0.01" name="items[{{ $loop->index }}][unit_price]" value="{{ $item['unit_price'] ?? '' }}" required></td><td><input aria-label="Quantity" class="form-control quantity" type="number" min="1" max="1000000" step="1" name="items[{{ $loop->index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required></td><td class="line-total"></td><td><button type="button" class="btn btn-sm btn-outline-danger remove-item">Remove</button></td></tr>
@endforeach
</tbody></table></div><button type="button" id="add-item" class="btn btn-outline-primary">Add Item</button><p class="text-end mt-3"><strong>Total: <span id="invoice-total">0.00</span></strong></p>
<label class="form-label" for="notes">Additional notes (optional)</label><textarea id="notes" name="notes" class="form-control mb-3" maxlength="2000">{{ old('notes', $invoice->notes ?? '') }}</textarea>
<button class="btn btn-primary">{{ $editing ? 'Save Changes' : 'Create Invoice' }}</button> <a class="btn btn-secondary" href="{{ $editing ? route('invoices.show', $invoice) : route('guests.show', $guest) }}">Cancel</a>
</div></div></form>
<script>
document.addEventListener('DOMContentLoaded', () => {
    const type = document.getElementById('billing-type');
    const toggle = () => {
        const company = type.value === 'company';
        document.getElementById('company-fields').hidden = !company;
        document.getElementById('guest-fields').hidden = company;
        document.getElementById('company-id').disabled = !company;
        document.getElementById('company-id').required = company;
        document.querySelectorAll('#guest-fields input').forEach(input => input.disabled = company);
        document.getElementById('bill-name').required = !company;
    };
    type.addEventListener('change', toggle); toggle();
    const body = document.getElementById('invoice-items');
    let next = body.rows.length;
    const recalculate = () => {
        let total = 0;
        Array.from(body.rows).forEach(row => {
            const cents = Math.round(Number(row.querySelector('.price').value) * 100) * Number(row.querySelector('.quantity').value);
            row.querySelector('.line-total').textContent = (cents / 100).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
            total += cents;
        });
        document.getElementById('invoice-total').textContent = (total / 100).toLocaleString('en', {minimumFractionDigits: 2, maximumFractionDigits: 2});
    };
    body.addEventListener('input', recalculate);
    body.addEventListener('click', event => { if (event.target.classList.contains('remove-item') && body.rows.length > 1) {event.target.closest('tr').remove(); recalculate();} });
    document.getElementById('add-item').addEventListener('click', () => {
        if (body.rows.length >= 100) return;
        const row = body.rows[0].cloneNode(true);
        row.querySelectorAll('input').forEach(input => { input.name = input.name.replace(/items\[\d+\]/, `items[${next}]`); input.value = input.classList.contains('quantity') ? '1' : ''; });
        next++; body.appendChild(row); recalculate();
    });
    recalculate();
});
</script>
@endsection

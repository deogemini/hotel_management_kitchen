@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Invoice {{ $invoice->invoice_number }}</h1>
<div class="d-flex flex-wrap gap-2 mb-3">
@include('invoices._owner_actions')
<a class="btn btn-secondary" href="{{ route('payments.index') }}">Back to Payments</a>
<a class="btn btn-outline-primary" href="{{ route('guests.show', $invoice->guest_id) }}">Guest History</a>
<a class="btn btn-outline-secondary" href="{{ route('invoices.print', $invoice) }}">Print / Save PDF</a>
@if(in_array($invoice->status, ['Unpaid', 'Partial']) && $invoice->balance_amount > 0)
<a class="btn btn-primary" href="{{ route('payments.create', ['target_type' => 'invoice', 'target_id' => $invoice->id]) }}">Confirm Payment</a>
@endif
</div>
<div class="card"><div class="card-body">
<div class="row"><div class="col-md-6"><h5>Bill to</h5><p>{{ $invoice->bill_to['name'] ?? $invoice->guest?->full_name }}</p><p>Guest: {{ $invoice->guest?->full_name }}</p></div>
<div class="col-md-6"><p>Date: {{ $invoice->issued_at?->format('Y-m-d') }} @if($invoice->due_date) | Due: {{ $invoice->due_date->format('Y-m-d') }} @endif</p><p><strong>Status: {{ $invoice->status }}</strong></p></div></div>
<div class="table-responsive"><table class="table"><thead><tr><th>Description</th><th>Quantity</th><th>Price</th><th>Amount</th></tr></thead><tbody>
@foreach($invoice->items as $item)<tr><td>{{ $item->description }}</td><td>{{ $item->quantity }}</td><td>{{ number_format($item->unit_price, 2) }}</td><td>{{ number_format($item->total_price, 2) }}</td></tr>@endforeach
</tbody></table></div>
<p class="text-end"><strong>Total: {{ number_format($invoice->subtotal, 2) }} | Paid: {{ number_format($invoice->paid_amount, 2) }} | Balance: {{ number_format($invoice->balance_amount, 2) }} {{ $invoice->issuer_details['currency'] ?? 'TZS' }}</strong></p>
@if($invoice->notes)<p style="white-space:pre-line">{{ $invoice->notes }}</p>@endif
</div></div>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Invoice Payments</h5></div><div class="card-body table-responsive"><table class="table"><thead><tr><th>Receipt</th><th>Date</th><th>Method</th><th>Reference</th><th>Amount</th><th></th></tr></thead><tbody>
@forelse($invoice->payments as $payment)<tr><td>{{ $payment->payment_number }}</td><td>{{ $payment->paid_at?->format('Y-m-d H:i') }}</td><td>{{ $payment->payment_method }}</td><td>{{ $payment->reference_number }}</td><td>{{ number_format($payment->amount, 2) }}</td><td><a href="{{ route('payments.receipt', $payment) }}" class="btn btn-sm btn-secondary">Receipt</a></td></tr>
@empty<tr><td colspan="6">No payments confirmed for this invoice yet.</td></tr>@endforelse
</tbody></table></div></div>
@endsection

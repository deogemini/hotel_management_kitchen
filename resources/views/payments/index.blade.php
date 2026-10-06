@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Payment</strong> Management</h1>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Saved Invoices</h5></div><div class="card-body table-responsive">
<table class="table"><thead><tr><th>Invoice</th><th>Bill to</th><th>Date</th><th>Total</th><th>Paid</th><th>Balance</th><th>Status</th><th>Actions</th></tr></thead><tbody>
@forelse($invoices as $invoice)
<tr><td>{{ $invoice->invoice_number }}</td><td>{{ $invoice->bill_to['name'] ?? $invoice->guest?->full_name }}</td><td>{{ $invoice->issued_at?->format('Y-m-d') }}</td><td>{{ number_format($invoice->subtotal, 2) }}</td><td>{{ number_format($invoice->paid_amount, 2) }}</td><td>{{ number_format($invoice->balance_amount, 2) }}</td><td>{{ $invoice->status }}</td><td>@if(auth()->user()?->hasPermission('guests.manage'))<a class="btn btn-sm btn-secondary" href="{{ route('invoices.show', $invoice) }}">Open Invoice</a>@include('invoices._owner_actions')@endif @if(in_array($invoice->status, ['Unpaid', 'Partial']) && $invoice->balance_amount > 0)<a class="btn btn-sm btn-primary" href="{{ route('payments.create', ['target_type' => 'invoice', 'target_id' => $invoice->id]) }}">Confirm Payment</a>@endif</td></tr>
@empty<tr><td colspan="8">No saved invoices found.</td></tr>@endforelse
</tbody></table></div></div>
<div class="card">
    <div class="card-header">
        <h5 class="card-title mb-0">Payments</h5>
        <a href="{{ route('payments.create') }}" class="btn btn-primary float-end mt-n4">Receive Payment</a>
    </div>
    <div class="card-body">
        <table class="table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Receipt</th>
                    <th>Customer</th>
                    <th>For</th>
                    <th>Method</th>
                    <th>Amount</th>
                    <th>Timeline</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @foreach($payments as $payment)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $payment->payment_number }}</td>
                        <td>{{ $payment->customerName() }}</td>
                        <td>{{ $payment->purposeLabel() }}</td>
                        <td>{{ $payment->payment_method }}</td>
                        <td>{{ number_format($payment->amount, 2) }}</td>
                        <td>{{ $payment->paid_at?->format('Y-m-d H:i') }}</td>
                        <td><a class="btn btn-sm btn-secondary" href="{{ route('payments.receipt', $payment) }}">Receipt</a> @if(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner')<form method="POST" action="{{ route('payments.destroy', $payment) }}" class="d-inline" onsubmit="return confirm('Delete this payment? The related balance will be restored.');">@csrf @method('DELETE')<button type="submit" class="btn btn-sm btn-danger">Delete</button></form>@endif</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr class="fw-bold">
                    <td colspan="5" class="text-end">Total</td>
                    <td>{{ number_format($payments->sum('amount'), 2) }}</td>
                    <td colspan="2"></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endsection

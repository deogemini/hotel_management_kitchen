@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Payment Receipt</h1>
<div class="card">
    <div class="card-body">
        <h4>{{ $payment->payment_number }}</h4>
        <p>Customer: {{ $payment->customerName() }}</p>
        <p>For: {{ $payment->purposeLabel() }}</p>
        <p>Method: {{ $payment->payment_method }}</p>
        <p>Amount: {{ number_format($payment->amount, 2) }}</p>
        <p>Date: {{ $payment->paid_at?->format('Y-m-d H:i') }}</p>
        <button onclick="window.print()" class="btn btn-primary">Print</button>
    </div>
</div>
@endsection

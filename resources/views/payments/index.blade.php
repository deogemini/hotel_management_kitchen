@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Payment</strong> Management</h1>
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
                    <th>Date</th>
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
        </table>
    </div>
</div>
@endsection

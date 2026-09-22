@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Other Money Usage</h1><a href="{{ route('expenses.create') }}" class="btn btn-primary">Record Expense</a></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Payment Method</th><th>Amount</th></tr></thead><tbody>
@forelse($expenses as $expense)<tr><td>{{ $expense->spent_at->format('Y-m-d') }}</td><td>{{ $expense->category }}</td><td>{{ $expense->description }}</td><td>{{ $expense->payment_method }}</td><td>{{ number_format($expense->amount, 2) }}</td></tr>@empty<tr><td colspan="5" class="text-muted">No expenses recorded.</td></tr>@endforelse
</tbody></table></div></div></div>
@endsection

@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Other Money Usage</h1><a href="{{ route('expenses.create') }}" class="btn btn-primary">Record Expense</a></div>
<div class="card"><div class="card-body"><div class="table-responsive"><table class="table table-hover"><thead><tr><th>Date</th><th>Category</th><th>Description</th><th>Payment Method</th><th>Amount</th><th>Actions</th></tr></thead><tbody>
@forelse($expenses as $expense)<tr><td>{{ $expense->spent_at->format('Y-m-d') }}</td><td>{{ $expense->category }}</td><td>{{ $expense->description }}</td><td>{{ $expense->payment_method }}</td><td>{{ number_format($expense->amount, 2) }}</td><td>@if(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner')<form method="POST" action="{{ route('expenses.destroy', $expense) }}" onsubmit="return confirm('Delete this expense?');">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form>@endif</td></tr>@empty<tr><td colspan="6" class="text-muted">No expenses recorded.</td></tr>@endforelse
</tbody>@if($expenses->isNotEmpty())<tfoot><tr class="fw-bold table-light"><td colspan="4" class="text-end">Total Expenses:</td><td>{{ number_format($totalExpenses, 2) }}</td><td></td></tr></tfoot>@endif</table></div></div></div>
@if(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner')
<script>document.querySelectorAll('form[action*="/expenses/"]').forEach(function(form){var link=document.createElement('a');link.href=form.action+'/edit';link.className='btn btn-sm btn-info me-1';link.textContent='Edit';form.parentElement.insertBefore(link,form);});</script>
@endif
@endsection

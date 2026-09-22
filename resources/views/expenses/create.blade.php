@extends('layouts.admin')
@section('content')<h1 class="h3 mb-3">Record Other Money Usage</h1><div class="card"><div class="card-body"><form method="POST" action="{{ route('expenses.store') }}">@csrf<div class="row g-3">
<div class="col-md-4"><label class="form-label">Category</label><input name="category" class="form-control" placeholder="Transport, utilities, repairs..." required></div>
<div class="col-md-4"><label class="form-label">Amount</label><input type="number" name="amount" step="0.01" min="0.01" class="form-control" required></div>
<div class="col-md-4"><label class="form-label">Date</label><input type="date" name="spent_at" value="{{ today()->toDateString() }}" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Description</label><input name="description" class="form-control" required></div>
<div class="col-md-6"><label class="form-label">Payment Method</label><select name="payment_method" class="form-select"><option>Cash</option><option>Mobile money</option><option>Card</option></select></div></div><button class="btn btn-primary mt-3">Save Expense</button> <a href="{{ route('expenses.index') }}" class="btn btn-outline-secondary mt-3">Cancel</a></form></div></div>@endsection

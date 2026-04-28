@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Receive Payment</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('payments.store') }}">@csrf
<div class="row">
<div class="col-md-4 mb-3"><label class="form-label">Target Type</label><select name="target_type" class="form-select"><option value="booking" @selected($targetType==='booking')>Booking</option><option value="restaurant_order" @selected($targetType==='restaurant_order')>Restaurant Order</option><option value="invoice" @selected($targetType==='invoice')>Invoice</option></select></div>
<div class="col-md-8 mb-3"><label class="form-label">Target ID</label><input name="target_id" class="form-control" value="{{ $targetId }}" required><small class="text-muted">Use the ID from the booking, restaurant order, or invoice link.</small></div>
<div class="col-md-4 mb-3"><label class="form-label">Amount</label><input type="number" step="0.01" name="amount" class="form-control" required></div>
<div class="col-md-4 mb-3"><label class="form-label">Method</label><select name="payment_method" class="form-select"><option>Cash</option><option>Mobile money</option><option>Card</option><option>Room charge</option></select></div>
<div class="col-md-4 mb-3"><label class="form-label">Reference</label><input name="reference_number" class="form-control"></div>
<div class="col-12 mb-3"><label class="form-label">Notes</label><textarea name="notes" class="form-control"></textarea></div>
</div><button class="btn btn-primary">Receive Payment</button></form></div></div>
@endsection

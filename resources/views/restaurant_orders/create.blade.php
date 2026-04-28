@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">New Restaurant Order</h1>
<div class="card"><div class="card-body">
<form method="POST" action="{{ route('restaurant-orders.store') }}">
@csrf
<div class="row">
    <div class="col-md-4 mb-3"><label class="form-label">Customer Type</label><select name="customer_type" class="form-select"><option>Room guest</option><option>Walk-in customer</option></select></div>
    <div class="col-md-4 mb-3"><label class="form-label">Active Room Booking</label><select name="booking_id" class="form-select"><option value="">Walk-in / none</option>@foreach($bookings as $booking)<option value="{{ $booking->id }}">{{ $booking->guest->full_name }} - Room {{ $booking->room?->room_number }}</option>@endforeach</select></div>
    <div class="col-md-4 mb-3"><label class="form-label">Walk-in Name</label><input name="walk_in_customer_name" class="form-control"></div>
    <div class="col-md-4 mb-3"><label class="form-label">Payment Method</label><select name="payment_method" class="form-select"><option>Cash</option><option>Mobile money</option><option>Card</option><option>Room charge</option></select></div>
    <div class="col-md-4 mb-3"><label class="form-label">Paid Amount</label><input type="number" step="0.01" name="paid_amount" class="form-control" value="0"></div>
</div>
<h5>Items</h5>
@for($i = 0; $i < 5; $i++)
<div class="row g-2 mb-2">
    <div class="col-md-8"><select name="menu_item_id[]" class="form-select"><option value="">Select item</option>@foreach($menuItems as $item)<option value="{{ $item->id }}">{{ $item->name }} - {{ number_format($item->price, 2) }}</option>@endforeach</select></div>
    <div class="col-md-4"><input type="number" min="1" name="quantity[]" class="form-control" value="{{ $i === 0 ? 1 : '' }}"></div>
</div>
@endfor
<button class="btn btn-primary">Send Order</button>
</form>
</div></div>
@endsection

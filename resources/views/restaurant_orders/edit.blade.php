@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Update Order</h1>
<div class="card"><div class="card-body"><form method="POST" action="{{ route('restaurant-orders.update', $restaurantOrder) }}">@csrf @method('PUT')
<div class="mb-3"><label class="form-label">Status</label><select name="status" class="form-select">@foreach(\App\Models\RestaurantOrder::STATUSES as $status)<option value="{{ $status }}" @selected($restaurantOrder->status===$status)>{{ $status }}</option>@endforeach</select></div>
<div class="mb-3"><label class="form-label">Payment Status</label><select name="payment_status" class="form-select"><option>Unpaid</option><option>Partial</option><option>Paid</option></select></div>
<button class="btn btn-primary">Save</button></form></div></div>
@endsection

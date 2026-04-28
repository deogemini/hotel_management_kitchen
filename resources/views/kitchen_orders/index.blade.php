@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Kitchen</strong> Orders</h1>
<div class="card"><div class="card-body">
<table class="table table-hover"><thead><tr><th>Order</th><th>Room</th><th>Items</th><th>Status</th><th>Update</th></tr></thead><tbody>
@foreach($restaurantOrders as $order)<tr><td>{{ $order->order_number }}</td><td>{{ $order->room?->room_number ?? 'Walk-in' }}</td><td>@foreach($order->items as $item)<div>{{ $item->quantity }} x {{ $item->menuItem->name }}</div>@endforeach</td><td>{{ $order->status }}</td><td><form method="POST" action="{{ route('kitchen-orders.update-status', $order) }}">@csrf @method('PATCH')<div class="input-group"><select name="status" class="form-select"><option>Pending</option><option>Preparing</option><option>Ready</option><option>Served</option><option>Cancelled</option></select><button class="btn btn-primary">Save</button></div></form></td></tr>@endforeach
</tbody></table></div></div>
@endsection

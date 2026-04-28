@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3"><strong>Menu</strong> Items</h1>
<div class="card"><div class="card-header"><h5 class="card-title mb-0">Food and Drinks</h5><a class="btn btn-primary float-end mt-n4" href="{{ route('menu-items.create') }}">Add Item</a></div><div class="card-body"><table class="table"><thead><tr><th>#</th><th>Name</th><th>Category</th><th>Price</th><th>Available</th><th>Actions</th></tr></thead><tbody>@foreach($menuItems as $item)<tr><td>{{ $loop->iteration }}</td><td>{{ $item->name }}</td><td>{{ $item->category }}</td><td>{{ number_format($item->price, 2) }}</td><td>{{ $item->is_available ? 'Yes' : 'No' }}</td><td><a class="btn btn-sm btn-info" href="{{ route('menu-items.edit', $item) }}">Edit</a></td></tr>@endforeach</tbody></table></div></div>
@endsection

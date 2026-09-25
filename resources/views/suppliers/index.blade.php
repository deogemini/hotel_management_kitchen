@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3"><h1 class="h3 mb-0">Supplier Management</h1><a href="{{ route('suppliers.create') }}" class="btn btn-primary">Add Supplier</a></div>
<div class="card"><div class="card-body table-responsive"><table class="table table-hover"><thead><tr><th>Name</th><th>Contact Person</th><th>Phone</th><th>Email</th><th>Actions</th></tr></thead><tbody>
@forelse($suppliers as $supplier)<tr><td>{{ $supplier->name }}</td><td>{{ $supplier->contact_person ?: '-' }}</td><td>{{ $supplier->phone ?: '-' }}</td><td>{{ $supplier->email ?: '-' }}</td><td><a href="{{ route('suppliers.edit', $supplier) }}" class="btn btn-sm btn-info">Edit</a> <form class="d-inline" method="POST" action="{{ route('suppliers.destroy', $supplier) }}" onsubmit="return confirm('Delete this supplier?');">@csrf @method('DELETE')<button class="btn btn-sm btn-danger">Delete</button></form></td></tr>@empty<tr><td colspan="5" class="text-muted">No suppliers found.</td></tr>@endforelse
</tbody></table></div></div>
@endsection

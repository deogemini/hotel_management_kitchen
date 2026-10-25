@extends('layouts.admin')
@section('content')
<div class="d-flex justify-content-between mb-3"><h1 class="h3">Companies / Organizations</h1><a class="btn btn-primary" href="{{ route('companies.create') }}">Register Company / Organization</a></div>
<a href="{{ route('guests.index') }}" class="btn btn-secondary mb-3">Back to Guests</a>
<div class="card"><div class="card-body table-responsive"><table class="table"><thead><tr><th>Name</th><th>TIN</th><th>Phone</th><th>Email</th><th>Address</th><th>Last action at</th><th>Invoices generated</th><th>Last action by</th><th>Actions</th></tr></thead><tbody>
@forelse($companies as $company)
<tr><td>{{ $company->name }}</td><td>{{ $company->tin }}</td><td>{{ $company->phone }}</td><td>{{ $company->email }}</td><td>{{ $company->address }}</td><td>{{ $company->updated_at?->format('Y-m-d H:i') ?? '—' }}</td><td><span class="badge bg-primary">{{ $company->invoices_count }}</span></td>@include('partials.performed_by_cell', ['record' => $company])<td><a class="btn btn-sm btn-info" href="{{ route('companies.edit', $company) }}">Edit</a></td></tr>
@empty <tr><td colspan="9">No companies or organizations registered.</td></tr> @endforelse
</tbody></table></div></div>
@endsection

@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-4">
        <div><h1 class="h3 mb-1">Website content</h1><p class="text-muted mb-0">Manage the public lodge website and guest enquiries.</p></div>
        <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('public.home') }}" target="_blank" rel="noopener">View website ↗</a><a class="btn btn-primary" href="{{ route('website.admin.settings') }}">Website settings</a></div>
    </div>
    <div class="row g-3 mb-4">
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="text-muted small">Content items</div><div class="fs-3 fw-bold">{{ $entries->count() }}</div><a href="{{ route('website.admin.entries.create') }}">Add a service, offer, or photo →</a></div></div></div>
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="text-muted small">New enquiries</div><div class="fs-3 fw-bold">{{ $newEnquiries }}</div><a href="{{ route('website.admin.enquiries') }}">Review guest enquiries →</a></div></div></div>
        <div class="col-md-4"><div class="card h-100"><div class="card-body"><div class="text-muted small">Website property</div><div class="fs-5 fw-bold">{{ $settings->lodge?->name ?: 'Not selected' }}</div><a href="{{ route('website.admin.settings') }}">Update public details →</a></div></div></div>
    </div>
    <div class="card mb-4"><div class="card-header d-flex justify-content-between align-items-center"><strong>Services, offers & gallery</strong><a class="btn btn-sm btn-primary" href="{{ route('website.admin.entries.create') }}">Add content item</a></div>
        <div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Category</th><th>Title</th><th>Status</th><th>Order</th><th class="text-end">Actions</th></tr></thead><tbody>
            @forelse($entries as $entry)<tr><td class="text-capitalize">{{ $entry->category }} @if($entry->category === 'gallery')<small class="text-muted">· {{ $entry->image_category }}</small>@endif</td><td>{{ $entry->title }}</td><td><span class="badge {{ $entry->is_active ? 'bg-success' : 'bg-secondary' }}">{{ $entry->is_active ? 'Published' : 'Hidden' }}</span></td><td>{{ $entry->sort_order }}</td><td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('website.admin.entries.edit', $entry) }}">Edit</a> <form method="POST" action="{{ route('website.admin.entries.destroy', $entry) }}" class="d-inline" onsubmit="return confirm('Delete this website item?')">@csrf @method('DELETE')<button class="btn btn-sm btn-outline-danger">Delete</button></form></td></tr>
            @empty<tr><td colspan="5" class="text-center text-muted py-4">No public content items yet. Add approved services, offers, or gallery photos.</td></tr>@endforelse
        </tbody></table></div>
    </div>
    <div class="card"><div class="card-header d-flex justify-content-between align-items-center"><strong>Recent enquiries</strong><a href="{{ route('website.admin.enquiries') }}">View all →</a></div><div class="table-responsive"><table class="table table-hover mb-0"><thead><tr><th>Received</th><th>Name</th><th>Email</th><th>Delivery</th><th>Status</th></tr></thead><tbody>
        @forelse($enquiries as $enquiry)<tr><td>{{ $enquiry->created_at->format('d M Y H:i') }}</td><td>{{ $enquiry->name }}</td><td><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a></td><td>{{ ucfirst($enquiry->delivery_status) }}</td><td>{{ $enquiry->status }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">No enquiries have been received.</td></tr>@endforelse
    </tbody></table></div></div>
</div>
@endsection

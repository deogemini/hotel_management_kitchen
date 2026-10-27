@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4"><div><h1 class="h3 mb-1">Website enquiries</h1><p class="text-muted mb-0">Messages are saved here even if the configured mail service is unavailable.</p></div><a class="btn btn-outline-secondary" href="{{ route('website.admin.index') }}">Back to website content</a></div>
    @forelse($enquiries as $enquiry)
        <article class="card mb-3"><div class="card-body"><div class="d-flex flex-wrap align-items-start justify-content-between gap-3"><div><div class="d-flex align-items-center gap-2"><h2 class="h5 mb-0">{{ $enquiry->name }}</h2><span class="badge {{ $enquiry->status === 'New' ? 'bg-primary' : 'bg-secondary' }}">{{ $enquiry->status }}</span></div><p class="text-muted small mb-2">{{ $enquiry->created_at->format('d M Y, H:i') }} · Email delivery {{ $enquiry->delivery_status }}</p><p class="mb-1"><a href="mailto:{{ $enquiry->email }}">{{ $enquiry->email }}</a>@if($enquiry->phone) · <a href="tel:{{ $enquiry->phone }}">{{ $enquiry->phone }}</a>@endif</p></div>
            @if($enquiry->status === 'New')<form method="POST" action="{{ route('website.admin.enquiries.status', $enquiry) }}">@csrf @method('PATCH')<input type="hidden" name="status" value="Reviewed"><button class="btn btn-sm btn-outline-primary">Mark reviewed</button></form>@endif
        </div><div class="border-top pt-3 mt-3" style="white-space:pre-wrap">{{ $enquiry->message }}</div></div></article>
    @empty<div class="card"><div class="card-body text-center text-muted py-5">No website enquiries have been received.</div></div>@endforelse
    <div>{{ $enquiries->links() }}</div>
</div>
@endsection

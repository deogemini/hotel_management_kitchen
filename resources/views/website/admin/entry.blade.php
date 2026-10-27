@extends('layouts.admin')

@section('content')
<div class="container-fluid p-0">
    <div class="d-flex align-items-center justify-content-between mb-4"><div><h1 class="h3 mb-1">{{ $entry->exists ? 'Edit website item' : 'Add website item' }}</h1><p class="text-muted mb-0">Publish only details and images approved by the lodge.</p></div><a class="btn btn-outline-secondary" href="{{ route('website.admin.index') }}">Back</a></div>
    <div class="card"><div class="card-body"><form method="POST" action="{{ $entry->exists ? route('website.admin.entries.update', $entry) : route('website.admin.entries.store') }}" enctype="multipart/form-data">
        @csrf @if($entry->exists) @method('PUT') @endif
        <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="category">Content type</label><select class="form-select" id="category" name="category" required>@foreach($categories as $category)<option value="{{ $category }}" @selected(old('category', $entry->category) === $category)>{{ ucfirst($category) }}</option>@endforeach</select><div class="form-text">Services appear on Facilities; offers appear on Home; gallery photos appear in Gallery.</div></div>
            <div class="col-md-4"><label class="form-label" for="image_category">Gallery category</label><select class="form-select" id="image_category" name="image_category" required>@foreach(['Rooms','Exterior','Dining','Facilities'] as $imageCategory)<option value="{{ $imageCategory }}" @selected(old('image_category', $entry->image_category ?: 'Facilities') === $imageCategory)>{{ $imageCategory }}</option>@endforeach</select></div>
            <div class="col-md-8"><label class="form-label" for="title">Title or caption</label><input class="form-control" id="title" name="title" maxlength="180" required value="{{ old('title', $entry->title) }}"></div>
            <div class="col-12"><label class="form-label" for="description">Description</label><textarea class="form-control" id="description" name="description" rows="4">{{ old('description', $entry->description) }}</textarea></div>
            <div class="col-md-8"><label class="form-label" for="image">Image</label><input type="file" class="form-control" id="image" name="image" accept="image/*"><div class="form-text">Optional for a service or offer. Gallery entries need an image to appear in the public gallery. Maximum 6 MB.</div>@if($entry->image_path)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($entry->image_path) }}" alt="Current item image" class="img-thumbnail mt-2" style="max-height:180px">@endif</div>
            <div class="col-md-4"><label class="form-label" for="sort_order">Display order</label><input type="number" class="form-control" id="sort_order" name="sort_order" min="0" max="10000" value="{{ old('sort_order', $entry->sort_order ?? 0) }}"></div>
            <div class="col-12"><input type="hidden" name="is_active" value="0"><label class="form-check"><input type="checkbox" class="form-check-input" name="is_active" value="1" @checked((bool) old('is_active', $entry->exists ? $entry->is_active : true))><span class="form-check-label">Publish on the public website</span></label></div>
        </div>
        <div class="mt-4"><button class="btn btn-primary">Save item</button> <a class="btn btn-outline-secondary" href="{{ route('website.admin.index') }}">Cancel</a></div>
    </form></div></div>
</div>
@endsection

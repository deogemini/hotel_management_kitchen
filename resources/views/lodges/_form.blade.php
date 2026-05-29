@csrf
<div class="row">
    <div class="col-md-6 mb-3"><label class="form-label">Lodge Name</label><input name="name" class="form-control" value="{{ old('name', $lodge->name) }}" required></div>
    <div class="col-md-6 mb-3"><label class="form-label">Phone Number</label><input name="phone_number" class="form-control" value="{{ old('phone_number', $lodge->phone_number) }}"></div>
    <div class="col-md-12 mb-3"><label class="form-label">Location</label><input name="location" class="form-control" value="{{ old('location', $lodge->location) }}"></div>
    <div class="col-md-12 mb-3"><label class="form-label">Description</label><textarea name="description" class="form-control" rows="3">{{ old('description', $lodge->description) }}</textarea></div>
</div>
<button class="btn btn-primary">Save Lodge</button>
<a href="{{ route('lodges.index') }}" class="btn btn-secondary">Back</a>

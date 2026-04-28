@csrf
<div class="row">
    <div class="col-md-4 mb-3">
        <label class="form-label">Room Number</label>
        <input name="room_number" class="form-control" value="{{ old('room_number', $room->room_number) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Room Type</label>
        <select name="room_type" class="form-select" required>
            @foreach(\App\Models\Room::TYPES as $type)
                <option value="{{ $type }}" @selected(old('room_type', $room->room_type) === $type)>{{ $type }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Price Per Night</label>
        <input type="number" step="0.01" name="price_per_night" class="form-control" value="{{ old('price_per_night', $room->price_per_night) }}" required>
    </div>
    <div class="col-md-4 mb-3">
        <label class="form-label">Status</label>
        <select name="status" class="form-select" required>
            @foreach(\App\Models\Room::STATUSES as $status)
                <option value="{{ $status }}" @selected(old('status', $room->status ?: 'Available') === $status)>{{ $status }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-8 mb-3">
        <label class="form-label">Features</label>
        <div class="d-flex flex-wrap gap-3">
            @foreach(\App\Models\Room::FEATURES as $feature)
                <label class="form-check">
                    <input class="form-check-input" type="checkbox" name="features[]" value="{{ $feature }}" @checked(in_array($feature, old('features', $room->features ?? []), true))>
                    <span class="form-check-label">{{ $feature }}</span>
                </label>
            @endforeach
        </div>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Description</label>
        <textarea name="description" class="form-control" rows="3">{{ old('description', $room->description) }}</textarea>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Room Images</label>
        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
    </div>
</div>
<button class="btn btn-primary">Save Room</button>
<a href="{{ route('rooms.index') }}" class="btn btn-secondary">Back</a>

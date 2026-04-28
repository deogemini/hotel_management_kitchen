@csrf
<div class="row">
    @foreach([
        'full_name' => 'Full Name',
        'phone_number' => 'Phone Number',
        'email' => 'Email',
        'id_type' => 'ID Type',
        'id_number' => 'ID Number',
        'nationality' => 'Nationality',
    ] as $field => $label)
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ $label }}</label>
        <input name="{{ $field }}" class="form-control" value="{{ old($field, $guest->$field) }}" {{ $field === 'full_name' ? 'required' : '' }}>
    </div>
    @endforeach
    <div class="col-12 mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control">{{ old('address', $guest->address) }}</textarea>
    </div>
</div>
<button class="btn btn-primary">Save Guest</button>
<a href="{{ route('guests.index') }}" class="btn btn-secondary">Back</a>

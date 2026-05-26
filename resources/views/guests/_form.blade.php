@csrf
@php
    $idTypes = ['NIDA', 'Driving Licence', 'Passport', 'Others'];
    $nationalities = ['Tanzanian', 'Kenyan', 'Ugandan'];
@endphp
<div class="row">
    @foreach([
        'full_name' => 'Full Name',
        'phone_number' => 'Phone Number',
        'email' => 'Email',
        'id_number' => 'ID Number',
    ] as $field => $label)
    <div class="col-md-6 mb-3">
        <label class="form-label">{{ $label }}</label>
        <input name="{{ $field }}" class="form-control" value="{{ old($field, $guest->$field) }}" {{ $field === 'full_name' ? 'required' : '' }}>
    </div>
    @endforeach
    <div class="col-md-6 mb-3">
        <label class="form-label">ID Type</label>
        <select name="id_type" class="form-select">
            <option value="">Select ID Type</option>
            @foreach($idTypes as $idType)
                <option value="{{ $idType }}" @selected(old('id_type', $guest->id_type) === $idType)>{{ $idType }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-md-6 mb-3">
        <label class="form-label">Nationality</label>
        <select name="nationality" class="form-select">
            <option value="">Select Nationality</option>
            @foreach($nationalities as $nationality)
                <option value="{{ $nationality }}" @selected(old('nationality', $guest->nationality) === $nationality)>{{ $nationality }}</option>
            @endforeach
        </select>
    </div>
    <div class="col-12 mb-3">
        <label class="form-label">Address</label>
        <textarea name="address" class="form-control">{{ old('address', $guest->address) }}</textarea>
    </div>
</div>
<button class="btn btn-primary">Save Guest</button>
<a href="{{ route('guests.index') }}" class="btn btn-secondary">Back</a>

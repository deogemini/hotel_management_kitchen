@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Ongeza Mtumiaji</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Fomu ya Usajili</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Jina</label>
                        <input type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}">
                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}">
                        @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Namba ya Simu</label>
                        <input type="text" class="form-control @error('phone') is-invalid @enderror" name="phone" value="{{ old('phone') }}">
                        @error('phone') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role_id" class="form-select @error('role_id') is-invalid @enderror">
                            @foreach($roles as $role)
                                <option value="{{ $role->id }}" {{ (int) old('role_id') === $role->id ? 'selected' : '' }}>{{ $role->display_name }}</option>
                            @endforeach
                        </select>
                        @error('role_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lodge</label>
                        <select name="lodge_id" class="form-select @error('lodge_id') is-invalid @enderror" required>
                            @foreach($lodges as $lodge)
                                <option value="{{ $lodge->id }}" {{ (int) old('lodge_id') === $lodge->id ? 'selected' : '' }}>{{ $lodge->name }}{{ $lodge->location ? ' - '.$lodge->location : '' }}</option>
                            @endforeach
                        </select>
                        @error('lodge_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" class="form-control @error('password') is-invalid @enderror" name="password">
                        @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Hifadhi</button>
                    <a href="{{ route('users.index') }}" class="btn btn-secondary">Rudi</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

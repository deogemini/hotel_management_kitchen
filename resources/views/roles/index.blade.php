@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3"><strong>Roles</strong> & Access Permissions</h1>

<div class="row">
    @foreach($roles as $role)
    <div class="col-12 col-xl-4">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">{{ $role->display_name }}</h5>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('roles.update', $role) }}">
                    @csrf
                    @method('PUT')

                    <div class="mb-3">
                        <label class="form-label">Display Name</label>
                        <input name="display_name" class="form-control" value="{{ old('display_name', $role->display_name) }}" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="description" class="form-control" rows="2">{{ old('description', $role->description) }}</textarea>
                    </div>

                    @foreach($permissions as $module => $items)
                    <div class="mb-3">
                        <h6 class="text-muted">{{ $module }}</h6>
                        @foreach($items as $permission)
                            <label class="form-check">
                                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $permission->id }}" @checked($role->permissions->contains('id', $permission->id))>
                                <span class="form-check-label">{{ $permission->display_name }}</span>
                            </label>
                        @endforeach
                    </div>
                    @endforeach

                    <button class="btn btn-primary">Save Permissions</button>
                </form>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endsection

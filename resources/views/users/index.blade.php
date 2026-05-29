@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3"><strong>Users</strong> Management</h1>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Orodha ya Watumiaji</h5>
                <a href="{{ route('users.create') }}" class="btn btn-primary float-end mt-n4">Ongeza Mtumiaji</a>
            </div>
            <div class="card-body">
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Jina</th>
                            <th>Email</th>
                            <th>Namba ya Simu</th>
                            <th>Lodge</th>
                            <th>Role</th>
                            <th>Login Lock</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $u)
                        <tr>
                            <td>{{ $loop->iteration }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>{{ $u->phone }}</td>
                            <td>{{ $u->lodge?->name ?? '-' }}</td>
                            <td><span class="badge {{ $u->effectiveRoleName() === 'hotel_manager' ? 'bg-danger' : 'bg-secondary' }}">{{ $u->roleRecord?->display_name ?? str_replace('_', ' ', $u->effectiveRoleName()) }}</span></td>
                            <td>
                                @if($u->account_locked_until && $u->account_locked_until->isFuture())
                                    <span class="badge bg-warning text-dark">Locked until {{ $u->account_locked_until->format('Y-m-d H:i') }}</span>
                                @elseif($u->failed_login_attempts > 0)
                                    <span class="badge bg-light text-dark">Attempts: {{ $u->failed_login_attempts }}</span>
                                @else
                                    <span class="badge bg-success">Normal</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('users.edit', $u->id) }}" class="btn btn-sm btn-info">Hariri</a>
                                @if(($u->account_locked_until && $u->account_locked_until->isFuture()) || $u->failed_login_attempts > 0)
                                    <form action="{{ route('users.unlock-login-lock', $u->id) }}" method="POST" class="d-inline">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-warning">Unlock Login</button>
                                    </form>
                                @endif
                                <form action="{{ route('users.destroy', $u->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" onclick="return confirm('are you sure to delete this data?')">Futa</button>
                                </form>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection

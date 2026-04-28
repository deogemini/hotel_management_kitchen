@extends('layouts.admin')

@section('content')
<div class="mb-3">
    <h1 class="h3 d-inline align-middle">Profile</h1>
</div>

<div class="row">
    <div class="col-md-12 col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Profile Information</h5>
            </div>
            <div class="card-body">
                @include('profile.partials.update-profile-information-form')
            </div>
        </div>
    </div>

    <div class="col-md-12 col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Update Password</h5>
            </div>
            <div class="card-body">
                 @include('profile.partials.update-password-form')
            </div>
        </div>
    </div>

{{--
    <div class="col-md-12 col-xl-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Delete Account</h5>
            </div>
            <div class="card-body">
                 @include('profile.partials.delete-user-form')
            </div>
        </div>
    </div>
--}}
</div>
@endsection

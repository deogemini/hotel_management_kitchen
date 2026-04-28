@extends('layouts.admin')

@section('content')
<div class="mb-3">
    <h1 class="h3 d-inline align-middle">SMS Gateway Settings</h1>
</div>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Flex SMS Gateway Configuration</h5>
                <h6 class="card-subtitle text-muted">Configure your SMS gateway credentials and status.</h6>
            </div>
            <div class="card-body">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible" role="alert">
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        <div class="alert-message">
                            {{ session('success') }}
                        </div>
                    </div>
                @endif

                <form action="{{ route('settings.sms.update') }}" method="POST">
                    @csrf
                    @method('PUT')
                    
                    <div class="mb-3">
                        <label class="form-label">Base URL</label>
                        <input type="text" name="base_url" class="form-control @error('base_url') is-invalid @enderror" value="{{ old('base_url', $setting->base_url) }}" placeholder="https://sms.flex.co.tz">
                        @error('base_url')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Client ID</label>
                        <input type="text" name="client_id" class="form-control @error('client_id') is-invalid @enderror" value="{{ old('client_id', $setting->client_id) }}" placeholder="F00102">
                        @error('client_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Client Secret</label>
                        <input type="password" name="client_secret" class="form-control @error('client_secret') is-invalid @enderror" value="{{ old('client_secret', $setting->client_secret) }}" placeholder="Your Client Secret">
                        @error('client_secret')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Sender ID</label>
                        <input type="text" name="sender_id" class="form-control @error('sender_id') is-invalid @enderror" value="{{ old('sender_id', $setting->sender_id) }}" placeholder="Flex">
                        @error('sender_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="is_enabled" value="1" {{ old('is_enabled', $setting->is_enabled) ? 'checked' : '' }}>
                            <span class="form-check-label">Enable SMS Sending</span>
                        </label>
                        <small class="text-muted">Turn off to stop all outgoing SMS notifications.</small>
                    </div>

                    <div class="mb-3">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Instructions</h5>
            </div>
            <div class="card-body">
                <p>To get your credentials, please visit <a href="https://sms.flex.co.tz" target="_blank">Flex SMS Dashboard</a>.</p>
                <ul>
                    <li><strong>Base URL:</strong> The main API endpoint (usually <code>https://sms.flex.co.tz</code>).</li>
                    <li><strong>Client ID:</strong> Your unique identification provided by Flex SMS.</li>
                    <li><strong>Client Secret:</strong> Your API secret key.</li>
                    <li><strong>Sender ID:</strong> The name that appears as the sender (e.g., <code>Flex</code> or your Parish Name).</li>
                </ul>
                <div class="alert alert-warning">
                    <i class="align-middle" data-feather="alert-triangle"></i>
                    Changing these settings will immediately affect the SMS notification system.
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

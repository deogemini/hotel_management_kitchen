@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Hariri Shukrani</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Maelezo ya Shukrani</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('shukranis.update', $shukrani->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Mtoto</label>
                        <select class="form-select @error('watoto_id') is-invalid @enderror" name="watoto_id">
                            @foreach($watoto as $c)
                                <option value="{{ $c->id }}" {{ old('watoto_id', $shukrani->watoto_id) == $c->id ? 'selected' : '' }}>
                                    {{ $c->jina_la_mtoto }} ({{ $c->jumuiya->jina_la_jumuiya }})
                                </option>
                            @endforeach
                        </select>
                        @error('watoto_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kiasi</label>
                        <input type="text" id="kiasiInput" class="form-control @error('kiasi') is-invalid @enderror" name="kiasi" value="{{ old('kiasi', $shukrani->kiasi) }}">
                        @error('kiasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mode ya Malipo</label>
                        @php($modes = ['cash' => 'Cash', 'mpesa' => 'M-Pesa', 'bank' => 'Bank', 'other' => 'Other'])
                        <select class="form-select @error('mode_ya_malipo') is-invalid @enderror" name="mode_ya_malipo">
                            @foreach($modes as $value => $label)
                                <option value="{{ $value }}" {{ old('mode_ya_malipo', $shukrani->mode_ya_malipo ?? 'cash') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('mode_ya_malipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hali ya Malipo</label>
                        @php($conditions = ['full' => 'Full', 'partial' => 'Partial'])
                        <select class="form-select @error('hali_ya_malipo') is-invalid @enderror" name="hali_ya_malipo">
                            @foreach($conditions as $value => $label)
                                <option value="{{ $value }}" {{ old('hali_ya_malipo', $shukrani->hali_ya_malipo ?? 'full') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('hali_ya_malipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tarehe ya Malipo</label>
                        <input type="date" class="form-control @error('paid_at') is-invalid @enderror" name="paid_at" value="{{ old('paid_at', optional($shukrani->paid_at)->format('Y-m-d')) }}">
                        @error('paid_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Hifadhi</button>
                    <a href="{{ route('shukranis.index') }}" class="btn btn-secondary">Ghairi</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const kiasiInput = document.getElementById('kiasiInput');
        if (kiasiInput) {
            const form = kiasiInput.closest('form');

            // Format initial value
            if (kiasiInput.value) {
                formatInput(kiasiInput);
            }

            kiasiInput.addEventListener('input', function() {
                formatInput(this);
            });

            form.addEventListener('submit', function() {
                kiasiInput.value = kiasiInput.value.replace(/,/g, '');
            });

            function formatInput(input) {
                let value = input.value.replace(/,/g, '');
                let parts = value.split('.');
                parts[0] = parts[0].replace(/\B(?=(\d{3})+(?!\d))/g, ",");
                if (parts.length > 1) {
                    input.value = parts[0] + '.' + parts[1];
                } else {
                    input.value = parts[0];
                }
            }
        }
    });
</script>
@endpush

@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Hariri Zaka</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Maelezo ya Zaka</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('zakas.update', $zaka->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Mwanajumuiya</label>
                        <input id="memberSearch" type="text" class="form-control mb-2" placeholder="Tafuta kwa jina la mwanajumuiya...">
                        <select id="memberSelect" class="form-select @error('mwanajumuiya_id') is-invalid @enderror" name="mwanajumuiya_id" size="6">
                            <option value="" disabled>Chagua Mwanajumuiya</option>
                            @foreach($wanajumuiya as $mwana)
                                <option value="{{ $mwana->id }}" {{ old('mwanajumuiya_id', $zaka->mwanajumuiya_id) == $mwana->id ? 'selected' : '' }}>
                                    {{ $mwana->jina_la_mwanajumuiya }} ({{ $mwana->jumuiya->jina_la_jumuiya }})
                                </option>
                            @endforeach
                        </select>
                        @error('mwanajumuiya_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kiasi</label>
                        <input type="text" id="kiasiInput" class="form-control @error('kiasi') is-invalid @enderror" name="kiasi" placeholder="Ingiza kiasi" value="{{ old('kiasi', $zaka->kiasi) }}">
                        @error('kiasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Risiti Namba</label>
                        <input type="text" class="form-control @error('risiti_namba') is-invalid @enderror" name="risiti_namba" placeholder="Ingiza risiti namba" value="{{ old('risiti_namba', $zaka->risiti_namba) }}">
                        @error('risiti_namba')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mode ya Malipo</label>
                        <select class="form-select @error('mode_ya_malipo') is-invalid @enderror" name="mode_ya_malipo">
                            @php($modes = ['cash' => 'Cash', 'mpesa' => 'M-Pesa', 'bank' => 'Bank', 'other' => 'Other'])
                            @foreach($modes as $value => $label)
                                <option value="{{ $value }}" {{ old('mode_ya_malipo', $zaka->mode_ya_malipo) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('mode_ya_malipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Hali ya Malipo</label>
                        <select class="form-select @error('hali_ya_malipo') is-invalid @enderror" name="hali_ya_malipo">
                            @php($conditions = ['full' => 'Full', 'partial' => 'Partial'])
                            <option value="">-- Hiari --</option>
                            @foreach($conditions as $value => $label)
                                <option value="{{ $value }}" {{ old('hali_ya_malipo', $zaka->hali_ya_malipo) == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('hali_ya_malipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Muda wa Malipo</label>
                        <input type="datetime-local" class="form-control @error('paid_at') is-invalid @enderror" name="paid_at" value="{{ old('paid_at', optional($zaka->paid_at)->format('Y-m-d\\TH:i')) }}">
                        @error('paid_at')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Sasisha</button>
                    <a href="{{ route('zakas.index') }}" class="btn btn-secondary">Ghairi</a>
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

        const memberSearch = document.getElementById('memberSearch');
        const memberSelect = document.getElementById('memberSelect');
        if (memberSearch && memberSelect) {
            const memberOptions = Array.from(memberSelect.options);

            memberSearch.addEventListener('input', function() {
                const query = this.value.trim().toLowerCase();
                memberOptions.forEach(option => {
                    if (!option.value) {
                        option.hidden = query.length > 0;
                        return;
                    }
                    const text = option.textContent.toLowerCase();
                    option.hidden = query.length > 0 && !text.includes(query);
                });
            });
        }
    });
</script>
@endpush

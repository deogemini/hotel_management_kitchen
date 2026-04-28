@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Rekodi Shukrani</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Maelezo ya Shukrani</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('shukranis.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Chagua Jumuiya</label>
                        <select id="jumuiya-filter" class="form-select">
                            <option value="">-- Chagua Jumuiya --</option>
                            @foreach(($jumuiyas ?? []) as $j)
                                <option value="{{ $j->id }}" {{ (string)($preselectedJumuiyaId ?? '') === (string)$j->id ? 'selected' : '' }}>
                                    {{ $j->jina_la_jumuiya }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tafuta Jina la Mtoto</label>
                        <input type="text" id="mtoto-search" class="form-control" placeholder="Andika jina kutafuta">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mtoto</label>
                        <select class="form-select @error('watoto_id') is-invalid @enderror" name="watoto_id">
                            <option selected disabled>Chagua Mtoto</option>
                            @foreach($watoto as $c)
                                <option value="{{ $c->id }}" {{ old('watoto_id', $preselectedId ?? null) == $c->id ? 'selected' : '' }}>
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
                        <input type="number" step="0.01" class="form-control @error('kiasi') is-invalid @enderror" name="kiasi" placeholder="Ingiza kiasi" value="{{ old('kiasi') }}">
                        @error('kiasi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Mode ya Malipo</label>
                        <select class="form-select @error('mode_ya_malipo') is-invalid @enderror" name="mode_ya_malipo">
                            <option disabled>Chagua Mode</option>
                            @php($modes = ['cash' => 'Cash', 'mpesa' => 'M-Pesa', 'bank' => 'Bank', 'other' => 'Other'])
                            @foreach($modes as $value => $label)
                                <option value="{{ $value }}" {{ old('mode_ya_malipo', 'cash') == $value ? 'selected' : '' }}>{{ $label }}</option>
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
                                <option value="{{ $value }}" {{ old('hali_ya_malipo', 'full') == $value ? 'selected' : '' }}>{{ $label }}</option>
                            @endforeach
                        </select>
                        @error('hali_ya_malipo')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tarehe ya Malipo</label>
                        <input type="date" class="form-control @error('paid_at') is-invalid @enderror" name="paid_at" value="{{ old('paid_at', date('Y-m-d')) }}">
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
<script>
    (function(){
        var members = @json($childData);
        var jumuiyaSelect = document.getElementById('jumuiya-filter');
        var searchInput = document.getElementById('mtoto-search');
        var memberSelect = document.querySelector('select[name="watoto_id"]');
        function renderOptions() {
            var jId = jumuiyaSelect ? jumuiyaSelect.value : '';
            var q = (searchInput ? searchInput.value : '').toLowerCase();
            var list = members.filter(function(m){
                var okJ = jId ? (String(m.jumuiya_id) === String(jId)) : true;
                var okQ = q ? (m.name.toLowerCase().includes(q)) : true;
                return okJ && okQ;
            });
            var current = memberSelect.value;
            memberSelect.innerHTML = '';
            var ph = document.createElement('option');
            ph.textContent = 'Chagua Mtoto';
            ph.disabled = true;
            ph.selected = !current;
            memberSelect.appendChild(ph);
            list.forEach(function(m){
                var opt = document.createElement('option');
                opt.value = m.id;
                opt.textContent = m.name + ' (' + m.jumuiya_name + ')';
                if (String(current) === String(m.id)) opt.selected = true;
                memberSelect.appendChild(opt);
            });
        }
        if (jumuiyaSelect) jumuiyaSelect.addEventListener('change', renderOptions);
        if (searchInput) searchInput.addEventListener('input', renderOptions);
        renderOptions();
    })();
    </script>
@endpush
@endsection

@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Ongeza Mwanajumuiya</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Maelezo ya Mwanajumuiya</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('mwanajumuiya.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Jumuiya</label>
                        <select class="selectpicker @error('jumuiya_id') is-invalid @enderror" name="jumuiya_id" data-live-search="true" data-width="100%">
                            <option selected disabled>Chagua Jumuiya</option>
                            @foreach($jumuiyas as $jumuiya)
                                <option value="{{ $jumuiya->id }}" {{ old('jumuiya_id', request('jumuiya_id')) == $jumuiya->id ? 'selected' : '' }}>{{ $jumuiya->jina_la_jumuiya }}</option>
                            @endforeach
                        </select>
                        @error('jumuiya_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jina la Mwanajumuiya</label>
                        <input type="text" class="form-control @error('jina_la_mwanajumuiya') is-invalid @enderror" name="jina_la_mwanajumuiya" placeholder="Ingiza jina la mwanajumuiya" value="{{ old('jina_la_mwanajumuiya') }}">
                        @error('jina_la_mwanajumuiya')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kadi Namba</label>
                        <input type="text" class="form-control @error('kadi_namba') is-invalid @enderror" name="kadi_namba" placeholder="Ingiza kadi namba" value="{{ old('kadi_namba') }}">
                        @error('kadi_namba')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Namba ya Simu</label>
                        <input type="text" class="form-control @error('namba_ya_simu') is-invalid @enderror" name="namba_ya_simu" placeholder="Ingiza namba ya simu" value="{{ old('namba_ya_simu') }}">
                        @error('namba_ya_simu')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Hifadhi</button>
                    <a href="{{ route('mwanajumuiya.index') }}" class="btn btn-secondary">Ghairi</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/css/bootstrap-select.min.css">
@endpush
@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap-select@1.14.0-beta3/dist/js/bootstrap-select.min.js"></script>
<script>
    $(function() {
        $('.selectpicker').selectpicker();
    });
</script>
@endpush

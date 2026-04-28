@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Ongeza Kanda</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Maelezo ya Kanda</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('kandas.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Jina la Kanda</label>
                        <input type="text" class="form-control @error('jina_la_kanda') is-invalid @enderror" name="jina_la_kanda" placeholder="Ingiza jina la kanda" value="{{ old('jina_la_kanda') }}">
                        @error('jina_la_kanda')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Eneo la Kanda</label>
                        <input type="text" class="form-control @error('eneo_la_kanda') is-invalid @enderror" name="eneo_la_kanda" placeholder="Ingiza eneo la kanda" value="{{ old('eneo_la_kanda') }}">
                        @error('eneo_la_kanda')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Hifadhi</button>
                    <a href="{{ route('kandas.index') }}" class="btn btn-secondary">Ghairi</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

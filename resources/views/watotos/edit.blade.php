@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Hariri Mtoto</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Maelezo ya Mtoto</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('watotos.update', $mtoto->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Jumuiya</label>
                        <select class="form-select @error('jumuiya_id') is-invalid @enderror" name="jumuiya_id">
                            <option selected disabled>Chagua Jumuiya</option>
                            @foreach($jumuiyas as $j)
                                <option value="{{ $j->id }}" {{ old('jumuiya_id', $mtoto->jumuiya_id) == $j->id ? 'selected' : '' }}>
                                    {{ $j->jina_la_jumuiya }}
                                </option>
                            @endforeach
                        </select>
                        @error('jumuiya_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jina la Mtoto</label>
                        <input type="text" class="form-control @error('jina_la_mtoto') is-invalid @enderror" name="jina_la_mtoto" value="{{ old('jina_la_mtoto', $mtoto->jina_la_mtoto) }}">
                        @error('jina_la_mtoto')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tarehe ya Kuzaliwa</label>
                        <input type="date" class="form-control @error('tarehe_ya_kuzaliwa') is-invalid @enderror" name="tarehe_ya_kuzaliwa" value="{{ old('tarehe_ya_kuzaliwa', $mtoto->tarehe_ya_kuzaliwa) }}">
                        @error('tarehe_ya_kuzaliwa')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Namba ya Mzazi</label>
                        <input type="text" class="form-control @error('namba_ya_mzazi') is-invalid @enderror" name="namba_ya_mzazi" value="{{ old('namba_ya_mzazi', $mtoto->namba_ya_mzazi) }}">
                        @error('namba_ya_mzazi')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Hifadhi</button>
                    <a href="{{ route('watotos.index') }}" class="btn btn-secondary">Ghairi</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

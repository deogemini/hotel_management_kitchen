@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Hariri Jumuiya</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title">Maelezo ya Jumuiya</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('jumuiyas.update', $jumuiya->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="mb-3">
                        <label class="form-label">Kanda</label>
                        <select class="form-select @error('kanda_id') is-invalid @enderror" name="kanda_id">
                            <option disabled>Chagua Kanda</option>
                            @foreach($kandas as $kanda)
                                <option value="{{ $kanda->id }}" {{ old('kanda_id', $jumuiya->kanda_id) == $kanda->id ? 'selected' : '' }}>{{ $kanda->jina_la_kanda }}</option>
                            @endforeach
                        </select>
                        @error('kanda_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jina la Jumuiya</label>
                        <input type="text" class="form-control @error('jina_la_jumuiya') is-invalid @enderror" name="jina_la_jumuiya" placeholder="Ingiza jina la jumuiya" value="{{ old('jina_la_jumuiya', $jumuiya->jina_la_jumuiya) }}">
                        @error('jina_la_jumuiya')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <button type="submit" class="btn btn-primary">Sasisha</button>
                    <a href="{{ route('jumuiyas.index') }}" class="btn btn-secondary">Ghairi</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

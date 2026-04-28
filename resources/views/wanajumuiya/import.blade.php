@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Import Wanajumuiya kutoka Excel</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Upload Faili ya Excel</h5>
                <div>
                    <a href="{{ route('mwanajumuiya.sample') }}" class="btn btn-outline-secondary btn-sm">Pakua Template</a>
                    <a href="{{ route('mwanajumuiya.export') }}" class="btn btn-outline-primary btn-sm">Pakua Orodha</a>
                </div>
            </div>
            <div class="card-body">
                <form action="{{ route('mwanajumuiya.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Weka Wanajumuiya katika Jumuiya hii (hiari)</label>
                        <select name="jumuiya_id" class="form-select @error('jumuiya_id') is-invalid @enderror">
                            <option value="">— Chagua —</option>
                            @foreach($jumuiyas as $j)
                                <option value="{{ $j->id }}" {{ old('jumuiya_id') == $j->id ? 'selected' : '' }}>{{ $j->jina_la_jumuiya }}</option>
                            @endforeach
                        </select>
                        @error('jumuiya_id')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Ukichagua hapa, faili halihitaji kolamu ya <strong>jumuiya</strong>; wote watawekwa kwenye Jumuiya uliyochagua.
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Faili (.xlsx, .xls, .csv)</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" accept=".xlsx,.xls,.csv">
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Kolamu lazima: <strong>jina_la_mwanajumuiya</strong>, <strong>kadi_namba</strong>, <strong>namba_ya_simu</strong>. 
                            Ukiacha kuchagua Jumuiya hapo juu, ongeza pia kolamu ya <strong>jumuiya</strong> (jina kamili la Jumuiya).
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Import</button>
                    <a href="{{ route('mwanajumuiya.index') }}" class="btn btn-secondary">Rudi Orodha</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

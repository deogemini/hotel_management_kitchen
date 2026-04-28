@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3">Import Zaka kutoka Excel</h1>

<div class="row">
    <div class="col-12 col-xl-6">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <h5 class="card-title mb-0">Upload Faili ya Excel</h5>
                <a href="{{ route('zakas.sample') }}" class="btn btn-outline-secondary btn-sm">Pakua Template ya Mfano</a>
            </div>
            <div class="card-body">
                <form action="{{ route('zakas.import') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Faili (.xlsx, .xls, .csv)</label>
                        <input type="file" class="form-control @error('file') is-invalid @enderror" name="file" accept=".xlsx,.xls,.csv">
                        @error('file')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                        <div class="form-text">
                            Kolamu zinazohitajika: kiasi, risiti_namba, mode_ya_malipo, paid_at.
                            Taja Mwanajumuiya kwa mojawapo ya njia:
                            - kadi_namba AU
                            - jina_la_mwanajumuiya + jumuiya (jina kamili).
                        </div>
                    </div>
                    <button type="submit" class="btn btn-primary">Import</button>
                    <a href="{{ route('zakas.index') }}" class="btn btn-secondary">Rudi Orodha</a>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

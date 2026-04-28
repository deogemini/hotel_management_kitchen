@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3"><strong>Mwanajumuiya</strong> Report</h1>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.mwanajumuiya') }}" class="row g-3" id="mwanajumuiyaFilterForm">
                    <div class="col-md-4">
                        <label for="mwanajumuiya_id" class="form-label">Mwanajumuiya</label>
                        <select id="mwanajumuiya_id" name="mwanajumuiya_id" class="selectpicker" data-live-search="true" data-width="100%">
                            <option value="">Chagua Mwanajumuiya</option>
                            @foreach($members as $m)
                                <option value="{{ $m->id }}" {{ ($mwanajumuiyaId ?? null) == $m->id ? 'selected' : '' }}>
                                    {{ $m->jina_la_mwanajumuiya }} — {{ $m->jumuiya->jina_la_jumuiya }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate ?? '' }}">
                    </div>
                    <div class="col-md-4">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate ?? '' }}">
                    </div>
                    <div class="col-md-12 d-flex align-items-end gap-2">
                        <button type="submit" class="btn btn-primary">Filter</button>
                        @if(($mwanajumuiyaId ?? null) || ($startDate ?? null) || ($endDate ?? null))
                            <a href="{{ route('reports.mwanajumuiya') }}" class="btn btn-outline-secondary">Clear</a>
                        @endif
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header d-flex justify-content-between">
                <h5 class="card-title mb-0">Zaka Entries</h5>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0 text-primary">Total: {{ number_format($total) }} TZS</h5>
                    <a class="btn btn-outline-success btn-sm"
                       href="{{ route('reports.mwanajumuiya.export', ['mwanajumuiya_id' => $mwanajumuiyaId, 'start_date' => $startDate, 'end_date' => $endDate]) }}">
                        Export Excel
                    </a>
                </div>
            </div>
            <div class="card-body">
                <table class="table table-hover my-0">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>Mwanajumuiya</th>
                            <th>Jumuiya</th>
                            <th>Amount (TZS)</th>
                            <th>Receipt No.</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($zakas as $zaka)
                        <tr>
                            <td>{{ $zaka->paid_at?->format('d/m/Y') }}</td>
                            <td>{{ $zaka->mwanajumuiya->jina_la_mwanajumuiya }}</td>
                            <td>{{ $zaka->mwanajumuiya->jumuiya->jina_la_jumuiya }}</td>
                            <td>{{ number_format($zaka->kiasi) }}</td>
                            <td>{{ $zaka->risiti_namba ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center">No records found.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
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
        $('#mwanajumuiya_id').on('changed.bs.select', function() {
            $('#mwanajumuiyaFilterForm').submit();
        });
    });
</script>
@endpush

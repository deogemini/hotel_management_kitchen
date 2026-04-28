@extends('layouts.admin')

@section('content')
<h1 class="h3 mb-3"><strong>Zaka</strong> Reports</h1>

<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h5 class="card-title mb-0">Filter Report</h5>
            </div>
            <div class="card-body">
                <form method="GET" action="{{ route('reports.zaka') }}" class="row g-3">
                    <div class="col-md-3">
                        <label for="year" class="form-label">Year</label>
                        <select id="year" name="year" class="form-select">
                            @for($y = date('Y'); $y >= 2020; $y--)
                                <option value="{{ $y }}" {{ $year == $y ? 'selected' : '' }}>{{ $y }}</option>
                            @endfor
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="month" class="form-label">Month</label>
                        <select id="month" name="month" class="form-select">
                            <option value="">All Months</option>
                            @foreach(range(1, 12) as $m)
                                <option value="{{ $m }}" {{ $month == $m ? 'selected' : '' }}>
                                    {{ date('F', mktime(0, 0, 0, $m, 1)) }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="jumuiya_id" class="form-label">Jumuiya</label>
                        <select id="jumuiya_id" name="jumuiya_id" class="form-select">
                            <option value="">All Jumuiyas</option>
                            @foreach($jumuiyas as $jumuiya)
                                <option value="{{ $jumuiya->id }}" {{ $jumuiyaId == $jumuiya->id ? 'selected' : '' }}>
                                    {{ $jumuiya->jina_la_jumuiya }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-3">
                        <label for="start_date" class="form-label">Start Date</label>
                        <input type="date" id="start_date" name="start_date" class="form-control" value="{{ $startDate ?? '' }}">
                    </div>
                    <div class="col-md-3">
                        <label for="end_date" class="form-label">End Date</label>
                        <input type="date" id="end_date" name="end_date" class="form-control" value="{{ $endDate ?? '' }}">
                    </div>
                    <div class="col-md-9 d-flex align-items-end">
                        <button type="submit" class="btn btn-primary w-100">Filter</button>
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
                <h5 class="card-title mb-0">Zaka Transactions</h5>
                <div class="d-flex align-items-center gap-2">
                    <h5 class="card-title mb-0 text-primary">Total: {{ number_format($total) }} TZS</h5>
                    <a class="btn btn-outline-success btn-sm"
                       href="{{ route('reports.zaka.export', ['year' => $year, 'month' => $month, 'start_date' => $startDate, 'end_date' => $endDate, 'jumuiya_id' => $jumuiyaId]) }}">
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
                            <td>{{ $zaka->paid_at->format('d/m/Y') }}</td>
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

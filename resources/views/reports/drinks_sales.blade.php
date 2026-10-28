@extends('layouts.admin')
@section('content')
<h1 class="h3 mb-3">Drinks Sales Report</h1>

<div class="card mb-3 report-filter">
    <div class="card-body">
        <form method="GET" class="row g-2 align-items-end">
            <div class="col-md-3">
                <label class="form-label" for="start-date">Start Date</label>
                <input id="start-date" type="date" name="start_date" class="form-control" value="{{ $startDate }}">
            </div>
            <div class="col-md-3">
                <label class="form-label" for="end-date">End Date</label>
                <input id="end-date" type="date" name="end_date" class="form-control" value="{{ $endDate }}">
            </div>
            @if(auth()->user()?->hasRole('hotel_manager'))
                <div class="col-md-3">
                    <label class="form-label" for="lodge-id">Lodge</label>
                    <select id="lodge-id" name="lodge_id" class="form-select">
                        <option value="">All Lodges</option>
                        @foreach($lodges as $lodge)
                            <option value="{{ $lodge->id }}" @selected((int) $selectedLodgeId === $lodge->id)>{{ $lodge->name }}</option>
                        @endforeach
                    </select>
                </div>
            @endif
            <div class="col-md-auto d-flex gap-2">
                <button type="submit" class="btn btn-primary">Pull Report</button>
                <a href="{{ route('reports.drinks-sales') }}" class="btn btn-secondary">Reset</a>
                <button type="submit" formaction="{{ route('reports.drinks-sales.export.excel') }}" class="btn btn-success">Export Excel</button>
            </div>
        </form>
    </div>
</div>

<div class="card mb-3">
    <div class="card-header"><h5 class="card-title mb-0">Daily Purchases and Sales</h5></div>
    <div class="card-body">
        <p class="text-muted">Period: {{ $startDate }} to {{ $endDate }}</p>
        <p class="small text-muted">Sales are grouped by the restaurant order date. Cancelled orders are excluded.</p>
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Date</th><th class="text-end">Total Purchase Cost</th><th class="text-end">Total Sales</th></tr></thead>
                <tbody>
                    @forelse($dailyTotals as $day)
                        <tr><td>{{ $day['date'] }}</td><td class="text-end">{{ number_format($day['purchase_cost'], 2) }}</td><td class="text-end">{{ number_format($day['sales_total'], 2) }}</td></tr>
                    @empty
                        <tr><td colspan="3" class="text-center text-muted py-4">No drink purchases or sales were recorded for this period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr class="table-light fw-bold"><td>TOTAL</td><td class="text-end">{{ number_format($totalPurchaseCost, 2) }}</td><td class="text-end">{{ number_format($totalSales, 2) }}</td></tr></tfoot>
            </table>
        </div>
    </div>
</div>

<div class="card">
    <div class="card-header"><h5 class="card-title mb-0">Profit &amp; Loss by Drink</h5></div>
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-hover">
                <thead><tr><th>Drink</th><th class="text-end">Qty Purchased</th><th class="text-end">Purchase Cost</th><th class="text-end">Qty Sold</th><th class="text-end">Sales</th><th class="text-end">Avg Unit Cost</th><th class="text-end">Cost of Sales</th><th class="text-end">Profit / (Loss)</th></tr></thead>
                <tbody>
                    @forelse($drinkRows as $row)
                        <tr>
                            <td>{{ $row['item']->name }}</td>
                            <td class="text-end">{{ number_format($row['quantity_purchased']) }}</td>
                            <td class="text-end">{{ number_format($row['purchase_cost'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['quantity_sold']) }}</td>
                            <td class="text-end">{{ number_format($row['sales_total'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['average_unit_cost'], 2) }}</td>
                            <td class="text-end">{{ number_format($row['cost_of_sales'], 2) }}</td>
                            <td class="text-end {{ $row['profit_loss'] < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($row['profit_loss'], 2) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-muted py-4">No drinks were purchased or sold for this period.</td></tr>
                    @endforelse
                </tbody>
                <tfoot><tr class="table-light fw-bold"><td>TOTAL</td><td class="text-end">{{ number_format($drinkRows->sum('quantity_purchased')) }}</td><td class="text-end">{{ number_format($totalPurchaseCost, 2) }}</td><td class="text-end">{{ number_format($drinkRows->sum('quantity_sold')) }}</td><td class="text-end">{{ number_format($totalSales, 2) }}</td><td></td><td class="text-end">{{ number_format($totalCostOfSales, 2) }}</td><td class="text-end">{{ number_format($totalProfitLoss, 2) }}</td></tr></tfoot>
            </table>
        </div>
        <p class="small text-muted mb-0">Cost of sales uses the weighted average recorded purchase cost through the end of the selected period. When purchase history is unavailable, the drink's current buying price is used.</p>
        <button type="button" class="btn btn-secondary mt-3" onclick="window.print()">Print</button>
    </div>
</div>
@endsection

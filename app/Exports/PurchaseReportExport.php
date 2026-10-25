<?php

namespace App\Exports;

use App\Models\Purchase;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseReportExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly Collection $purchases) {}

    public function collection(): Collection
    {
        $rows = $this->purchases->map(fn (Purchase $purchase) => [
            $purchase->purchased_at?->format('Y-m-d'),
            $purchase->menuItem?->name,
            $purchase->quantity,
            (float) $purchase->unit_cost,
            (float) $purchase->total_cost,
            $purchase->supplier ?: '-',
            $purchase->performedBy?->name ?? 'Not recorded',
        ]);

        $rows->push([null, null, null, 'Total', (float) $this->purchases->sum('total_cost'), null, null]);

        return $rows;
    }

    public function headings(): array
    {
        return ['Date', 'Item', 'Quantity', 'Unit Cost', 'Total Cost', 'Supplier', 'Last action by'];
    }
}

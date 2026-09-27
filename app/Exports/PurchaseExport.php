<?php

namespace App\Exports;

use App\Models\Purchase;
use IlluminateSupportCollection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PurchaseExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly ?string $fromDate = null, private readonly ?string $toDate = null) {}

    public function collection(): Collection
    {
        return Purchase::with('menuItem')
            ->when(! (auth()->user()?->hasRole('hotel_manager') ?? false), fn ($query) => $query->where('lodge_id', auth()->user()?->lodge_id))
            ->when($this->fromDate, fn ($query) => $query->whereDate('purchased_at', '>=', $this->fromDate))
            ->when($this->toDate, fn ($query) => $query->whereDate('purchased_at', '<=', $this->toDate))
            ->latest('purchased_at')->latest()->get()
            ->map(fn (Purchase $purchase) => [
                $purchase->purchased_at?->format('Y-m-d'),
                $purchase->created_at?->format('Y-m-d H:i'),
                $purchase->menuItem?->name,
                $purchase->quantity,
                $purchase->unit_cost,
                $purchase->total_cost,
                $purchase->supplier ?: '-',
            ]);
    }

    public function headings(): array
    {
        return ['Date', 'Time Registered', 'Item', 'Quantity', 'Unit Cost', 'Total Cost', 'Supplier'];
    }
}

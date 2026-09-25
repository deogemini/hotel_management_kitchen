<?php

namespace App\Exports;

use App\Models\MenuItem;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class StockExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly bool $drinksOnly = false) {}

    public function collection(): Collection
    {
        $query = MenuItem::query();

        if (! (auth()->user()?->hasRole('hotel_manager') ?? false)) {
            $query->where('lodge_id', auth()->user()?->lodge_id);
        }

        if ($this->drinksOnly) {
            $query->where('category', 'Drinks');
        }

        return $query->orderBy('category')->orderBy('name')->get()->map(function (MenuItem $item) {
            return [
                $item->name,
                $item->category,
                $item->stock_quantity,
                $item->low_stock_quantity,
                $item->stock_quantity <= 0 ? 'Out of stock' : ($item->stock_quantity <= $item->low_stock_quantity ? 'Low stock' : 'In stock'),
            ];
        });
    }

    public function headings(): array
    {
        return ['Item', 'Category', 'Current Stock', 'Low Alert', 'Status'];
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\AuditService;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::orderBy('category')->orderBy('name')->get();

        return view('stocks.index', compact('menuItems'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $data = $request->validate([
            'stock_quantity' => ['required', 'integer', 'min:0'],
            'low_stock_quantity' => ['required', 'integer', 'min:0'],
        ]);

        $original = $menuItem->only(['stock_quantity', 'low_stock_quantity']);
        $menuItem->update($data);
        AuditService::log('stock.update', $menuItem, ['from' => $original, 'to' => $data]);

        return back()->with('success', 'Stock updated successfully.');
    }
}

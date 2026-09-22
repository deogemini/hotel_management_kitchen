<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Purchase;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = $this->lodgeQuery(Purchase::with('menuItem'))->latest('purchased_at')->latest()->get();
        return view('purchases.index', compact('purchases'));
    }

    public function create()
    {
        $menuItems = $this->lodgeQuery(MenuItem::query())->orderBy('name')->get();
        return view('purchases.create', compact('menuItems'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'menu_item_id' => ['required', 'exists:menu_items,id'],
            'quantity' => ['required', 'integer', 'min:1'],
            'unit_cost' => ['required', 'numeric', 'min:0'],
            'supplier' => ['nullable', 'string', 'max:255'],
            'purchased_at' => ['required', 'date'],
            'notes' => ['nullable', 'string', 'max:1000'],
        ]);

        DB::transaction(function () use ($data, $request) {
            $item = $this->lodgeQuery(MenuItem::query())->lockForUpdate()->findOrFail($data['menu_item_id']);
            $total = $data['quantity'] * $data['unit_cost'];
            $purchase = Purchase::create([
                ...$data,
                'lodge_id' => $item->lodge_id,
                'total_cost' => $total,
                'created_by' => auth()->id(),
            ]);
            $item->update(['buying_price' => $data['unit_cost']]);
            $item->increment('stock_quantity', $data['quantity']);
            AuditService::log('purchase.created', $purchase, ['item' => $item->name, 'quantity' => $data['quantity']]);
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded and stock updated successfully.');
    }

    private function lodgeQuery($query)
    {
        if (! (auth()->user()?->hasRole('hotel_manager') ?? false)) {
            $query->where('lodge_id', auth()->user()?->lodge_id);
        }
        return $query;
    }
}

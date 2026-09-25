<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Models\Purchase;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PurchaseController extends Controller
{
    public function index()
    {
        $purchases = $this->lodgeQuery(Purchase::with('menuItem'))->latest('purchased_at')->latest()->get();
        $totalCost = $purchases->sum('total_cost');
        return view('purchases.index', compact('purchases', 'totalCost'));
    }

    public function create()
    {
        $menuItems = $this->lodgeQuery(MenuItem::query())->orderBy('name')->get();
        $suppliers = $this->lodgeQuery(Supplier::query())->orderBy('name')->get();
        return view('purchases.create', compact('menuItems', 'suppliers'));
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
            StockMovement::create([
                'lodge_id' => $item->lodge_id, 'menu_item_id' => $item->id, 'type' => 'purchase',
                'quantity' => $data['quantity'], 'stock_before' => $item->stock_quantity,
                'stock_after' => $item->stock_quantity + $data['quantity'], 'unit_price' => $data['unit_cost'],
                'reference_type' => Purchase::class, 'reference_id' => $purchase->id,
                'movement_date' => $data['purchased_at'], 'created_by' => auth()->id(),
            ]);
            $item->increment('stock_quantity', $data['quantity']);
            AuditService::log('purchase.created', $purchase, ['item' => $item->name, 'quantity' => $data['quantity']]);
        });

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded and stock updated successfully.');
    }

    public function destroy(Purchase $purchase)
    {
        abort_unless(strtolower((string) auth()->user()?->effectiveRoleName()) === 'owner', 403);
        DB::transaction(function () use ($purchase) {
            $item = $this->lodgeQuery(MenuItem::query())->lockForUpdate()->findOrFail($purchase->menu_item_id);
            $item->update(['stock_quantity' => max(0, $item->stock_quantity - $purchase->quantity)]);
            StockMovement::where('reference_type', Purchase::class)->where('reference_id', $purchase->id)->delete();
            $purchase->delete();
        });
        return redirect()->route('purchases.index')->with('success', 'Purchase deleted and stock adjusted successfully.');
    }

    private function lodgeQuery($query)
    {
        if (! (auth()->user()?->hasRole('hotel_manager') ?? false)) {
            $query->where('lodge_id', auth()->user()?->lodge_id);
        }
        return $query;
    }
}

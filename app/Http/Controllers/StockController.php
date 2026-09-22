<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;

class StockController extends Controller
{
    public function index()
    {
        $menuItems = $this->lodgeQuery(MenuItem::query())->orderBy('category')->orderBy('name')->get();

        return view('stocks.index', compact('menuItems'));
    }

    public function drinks()
    {
        $menuItems = $this->lodgeQuery(MenuItem::query())
            ->where('category', 'Drinks')
            ->orderBy('name')
            ->get();

        return view('stocks.index', compact('menuItems'))->with('isDrinksPage', true);
    }

    private function lodgeQuery($query)
    {
        if (! (auth()->user()?->hasRole('hotel_manager') ?? false)) {
            $query->where('lodge_id', auth()->user()?->lodge_id);
        }

        return $query;
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Exports\StockExport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;

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

    public function excel(Request $request)
    {
        $drinksOnly = $request->boolean('drinks');
        $name = $drinksOnly ? 'drinks-stock.xlsx' : 'restaurant-stock.xlsx';

        return Excel::download(new StockExport($drinksOnly), $name);
    }

    public function pdf(Request $request)
    {
        $drinksOnly = $request->boolean('drinks');
        $query = $this->lodgeQuery(MenuItem::query());

        if ($drinksOnly) {
            $query->where('category', 'Drinks');
        }

        $menuItems = $query->orderBy('category')->orderBy('name')->get();

        return view('stocks.print', compact('menuItems', 'drinksOnly'));
    }

    private function lodgeQuery($query)
    {
        if (! (auth()->user()?->hasRole('hotel_manager') ?? false)) {
            $query->where('lodge_id', auth()->user()?->lodge_id);
        }

        return $query;
    }
}

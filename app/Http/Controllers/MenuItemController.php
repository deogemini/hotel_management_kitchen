<?php

namespace App\Http\Controllers;

use App\Models\MenuItem;
use App\Services\AuditService;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    public function index()
    {
        $menuItems = MenuItem::orderBy('category')->orderBy('name')->get();

        return view('menu_items.index', compact('menuItems'));
    }

    public function create()
    {
        return view('menu_items.create', ['menuItem' => new MenuItem()]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_available'] = $request->boolean('is_available');
        $data['created_by'] = auth()->id();
        $menuItem = MenuItem::create($data);
        AuditService::log('menu_item.create', $menuItem, $menuItem->getAttributes());

        return redirect()->route('menu-items.index')->with('success', 'Menu item created successfully.');
    }

    public function edit(MenuItem $menuItem)
    {
        return view('menu_items.edit', compact('menuItem'));
    }

    public function update(Request $request, MenuItem $menuItem)
    {
        $data = $this->validated($request);
        $data['is_available'] = $request->boolean('is_available');
        $menuItem->update($data);

        return redirect()->route('menu-items.index')->with('success', 'Menu item updated successfully.');
    }

    public function destroy(MenuItem $menuItem)
    {
        $menuItem->delete();

        return redirect()->route('menu-items.index')->with('success', 'Menu item deleted successfully.');
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
            'price' => ['required', 'numeric', 'min:0'],
            'is_available' => ['nullable', 'boolean'],
        ]);
    }
}

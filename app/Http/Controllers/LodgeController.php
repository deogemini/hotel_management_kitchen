<?php

namespace App\Http\Controllers;

use App\Models\Lodge;
use App\Services\AuditService;
use Illuminate\Http\Request;

class LodgeController extends Controller
{
    public function index()
    {
        $lodges = Lodge::withCount(['users', 'rooms'])->orderBy('name')->get();

        return view('lodges.index', compact('lodges'));
    }

    public function create()
    {
        return view('lodges.create', ['lodge' => new Lodge()]);
    }

    public function store(Request $request)
    {
        $lodge = Lodge::create($this->validated($request));
        AuditService::log('lodge.create', $lodge, $lodge->getAttributes());

        return redirect()->route('lodges.index')->with('success', 'Lodge created successfully.');
    }

    public function edit(Lodge $lodge)
    {
        return view('lodges.edit', compact('lodge'));
    }

    public function update(Request $request, Lodge $lodge)
    {
        $original = $lodge->getOriginal();
        $lodge->update($this->validated($request, $lodge));
        AuditService::log('lodge.update', $lodge, ['from' => $original, 'to' => $lodge->getAttributes()]);

        return redirect()->route('lodges.index')->with('success', 'Lodge updated successfully.');
    }

    private function validated(Request $request, ?Lodge $lodge = null): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:lodges,name,'.($lodge?->id ?? 'NULL')],
            'location' => ['nullable', 'string', 'max:255'],
            'phone_number' => ['nullable', 'string', 'max:50'],
            'description' => ['nullable', 'string'],
        ]);
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\Kanda;
use App\Services\AuditService;
use Illuminate\Http\Request;

class KandaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $kandas = Kanda::withCount('jumuiyas')->get();
        return view('kandas.index', compact('kandas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('kandas.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jina_la_kanda' => 'required|string|max:255',
            'eneo_la_kanda' => 'required|string|max:255',
        ]);

        $kanda = Kanda::create($request->all());
        AuditService::log('kanda.create', $kanda, $kanda->getAttributes());

        return redirect()->route('kandas.index')->with('success', 'Kanda imetengenezwa kikamilifu.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id)
    {
        $kanda = Kanda::findOrFail($id);
        return view('kandas.edit', compact('kanda'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'jina_la_kanda' => 'required|string|max:255',
            'eneo_la_kanda' => 'required|string|max:255',
        ]);

        $kanda = Kanda::findOrFail($id);
        $original = $kanda->getOriginal();
        $kanda->update($request->all());
        $changes = [];
        foreach ($kanda->getChanges() as $key => $value) {
            $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
        }
        AuditService::log('kanda.update', $kanda, $changes);

        return redirect()->route('kandas.index')->with('success', 'Kanda imesasishwa kikamilifu.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $kanda = Kanda::findOrFail($id);
        $kanda->delete();
        AuditService::log('kanda.delete', $kanda, ['deleted' => true]);

        return redirect()->route('kandas.index')->with('success', 'Kanda imefutwa kikamilifu.');
    }
}

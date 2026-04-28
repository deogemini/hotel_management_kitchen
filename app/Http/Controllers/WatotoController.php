<?php

namespace App\Http\Controllers;

use App\Models\Watoto;
use App\Models\Jumuiya;
use App\Services\AuditService;
use Illuminate\Http\Request;

class WatotoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $watotos = Watoto::with('jumuiya')->withCount('shukranis')->orderBy('jina_la_mtoto')->get();
        return view('watotos.index', compact('watotos'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jumuiyas = Jumuiya::orderBy('jina_la_jumuiya')->get();
        return view('watotos.create', compact('jumuiyas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jumuiya_id' => 'required|exists:jumuiyas,id',
            'jina_la_mtoto' => 'required|string|max:255',
            'tarehe_ya_kuzaliwa' => 'nullable|date',
            'namba_ya_mzazi' => 'nullable|string|max:20',
        ]);
        $mtoto = Watoto::create($request->all());
        AuditService::log('watoto.create', $mtoto, $mtoto->getAttributes());
        return redirect()->route('watotos.index')->with('success', 'Mtoto amesajiliwa kikamilifu.');
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
        $mtoto = Watoto::findOrFail($id);
        $jumuiyas = Jumuiya::orderBy('jina_la_jumuiya')->get();
        return view('watotos.edit', compact('mtoto', 'jumuiyas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'jumuiya_id' => 'required|exists:jumuiyas,id',
            'jina_la_mtoto' => 'required|string|max:255',
            'tarehe_ya_kuzaliwa' => 'nullable|date',
            'namba_ya_mzazi' => 'nullable|string|max:20',
        ]);
        $mtoto = Watoto::findOrFail($id);
        $original = $mtoto->getOriginal();
        $mtoto->update($request->all());
        $changes = [];
        foreach ($mtoto->getChanges() as $key => $value) {
            $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
        }
        AuditService::log('watoto.update', $mtoto, $changes);
        return redirect()->route('watotos.index')->with('success', 'Taarifa za mtoto zimesasishwa kikamilifu.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mtoto = Watoto::findOrFail($id);
        $mtoto->delete();
        AuditService::log('watoto.delete', $mtoto, ['deleted' => true]);
        return redirect()->route('watotos.index')->with('success', 'Mtoto amefutwa kikamilifu.');
    }
}

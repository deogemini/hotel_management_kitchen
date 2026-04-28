<?php

namespace App\Http\Controllers;

use App\Models\Jumuiya;
use App\Models\Kanda;
use App\Services\AuditService;
use Illuminate\Http\Request;

class JumuiyaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $query = Jumuiya::with('kanda')->withCount('wanajumuiya');
        if (request()->filled('kanda_id')) {
            $query->where('kanda_id', request('kanda_id'));
        }
        $jumuiyas = $query->get();
        $kandas = Kanda::orderBy('jina_la_kanda')->get();
        return view('jumuiyas.index', compact('jumuiyas', 'kandas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $kandas = Kanda::all();
        return view('jumuiyas.create', compact('kandas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kanda_id' => 'required|exists:kandas,id',
            'jina_la_jumuiya' => 'required|string|max:255',
        ]);

        $jumuiya = Jumuiya::create($request->all());
        AuditService::log('jumuiya.create', $jumuiya, $jumuiya->getAttributes());

        return redirect()->route('jumuiyas.index')->with('success', 'Jumuiya imetengenezwa kikamilifu.');
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
        $jumuiya = Jumuiya::findOrFail($id);
        $kandas = Kanda::all();
        return view('jumuiyas.edit', compact('jumuiya', 'kandas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'kanda_id' => 'required|exists:kandas,id',
            'jina_la_jumuiya' => 'required|string|max:255',
        ]);

        $jumuiya = Jumuiya::findOrFail($id);
        $original = $jumuiya->getOriginal();
        $jumuiya->update($request->all());
        $changes = [];
        foreach ($jumuiya->getChanges() as $key => $value) {
            $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
        }
        AuditService::log('jumuiya.update', $jumuiya, $changes);

        return redirect()->route('jumuiyas.index')->with('success', 'Jumuiya imesasishwa kikamilifu.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $jumuiya = Jumuiya::findOrFail($id);
        $jumuiya->delete();
        AuditService::log('jumuiya.delete', $jumuiya, ['deleted' => true]);

        return redirect()->route('jumuiyas.index')->with('success', 'Jumuiya imefutwa kikamilifu.');
    }
}

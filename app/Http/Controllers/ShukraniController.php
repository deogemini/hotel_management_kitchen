<?php

namespace App\Http\Controllers;

use App\Models\Shukrani;
use App\Models\Watoto;
use App\Models\Jumuiya;
use App\Services\AuditService;
use Illuminate\Http\Request;

class ShukraniController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Shukrani::with('mtoto.jumuiya')->orderByDesc('paid_at');

        if ($request->filled('jumuiya_id')) {
            $query->whereHas('mtoto', function($q) use ($request) {
                $q->where('jumuiya_id', $request->jumuiya_id);
            });
        }

        $shukranis = $query->get();
        $jumuiyas = Jumuiya::orderBy('jina_la_jumuiya')->get();

        return view('shukranis.index', compact('shukranis', 'jumuiyas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $watoto = Watoto::with('jumuiya')->orderBy('jina_la_mtoto')->get();
        $jumuiyas = Jumuiya::orderBy('jina_la_jumuiya')->get();
        $preselectedId = $request->query('watoto_id');
        $preselectedJumuiyaId = null;
        if ($preselectedId) {
            $found = $watoto->firstWhere('id', (int) $preselectedId);
            if ($found) {
                $preselectedJumuiyaId = optional($found->jumuiya)->id;
            }
        }
        $childData = $watoto->map(function($c){
            return [
                'id' => $c->id,
                'name' => $c->jina_la_mtoto,
                'jumuiya_id' => optional($c->jumuiya)->id,
                'jumuiya_name' => optional($c->jumuiya)->jina_la_jumuiya,
            ];
        })->values();
        return view('shukranis.create', compact('watoto', 'jumuiyas', 'preselectedId', 'preselectedJumuiyaId', 'childData'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'watoto_id' => 'required|exists:watotos,id',
            'kiasi' => 'required|numeric|min:0',
            'mode_ya_malipo' => 'nullable|string|max:100',
            'hali_ya_malipo' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
        ]);
        $data = $request->all();
        $data['mode_ya_malipo'] = $data['mode_ya_malipo'] ?? 'cash';
        $data['hali_ya_malipo'] = $data['hali_ya_malipo'] ?? 'full';
        $shukrani = Shukrani::create($data);
        AuditService::log('shukrani.create', $shukrani, $shukrani->getAttributes());
        return redirect()->route('shukranis.index')->with('success', 'Shukrani imeandikishwa kikamilifu.');
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
        $shukrani = Shukrani::findOrFail($id);
        $watoto = Watoto::with('jumuiya')->orderBy('jina_la_mtoto')->get();
        return view('shukranis.edit', compact('shukrani', 'watoto'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'watoto_id' => 'required|exists:watotos,id',
            'kiasi' => 'required|numeric|min:0',
            'mode_ya_malipo' => 'nullable|string|max:100',
            'hali_ya_malipo' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
        ]);
        $shukrani = Shukrani::findOrFail($id);
        $original = $shukrani->getOriginal();
        $data = $request->all();
        $data['mode_ya_malipo'] = $data['mode_ya_malipo'] ?? 'cash';
        $data['hali_ya_malipo'] = $data['hali_ya_malipo'] ?? 'full';
        $shukrani->update($data);
        $changes = [];
        foreach ($shukrani->getChanges() as $key => $value) {
            $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
        }
        AuditService::log('shukrani.update', $shukrani, $changes);
        return redirect()->route('shukranis.index')->with('success', 'Shukrani imesasishwa kikamilifu.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $shukrani = Shukrani::findOrFail($id);
        $shukrani->delete();
        AuditService::log('shukrani.delete', $shukrani, ['deleted' => true]);
        return redirect()->route('shukranis.index')->with('success', 'Shukrani imefutwa kikamilifu.');
    }
}

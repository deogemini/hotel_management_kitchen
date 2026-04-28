<?php

namespace App\Http\Controllers;

use App\Models\Mwanajumuiya;
use App\Models\Jumuiya;
use App\Services\AuditService;
use App\Imports\MwanajumuiyasImport;
use App\Exports\MwanajumuiyaSampleTemplateExport;
use App\Exports\MwanajumuiyaExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;

class MwanajumuiyaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $q = request('q');
        $jumuiyaId = request('jumuiya_id');
        $wanajumuiya = Mwanajumuiya::with('jumuiya')
            ->withCount('zakas')
            ->when($jumuiyaId, function ($query) use ($jumuiyaId) {
                $query->where('jumuiya_id', $jumuiyaId);
            })
            ->when($q, function ($query) use ($q) {
                $query->where(function ($q2) use ($q) {
                    $q2->where('jina_la_mwanajumuiya', 'like', "%{$q}%")
                        ->orWhere('kadi_namba', 'like', "%{$q}%")
                        ->orWhere('namba_ya_simu', 'like', "%{$q}%")
                        ->orWhereHas('jumuiya', function ($jq) use ($q) {
                            $jq->where('jina_la_jumuiya', 'like', "%{$q}%");
                        });
                });
            })
            ->get();
        $jumuiyas = Jumuiya::orderBy('jina_la_jumuiya')->get();
        return view('wanajumuiya.index', compact('wanajumuiya', 'q', 'jumuiyas'));
    }

    public function importForm()
    {
        $jumuiyas = Jumuiya::orderBy('jina_la_jumuiya')->get();
        return view('wanajumuiya.import', compact('jumuiyas'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
            'jumuiya_id' => 'nullable|exists:jumuiyas,id',
        ]);

        $import = new MwanajumuiyasImport($request->jumuiya_id);
        Excel::import($import, $request->file('file'));
        $summary = $import->summary();
        AuditService::log('mwanajumuiya.import', null, [
            'file' => $request->file('file')->getClientOriginalName(),
            'created' => $summary['created'],
            'skipped' => $summary['skipped'],
            'jumuiya_id' => $request->jumuiya_id,
        ]);

        $message = "Import complete: {$summary['created']} created, {$summary['skipped']} skipped.";
        if (!empty($summary['errors'])) {
            $message .= " Errors: ".implode(' | ', array_slice($summary['errors'], 0, 5));
        }

        return redirect()->route('mwanajumuiya.index')->with('success', $message);
    }

    public function sample()
    {
        AuditService::log('mwanajumuiya.sample_download', null, ['template' => 'mwanajumuiya_template.xlsx']);
        return Excel::download(new MwanajumuiyaSampleTemplateExport(), 'mwanajumuiya_template.xlsx');
    }

    public function export()
    {
        AuditService::log('mwanajumuiya.export', null, ['export' => 'wanajumuiya.xlsx']);
        return Excel::download(new MwanajumuiyaExport(), 'wanajumuiya.xlsx');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $jumuiyas = Jumuiya::all();
        return view('wanajumuiya.create', compact('jumuiyas'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'jumuiya_id' => 'required|exists:jumuiyas,id',
            'jina_la_mwanajumuiya' => 'required|string|max:255',
            'kadi_namba' => 'required|string|max:255',
            'namba_ya_simu' => 'required|string|max:20|unique:mwanajumuiyas,namba_ya_simu',
        ]);

        $mwanajumuiya = Mwanajumuiya::create($request->all());
        AuditService::log('mwanajumuiya.create', $mwanajumuiya, $mwanajumuiya->getAttributes());

        return redirect()->route('mwanajumuiya.index')->with('success', 'Mwanajumuiya amesajiliwa kikamilifu.');
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
        $mwanajumuiya = Mwanajumuiya::findOrFail($id);
        $jumuiyas = Jumuiya::all();
        return view('wanajumuiya.edit', compact('mwanajumuiya', 'jumuiyas'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'jumuiya_id' => 'required|exists:jumuiyas,id',
            'jina_la_mwanajumuiya' => 'required|string|max:255',
            'kadi_namba' => 'required|string|max:255',
            'namba_ya_simu' => 'required|string|max:20|unique:mwanajumuiyas,namba_ya_simu,' . $id,
        ]);

        $mwanajumuiya = Mwanajumuiya::findOrFail($id);
        $original = $mwanajumuiya->getOriginal();
        $mwanajumuiya->update($request->all());
        $changes = [];
        foreach ($mwanajumuiya->getChanges() as $key => $value) {
            $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
        }
        AuditService::log('mwanajumuiya.update', $mwanajumuiya, $changes);

        return redirect()->route('mwanajumuiya.index')->with('success', 'Taarifa zimesasishwa kikamilifu.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $mwanajumuiya = Mwanajumuiya::findOrFail($id);
        $mwanajumuiya->delete();
        AuditService::log('mwanajumuiya.delete', $mwanajumuiya, ['deleted' => true]);

        return redirect()->route('mwanajumuiya.index')->with('success', 'Mwanajumuiya amefutwa kikamilifu.');
    }
}

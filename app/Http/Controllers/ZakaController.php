<?php

namespace App\Http\Controllers;

use App\Models\Zaka;
use App\Models\Jumuiya;
use App\Models\Mwanajumuiya;
use App\Imports\ZakasImport;
use App\Exports\ZakaSampleTemplateExport;
use App\Jobs\SendZakaSmsJob;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\AuditService;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class ZakaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Zaka::with('mwanajumuiya.jumuiya');

        if ($request->has('jumuiya_id') && $request->jumuiya_id) {
            $query->whereHas('mwanajumuiya', function ($q) use ($request) {
                $q->where('jumuiya_id', $request->jumuiya_id);
            });
        }

        if ($request->has('mwanajumuiya_id') && $request->mwanajumuiya_id) {
            $query->where('mwanajumuiya_id', $request->mwanajumuiya_id);
        }

        $zakas = $query->orderByDesc('paid_at')->get();
        $jumuiyas = Jumuiya::orderBy('jina_la_jumuiya')->get();

        return view('zakas.index', compact('zakas', 'jumuiyas'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(Request $request)
    {
        $wanajumuiya = Mwanajumuiya::with('jumuiya')->get();
        $jumuiyas = \App\Models\Jumuiya::orderBy('jina_la_jumuiya')->get();
        $preselectedId = $request->query('mwanajumuiya_id');
        $preselectedJumuiyaId = null;
        if ($preselectedId) {
            $found = $wanajumuiya->firstWhere('id', (int) $preselectedId);
            if ($found) {
                $preselectedJumuiyaId = $found->jumuiya->id;
            }
        }
        $memberData = $wanajumuiya->map(function($m){
            return [
                'id' => $m->id,
                'name' => $m->jina_la_mwanajumuiya,
                'jumuiya_id' => optional($m->jumuiya)->id,
                'jumuiya_name' => optional($m->jumuiya)->jina_la_jumuiya,
            ];
        })->values();
        return view('zakas.create', compact('wanajumuiya', 'jumuiyas', 'preselectedId', 'preselectedJumuiyaId', 'memberData'));
    }

    public function importForm()
    {
        return view('zakas.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv',
        ]);

        $import = new ZakasImport();
        Excel::import($import, $request->file('file'));
        $summary = $import->summary();
        AuditService::log('zaka.import', null, [
            'file' => $request->file('file')->getClientOriginalName(),
            'created' => $summary['created'],
            'skipped' => $summary['skipped'],
        ]);

        $message = "Import complete: {$summary['created']} created, {$summary['skipped']} skipped.";
        if (!empty($summary['errors'])) {
            $message .= " Errors: ".implode(' | ', array_slice($summary['errors'], 0, 5));
        }

        return redirect()->route('zakas.index')->with('success', $message);
    }

    public function sample()
    {
        AuditService::log('zaka.sample_download', null, ['template' => 'zaka_template.xlsx']);
        return Excel::download(new ZakaSampleTemplateExport(), 'zaka_template.xlsx');
    }

    public function resendSms(Zaka $zaka)
    {
        SendZakaSmsJob::dispatch($zaka)->afterResponse();
        return back()->with('success', 'SMS imetumwa tena kwenye foleni.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'mwanajumuiya_id' => 'required|exists:mwanajumuiyas,id',
            'kiasi' => 'required|numeric|min:0',
            'risiti_namba' => 'required|string|max:255',
            'mode_ya_malipo' => 'nullable|string|max:100',
            'hali_ya_malipo' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
        ]);

        if (Zaka::where('mwanajumuiya_id', $request->mwanajumuiya_id)
            ->where('risiti_namba', $request->risiti_namba)
            ->whereDate('paid_at', $request->paid_at)
            ->exists()) {
            throw ValidationException::withMessages([
                'risiti_namba' => 'Nambari ya risiti tayari imeandikishwa kwa mwanajumuiya huyu tarehe hii.',
            ]);
        }

        $data = $request->all();
        $data['mode_ya_malipo'] = $data['mode_ya_malipo'] ?? 'cash';
        $data['hali_ya_malipo'] = $data['hali_ya_malipo'] ?? 'full';
        $zaka = Zaka::create($data);
        AuditService::log('zaka.create', $zaka, $zaka->getAttributes());

        return redirect()->route('zakas.index')->with('success', 'Zaka imeandikishwa kikamilifu.');
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
        $zaka = Zaka::findOrFail($id);
        $wanajumuiya = Mwanajumuiya::with('jumuiya')->get();
        return view('zakas.edit', compact('zaka', 'wanajumuiya'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $request->validate([
            'mwanajumuiya_id' => 'required|exists:mwanajumuiyas,id',
            'kiasi' => 'required|numeric|min:0',
            'risiti_namba' => 'required|string|max:255',
            'mode_ya_malipo' => 'nullable|string|max:100',
            'hali_ya_malipo' => 'nullable|string|max:100',
            'paid_at' => 'required|date',
        ]);

        $zaka = Zaka::findOrFail($id);
        if (Zaka::where('mwanajumuiya_id', $request->mwanajumuiya_id)
            ->where('risiti_namba', $request->risiti_namba)
            ->whereDate('paid_at', $request->paid_at)
            ->where('id', '!=', $zaka->id)
            ->exists()) {
            throw ValidationException::withMessages([
                'risiti_namba' => 'Nambari ya risiti tayari imeandikishwa kwa mwanajumuiya huyu tarehe hii.',
            ]);
        }

        $original = $zaka->getOriginal();
        $originalMwanajumuiyaId = $zaka->mwanajumuiya_id;
        $data = $request->all();
        $data['mode_ya_malipo'] = $data['mode_ya_malipo'] ?? 'cash';
        $data['hali_ya_malipo'] = $data['hali_ya_malipo'] ?? 'full';

        if ($originalMwanajumuiyaId != $request->mwanajumuiya_id) {
            $data['sms_sent'] = false;
        }

        $zaka->update($data);
        $changes = [];
        foreach ($zaka->getChanges() as $key => $value) {
            $changes[$key] = ['from' => $original[$key] ?? null, 'to' => $value];
        }
        AuditService::log('zaka.update', $zaka, $changes);

        if ($originalMwanajumuiyaId != $request->mwanajumuiya_id) {
            SendZakaSmsJob::dispatch($zaka)->afterResponse();
        }

        return redirect()->route('zakas.index')->with('success', 'Zaka imesasishwa kikamilifu.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $zaka = Zaka::findOrFail($id);
        $zaka->delete();
        AuditService::log('zaka.delete', $zaka, ['deleted' => true]);

        return redirect()->route('zakas.index')->with('success', 'Zaka imefutwa kikamilifu.');
    }
}

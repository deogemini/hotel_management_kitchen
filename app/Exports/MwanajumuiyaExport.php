<?php

namespace App\Exports;

use App\Models\Mwanajumuiya;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MwanajumuiyaExport implements FromCollection, WithHeadings
{
    public function headings(): array
    {
        return [
            'jina_la_mwanajumuiya',
            'kadi_namba',
            'namba_ya_simu',
            'jumuiya',
        ];
    }

    public function collection()
    {
        return Mwanajumuiya::with('jumuiya')
            ->get()
            ->map(function ($m) {
                return [
                    $m->jina_la_mwanajumuiya,
                    $m->kadi_namba,
                    $m->namba_ya_simu,
                    $m->jumuiya->jina_la_jumuiya,
                ];
            });
    }
}

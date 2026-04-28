<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ZakaSampleTemplateExport implements FromArray, WithHeadings
{
    public function headings(): array
    {
        return [
            'jina_la_mwanajumuiya',
            'jumuiya',
            'kadi_namba',
            'kiasi',
            'risiti_namba',
            'mode_ya_malipo',
            'hali_ya_malipo',
            'paid_at',
        ];
    }

    public function array(): array
    {
        return [
            ['John Doe', 'mtakatifu jeronimo', 'KADI12345', '10000', 'RISITI-001', 'cash', 'full', '2026-01-27 10:30'],
            ['Jane Doe', 'mtakatifu jeronimo', 'KADI67890', '25000', 'RISITI-002', 'mpesa', 'partial', '2026-01-27 11:00'],
        ];
    }
}

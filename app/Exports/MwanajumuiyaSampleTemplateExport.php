<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;

class MwanajumuiyaSampleTemplateExport implements FromArray, WithHeadings
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

    public function array(): array
    {
        return [
            ['John Doe', 'KADI001', '0712345678', 'mtakatifu jeronimo'],
            ['Jane Doe', 'KADI002', '0787654321', 'mtakatifu jeronimo'],
        ];
    }
}

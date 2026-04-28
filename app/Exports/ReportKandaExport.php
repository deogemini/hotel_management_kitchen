<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportKandaExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ?int $year = null,
        protected ?int $month = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null
    ) {}

    public function headings(): array
    {
        return ['Kanda', 'Total Zaka (TZS)'];
    }

    public function collection()
    {
        $query = DB::table('kandas')
            ->join('jumuiyas', 'kandas.id', '=', 'jumuiyas.kanda_id')
            ->join('mwanajumuiyas', 'jumuiyas.id', '=', 'mwanajumuiyas.jumuiya_id')
            ->join('zakas', 'mwanajumuiyas.id', '=', 'zakas.mwanajumuiya_id')
            ->select('kandas.jina_la_kanda', DB::raw('SUM(zakas.kiasi) as total'))
            ->groupBy('kandas.id', 'kandas.jina_la_kanda')
            ->orderByDesc('total');

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $query->whereBetween('zakas.paid_at', [$start, $end]);
        } else {
            if ($this->year) {
                $query->whereYear('zakas.paid_at', $this->year);
            }
            if ($this->month) {
                $query->whereMonth('zakas.paid_at', $this->month);
            }
        }

        return collect($query->get())->map(function ($row) {
            return [$row->jina_la_kanda, $row->total];
        });
    }
}

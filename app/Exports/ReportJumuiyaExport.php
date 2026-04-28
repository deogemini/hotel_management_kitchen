<?php

namespace App\Exports;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportJumuiyaExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ?int $year = null,
        protected ?int $month = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null
    ) {}

    public function headings(): array
    {
        return ['Jumuiya', 'Total Zaka (TZS)'];
    }

    public function collection()
    {
        $query = DB::table('jumuiyas')
            ->join('mwanajumuiyas', 'jumuiyas.id', '=', 'mwanajumuiyas.jumuiya_id')
            ->join('zakas', 'mwanajumuiyas.id', '=', 'zakas.mwanajumuiya_id')
            ->select('jumuiyas.jina_la_jumuiya', DB::raw('SUM(zakas.kiasi) as total'))
            ->groupBy('jumuiyas.id', 'jumuiyas.jina_la_jumuiya')
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
            return [$row->jina_la_jumuiya, $row->total];
        });
    }
}

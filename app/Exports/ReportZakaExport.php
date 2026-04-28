<?php

namespace App\Exports;

use App\Models\Zaka;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportZakaExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ?int $year = null,
        protected ?int $month = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null,
        protected ?int $jumuiyaId = null
    ) {}

    public function headings(): array
    {
        return ['Date', 'Mwanajumuiya', 'Jumuiya', 'Amount (TZS)', 'Receipt No.'];
    }

    public function collection()
    {
        $query = Zaka::with('mwanajumuiya.jumuiya');

        if ($this->jumuiyaId) {
            $query->whereHas('mwanajumuiya', function ($q) {
                $q->where('jumuiya_id', $this->jumuiyaId);
            });
        }

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $query->whereBetween('paid_at', [$start, $end]);
        } else {
            if ($this->year) {
                $query->whereYear('paid_at', $this->year);
            }
            if ($this->month) {
                $query->whereMonth('paid_at', $this->month);
            }
        }

        return $query->orderBy('paid_at', 'desc')
            ->get()
            ->map(function ($z) {
                return [
                    optional($z->paid_at)->format('Y-m-d'),
                    $z->mwanajumuiya->jina_la_mwanajumuiya,
                    $z->mwanajumuiya->jumuiya->jina_la_jumuiya,
                    $z->kiasi,
                    $z->risiti_namba,
                ];
            });
    }
}

<?php

namespace App\Exports;

use App\Models\Zaka;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ReportMwanajumuiyaExport implements FromCollection, WithHeadings
{
    public function __construct(
        protected ?int $mwanajumuiyaId = null,
        protected ?string $startDate = null,
        protected ?string $endDate = null
    ) {}

    public function headings(): array
    {
        return ['Date', 'Mwanajumuiya', 'Jumuiya', 'Amount (TZS)', 'Receipt No.'];
    }

    public function collection()
    {
        $query = Zaka::with('mwanajumuiya.jumuiya');

        if ($this->mwanajumuiyaId) {
            $query->where('mwanajumuiya_id', $this->mwanajumuiyaId);
        }

        if ($this->startDate && $this->endDate) {
            $start = Carbon::parse($this->startDate)->startOfDay();
            $end = Carbon::parse($this->endDate)->endOfDay();
            $query->whereBetween('paid_at', [$start, $end]);
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

<?php

namespace App\Imports;

use App\Models\Mwanajumuiya;
use App\Models\Zaka;
use Carbon\Carbon;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ZakasImport implements ToCollection, WithHeadingRow
{
    protected int $created = 0;
    protected int $skipped = 0;
    protected array $errors = [];

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $kadi = $row['kadi_namba'] ?? null;
                $name = $row['jina_la_mwanajumuiya'] ?? null;
                $jumuiyaName = $row['jumuiya'] ?? null;
                $kiasi = $row['kiasi'] ?? null;
                $risiti = $row['risiti_namba'] ?? null;
                $mode = $row['mode_ya_malipo'] ?? null;
                $hali = $row['hali_ya_malipo'] ?? null;
                $paidAt = $row['paid_at'] ?? null;

                if ((!$kadi && !$name) || !$kiasi || !$risiti || !$mode || !$paidAt) {
                    $this->skipped++;
                    $this->errors[] = "Row ".($index+2).": Missing required fields (need kadi_namba or jina_la_mwanajumuiya).";
                    continue;
                }

                $mwana = null;
                if ($kadi) {
                    $mwana = Mwanajumuiya::where('kadi_namba', $kadi)->first();
                } else {
                    if ($jumuiyaName) {
                        $mwana = Mwanajumuiya::where('jina_la_mwanajumuiya', $name)
                            ->whereHas('jumuiya', function ($q) use ($jumuiyaName) {
                                $q->where('jina_la_jumuiya', $jumuiyaName);
                            })->first();
                    } else {
                        $matches = Mwanajumuiya::where('jina_la_mwanajumuiya', $name)->get();
                        if ($matches->count() === 1) {
                            $mwana = $matches->first();
                        } else {
                            $this->skipped++;
                            $this->errors[] = "Row ".($index+2).": Mwanajumuiya '{$name}' ambiguous or not found. Provide 'jumuiya' or 'kadi_namba'.";
                            continue;
                        }
                    }
                }
                if (!$mwana) {
                    $this->skipped++;
                    $this->errors[] = "Row ".($index+2).": Mwanajumuiya not found (kadi_namba='{$kadi}' name='{$name}' jumuiya='{$jumuiyaName}').";
                    continue;
                }

                if (!$risiti) {
                    $this->skipped++;
                    $this->errors[] = "Row ".($index+2).": Missing 'risiti_namba'.";
                    continue;
                }

                $paidDate = Carbon::parse($paidAt)->toDateString();
                if (Zaka::where('mwanajumuiya_id', $mwana->id)
                    ->where('risiti_namba', $risiti)
                    ->whereDate('paid_at', $paidDate)
                    ->exists()) {
                    $this->skipped++;
                    $this->errors[] = "Row ".($index+2).": Duplicate receipt for this member on {$paidDate}.";
                    continue;
                }

                Zaka::create([
                    'mwanajumuiya_id' => $mwana->id,
                    'kiasi' => (float) $kiasi,
                    'risiti_namba' => (string) $risiti,
                    'mode_ya_malipo' => (string) ($mode ?: 'cash'),
                    'hali_ya_malipo' => (string) ($hali ?: 'full'),
                    'paid_at' => Carbon::parse($paidAt),
                ]);

                $this->created++;
            } catch (\Throwable $e) {
                $this->skipped++;
                $this->errors[] = "Row ".($index+2).": ".$e->getMessage();
            }
        }
    }

    public function summary(): array
    {
        return [
            'created' => $this->created,
            'skipped' => $this->skipped,
            'errors' => $this->errors,
        ];
    }
}

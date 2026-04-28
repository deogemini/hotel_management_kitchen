<?php

namespace App\Imports;

use App\Models\Jumuiya;
use App\Models\Mwanajumuiya;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class MwanajumuiyasImport implements ToCollection, WithHeadingRow
{
    protected int $created = 0;
    protected int $skipped = 0;
    protected array $errors = [];
    protected ?int $jumuiyaId = null;

    public function __construct(?int $jumuiyaId = null)
    {
        $this->jumuiyaId = $jumuiyaId;
    }

    protected function getFirstValue(array $row, array $keys): ?string
    {
        foreach ($keys as $key) {
            if (isset($row[$key]) && $row[$key] !== null && $row[$key] !== '') {
                return trim((string) $row[$key]);
            }
        }
        return null;
    }

    protected function normalizePhone(?string $phone): ?string
    {
        if ($phone === null) return null;
        $p = preg_replace('/\s+/', '', $phone);
        return $p;
    }

    public function collection(Collection $rows)
    {
        foreach ($rows as $index => $row) {
            try {
                $data = is_array($row) ? $row : $row->toArray();
                $name = $this->getFirstValue($data, ['jina_la_mwanajumuiya', 'jina', 'mwanajumuiya', 'mwanajumuiya_name']);
                $kadi = $this->getFirstValue($data, ['kadi_namba', 'kadi', 'card_no', 'kadi_no']);
                $simuRaw = $this->getFirstValue($data, ['namba_ya_simu', 'simu', 'phone', 'namba', 'contact']);
                $simu = $this->normalizePhone($simuRaw);
                $jumuiyaName = $this->getFirstValue($data, ['jumuiya', 'jina_la_jumuiya', 'community']);

                if ($this->jumuiyaId) {
                    if (!$name) {
                        $this->skipped++;
                        $missing = [];
                        if (!$name) $missing[] = 'jina_la_mwanajumuiya';
                        $this->errors[] = "Row ".($index+2).": Missing required fields: ".implode(', ', $missing).".";
                        continue;
                    }
                } else {
                    if (!$name || !$jumuiyaName) {
                        $this->skipped++;
                        $missing = [];
                        if (!$name) $missing[] = 'jina_la_mwanajumuiya';
                        if (!$jumuiyaName) $missing[] = 'jumuiya';
                        $this->errors[] = "Row ".($index+2).": Missing required fields: ".implode(', ', $missing).".";
                        continue;
                    }
                }

                if (!$kadi || Mwanajumuiya::where('kadi_namba', $kadi)->exists()) {
                    $kadi = '0';
                }

                if ($simu && $simu !== '' && Mwanajumuiya::where('namba_ya_simu', $simu)->exists()) {
                    $this->skipped++;
                    $this->errors[] = "Row ".($index+2).": Duplicate namba_ya_simu '{$simu}'.";
                    continue;
                }

                if ($this->jumuiyaId) {
                    $jumuiya = Jumuiya::find($this->jumuiyaId);
                    if (!$jumuiya) {
                        $this->skipped++;
                        $this->errors[] = "Row ".($index+2).": Selected Jumuiya not found.";
                        continue;
                    }
                } else {
                    $jumuiya = Jumuiya::where('jina_la_jumuiya', $jumuiyaName)->first();
                    if (!$jumuiya) {
                        $this->skipped++;
                        $this->errors[] = "Row ".($index+2).": Jumuiya '{$jumuiyaName}' not found.";
                        continue;
                    }
                }

                Mwanajumuiya::create([
                    'jumuiya_id' => $jumuiya->id,
                    'jina_la_mwanajumuiya' => (string) $name,
                    'kadi_namba' => (string) $kadi,
                    'namba_ya_simu' => (string) ($simu ?? ''),
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

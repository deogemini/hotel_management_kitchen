<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HotelReportExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    private array $rows;

    private array $sectionRows = [];

    private array $headingRows = [];

    private array $totalRows = [];

    private array $numberFormatRanges = [];

    private int $columnCount = 1;

    public function __construct(
        private readonly string $reportTitle,
        private readonly string $startDate,
        private readonly string $endDate,
        array $sections,
    ) {
        $this->rows = [
            [$this->reportTitle],
            ['Period', $this->startDate, 'to', $this->endDate],
            [],
        ];

        foreach ($sections as $section) {
            $this->columnCount = max($this->columnCount, count($section['headings']));

            if (! empty($section['title'])) {
                $this->sectionRows[] = count($this->rows) + 1;
                $this->rows[] = [$section['title']];
            }

            $this->headingRows[] = count($this->rows) + 1;
            $this->rows[] = $section['headings'];
            $firstDataRow = count($this->rows) + 1;

            $emptyMessage = array_key_exists('empty_message', $section)
                ? $section['empty_message']
                : 'No records were found for this period.';
            if ($section['rows'] === [] && $emptyMessage !== null) {
                $this->rows[] = [$emptyMessage];
            }

            foreach ($section['rows'] as $row) {
                $this->rows[] = $row;
            }

            $lastDataRow = count($this->rows);
            if (isset($section['total'])) {
                $this->totalRows[] = count($this->rows) + 1;
                $this->rows[] = $section['total'];
                $lastDataRow = count($this->rows);
            }

            if ($lastDataRow >= $firstDataRow) {
                foreach ($section['money_columns'] ?? [] as $columnNumber) {
                    $this->numberFormatRanges[] = [$columnNumber, $firstDataRow, $lastDataRow, '#,##0.00'];
                }

                foreach ($section['integer_columns'] ?? [] as $columnNumber) {
                    $this->numberFormatRanges[] = [$columnNumber, $firstDataRow, $lastDataRow, '#,##0'];
                }
            }

            $this->rows[] = [];
        }
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return substr($this->reportTitle, 0, 31);
    }

    public function styles(Worksheet $sheet): array
    {
        $lastColumn = Coordinate::stringFromColumnIndex($this->columnCount);
        $sheet->mergeCells("A1:{$lastColumn}1");

        foreach ($this->sectionRows as $row) {
            $sheet->mergeCells("A{$row}:{$lastColumn}{$row}");
        }

        foreach ($this->numberFormatRanges as [$columnNumber, $firstRow, $lastRow, $format]) {
            $column = Coordinate::stringFromColumnIndex($columnNumber);
            $sheet->getStyle("{$column}{$firstRow}:{$column}{$lastRow}")
                ->getNumberFormat()->setFormatCode($format);
        }

        foreach ($this->totalRows as $row) {
            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")->getFont()->setBold(true);
            $sheet->getStyle("A{$row}:{$lastColumn}{$row}")
                ->getFill()->setFillType('solid')->getStartColor()->setRGB('E9EEF5');
        }

        $styles = [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true, 'color' => ['rgb' => '667085']]],
        ];

        foreach ($this->sectionRows as $row) {
            $styles[$row] = [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DCE6F1']],
            ];
        }

        foreach ($this->headingRows as $row) {
            $styles[$row] = ['font' => ['bold' => true]];
        }

        return $styles;
    }
}

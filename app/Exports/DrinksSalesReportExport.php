<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class DrinksSalesReportExport implements FromArray, ShouldAutoSize, WithStyles, WithTitle
{
    private array $rows;

    private int $dailyTotalRow;

    private int $profitSectionRow;

    private int $profitHeadingsRow;

    private int $profitTotalRow;

    public function __construct(
        private readonly string $startDate,
        private readonly string $endDate,
        private readonly string $lodgeName,
        array $report,
    ) {
        $this->rows = [
            ['Drinks Sales Report'],
            ['Period', $this->startDate, 'to', $this->endDate],
            ['Lodge', $this->lodgeName],
            [],
            ['Daily Purchases and Sales'],
            ['Date', 'Total Purchase Cost', 'Total Sales'],
        ];

        foreach ($report['dailyTotals'] as $day) {
            $this->rows[] = [
                $day['date'],
                (float) $day['purchase_cost'],
                (float) $day['sales_total'],
            ];
        }

        if ($report['dailyTotals']->isEmpty()) {
            $this->rows[] = ['No drink purchases or sales were recorded for this period.'];
        }

        $this->dailyTotalRow = count($this->rows) + 1;
        $this->rows[] = ['TOTAL', (float) $report['totalPurchaseCost'], (float) $report['totalSales']];
        $this->rows[] = [];

        $this->profitSectionRow = count($this->rows) + 1;
        $this->rows[] = ['Profit & Loss by Drink'];
        $this->profitHeadingsRow = count($this->rows) + 1;
        $this->rows[] = [
            'Drink', 'Qty Purchased', 'Purchase Cost', 'Qty Sold', 'Sales',
            'Avg Unit Cost', 'Cost of Sales', 'Profit / (Loss)',
        ];

        foreach ($report['drinkRows'] as $row) {
            $this->rows[] = [
                $row['item']->name,
                (int) $row['quantity_purchased'],
                (float) $row['purchase_cost'],
                (int) $row['quantity_sold'],
                (float) $row['sales_total'],
                (float) $row['average_unit_cost'],
                (float) $row['cost_of_sales'],
                (float) $row['profit_loss'],
            ];
        }

        if ($report['drinkRows']->isEmpty()) {
            $this->rows[] = ['No drinks were purchased or sold for this period.'];
        }

        $this->profitTotalRow = count($this->rows) + 1;
        $this->rows[] = [
            'TOTAL',
            (int) $report['drinkRows']->sum('quantity_purchased'),
            (float) $report['totalPurchaseCost'],
            (int) $report['drinkRows']->sum('quantity_sold'),
            (float) $report['totalSales'],
            null,
            (float) $report['totalCostOfSales'],
            (float) $report['totalProfitLoss'],
        ];
        $this->rows[] = [
            "Cost of sales uses the weighted average recorded purchase cost through the end of the selected period. When purchase history is unavailable, the drink's current buying price is used.",
        ];
    }

    public function array(): array
    {
        return $this->rows;
    }

    public function title(): string
    {
        return 'Drinks Sales';
    }

    public function styles(Worksheet $sheet): array
    {
        $sheet->mergeCells('A1:H1');
        $sheet->mergeCells('A5:H5');
        $sheet->mergeCells("A{$this->profitSectionRow}:H{$this->profitSectionRow}");
        $sheet->mergeCells('A'.($this->profitTotalRow + 1).':H'.($this->profitTotalRow + 1));

        $sheet->getStyle("B7:C{$this->dailyTotalRow}")
            ->getNumberFormat()->setFormatCode('#,##0.00');

        $firstProfitDataRow = $this->profitHeadingsRow + 1;
        $sheet->getStyle("B{$firstProfitDataRow}:B{$this->profitTotalRow}")
            ->getNumberFormat()->setFormatCode('#,##0');
        $sheet->getStyle("D{$firstProfitDataRow}:D{$this->profitTotalRow}")
            ->getNumberFormat()->setFormatCode('#,##0');
        foreach (['C', 'E', 'F', 'G', 'H'] as $column) {
            $sheet->getStyle("{$column}{$firstProfitDataRow}:{$column}{$this->profitTotalRow}")
                ->getNumberFormat()->setFormatCode('#,##0.00');
        }

        $sheet->getStyle("A{$this->dailyTotalRow}:H{$this->dailyTotalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$this->dailyTotalRow}:H{$this->dailyTotalRow}")
            ->getFill()->setFillType('solid')->getStartColor()->setRGB('E9EEF5');
        $sheet->getStyle("A{$this->profitTotalRow}:H{$this->profitTotalRow}")->getFont()->setBold(true);
        $sheet->getStyle("A{$this->profitTotalRow}:H{$this->profitTotalRow}")
            ->getFill()->setFillType('solid')->getStartColor()->setRGB('E9EEF5');

        return [
            1 => ['font' => ['bold' => true, 'size' => 16]],
            2 => ['font' => ['italic' => true, 'color' => ['rgb' => '667085']]],
            3 => ['font' => ['italic' => true, 'color' => ['rgb' => '667085']]],
            5 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DCE6F1']],
            ],
            6 => ['font' => ['bold' => true]],
            $this->profitSectionRow => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => 'DCE6F1']],
            ],
            $this->profitHeadingsRow => ['font' => ['bold' => true]],
            $this->profitTotalRow + 1 => ['font' => ['italic' => true, 'color' => ['rgb' => '667085']]],
        ];
    }
}

<?php

namespace App\Exports;

use App\Models\Payment;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class PaymentExport implements FromCollection, WithHeadings, ShouldAutoSize
{
    public function __construct(private readonly Collection $payments) {}

    public function collection(): Collection
    {
        return $this->payments->map(fn (Payment $payment) => [
            $payment->payment_number,
            $payment->customerName(),
            $payment->purposeLabel(),
            $payment->payment_method,
            $payment->amount,
            $payment->paid_at?->format('Y-m-d H:i'),
            $payment->performedBy?->name ?? 'Not recorded',
        ]);
    }

    public function headings(): array
    {
        return ['Receipt', 'Customer', 'For', 'Method', 'Amount', 'Timeline', 'Last action by'];
    }
}

<?php

namespace App\Exports;

use App\Models\OfflinePayment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class OfflinePaymentsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return OfflinePayment::with('client', 'markedByUser')
            ->orderBy('period_year', 'desc')
            ->orderBy('period_month', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Invoice #',
            'Client',
            'Billing Mode',
            'Period',
            'Amount (RM)',
            'Notes',
            'Paid At',
            'Marked By',
        ];
    }

    public function map($payment): array
    {
        return [
            $payment->invoice_number ?? '—',
            $payment->client->company_name ?? '—',
            ucfirst($payment->client->billing_mode ?? 'fixed'),
            \Carbon\Carbon::create($payment->period_year, $payment->period_month)->format('F Y'),
            number_format($payment->amount, 2),
            $payment->notes ?? '',
            $payment->paid_at ? $payment->paid_at->format('d M Y, h:i A') : '—',
            $payment->markedByUser->name ?? '—',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'size' => 11]],
        ];
    }
}

<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;
    protected $orders;

    public function __construct(array $filters = [], $orders = null)
    {
        $this->filters = $filters;
        $this->orders = $orders;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        return collect($this->orders);
    }

    public function headings(): array
    {
        return [
            'Order / Invoice ID',
            'Date',
            'Customer Name',
            'Email',
            'Phone',
            'Company',
            'Website',
            'Plan',
            'Strategy',
            'Amount',
            'Status',
        ];
    }

    public function map($order): array
    {
        return [
            $order->order_id,
            $order->created_at->format('Y-m-d H:i'),
            $order->customer_name,
            $order->customer_email,
            $order->phone,
            $order->company_name,
            $order->website_url,
            $order->plan,
            $order->strategy,
            $order->total_amount,
            $order->status,
        ];
    }
}

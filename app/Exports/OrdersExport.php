<?php

namespace App\Exports;

use App\Models\Order;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class OrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $filters;

    public function __construct(array $filters = [])
    {
        $this->filters = $filters;
    }

    /**
     * @return \Illuminate\Support\Collection
     */
    public function collection()
    {
        $query = Order::query();

        if (isset($this->filters['date_start']) && $this->filters['date_start']) {
            $query->whereDate('created_at', '>=', $this->filters['date_start']);
        }
        if (isset($this->filters['date_end']) && $this->filters['date_end']) {
            $query->whereDate('created_at', '<=', $this->filters['date_end']);
        }
        if (isset($this->filters['plan']) && $this->filters['plan'] != 'All') {
            $query->where('plan', $this->filters['plan']);
        }

        return $query->get();
    }

    public function headings(): array
    {
        return [
            'Order ID',
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

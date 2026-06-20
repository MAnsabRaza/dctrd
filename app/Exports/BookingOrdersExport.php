<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingOrdersExport implements FromCollection, WithHeadings, WithMapping
{
    protected $orders;

    public function __construct($orders)
    {
        $this->orders = $orders;
    }

    public function collection()
    {
        return $this->orders;
    }

    public function headings(): array
    {
        return [
            'ID',
            'Booking Title',
            'Customer',
            'Seller',
            'Quantity',
            'Paid Amount',
            'Discount',
            'Tax',
            'Date',
            'Status',
        ];
    }

    public function map($order): array
    {
        return [
            $order->id,
            optional($order->booking)->title ?? '-',
            optional($order->buyer)->full_name ?? '-',
            optional($order->seller)->full_name ?? '-',
            $order->quantity,
            optional($order->sale)->total_amount ?? 0,
            optional($order->sale)->discount ?? 0,
            optional($order->sale)->tax ?? 0,
            dateTimeFormat($order->created_at, 'j F Y H:i'),
            $order->status,
        ];
    }
}
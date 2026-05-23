<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class salesExport implements FromCollection, WithHeadings, WithMapping
{
    protected $sales;

    public function __construct($sales)
    {
        $this->sales = $sales;
    }

    /**
     * @return Collection
     */
    public function collection()
    {
        return $this->sales;
    }

    /**
     * @inheritDoc
     */
    public function headings(): array
    {
        return [
            trans('admin/main.id'),
            trans('admin/main.student'),
            trans('admin/main.student') . ' ' . trans('admin/main.id'),
            trans('admin/main.instructor'),
            trans('admin/main.instructor') . ' ' . trans('admin/main.id'),
            trans('admin/main.paid_amount'),
            trans('update.display_currency'),
            trans('update.product_weight'),
            trans('update.property_area'),
            trans('update.order_distance'),
            trans('admin/main.item'),
            trans('admin/main.item') . ' ' . trans('admin/main.id'),
            trans('admin/main.sale_type'),
            trans('admin/main.date'),
            trans('admin/main.status'),
        ];
    }

    /**
     * @inheritDoc
     */
    public function map($sale): array
    {

        if ($sale->payment_method == \App\Models\Sale::$subscribe) {
            $paidAmount = trans('admin/main.subscribe');
        } else {
            if (!empty($sale->total_amount)) {
                $paidAmount = handlePrice($sale->total_amount);
            } else {
                $paidAmount = trans('public.free');
            }
        }

        $status = (!empty($sale->refund_at)) ? trans('admin/main.refund') : trans('admin/main.success');
        $metrics = getReportUnitMetrics($sale, auth()->user());

        return [
            $sale->id,
            !empty($sale->buyer) ? $sale->buyer->full_name : 'Deleted User',
            !empty($sale->buyer) ? $sale->buyer->id : 'Deleted User',
            $sale->item_seller,
            $sale->seller_id,
            $paidAmount,
            getUserCurrency(),
            data_get($metrics, 'product_weight', '-'),
            data_get($metrics, 'property_area', '-'),
            data_get($metrics, 'order_distance', '-'),
            $sale->item_title,
            $sale->item_id,
            trans('admin/main.' . $sale->type),
            dateTimeFormat($sale->created_at, 'j M Y H:i'),
            $status
        ];
    }
}

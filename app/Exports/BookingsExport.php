<?php

namespace App\Exports;

use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

class BookingsExport implements FromCollection, WithHeadings, WithMapping
{
    protected $bookings;

    public function __construct($bookings)
    {
        $this->bookings = $bookings;
    }

    public function collection()
    {
        return $this->bookings;
    }

    public function headings(): array
    {
        return [
            trans('admin/main.id'),
            trans('admin/main.title'),
            trans('admin/main.creator'),
            trans('admin/main.booking_type'),
            trans('admin/main.category'),
            trans('admin/main.price'),
            trans('admin/main.sales'),
            trans('admin/main.income'),
            trans('admin/main.updated_at'),
            trans('admin/main.created_at'),
            trans('admin/main.status'),
        ];
    }

    public function map($booking): array
    {
        return [
            $booking->id,
            $booking->title,
            !empty($booking->creator) ? $booking->creator->full_name : '-',
            ucfirst($booking->booking_type),
            !empty($booking->category) ? $booking->category->title : '-',
            $this->formatPrice($booking),
            $booking->sales_count ?? 0,
            $this->formatMoney($booking->booking_income ?? 0, $booking->currency),
            dateTimeFormat($booking->updated_at, 'Y M j | H:i'),
            dateTimeFormat($booking->created_at, 'Y M j | H:i'),
            $this->statusLabel($booking->status),
        ];
    }

    private function formatPrice($booking): string
    {
        $price = (!empty($booking->discount_price) and $booking->discount_price < $booking->price)
            ? $booking->discount_price
            : $booking->price;

        return $this->formatMoney($price, $booking->currency);
    }

    private function formatMoney($amount, ?string $currency): string
    {
        return trim(($currency ?: getDefaultCurrency()) . ' ' . number_format((float) $amount, 2));
    }

    private function statusLabel(?string $status): string
    {
        switch ($status) {
            case 'published':
                return trans('admin/main.published');
            case 'pending':
                return trans('admin/main.pending');
            case 'rejected':
            case 'inactive':
                return trans('public.rejected');
            case 'draft':
            default:
                return trans('admin/main.draft');
        }
    }
}

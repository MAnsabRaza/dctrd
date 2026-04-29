<?php

namespace App\Exports;

use App\Models\Testimonial;
use App\Models\TestimonialTranslation;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
class TestimonialsExport implements FromCollection, WithHeadings, WithStyles
{
    public function collection()
    {
        return Testimonial::with('translations') // Load translations properly
            ->get()
            ->map(function ($testimonial) {
                return [
                    'user_avatar' => $testimonial->user_avatar,
                    'user_name' => $testimonial->user_name ?? 'N/A', // Uses the accessor
                    'user_bio' => $testimonial->user_bio ?? 'N/A',
                    'rate' => $testimonial->rate ?? 0,
                    'comment' => $testimonial->comment ?? 'N/A',
                    'status' => $testimonial->status ?? 'disable',
                    'created_at' => $testimonial->created_at ? date('Y-m-d', $testimonial->created_at) : 'N/A',
                ];
            });
    }

    public function headings(): array
    {
        return [
            'User Avatar',
            'User Name',
            'Job Title',
            'Rate',
            'Comment',
            'Status',
            'Created At',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Make the first row (headers) bold
        ];
    }
}


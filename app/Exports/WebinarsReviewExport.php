<?php

namespace App\Exports;

use App\Models\WebinarReview;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class WebinarsReviewExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return WebinarReview::with([
            'webinar:id,slug',
            'bundle:id,slug',
            'creator:id,full_name'
        ])->withCount('comments')->get(); // 🔥 Get comment count here!
    }

    public function headings(): array
    {
        return [
            'Title',
            'Student',
            'Type',
            'Comment',
            'Reply',
            'Rating (5)',
            'Created Date',
            'Status',
        ];
    }

    public function map($review): array
    {
        return [
            $review->webinar->slug ?? ($review->bundle->slug ?? 'N/A'), // Webinar or Bundle slug
            $review->creator->full_name ?? 'N/A', // Customer name
            $review->webinar ? 'Course' : ($review->bundle ? 'Bundle' : 'N/A'), // <-- Added Type Column
            $review->description ?? 'N/A', // Comment
            $review->reply ?? '0', // Reply
            $review->rates ?? 0, // Rating (5)
            (int) ($review->comments_count ?? 0), // Comments count
            $review->created_at->format('Y-m-d'), // Formatted date
            $review->status ?? 'N/A', // Status
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Make column names bold
        ];
    }
}

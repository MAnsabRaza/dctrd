<?php

namespace App\Exports;

use App\Models\ProductReview;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProductReviewExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ProductReview::with([
            'product:id,slug',
            'creator:id,full_name'
        ])->withCount('comments')->get(); // 🔥 Get comment count here!
    }

    public function headings(): array
    {
        return [
            'Product',
            'Customer',
            // 'Comment',
            'Reply',
            'Rating (5)',
            'Created Date',
            'Status',
        ];
    }

    public function map($review): array
    {
        return [
            $review->product->title ?? 'N/A', // Product name
            $review->creator->full_name ?? 'N/A', // Customer name
            // $review->description ?? 'N/A', // Comment
            (int) ($review->comments_count ?? 0),
            $review->rates ?? 0, // Rating (5)
            date('Y-m-d', $review->created_at), // Formatted date
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

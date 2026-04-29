<?php

namespace App\Exports;

use App\Models\ForumRecommendedTopic;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class ForumRecommendedTopicExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ForumRecommendedTopic::with('topics') // Ensure topics are eager-loaded
            ->orderBy('created_at', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            'Icon',
            'Title',
            'Topics', // This is from forum_recommended_topic_items
            'Created At',
        ];
    }

    public function map($recommendedTopic): array
    {
        return [
            $recommendedTopic->icon,
            $recommendedTopic->title,
            $recommendedTopic->topics->count(), // Get topic titles from related topics
            $recommendedTopic->created_at ? date('Y-m-d H:i:s', $recommendedTopic->created_at) : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Bold header row
        ];
    }
}

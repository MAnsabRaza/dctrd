<?php

namespace App\Exports;

use App\Models\ForumFeaturedTopic;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;

class ForumFeaturedTopicExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    public function collection()
    {
        return ForumFeaturedTopic::orderBy('created_at', 'desc')->get();
    }

    public function headings(): array
    {
        return [
            'Topic',
            'Icon',
            'Created At',
        ];
    }

    public function map($featuredTopic): array
    {
        return [
            $featuredTopic->topic ? $featuredTopic->topic->title : 'N/A', // Show topic title instead of ID
            $featuredTopic->icon ,
            $featuredTopic->created_at ? date('Y-m-d H:i:s', $featuredTopic->created_at) : 'N/A',
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Bold header row
        ];
    }
}

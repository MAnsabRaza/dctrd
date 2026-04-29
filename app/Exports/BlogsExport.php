<?php

namespace App\Exports;

use App\Models\Blog;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
class BlogsExport implements  FromCollection, WithHeadings, WithMapping, WithStyles
{


    public function collection()
    {
        return Blog::with([
            'category:id,slug',
            'author:id,full_name',
        ])->withCount('comments')->get();
    }

    public function headings(): array
    {
        return [
            trans('admin/main.title'),
            trans('admin/main.category'),
            trans('admin/main.author'),
            trans('admin/main.comments'),
            trans('admin/main.created_date'),
            trans('admin/main.status'),
        ];
    }


    public function map($blog): array
    {
        return [
            $blog->title,
            $blog->category->title ?? 'N/A',
            $blog->author->full_name ?? 'Deleted',
            $blog->comments_count,
            Carbon::parse($blog->created_at)->format('Y-m-d H:i'), // ✅ Convert timestamp to Date
            trans('admin/main.' . $blog->status),
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Make column names bold
        ];
    }
}

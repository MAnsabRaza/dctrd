<?php

namespace App\Exports;

use App\Models\Forum;
use App\Models\ForumTopic;
use App\Models\ForumTopicPost;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Support\Carbon;


class ForumsExport implements FromCollection, WithHeadings, WithMapping, WithStyles
{
    protected $subForums;

    public function __construct($subForums = null)
    {
        $this->subForums = $subForums;
    }

    public function collection()
    {
        return Forum::where(function ($query) {
            if (!empty($this->subForums)) {
                $query->where('parent_id', $this->subForums);
            } else {
                $query->whereNull('parent_id');
            }
        })
            ->with([
                'subForums' => function ($query) {
                    $query->where('status', 'active');
                },
            ])
            ->orderBy('id', 'desc')
            ->get();
    }

    public function headings(): array
    {
        return [
            trans('admin/main.icon'),
            trans('admin/main.title'),
            trans('admin/main.sub-forums'),
            trans('admin/main.topics'),
            trans('admin/main.posts'),
            trans('admin/main.closed'),
            trans('admin/main.status'),
        ];
    }

    public function map($forum): array
    {
        // Get sub-forums count
        $subForumsCount = $forum->subForums->count();

        // Get topics count
        $forumIds = Forum::where('parent_id', $forum->id)->pluck('id')->toArray();
        $forumIds[] = $forum->id;
        $topicsCount = ForumTopic::whereIn('forum_id', $forumIds)->count();

        // Get posts count
        $topicsIds = ForumTopic::whereIn('forum_id', $forumIds)->pluck('id')->toArray();
        $postsCount = ForumTopicPost::whereIn('topic_id', $topicsIds)->count();

        return [
            $forum->icon, // Assuming 'icon' exists in the table
            $forum->title,
            $subForumsCount,
            $topicsCount,
            $postsCount,
            $forum->closed ? trans('admin/main.yes') : trans('admin/main.no'),
            $forum->status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true]], // Make column headings bold
        ];
    }
}

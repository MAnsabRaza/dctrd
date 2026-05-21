<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    private array $templates = [
        [
            'title' => 'New booking created',
            'template' => '[u.name] created booking [c.title].',
        ],
        [
            'title' => 'Booking updated',
            'template' => '[u.name] updated booking [c.title].',
        ],
        [
            'title' => 'New booking order',
            'template' => '[u.name] booked [c.title]. Amount: [amount]. Date: [time.date]',
        ],
        [
            'title' => 'Booking confirmed',
            'template' => 'Your booking for [c.title] has been confirmed. Amount: [amount]. Date: [time.date]',
        ],
        [
            'title' => 'New booking comment',
            'template' => '[u.name] commented on [c.title].',
        ],
        [
            'title' => 'New booking rating',
            'template' => '[u.name] rated [c.title] with [rate.count] stars.',
        ],
        [
            'title' => 'New booking favorite',
            'template' => '[u.name] added [c.title] to favorites.',
        ],
    ];

    public function up()
    {
        foreach ($this->templates as $data) {

            $exists = DB::table('notification_templates')
                ->where('title', $data['title'])
                ->exists();

            if (!$exists) {

                DB::table('notification_templates')->insert([
                    'title'    => $data['title'],
                    'template' => $data['template'],
                ]);
            }
        }
    }

    public function down()
    {
        foreach ($this->templates as $data) {

            DB::table('notification_templates')
                ->where('title', $data['title'])
                ->delete();
        }
    }
};
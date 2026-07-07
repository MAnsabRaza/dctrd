<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class CalendarSetting extends Model
{
    use HasFactory;
    protected $table = 'calendar_settings';
     protected $fillable = [
        'user_id', 'provider',
        'event_title_template',
        'event_description_template',
        'add_customer_as_attendee',
        'debug_mode', 'ical_export_enabled',
        'sync_status_filter', 'ical_token'
    ];

    protected $casts = [
        'sync_status_filter'        => 'array',
        'add_customer_as_attendee'  => 'boolean',
        'debug_mode'                => 'boolean',
        'ical_export_enabled'       => 'boolean',
    ];
     public function user()
    {
        return $this->belongsTo(User::class);
    }
}

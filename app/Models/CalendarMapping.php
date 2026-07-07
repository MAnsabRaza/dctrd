<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CalendarMapping extends Model
{
    use HasFactory;
    protected $table = 'calendar_mappings';
      protected $fillable = [
        'user_id', 'rocket_entity_type',
        'rocket_entity_id', 'rocket_event_id', 'provider',
        'provider_event_id', 'last_synced_at'
    ];

    protected $casts = [
        'last_synced_at' => 'datetime'
    ];
}

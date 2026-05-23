<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingCalendarIntegration extends Model
{
    use SoftDeletes;

    protected $fillable = ['user_id', 'booking_id', 'provider', 'external_calendar_id', 'access_token', 'refresh_token', 'token_expires_at', 'last_synced_at', 'status', 'settings'];

    protected $hidden = ['access_token', 'refresh_token'];

    protected $casts = ['token_expires_at' => 'datetime', 'last_synced_at' => 'datetime', 'status' => 'boolean', 'settings' => 'array'];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

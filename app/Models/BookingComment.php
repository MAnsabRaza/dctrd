<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingComment extends Model
{
    protected $table = 'booking_comments';

    protected $fillable = [
        'booking_id',
        'user_id',
        'comment',
        'is_active',
    ];

    // ─────────────────────────────────────────────
    // RELATIONS
    // ─────────────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
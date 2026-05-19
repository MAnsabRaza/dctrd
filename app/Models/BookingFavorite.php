<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingFavorite extends Model
{
    protected $table = 'booking_favorites';

    protected $fillable = [
        'user_id',
        'booking_id',
    ];

    // ─────────────────────────────────────────────
    // RELATIONS
    // ─────────────────────────────────────────────

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }
}
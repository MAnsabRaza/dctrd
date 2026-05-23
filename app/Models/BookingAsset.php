<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingAsset extends Model
{
    use SoftDeletes;

    protected $fillable = ['booking_id', 'type', 'path', 'title', 'alt', 'order', 'status', 'meta'];

    protected $casts = ['status' => 'boolean', 'meta' => 'array'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingDiscount extends Model
{
    use SoftDeletes;

    protected $fillable = ['booking_id', 'bundle_id', 'title', 'discount_type', 'amount', 'starts_at', 'expires_at', 'usage_limit', 'used_count', 'status', 'meta'];

    protected $casts = ['amount' => 'float', 'starts_at' => 'datetime', 'expires_at' => 'datetime', 'status' => 'boolean', 'meta' => 'array'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function bundle()
    {
        return $this->belongsTo(BookingBundle::class, 'bundle_id');
    }
}

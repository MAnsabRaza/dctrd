<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingCoupon extends Model
{
    use SoftDeletes;

    protected $fillable = ['code', 'title', 'booking_id', 'bundle_id', 'discount_type', 'amount', 'minimum_order_amount', 'usage_limit', 'used_count', 'starts_at', 'expires_at', 'status', 'meta'];

    protected $casts = ['amount' => 'float', 'minimum_order_amount' => 'float', 'starts_at' => 'datetime', 'expires_at' => 'datetime', 'status' => 'boolean', 'meta' => 'array'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function bundle()
    {
        return $this->belongsTo(BookingBundle::class, 'bundle_id');
    }
}

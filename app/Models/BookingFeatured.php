<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingFeatured extends Model
{
    use SoftDeletes;

    protected $table = 'booking_featured';

    protected $fillable = ['booking_id', 'category_id', 'placement', 'starts_at', 'expires_at', 'order', 'status'];

    protected $casts = ['starts_at' => 'datetime', 'expires_at' => 'datetime', 'status' => 'boolean'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function category()
    {
        return $this->belongsTo(BookingCategory::class, 'category_id');
    }
}

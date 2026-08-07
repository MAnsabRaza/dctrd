<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingReview extends Model
{
    use HasFactory;

    protected $table = 'booking_reviews';
    public $timestamps = false;
    protected $dateFormat = 'U';

    protected $fillable = [
        'booking_id',
        'creator_id',
        'product_quality',
        'purchase_worth',
        'delivery_quality',
        'seller_quality',
        'rates',
        'description',
        'status',
        'created_at'
    ];

    protected $casts = [
        'booking_id' => 'integer',
        'creator_id' => 'integer',
        'product_quality' => 'integer',
        'purchase_worth' => 'integer',
        'delivery_quality' => 'integer',
        'seller_quality' => 'integer',
        'rates' => 'float',
        'description' => 'string',
        'status' => 'string',
        'created_at' => 'timestamp',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function bookings()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id', 'id');
    }

    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id', 'id');
    }

    public function creator()
    {
        return $this->belongsTo('App\User', 'creator_id', 'id');
    }

    public function comments()
    {
        return $this->hasMany('App\Models\Comment', 'booking_review_id', 'id');
    }

    public function getBookingQualityAttribute()
    {
        return $this->product_quality;
    }

    public function getProviderQualityAttribute()
    {
        return $this->seller_quality;
    }

    public function getValueForMoneyAttribute()
    {
        return $this->purchase_worth;
    }

    public function getLocationQualityAttribute()
    {
        return $this->delivery_quality;
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;
class BookingReview extends Model
{
    use HasFactory;

    protected $table = 'booking_reviews';

    protected $fillable = [
        'customer_id',
        'booking_id',
        'order_id',
        'rating',
        'comment',
        'value_rating',
        'delivery_rating',
        'seller_rating',
        'status',
        'reply',
        'replied_at'
    ];

    protected $casts = [
        'rating' => 'integer',
        'value_rating' => 'integer',
        'delivery_rating' => 'integer',
        'seller_rating' => 'integer',
        'replied_at' => 'datetime',
    ];

    // ─── Relationships ───────────────────────────────────────

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function order()
    {
        return $this->belongsTo(Order::class, 'order_id');
    }

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    // ─── Scopes ──────────────────────────────────────────────

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    // ─── Accessors ───────────────────────────────────────────

    public function getAverageRatingAttribute()
    {
        $ratings = array_filter([
            $this->rating,
            $this->value_rating,
            $this->delivery_rating,
            $this->seller_rating,
        ]);

        return count($ratings) ? round(array_sum($ratings) / count($ratings), 1) : 0;
    }
}
<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Booking extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'bookings';
    public $timestamps = false;
    protected $fillable = [

        // Relations
        'creator_id',
        'category_id',

        // Basic info
        'title',
        'slug',
        'booking_type',
        'sub_type',
        'description',
        'requirements',
        'language',
        'thumbnail',
        'cover',

        // Pricing
        'price',
        'price_per',
        'price_unit',
        'discount_price',
        'currency',
        'tax',
        'commission',
        'deposit_enabled',
        'deposit_amount',
        'deposit_type',

        // Capacity
        'capacity',
        'min_persons',
        'max_persons',
        'max_children',
        'children_allowed',

        // Duration
        'duration_minutes',
        'buffer_before',
        'buffer_after',
        'lead_time_hours',
        'cutoff_time_hours',

        // Booking options
        'instant_booking',
        'requires_approval',
        'allow_reschedule',
        'reschedule_before_hours',
        'waitlist_enabled',
        'inventory',

        // Location
        'location_enabled',
        'address_line',
        'city',
        'state',
        'country',
        'postal_code',
        'lat',
        'lng',

        // Extras
        'forum_enabled',
        'comments_enabled',
        'reviews_enabled',
        'checkout_message',
        'reviewer_message',
        'meta',

        // Status
        'status',
        'featured',
        'sales',
        'views',
        'rating',
        'review_count',
    ];
    public $casts = [
        'price' => 'decimal:2',
        'price_per' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'commission' => 'decimal:2',

        'deposit_enabled' => 'boolean',
        'children_allowed' => 'boolean',
        'instant_booking' => 'boolean',
        'requires_approval' => 'boolean',
        'allow_reschedule' => 'boolean',
        'waitlist_enabled' => 'boolean',
        'location_enabled' => 'boolean',
        'forum_enabled' => 'boolean',
        'comments_enabled' => 'boolean',
        'reviews_enabled' => 'boolean',
        'featured' => 'boolean',

        'meta' => 'array',

        'lat' => 'decimal:7',
        'lng' => 'decimal:7',
    ];

    /**
     * Relationships
     */

    // Creator (User)
    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    // Category
    public function category()
    {
        return $this->belongsTo(BookingCategory::class, 'category_id');
    }

    /**
     * Scopes
     */
    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeFeatured($query)
    {
        return $query->where('featured', true);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', ['published', 'pending']);
    }

    /**
     * Accessors
     */
    public function getFullPriceAttribute()
    {
        return $this->discount_price && $this->discount_price > 0
            ? $this->discount_price
            : $this->price;
    }
    public function getFullAddressAttribute()
    {
        return collect([
            $this->address_line,
            $this->city,
            $this->state,
            $this->country,
        ])->filter()->implode(', ');
    }

    /**
     * Boot (Auto Slug - optional but useful)
     */
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->slug)) {
                $booking->slug = \Str::slug($booking->title) . '-' . uniqid();
            }
        });
    }
}
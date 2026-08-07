<?php
namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use App\Models\Traits\HasNearbyLocation;
use App\Traits\HasQrCode;

class Booking extends Model
{
    use HasFactory, SoftDeletes, HasNearbyLocation,HasQrCode;

    protected $table = 'bookings';

    protected $fillable = [
        'creator_id',
        'category_id',
        'title',
        'slug',
        'booking_type',
        'sub_type',
        'description',
        'requirements',
        'language',
        'thumbnail',
        'cover',
        'order',

        // Pricing — migration se match
        'price',
        'price_per',        // decimal(12,2) — migration mein decimal hai, string NAHI
        'price_unit',       // string — "per night", "per adult" etc.
        'discount_price',
        'currency',
        'tax',
        'commission',
        'deposit_enabled',
        'deposit_amount',
        'deposit_type',
        'allowed_customer_groups',

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
        //qr scan code 
        'qr_enabled',
        'short_url',
        'short_code',
        'qr_image_path',
        'qr_last_refreshed_at',
        'qr_revoked_at',
    ];

    protected $casts = [
        // Pricing — migration se exact match
        'price' => 'decimal:2',
        'price_per' => 'decimal:2',   // ✅ decimal hai migration mein
        'discount_price' => 'decimal:2',
        'deposit_amount' => 'decimal:2',
        'tax' => 'decimal:2',
        'commission' => 'decimal:2',
        'rating' => 'decimal:1',
        'lat' => 'decimal:8',
        'lng' => 'decimal:8',

        // Booleans
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

        // JSON
        'meta' => 'array',
        'allowed_customer_groups' => 'array',
    ];

    // ─── Relationships ───────────────────────────────────────────────

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function category()
    {
        return $this->belongsTo(BookingCategory::class, 'category_id');
    }

    public function orderItems()
    {
        return $this->hasMany(BookingOrderItem::class, 'booking_id');
    }

    // ─── Scopes ──────────────────────────────────────────────────────

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

    // ─── Accessors ───────────────────────────────────────────────────

    /**
     * Effective price — discount lagao agar hai
     */
    public function getEffectivePriceAttribute(): float
    {
        return ($this->discount_price && $this->discount_price > 0)
            ? (float) $this->discount_price
            : (float) $this->price;
    }

    public function getThumbnailUrlAttribute(): string
    {
        if (!$this->thumbnail) {
            return asset('assets/default/img/icons/installment/meeting_default.svg');
        }

        if (str_starts_with($this->thumbnail, '/')) {
            return asset(ltrim($this->thumbnail, '/'));
        }

        return str_starts_with($this->thumbnail, 'http')
            ? $this->thumbnail
            : asset('storage/' . $this->thumbnail);
    }

    public function getCoverUrlAttribute(): string
    {
        if (empty($this->cover)) {
            return $this->thumbnail_url;
        }

        if (str_starts_with($this->cover, '/')) {
            return asset(ltrim($this->cover, '/'));
        }

        return str_starts_with($this->cover, 'http')
            ? $this->cover
            : asset('storage/' . $this->cover);
    }

    /**
     * Full address string
     */
    public function getFullAddressAttribute(): string
    {
        return collect([$this->address_line, $this->city, $this->state, $this->country])
            ->filter()
            ->implode(', ');
    }

    /**
     * Public URL — apne project ka route yahan use karo
     */
    public function getUrl(): ?string
    {
        if (!$this->slug)
            return null;
        return url('/bookings/' . $this->slug);
    }
    public function getPublicUrlAttribute(): string
    {
        return $this->getUrl() ?? url('/');
    }

    public function getPriceLabelAttribute(): string
    {
        if (empty($this->effective_price) or (float) $this->effective_price <= 0) {
            return trans('public.free');
        }

        return formatCurrencyForUser((float) $this->effective_price, auth()->user(), $this->currency ?: getDefaultCurrency());
    }

    public function getRate(): float
{
    $rate = 0;

    $reviews = $this->reviews()
        ->where('status', 'active')
        ->get();

    if (!empty($reviews) and $reviews->count() > 0) {
        $rate = number_format($reviews->avg('rates'), 2);
    }

    if ($rate > 5) {
        $rate = 5;
    }

    return $rate > 0 ? (float) number_format($rate, 2) : 0;
}

public function getRateCount(): int
{
    return $this->reviews()
        ->where('status', 'active')
        ->count();
}

public function checkUserHasBought($user = null): bool
{
    $user = $user ?: auth()->user();

    if (empty($user)) {
        return false;
    }

    return BookingOrder::query()
        ->where('booking_id', $this->id)
        ->where('buyer_id', $user->id)
        ->whereNotIn('status', [BookingOrder::$pending, BookingOrder::$canceled])
        ->exists();
}

    // ─── Auto Slug ───────────────────────────────────────────────────

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            if (empty($booking->slug)) {
                $booking->slug = Str::slug($booking->title) . '-' . uniqid();
            }
        });
    }


    public function resources()
    {
        return $this->hasMany(BookingResource::class, 'booking_id');
    }
    public function ratePlans()
    {
        return $this->hasMany(BookingRatePlan::class, 'booking_id');
    }
    public function seasons()
    {
        return $this->hasMany(BookingSeason::class);
    }
    public function availabilities()
    {
        return $this->hasMany(BookingAvailability::class);
    }
    public function policy()
    {
        return $this->hasOne(BookingPolicy::class, 'booking_id');
    }
    public function variants()
    {
        return $this->hasMany(BookingVariant::class)->orderBy('sort_order');
    }
   public function specifications()
{
    return $this->hasManyThrough(
        BookingSpecification::class,
        BookingCategory::class,
        'id',          // booking_categories.id
        'id',          // booking_specifications.id — yeh hasManyThrough ke liye sahi nahi
        'category_id', // bookings.category_id
        'id'
    );
}

public function getCategorySpecificationsAttribute()
{
    if (empty($this->category_id)) return collect();
    
    return BookingSpecification::query()
        ->active()
        ->ordered()
        ->whereHas('categories', fn($q) => $q->where('booking_categories.id', $this->category_id))
        ->orWhereDoesntHave('categories')
        ->with('bookingValues')
        ->get();
}
    public function reviews()
    {
        return $this->hasMany(BookingReview::class, 'booking_id');
    }
    public function comments()
    {
        return $this->hasMany(BookingComment::class, 'booking_id');
    }
    public function favorites()
    {
        return $this->hasMany(BookingFavorite::class, 'booking_id');
    }
    public function bundleItems()
    {
        return $this->hasMany(BookingBundleItem::class, 'booking_id');
    }
     public function faqs()
    {
        return $this->hasMany(BookingFaqs::class, 'booking_id');
    }

    // Bundles relation (many-to-many)
    public function bundles()
    {
        return $this->belongsToMany(
            BookingBundle::class,
            'booking_bundle_items',
            'booking_id',
            'bundle_id'
        )->withPivot(['quantity', 'sort_order'])->withTimestamps();
    }

    public function orders(){
        return $this->hasMany(BookingOrder::class, 'booking_id');
    }
    public function packageItems()
    {
        return $this->hasMany(BookingPackageItem::class, 'booking_id');
    }

    public function packages()
    {
        return $this->belongsToMany(
            BookingPackage::class,
            'booking_package_items',
            'booking_id',
            'package_id'
        )->withPivot(['resource_id', 'quantity', 'included_minutes', 'sort_order', 'rules'])->withTimestamps();
    }

    public function timeSlots()
    {
        return $this->hasMany(BookingTimeSlot::class, 'booking_id');
    }

    public function slots()
    {
        return $this->hasMany(BookingSlot::class, 'booking_id');
    }

    public function rules()
    {
        return $this->hasMany(BookingRule::class, 'booking_id');
    }

    public function discounts()
    {
        return $this->hasMany(BookingDiscount::class, 'booking_id');
    }

    public function coupons()
    {
        return $this->hasMany(BookingCoupon::class, 'booking_id');
    }

    public function assets()
    {
        return $this->hasMany(BookingAsset::class, 'booking_id');
    }

    public function reports()
    {
        return $this->hasMany(BookingReport::class, 'booking_id');
    }

    public function waitlists()
    {
        return $this->hasMany(BookingWaitlist::class, 'booking_id');
    }

    public function featuredPlacements()
    {
        return $this->hasMany(BookingFeatured::class, 'booking_id');
    }
    // Ek booking ka ek review hoga
   // Ek booking ka ek review hoga
    public function review()
    {
        return $this->hasOne(BookingReview::class, 'booking_id');
    }

    // ════════════════════════════════════════════════════
    // ──── Naya code yahan se shuru ────
    // ════════════════════════════════════════════════════

    public function bookingOrders()
    {
        return $this->hasMany(BookingOrder::class, 'booking_id');
    }

    public function visits()
{
    return $this->morphMany(\App\Models\VisitLog::class, 'targetable');
}

    public function sales($excludeRefunded = false, $orderByLatest = false)
    {
        $query = Sale::query()
            ->join('booking_orders', 'sales.booking_order_id', '=', 'booking_orders.id')
            ->where('booking_orders.booking_id', $this->id)
            ->whereNotIn('booking_orders.status', [BookingOrder::$canceled, BookingOrder::$pending])
            ->select('sales.*');

        if ($excludeRefunded) {
            $query->whereNull('sales.refund_at');
        }

        if ($orderByLatest) {
            $query->orderBy('sales.created_at', 'desc');
        }

        return $query;
    }

    public function salesCount(): int
    {
        return (int) $this->sales(true)->count();
    }

    public function getAvailability(): int
    {
        if (!empty($this->unlimited_inventory) || is_null($this->inventory)) {
            return PHP_INT_MAX;
        }

        $sold = (int) $this->orderItems()
            ->whereHas('order', function ($q) {
                $q->whereNotIn('status', [BookingOrder::$canceled, BookingOrder::$pending]);
            })
            ->sum('quantity');

        return max(0, (int) $this->inventory - $sold);
    }

    public function getActiveDiscount()
    {
        return $this->discounts()
            ->where('status', 'active')
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', now());
            })
            ->first();
    }

    public function getPriceWithActiveDiscountPrice()
    {
        $discount = $this->getActiveDiscount();

        if (empty($discount)) {
            return (float) $this->price;
        }

        if (!empty($discount->percent)) {
            return round((float) $this->price * (1 - ($discount->percent / 100)), 2);
        }

        if (!empty($discount->amount)) {
            return max(0, (float) $this->price - (float) $discount->amount);
        }

        return (float) $this->price;
    }

    public function isOnsite(): bool
    {
        return in_array($this->booking_type, ['tour', 'activity', 'accommodation', 'rental', 'event']);
    }
    

}   

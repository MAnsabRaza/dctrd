<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class BookingBundle extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'booking_bundles'; // ✅ correct

    protected $fillable = [
        'creator_id',
        'title',
        'slug',
        'description',
        'thumbnail',
        'cover',
        'language',
        'price',
        'discount_price',
        'currency',
        'validity_days',
        'availability_status',
        'availability_note',
        'status',
        'featured',
        'sales',
        'rating',
    ];

    protected $casts = [
        'featured' => 'boolean',
        'price' => 'float',
        'discount_price' => 'float',
        'rating' => 'float',
    ];


    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function items()
    {
        return $this->hasMany(BookingBundleItem::class, 'bundle_id');
    }
    public function bookings()
    {
        return $this->belongsToMany(
            Booking::class,
            'booking_bundle_items',
            'bundle_id',
            'booking_id'
        )->withPivot(['quantity', 'sort_order'])->withTimestamps();
    }
     public function getThumbnailUrlAttribute(): string
    {
        if (!$this->thumbnail) return asset('assets/images/bundle-placeholder.jpg');
        return str_starts_with($this->thumbnail, 'http')
            ? $this->thumbnail
            : asset('storage/' . $this->thumbnail);
    }
}

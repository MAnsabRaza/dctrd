<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class BookingPackage extends Model
{
    use HasFactory;

    protected $table = 'booking_packages';

    protected $fillable = [
        'creator_id',
        'category_id',
        'title',
        'slug',
        'description',
        'price',
        'discount_price',
        'currency',
        'validity_days',
        'usage_limit',
        'rules',
        'status',
        'featured',
        'sales',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_price' => 'decimal:2',
        'rules' => 'array',
        'featured' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($package) {
            if (empty($package->slug)) {
                $package->slug = Str::slug($package->title) . '-' . uniqid();
            }
        });
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'creator_id');
    }

    public function category()
    {
        return $this->belongsTo(BookingCategory::class, 'category_id');
    }

    public function items()
    {
        return $this->hasMany(BookingPackageItem::class, 'package_id')->orderBy('sort_order');
    }

    public function bookings()
    {
        return $this->belongsToMany(Booking::class, 'booking_package_items', 'package_id', 'booking_id')
            ->withPivot(['resource_id', 'quantity', 'included_minutes', 'sort_order', 'rules'])
            ->withTimestamps();
    }
}

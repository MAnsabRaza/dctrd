<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSpecification extends Model
{
    use HasFactory;

    protected $table = 'booking_specifications';

    protected $fillable = [
        'title',
        'type',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'status'     => 'boolean',
        'sort_order' => 'integer',
    ];

    public function categories()
    {
        return $this->belongsToMany(
            BookingCategory::class,
            'booking_category_specifications',
            'specification_id',
            'category_id'
        );
    }

    public function bookingValues()
    {
        return $this->hasMany(BookingSpecificationValue::class, 'specification_id');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    // کسی category کی specs (global + category-specific)
    public function scopeForCategory($query, int $categoryId)
    {
        return $query->where(function ($q) use ($categoryId) {
            $q->doesntHave('categories')
              ->orWhereHas('categories', fn($q2) =>
                  $q2->where('booking_categories.id', $categoryId)
              );
        });
    }
}
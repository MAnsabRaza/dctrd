<?php

namespace App\Models;

use App\Services\BookingTemplateConfig;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingCategory extends Model
{
    use SoftDeletes;

    protected $table = 'booking_categories';

    protected $fillable = [
        'parent_id',
        'booking_type',   // set on ROOT categories only (1 of 7 parent types)
        'template_key',   // set on CHILD categories only (1 of 23 templates)
        'user_id',
        'title',
        'slug',
        'icon',
        'subtitle',
        'description',
        'order',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function parent()
    {
        return $this->belongsTo(BookingCategory::class, 'parent_id');
    }

    public function children()
    {
        return $this->hasMany(BookingCategory::class, 'parent_id')->orderBy('order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'category_id');
    }

    /**
     * The booking_type this category effectively belongs to.
     * Root category -> its own booking_type.
     * Child category -> inherited from its parent.
     */
    public function getEffectiveBookingTypeAttribute(): ?string
    {
        return $this->booking_type ?: optional($this->parent)->booking_type;
    }

    /**
     * The 23-template config this category maps to.
     * Only child categories carry a template_key; a root category has none
     * (it's just the type grouping, not a bookable template by itself).
     */
    public function getTemplateConfigAttribute(): ?BookingTemplateConfig
    {
        return $this->template_key ? BookingTemplateConfig::for($this->template_key) : null;
    }

    /** Only categories (root or child) that belong to a given booking type */
    public function scopeForType($query, string $type)
    {
        return $query->where(function ($q) use ($type) {
            $q->where('booking_type', $type)
              ->orWhereHas('parent', fn($p) => $p->where('booking_type', $type));
        });
    }

    public function getSelfAndChildBookingsCount($bookingType = null)
    {
        $ids = array_merge([$this->id], $this->children->pluck('id')->toArray());
        $query = Booking::whereIn('category_id', $ids);
        if (!empty($bookingType)) {
            $query->where('booking_type', $bookingType);
        }
        return $query->count();
    }

    public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function specifications()
    {
        return $this->belongsToMany(
            BookingSpecification::class,
            'booking_category_specifications',
            'category_id',
            'specification_id'
        );
    }

    public function getUrl()
    {
        return '/bookings?category_id=' . $this->id;
    }
}
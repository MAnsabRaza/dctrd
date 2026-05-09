<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingVariant extends Model
{
    use HasFactory;

    protected $table = 'booking_variants';

    protected $fillable = [
        'booking_id',
        'name',
        'options',
        'price_modifier',
        'affects_availability',
        'status',
        'sort_order'
    ];

    // ✅ CASTS (VERY IMPORTANT)
    protected $casts = [
        'options' => 'array',                // JSON → array
        'price_modifier' => 'decimal:2',     // decimal
        'affects_availability' => 'boolean', // true/false
        'status' => 'boolean',               // active/inactive
        'sort_order' => 'integer',
    ];

    // ─── RELATIONSHIPS ─────────────────────────────────

    /**
     * Variant belongs to a booking
     */
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    // ─── SCOPES (OPTIONAL but useful) ──────────────────

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }
}
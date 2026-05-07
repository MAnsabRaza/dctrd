<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingRatePlan extends Model
{
    use HasFactory;

    protected $table = 'booking_rate_plan';

    protected $fillable = [
        'booking_id',
        'name',
        'type',
        'price',
        'price_unit',
        'calculation_type',
        'priority',
        'conditions',
        'status'
    ];

    protected $casts = [
        'price' => 'decimal:2',    
        'conditions' => 'array',   
        'status' => 'boolean',   
        'priority' => 'integer',
    ];

    // ✅ Relation
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
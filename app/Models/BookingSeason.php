<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSeason extends Model
{
    use HasFactory;

    protected $table = 'booking_seasons';

    protected $fillable = [
        'booking_id',
        'name',
        'start_date',
        'end_date',
        'price_modifier',
        'modifier_type',
        'status'
    ];

    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',        
        'price_modifier' => 'decimal:4',  
        'status' => 'boolean',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
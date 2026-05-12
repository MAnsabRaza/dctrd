<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
class BookingResource extends Model
{
    use HasFactory;
    protected $table = 'booking_resources';
    protected $fillable = [
        'booking_id',
        'name',
        'type',
        'description',
        'capacity',
        'extra_price',
        'attributes',
        'image',
        'status',
        'order'
    ];
    protected $casts = [
        'attributes' => 'array',
        'status' => 'boolean',
    ];
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
    public function availabilities()
    {
        return $this->hasMany(BookingAvailability::class, 'resource_id');
    }

    public function timeSlots()
    {
        return $this->hasMany(BookingTimeSlot::class, 'resource_id');
    }
}

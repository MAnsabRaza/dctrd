<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingSlot extends Model
{
    use SoftDeletes;

    protected $fillable = ['booking_id', 'resource_id', 'day_of_week', 'date', 'start_time', 'end_time', 'capacity', 'buffer_before', 'buffer_after', 'status'];

    protected $casts = ['date' => 'date', 'status' => 'boolean'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function resource()
    {
        return $this->belongsTo(BookingResource::class, 'resource_id');
    }
}

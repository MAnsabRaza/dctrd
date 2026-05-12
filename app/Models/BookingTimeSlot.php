<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTimeSlot extends Model
{
    use HasFactory;
    protected $table='booking_time_slots';
    protected $fillable=[
        'booking_id',
        'resource_id',
        'day_of_week',
        'start_time',
        'end_time',
        'duration_minutes',
        'buffer_minutes',
        'max_bookings',
        'status'
    ];
    protected $casts = [
        'status' => 'boolean',
        'duration_minutes' => 'integer',
        'buffer_minutes' => 'integer',
        'max_bookings' => 'integer',
    ];

    public function getDayOfWeekAttribute($value)
    {
        if (is_array($value)) {
            return $value;
        }

        if (empty($value)) {
            return [];
        }

        $decoded = json_decode($value, true);
        if (json_last_error() === JSON_ERROR_NONE && is_array($decoded)) {
            return $decoded;
        }

        return array_values(array_filter(explode(',', $value), fn($item) => trim($item) !== ''));
    }

    public function setDayOfWeekAttribute($value)
    {
        if (is_array($value)) {
            $this->attributes['day_of_week'] = json_encode(array_values(array_filter($value, fn($item) => trim((string)$item) !== '')));
            return;
        }

        $this->attributes['day_of_week'] = $value;
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class,'booking_id');
    }

    public function resource()
    {
        return $this->belongsTo(BookingResource::class,'resource_id');
    }
}

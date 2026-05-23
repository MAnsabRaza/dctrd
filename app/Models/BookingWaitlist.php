<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingWaitlist extends Model
{
    use SoftDeletes;

    protected $fillable = ['booking_id', 'resource_id', 'user_id', 'name', 'email', 'phone', 'booking_date', 'start_time', 'end_time', 'persons', 'status', 'meta'];

    protected $casts = ['booking_date' => 'date', 'meta' => 'array'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

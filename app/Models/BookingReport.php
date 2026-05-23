<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingReport extends Model
{
    use SoftDeletes;

    protected $fillable = ['booking_id', 'order_id', 'user_id', 'reason', 'message', 'status', 'reviewed_by', 'reviewed_at', 'meta'];

    protected $casts = ['reviewed_at' => 'datetime', 'meta' => 'array'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

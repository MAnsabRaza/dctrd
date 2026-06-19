<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class BookingFeatured extends Model
{
    use SoftDeletes;

    protected $table = 'booking_featured';

    protected $fillable = ['language','user_id','booking_id', 'page', 'title', 'description', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}

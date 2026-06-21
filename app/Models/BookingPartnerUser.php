<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPartnerUser extends Model
{
    use HasFactory;
    protected $table = 'booking_partner_users';
    protected $fillable = [
        'booking_id',
        'user_id',
    ];
    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id', 'id');
    }
    public function user()
    {
        return $this->belongsTo('App\User', 'user_id', 'id');
    }
}

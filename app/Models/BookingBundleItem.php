<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingBundleItem extends Model
{
    use HasFactory;
    protected $table = 'booking_bundle_items';
    protected $fillable = [
        'bundle_id',
        'booking_id',
        'quantity',
        'sort_order',
    ];

    protected $casts = [
        'quantity' => 'integer',
        'sort_order' => 'integer',
    ];


    public function bundle()
    {
        return $this->belongsTo(BookingBundle::class, 'bundle_id');
    }
    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}
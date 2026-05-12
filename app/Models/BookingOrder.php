<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\User;

class BookingOrder extends Model
{
    use HasFactory;
    protected $table = 'booking_orders';

    protected $fillable = [
        'order_number',
        'user_id',
        'subtotal',
        'discount_amount',
        'tax_amount',
        'total',
        'currency',
        'status',
        'payment_status',
        'notes'
    ];

    protected $casts = [
        'subtotal' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'tax_amount' => 'decimal:2',
        'total' => 'decimal:2',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
    public function items()
    {
        return $this->hasMany(BookingOrderItem::class, 'order_id');
    }
    public function bookings()
    {
        return $this->hasManyThrough(
            Booking::class,
            BookingOrderItem::class,
            'order_id', // Foreign key on BookingOrderItem table...
            'id', // Foreign key on Booking table...
            'id', // Local key on BookingOrder table...
            'booking_id' // Local key on BookingOrderItem table...
        );
    }
    public function bundles()
    {
        return $this->hasManyThrough(
            BookingBundle::class,
            BookingOrderItem::class,
            'order_id', // Foreign key on BookingOrderItem table...
            'id', // Foreign key on BookingBundle table... 
            'id', // Local key on BookingOrder table...
             'bundle_id' // Local key on BookingOrderItem table...
        );

    }
    public function resources()
    {
        return $this->hasManyThrough(
            BookingResource::class,
            BookingOrderItem::class,
            'order_id', // Foreign key on BookingOrderItem table...
            'id', // Foreign key on BookingResource table... 
            'id', // Local key on BookingOrder table...
             'resource_id' // Local key on BookingOrderItem table...
        );
    }


}

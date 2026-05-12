<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingOrderItem extends Model
{
    use HasFactory;

    protected $table = 'booking_order_items';

    protected $fillable = [
        'order_id',
        'item_type',
        'booking_id',
        'bundle_id',
        'resource_id',
        'booking_date',
        'start_time',
        'end_time',
        'quantity',
        'persons',
        'unit_price',
        'total_price',
        'selected_variants',
        'status',
    ];

    protected $casts = [
        'booking_date' => 'date',
        'quantity' => 'integer',
        'persons' => 'integer',
        'unit_price' => 'decimal:2',
        'total_price' => 'decimal:2',
        'selected_variants' => 'array',
    ];

    public function order()
    {
        return $this->belongsTo(BookingOrder::class, 'order_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function bundle()
    {
        return $this->belongsTo(BookingBundle::class, 'bundle_id');
    }

    public function resource()
    {
        return $this->belongsTo(BookingResource::class, 'resource_id');
    }
}

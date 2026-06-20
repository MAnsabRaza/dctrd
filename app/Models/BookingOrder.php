<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;

class BookingOrder extends Model
{
    use HasFactory, SoftDeletes;
    protected $table = 'booking_orders';

    protected $fillable = [
        'booking_id',
        'seller_id',
        'buyer_id',
        'sale_id',
        'booking_discount_id',
        'specifications',
        'quantity',
        'message_to_seller',
        'tracking_code',
        'status',
        'created_at',
    ];

    protected $casts = [
        'specifications' => 'array',
        'quantity' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo('App\Models\Booking', 'booking_id', 'id');
    }

    public function seller()
    {
        return $this->belongsTo('App\User', 'seller_id', 'id');
    }

    public function buyer()
    {
        return $this->belongsTo('App\User', 'buyer_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale', 'sale_id', 'id');
    }

    public function bookingDiscount()
    {
        return $this->belongsTo('App\Models\BookingDiscount', 'booking_discount_id', 'id');
    }

    public function sendBookingNotifications(string $event = 'created'): void
    {
        $this->loadMissing(['booking.creator', 'seller', 'buyer']);

        $title = !empty($this->booking) ? $this->booking->title : ('#' . $this->id);
        $sellerId = !empty($this->booking) ? $this->booking->creator_id : $this->seller_id;

        $notifyOptions = [
            '[c.title]' => $title,
            '[item_title]' => $title,
            '[u.name]' => optional($this->buyer)->full_name,
            '[amount]' => function_exists('handlePrice') ? handlePrice(optional($this->sale)->total_amount ?? 0) : (optional($this->sale)->total_amount ?? 0),
        ];

        if ($event === 'confirmed') {
            sendNotification('booking_order_confirmed', $notifyOptions, $this->buyer_id);
        } else {
            if (!empty($sellerId)) {
                sendNotification('new_booking_order', $notifyOptions, $sellerId);
            }

            sendNotification('new_booking_order', $notifyOptions, 1);
        }
    }
}
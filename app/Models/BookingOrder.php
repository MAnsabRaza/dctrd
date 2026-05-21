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
    public function packages()
    {
        return $this->hasManyThrough(
            BookingPackage::class,
            BookingOrderItem::class,
            'order_id',
            'id',
            'id',
            'package_id'
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

    public function sendBookingNotifications(string $event = 'created'): void
    {
        $this->loadMissing(['user', 'items.booking.creator', 'items.bundle.creator', 'items.package.creator']);

        foreach ($this->items as $item) {
            $title = $this->getNotificationItemTitle($item);
            $sellerId = $this->getNotificationSellerId($item);

            $notifyOptions = [
                '[c.title]' => $title,
                '[item_title]' => $title,
                '[u.name]' => optional($this->user)->full_name,
                '[amount]' => function_exists('handlePrice') ? handlePrice($item->total_price) : $item->total_price,
                '[time.date]' => $item->booking_date
                    ? trim($item->booking_date->format('Y-m-d') . ' ' . $item->start_time)
                    : $this->created_at->format('Y-m-d H:i'),
            ];

            if ($event === 'confirmed') {
                sendNotification('booking_order_confirmed', $notifyOptions, $this->user_id);
            } else {
                if (!empty($sellerId)) {
                    sendNotification('new_booking_order', $notifyOptions, $sellerId);
                }

                sendNotification('new_booking_order', $notifyOptions, 1);
            }
        }
    }

    private function getNotificationItemTitle(BookingOrderItem $item): string
    {
        if (!empty($item->booking)) {
            return $item->booking->title;
        }

        if (!empty($item->bundle)) {
            return $item->bundle->title;
        }

        if (!empty($item->package)) {
            return $item->package->title;
        }

        return $this->order_number;
    }

    private function getNotificationSellerId(BookingOrderItem $item): ?int
    {
        if (!empty($item->booking)) {
            return $item->booking->creator_id;
        }

        if (!empty($item->bundle)) {
            return $item->bundle->creator_id;
        }

        if (!empty($item->package)) {
            return $item->package->creator_id;
        }

        return null;
    }

}

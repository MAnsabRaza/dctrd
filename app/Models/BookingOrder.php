<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\User;
/**
 * @property int $id
 * @property string $status
 * @property \App\Models\Sale|null $sale
 * @property \App\Models\Booking|null $booking
 */
class BookingOrder extends Model
{
     public $timestamps = false;
    use HasFactory, SoftDeletes;
    protected $table = 'booking_orders';

    // ---- Booking status constants ----
    public static $status = [
        'pending',
        'confirmed',
        'completed',
        'cancelled',
        'no_show',
    ];

    public static $pending = 'pending';
    public static $confirmed = 'confirmed';
    public static $completed = 'completed';
    public static $cancelled = 'cancelled';
    public static $noShow = 'no_show';

    // Deprecated aliases kept so older code paths resolve to canonical values.
    public static $waitingDelivery = 'confirmed';
    public static $shipped = 'completed';
    public static $success = 'completed';
    public static $canceled = 'cancelled';
    // ----------------------------------------------------------

    public static function activeStatuses(): array
    {
        return [self::$confirmed, self::$completed];
    }

    public static function paidStatuses(): array
    {
        return [self::$confirmed, self::$completed];
    }

    public static function reservingStatuses(): array
    {
        return [self::$pending, self::$confirmed, self::$completed];
    }

    protected $fillable = [
        'booking_id',
        'bundle_id',
        'seller_id',
         'resource_id',   // ✅ naya
    'booking_date',  // ✅ naya
    'start_time',    // ✅ naya
    'end_time', 
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

    public function resource()
    {
        return $this->belongsTo(BookingResource::class, 'resource_id', 'id');
    }

    public function bundle()
    {
        return $this->belongsTo('App\Models\BookingBundle', 'bundle_id', 'id');
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

    /**
     * The purchased item, whichever type this order is for.
     */
    public function getItemAttribute()
    {
        return $this->bundle_id ? $this->bundle : $this->booking;
    }

    /**
     * Was this order created manually by an admin (no checkout/Sale behind it)?
     */
        public function getIsManualAttribute()
    {
        return empty($this->sale_id);
    }

    /**
     * Extra services selected for this order — read from
     * order_item_metas.price_breakdown (key = 'price_breakdown').
     * Returns collection of ['label' => ..., 'price' => ...]
     */
    public function getExtraServicesAttribute()
    {
        if (empty($this->id)) {
            return collect();
        }

        $priceBreakdown = \Illuminate\Support\Facades\DB::table('order_item_metas')
            ->join('order_items', 'order_items.id', '=', 'order_item_metas.order_item_id')
            ->where('order_items.booking_order_id', $this->id)
            ->where('order_item_metas.key', 'price_breakdown')
            ->value('order_item_metas.value');

        if (empty($priceBreakdown)) {
            return collect();
        }

                $decoded = json_decode($priceBreakdown, true);

        if (is_string($decoded)) {
            $decoded = json_decode($decoded, true);
        }

        return collect($decoded['extra_services']['selected'] ?? []);
    }

    public function getExtraServicesTotalAttribute()
    {
        return $this->extra_services->sum('price');
    }

    public function sendBookingNotifications(string $event = 'created'): void
    {
        $this->loadMissing(['booking.creator', 'bundle.creator', 'seller', 'buyer']);

        $item = $this->bundle_id ? $this->bundle : $this->booking;
        $title = !empty($item) ? $item->title : ('#' . $this->id);
        $sellerId = !empty($item) ? $item->creator_id : $this->seller_id;

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

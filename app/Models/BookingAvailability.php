<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingAvailability extends Model
{
    use HasFactory;

    protected $table = 'booking_availabilities';

    protected $fillable = [
        'booking_id',
        'resource_id',
        'date',
        'is_available',
        'slots_available',
        'price_override',
        'close_reason',
    ];

    protected $casts = [
        'date'           => 'date',
        'is_available'   => 'boolean',
        'price_override' => 'decimal:2',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class);
    }

    public function resource()
    {
        return $this->belongsTo(BookingResource::class, 'resource_id');
    }
}
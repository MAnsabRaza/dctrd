<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingPackageItem extends Model
{
    use HasFactory;

    protected $table = 'booking_package_items';

    protected $fillable = [
        'package_id',
        'booking_id',
        'resource_id',
        'quantity',
        'included_minutes',
        'sort_order',
        'rules',
    ];

    protected $casts = [
        'rules' => 'array',
    ];

    public function package()
    {
        return $this->belongsTo(BookingPackage::class, 'package_id');
    }

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function resource()
    {
        return $this->belongsTo(BookingResource::class, 'resource_id');
    }
}

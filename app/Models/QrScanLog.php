<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class QrScanLog extends Model
{
    protected $fillable = [
        'short_code',
        'item_type',
        'item_id',
        'user_id',
        'ip_address',
        'user_agent',
        'referrer',
        'is_checkin',
        'checked_in_at',
    ];

    protected $casts = [
        'is_checkin'    => 'boolean',
        'checked_in_at' => 'datetime',
    ];

    /**
     * Polymorphic parent: Product, Course, Booking, ya Bundle jis pe scan hua.
     * (Column names 'item_type' / 'item_id' se automatically match ho jayega
     *  kyunki relation ka naam 'item' hai.)
     */
    public function item()
    {
        return $this->morphTo();
    }
}

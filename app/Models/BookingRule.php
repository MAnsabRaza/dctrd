<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingRule extends Model
{
    use SoftDeletes;

    protected $fillable = ['booking_id', 'rule_type', 'conditions', 'actions', 'starts_at', 'ends_at', 'status'];

    protected $casts = ['conditions' => 'array', 'actions' => 'array', 'status' => 'boolean', 'starts_at' => 'date', 'ends_at' => 'date'];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }
}

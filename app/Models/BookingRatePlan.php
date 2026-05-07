<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingRatePlan extends Model
{
    use HasFactory,SoftDeletes;
    protected $table='booking_rate_plan';
    protected $fillable=[
        'booking_id',
        'name',
        'type',
        'price',
        'price_unit',
        'calculation_type',
        'priority',
        'conditions',
        'status'
    ];
}

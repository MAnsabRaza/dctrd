<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
class BookingResource extends Model
{
    use HasFactory,SoftDeletes;
    protected $table='booking_resources';
    protected $fillable=[
        'booking_id',
        'name',
        'type',
        'description',
        'capacity',
        'extra_price',
        'attributes',
        'image',
        'status',
        'sort_order'
    ];
    

}

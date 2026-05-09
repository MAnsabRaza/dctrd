<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSpecificationValue extends Model
{
    use HasFactory;

    protected $table = 'booking_specification_values';

    protected $fillable = [
        'booking_id',
        'specification_id',
        'value',
    ];

    protected $casts = [
        'booking_id'       => 'integer',
        'specification_id' => 'integer',
    ];

    public function booking()
    {
        return $this->belongsTo(Booking::class, 'booking_id');
    }

    public function specification()
    {
        return $this->belongsTo(BookingSpecification::class, 'specification_id');
    }
}
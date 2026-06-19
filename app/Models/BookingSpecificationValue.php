<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingSpecificationValue extends Model
{
    use HasFactory;

    protected $table = 'booking_specification_values';

    protected $fillable = [
        'specification_id',
        'value',
    ];

    protected $casts = [
        'specification_id' => 'integer',
    ];

    public function specification()
    {
        return $this->belongsTo(BookingSpecification::class, 'specification_id');
    }
}
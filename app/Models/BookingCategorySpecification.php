<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookingCategorySpecification extends Model
{
    protected $table = 'booking_category_specifications';

    protected $fillable = [
        'category_id',
        'specification_id',
    ];

    protected $casts = [
        'category_id'      => 'integer',
        'specification_id' => 'integer',
    ];

    public function category()
    {
        return $this->belongsTo(BookingCategory::class, 'category_id');
    }

    public function specification()
    {
        return $this->belongsTo(BookingSpecification::class, 'specification_id');
    }
}
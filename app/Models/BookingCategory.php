<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingCategory extends Model
{
    protected $table='booking_categories';
    protected $fillable=[
        'parent_id',
        'title',
        'slug',
        'icon',
        'subtitle',
        'description',
        'order',
        'status',
    ];
     protected $casts = [
        'status' => 'boolean',
    ];
    public function parent()
    {
        return $this->belongsTo(BookingCategory::class, 'parent_id');
    }
       public function children()
    {
        return $this->hasMany(BookingCategory::class, 'parent_id')->orderBy('order');
    }

    public function bookings()
    {
        return $this->hasMany(Booking::class, 'category_id');
    }
     public function scopeRoots($query)
    {
        return $query->whereNull('parent_id')->orderBy('order');
    }

    public function scopeActive($query)
    {
        return $query->where('status', true);
    }

}

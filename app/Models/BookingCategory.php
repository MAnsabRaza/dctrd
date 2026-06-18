<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingCategory extends Model
{
    use SoftDeletes;

    protected $table='booking_categories';
    protected $fillable=[
        'parent_id',
        'user_id',
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
    public function specifications()
{
    return $this->belongsToMany(
        BookingSpecification::class,
        'booking_category_specifications',
        'category_id',
        'specification_id'
    );
}

}

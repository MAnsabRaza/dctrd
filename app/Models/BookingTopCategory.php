<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingTopCategory extends Model
{
    use HasFactory;
    protected $table = 'booking_top_categories';
    protected $fillable = ['category_id', 'image'];
    public function category()
    {
        return $this->belongsTo(BookingCategory::class, 'category_id', 'id');
    }

}

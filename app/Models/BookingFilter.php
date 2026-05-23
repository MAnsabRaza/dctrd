<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class BookingFilter extends Model
{
    use SoftDeletes;

    protected $fillable = ['category_id', 'title', 'type', 'options', 'is_required', 'status', 'order'];

    protected $casts = ['options' => 'array', 'is_required' => 'boolean', 'status' => 'boolean'];

    public function category()
    {
        return $this->belongsTo(BookingCategory::class, 'category_id');
    }
}

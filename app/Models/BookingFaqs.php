<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BookingFaqs extends Model
{
    use HasFactory;
    protected $table = 'booking_faqs';
    protected $fillable = ['booking_id','creator_id','title', 'answer','locale'];
    public function buyer()
    {
        return $this->belongsTo('App\User', 'buyer_id', 'id');
    }

    public function sale()
    {
        return $this->belongsTo('App\Models\Sale', 'sale_id', 'id');
    }
}

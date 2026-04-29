<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialOfferProductDiscount extends Model
{
    protected $table = 'special_offer_product_discounts';
    public $timestamps = false;
    protected $dateFormat = 'U';
    protected $guarded = [];

    public static $active = 'active';
    public static $inactive = 'inactive';

    public function product()
    {
        return $this->belongsTo('App\Models\Product', 'product_id', 'id');
    }

    public function getRemainingTimes()
    {
        $current_time = time();
        $date = $this->to_date;
        $difference = $date - $current_time;

        return time2string($difference);
    }
}

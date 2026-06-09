<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutModule extends Model
{
    use HasFactory;
    protected $table = 'CheckoutModules';
    protected $fillable = [
        'name',
        'input_type',
        'config',
        'price_rule',
        'order_index',
        'is_active',
        'is_required',
    ];
    
}

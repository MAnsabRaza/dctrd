<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItemMeta extends Model
{
    use HasFactory;
    protected $table = 'order_item_meta';
    protected $fillable = [
        'order_item_id',
        'key',
        'value',
    ];
}

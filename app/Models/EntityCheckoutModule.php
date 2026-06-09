<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EntityCheckoutModule extends Model
{
    use HasFactory;
    protected $table = 'entity_checkout_modules';
    protected $fillable = [
        'entity_type',
        'entity_id',
        'module_id',
        'enabled',
        'config_override',
    ];
}

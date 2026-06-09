<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutModuleTranslation extends Model
{
    use HasFactory;
    protected $table = 'checkout_module_translations';
    protected $fillable = [
        'locale',
        'module_id',
        'label',
        'help_text',
    ];

}

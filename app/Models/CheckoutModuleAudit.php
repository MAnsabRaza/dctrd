<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CheckoutModuleAudit extends Model
{
    use HasFactory;
    protected $table = 'checkout_module_audits';
    protected $fillable = [
        'order_id',
        'module_name',
        'old_value',
        'new_value',
        'changed_by',
        'reason',
    ];
}

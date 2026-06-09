<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrgCheckoutModule extends Model
{
    use HasFactory;
    protected $table = 'org_checkout_modules';
    protected $fillable = [
        'org_id',
        'module_id',
        'enabled',
    ];
}

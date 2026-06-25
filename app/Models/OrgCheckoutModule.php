<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrgCheckoutModule extends Model
{
    protected $table = 'org_checkout_modules';

    protected $fillable = [
        'org_id',
        'module_id',
        'enabled',
        'required',
    ];

    protected $casts = [
        'enabled'  => 'boolean',
        'required' => 'boolean',
    ];

    public function module()
    {
        return $this->belongsTo(CheckoutModule::class, 'module_id');
    }

    public function org()
    {
        return $this->belongsTo(User::class, 'org_id');
    }
}
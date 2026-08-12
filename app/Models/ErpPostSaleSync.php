<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ErpPostSaleSync extends Model
{
    protected $table = 'erp_post_sale_syncs';

    protected $fillable = [
        'vendor_id',
        'order_id',
        'sale_id',
        'product_id',
        'invoice_number',
        'remote_project_id',
        'status',
        'attempts',
        'request_payload',
        'response_payload',
        'error_message',
        'last_attempted_at',
    ];

    protected $casts = [
        'request_payload' => 'array',
        'response_payload' => 'array',
        'last_attempted_at' => 'datetime',
    ];
}

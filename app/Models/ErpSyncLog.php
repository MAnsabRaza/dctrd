<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpSyncLog extends Model
{
    use HasFactory;

    protected $table = 'erp_sync_logs';

    protected $fillable = [
        'vendor_id', 'entity_type', 'local_id', 'remote_id', 'action',
        'status', 'attempts', 'request_payload', 'response_payload', 'error_message',
    ];

    protected $casts = [
        'request_payload'  => 'array',
        'response_payload' => 'array',
    ];

    public function vendor()
    {
        return $this->belongsTo(\App\User::class, 'vendor_id');
    }
}

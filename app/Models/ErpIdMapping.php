<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpIdMapping extends Model
{
    use HasFactory;
    protected $table = 'erp_id_mappings';

    protected $fillable = [
        'vendor_id', 'module', 'local_id', 'erp_id', 'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];
}

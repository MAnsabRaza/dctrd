<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbilitySyncLog extends Model
{
    use HasFactory;
    protected $table='ability_sync_logs';
     protected $fillable = [
        'vendor_ability_id', 'entity', 'local_id', 'remote_id',
        'status', 'response_payload', 'error_message',
    ];

    public function vendorAbility()
    {
        return $this->belongsTo(VendorAbility::class);
    }
}

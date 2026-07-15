<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpIdMapping extends Model
{
    use HasFactory;

    protected $table = 'erp_id_mappings';

    protected $fillable = [
        'vendor_id', 'vendor_ability_id', 'entity', 'local_id',
        'remote_id', 'sync_hash', 'last_synced_at',
    ];

    protected $casts = [
        'last_synced_at' => 'datetime',
    ];

    public function vendorAbility()
    {
        return $this->belongsTo(VendorAbility::class);
    }

    public function scopeForLocal($query, int $vendorAbilityId, string $entity, int $localId)
    {
        return $query->where('vendor_ability_id', $vendorAbilityId)
            ->where('entity', $entity)
            ->where('local_id', $localId);
    }

    public function scopeForRemote($query, int $vendorAbilityId, string $entity, string $remoteId)
    {
        return $query->where('vendor_ability_id', $vendorAbilityId)
            ->where('entity', $entity)
            ->where('remote_id', $remoteId);
    }
}
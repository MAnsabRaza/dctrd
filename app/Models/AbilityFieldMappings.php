<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AbilityFieldMappings extends Model
{
    use HasFactory;
    protected $table='ability_field_mappings';
    protected $fillable = [
        'vendor_ability_id', 'entity', 'local_field', 'remote_field', 'direction',
    ];

    public function vendorAbility()
    {
        return $this->belongsTo(VendorAbility::class);
    }
}

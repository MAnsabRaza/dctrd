<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ability extends Model
{
    use HasFactory;
    protected $table='abilities';
    protected $fillable = [
        'key', 'name', 'type', 'driver_class', 'schema_json', 'description', 'is_active',
    ];

    protected $casts = [
        'schema_json' => 'array',
        'is_active'   => 'boolean',
    ];

    public function vendorAbilities()
    {
        return $this->hasMany(VendorAbility::class);
    }

    /**
     * schema_json se dynamic fields nikalna, form build karne ke liye
     */
    public function getConfigFields(): array
    {
        return $this->schema_json['fields'] ?? [];
    }
}

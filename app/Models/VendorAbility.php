<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorAbility extends Model
{
    use HasFactory;
    protected $table='vendor_abilities';
     protected $fillable = [
        'vendor_id', 'ability_id', 'config_json', 'enabled',
        'last_synced_at', 'sync_status', 'last_error',
    ];

    protected $casts = [
        'config_json'    => 'array',
        'enabled'        => 'boolean',
        'last_synced_at' => 'datetime',
    ];

    public function ability()
    {
        return $this->belongsTo(Ability::class);
    }

    public function vendor()
    {
        return $this->belongsTo(\App\Models\User::class, 'vendor_id');
    }

    public function fieldMappings()
    {
        return $this->hasMany(AbilityFieldMapping::class);
    } public function syncLogs()
    {
        return $this->hasMany(AbilitySyncLog::class);
    }

    /**
     * Sensitive fields (api_key, secret, token, password) ko encrypt karke save karna
     */
    public function setConfigJsonAttribute($value)
    {
        if (is_string($value)) {
            $value = json_decode($value, true) ?? [];
        }

        foreach ($value as $key => $val) {
            if ($this->isSensitiveField($key) && !empty($val)) {
                $value[$key] = Crypt::encryptString($val);
            }
        }

        $this->attributes['config_json'] = json_encode($value);
    }
  public function getConfigJsonAttribute($value)
    {
        $decoded = json_decode($value, true) ?? [];

        foreach ($decoded as $key => $val) {
            if ($this->isSensitiveField($key) && !empty($val)) {
                try {
                    $decoded[$key] = Crypt::decryptString($val);
                } catch (\Exception $e) {
                    $decoded[$key] = null; // corrupted/old value
                }
            }
        }

        return $decoded;
    } 
    protected function isSensitiveField(string $key): bool
    {
        return str_contains(strtolower($key), 'key')
            || str_contains(strtolower($key), 'secret')
            || str_contains(strtolower($key), 'token')
            || str_contains(strtolower($key), 'password');
    }
}

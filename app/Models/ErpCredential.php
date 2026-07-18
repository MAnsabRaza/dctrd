<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;

class ErpCredential extends Model
{
    use HasFactory;

    protected $table = 'erp_credentials';

    protected $fillable = [
        'vendor_id', 'type', 'base_url', 'api_key', 'is_active',
        'export_ability_enabled', 'checklist', 'import_dropshipping_enabled',
        'rate_limit_per_minute', 'last_regenerated_at',
    ];

    protected $casts = [
        'is_active'                   => 'boolean',
        'export_ability_enabled'      => 'boolean',
        'import_dropshipping_enabled' => 'boolean',
        'checklist'                   => 'array',
        'last_regenerated_at'         => 'datetime',
    ];

    public const CHECKLIST_KEYS = [
        'dropship_price',
        'stock_availability',
        'product_approval_status',
        'product_images',
        'shipping_rules',
        'feed_refresh_frequency',
        'tracking_order',
        'tickets_complaints',
    ];

    public function vendor()
    {
        return $this->belongsTo(\App\User::class, 'vendor_id');
    }

    /**
     * api_key encrypted store hoti hai (jaise VendorAbility config_json karta hai)
     */
    public function setApiKeyAttribute($value)
    {
        $this->attributes['api_key'] = !empty($value) ? Crypt::encryptString($value) : null;
    }

    public function getApiKeyAttribute($value)
    {
        if (empty($value)) {
            return null;
        }

        try {
            return Crypt::decryptString($value);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Naya random API key banane ke liye — controller isko call karega
     */
    public static function generateApiKey(): string
    {
        return 'erp_' . Str::random(40);
    }
}

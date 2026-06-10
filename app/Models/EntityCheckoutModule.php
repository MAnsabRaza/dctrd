<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EntityCheckoutModule extends Model
{
    use HasFactory;
    protected $table = 'entity_checkout_modules';
    protected $fillable = [
        'entity_type',
        'entity_id',
        'module_id',
        'enabled',
        'config_override',
    ];

    protected $casts = [
        'enabled' => 'boolean',
        'config_override' => 'array',
    ];

    // ─────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────

    public function module(): BelongsTo
    {
        return $this->belongsTo(CheckoutModule::class, 'module_id');
    }
}

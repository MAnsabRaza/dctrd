<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrgCheckoutModule extends Model
{
    use HasFactory;
    protected $table = 'org_checkout_modules';
    protected $fillable = [
        'org_id',
        'module_id',
        'enabled',
    ];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    // ─────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────

    public function org(): BelongsTo
    {
        return $this->belongsTo(User::class, 'org_id');
    }

    public function module(): BelongsTo
    {
        return $this->belongsTo(CheckoutModule::class, 'module_id');
    }
}

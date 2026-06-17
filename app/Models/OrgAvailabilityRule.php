<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
USE App\User;

class OrgAvailabilityRule extends Model
{
    protected $table = 'org_availability_rules';

    protected $fillable = [
        'org_id',
        'availability_mode',
        'product_specific_takes_precedence',
        'make_all_unavailable_by_default',
    ];

    protected $casts = [
        'product_specific_takes_precedence' => 'boolean',
        'make_all_unavailable_by_default'   => 'boolean',
    ];

    public function ranges(): HasMany
    {
        return $this->hasMany(OrgAvailabilityRange::class, 'org_id', 'org_id');
    }

    public function org(): BelongsTo
    {
        return $this->belongsTo(User::class, 'org_id');
    }
}
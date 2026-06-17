<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\User;

class OrgAvailabilityRange extends Model
{
    protected $table = 'org_availability_ranges';

    protected $fillable = [
        'org_id',
        'range_type',
        'from_date',
        'to_date',
        'bookable',
    ];

    protected $casts = [
        'bookable'   => 'boolean',
        'from_date'  => 'date',
        'to_date'    => 'date',
    ];

    public function org(): BelongsTo
    {
        return $this->belongsTo(User::class, 'org_id');
    }
}
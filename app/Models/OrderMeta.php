<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderMeta extends Model
{
    use HasFactory;
    protected $table = 'order_metas';
    protected $fillable = [
        'order_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    // ─────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class, 'order_id');
    }
}

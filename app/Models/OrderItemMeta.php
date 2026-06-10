<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItemMeta extends Model
{
    use HasFactory;
    protected $table = 'order_item_metas';
    protected $fillable = [
        'order_item_id',
        'key',
        'value',
    ];

    protected $casts = [
        'value' => 'array',
    ];

    // ─────────────────────────────────────────
    // RELATIONSHIPS
    // ─────────────────────────────────────────

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }
}

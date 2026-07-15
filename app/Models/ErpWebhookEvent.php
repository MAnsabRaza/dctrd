<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErpWebhookEvent extends Model
{
    use HasFactory;

    protected $table = 'erp_webhook_events';

    protected $fillable = [
        'vendor_ability_id', 'event_type', 'payload',
        'processed', 'processed_at', 'error_message',
    ];

    protected $casts = [
        'payload'      => 'array',
        'processed'    => 'boolean',
        'processed_at' => 'datetime',
    ];

    public function vendorAbility()
    {
        return $this->belongsTo(VendorAbility::class);
    }

    public function markProcessed(): void
    {
        $this->update(['processed' => true, 'processed_at' => now(), 'error_message' => null]);
    }

    public function markFailed(string $message): void
    {
        $this->update(['processed' => false, 'error_message' => $message]);
    }
}
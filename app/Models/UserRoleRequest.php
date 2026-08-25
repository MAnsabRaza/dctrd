<?php

namespace App\Models;

use App\User;
use Illuminate\Database\Eloquent\Model;

class UserRoleRequest extends Model
{
    protected $table = 'user_role_requests';

    protected $fillable = [
        'user_id', 'role_catalog_id', 'status', 'is_primary',
        'requested_at', 'reviewed_at', 'reviewed_by', 'rejection_reason',
    ];

    protected $casts = [
        'is_primary'   => 'boolean',
        'requested_at' => 'datetime',
        'reviewed_at'  => 'datetime',
    ];

    const STATUS_PENDING  = 'pending';
    const STATUS_ACTIVE   = 'active';
    const STATUS_REJECTED = 'rejected';
    const STATUS_REVOKED = 'revoked';
    const STATUS_DEACTIVATED = 'deactivated';

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function roleCatalog()
    {
        return $this->belongsTo(RoleCatalog::class, 'role_catalog_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function scopeActive($query)
    {
        return $query->where('status', self::STATUS_ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', self::STATUS_PENDING);
    }

    /**
     * Admin approve karta hai — role active ho jata hai.
     */
    public function approve(int $adminId): void
    {
        abort_unless($this->status === self::STATUS_PENDING, 422, 'Only pending role requests can be approved.');

        $this->update([
            'status'      => self::STATUS_ACTIVE,
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
        ]);
    }

    /**
     * Admin reject karta hai.
     */
    public function reject(int $adminId, ?string $reason = null): void
    {
        abort_unless($this->status === self::STATUS_PENDING, 422, 'Only pending role requests can be rejected.');

        $this->update([
            'status'           => self::STATUS_REJECTED,
            'reviewed_at'      => now(),
            'reviewed_by'      => $adminId,
            'rejection_reason' => $reason,
        ]);
    }

    public function revoke(int $adminId): void
    {
        abort_unless($this->status === self::STATUS_ACTIVE, 422, 'Only active roles can be revoked.');

        $this->update([
            'status' => self::STATUS_REVOKED,
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
        ]);
    }

    public function deactivate(int $adminId): void
    {
        abort_unless($this->status === self::STATUS_ACTIVE, 422, 'Only active roles can be deactivated.');

        $this->update([
            'status' => self::STATUS_DEACTIVATED,
            'reviewed_at' => now(),
            'reviewed_by' => $adminId,
        ]);
    }
}

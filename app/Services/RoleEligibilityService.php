<?php

namespace App\Services;

use App\Models\RoleCatalog;
use App\Models\UserRoleRequest;
use App\User;

/**
 * Yeh service decide karti hai ke user kaunse naye roles "Add Role" mein
 * dekh sakta hai — superset logic ke sath (agar user ke paas already
 * koi role hai jo naye role ko "cover" karta hai, to naya role list mein
 * nahi aayega).
 */
class RoleEligibilityService
{
    /**
     * User ke sare active + pending role keys (role_catalog.key values).
     */
    public function currentRoleKeys(User $user): array
    {
        return UserRoleRequest::where('user_id', $user->id)
            ->whereIn('status', [UserRoleRequest::STATUS_ACTIVE, UserRoleRequest::STATUS_PENDING])
            ->with('roleCatalog')
            ->get()
            ->pluck('roleCatalog.key')
            ->filter()
            ->values()
            ->all();
    }

    /**
     * User abhi jo roles add kar sakta hai (eligible list) — supersets
     * aur already-held roles exclude karke.
     */
    public function eligibleRoles(User $user): \Illuminate\Support\Collection
    {
        $currentKeys = $this->currentRoleKeys($user);

        // Har current role jo "covers" karta hai unko bhi exclude list mein daal do
        $coveredKeys = RoleCatalog::whereIn('key', $currentKeys)
            ->get()
            ->flatMap(fn ($role) => $role->supersedes ?? [])
            ->all();

        $excludeKeys = array_unique(array_merge($currentKeys, $coveredKeys));

        return RoleCatalog::active()
            ->whereNotIn('key', $excludeKeys)
            ->orderBy('family')
            ->orderBy('sort_order')
            ->get();
    }

    /**
     * User ke current roles ki status ke sath list (badges dikhane ke liye).
     */
    public function userRolesWithStatus(User $user): \Illuminate\Support\Collection
    {
        return UserRoleRequest::where('user_id', $user->id)
            ->whereIn('status', [UserRoleRequest::STATUS_ACTIVE, UserRoleRequest::STATUS_PENDING])
            ->with('roleCatalog')
            ->get();
    }

    public function ineligibilityMessage(User $user, int $roleCatalogId): string
    {
        $role = RoleCatalog::find($roleCatalogId);

        if (!$role) {
            return 'Selected role was not found.';
        }

        $existing = UserRoleRequest::where('user_id', $user->id)
            ->where('role_catalog_id', $roleCatalogId)
            ->whereIn('status', [UserRoleRequest::STATUS_ACTIVE, UserRoleRequest::STATUS_PENDING])
            ->latest('id')
            ->first();

        if ($existing && $existing->status === UserRoleRequest::STATUS_ACTIVE) {
            return "This role is already active for {$user->full_name}.";
        }

        if ($existing && $existing->status === UserRoleRequest::STATUS_PENDING) {
            return "This role already has a pending request for {$user->full_name}.";
        }

        $currentKeys = $this->currentRoleKeys($user);
        $coveringRole = RoleCatalog::whereIn('key', $currentKeys)
            ->get()
            ->first(fn ($currentRole) => in_array($role->key, $currentRole->supersedes ?? [], true));

        if ($coveringRole) {
            return "{$role->label} is already covered by the active/pending {$coveringRole->label} role.";
        }

        return 'This role is not eligible for the selected user.';
    }

    /**
     * Naya role request banao (duplicate check ke sath).
     */
    public function requestRole(User $user, int $roleCatalogId, bool $isPrimary = false): UserRoleRequest
    {
        $existing = UserRoleRequest::where('user_id', $user->id)
            ->where('role_catalog_id', $roleCatalogId)
            ->whereIn('status', [UserRoleRequest::STATUS_ACTIVE, UserRoleRequest::STATUS_PENDING])
            ->first();

        if ($existing) {
            return $existing;
        }

        $role = RoleCatalog::active()->findOrFail($roleCatalogId);
        $initialStatus = $role->requires_approval
            ? UserRoleRequest::STATUS_PENDING
            : UserRoleRequest::STATUS_ACTIVE;

        // Rejected/revoked/deactivated requests are kept as history. A new
        // submission creates a fresh row instead of overwriting old records.
        return UserRoleRequest::create([
            'user_id'         => $user->id,
            'role_catalog_id' => $roleCatalogId,
            'status'          => $initialStatus,
            'is_primary'      => $isPrimary,
            'requested_at'    => now(),
            'reviewed_at'     => $initialStatus === UserRoleRequest::STATUS_ACTIVE ? now() : null,
        ]);
    }
}

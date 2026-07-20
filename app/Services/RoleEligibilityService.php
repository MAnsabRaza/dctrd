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

        return UserRoleRequest::create([
            'user_id'         => $user->id,
            'role_catalog_id' => $roleCatalogId,
            'status'          => UserRoleRequest::STATUS_PENDING,
            'is_primary'      => $isPrimary,
            'requested_at'    => now(),
        ]);
    }
}
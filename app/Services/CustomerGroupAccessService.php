<?php

namespace App\Services;

use App\User;

class CustomerGroupAccessService
{
    /**
     * $item = Product/Booking/Webinar/Bundle model with 'allowed_customer_groups' column.
     */
    public function canPurchase($item, ?User $user): bool
    {
        $allowedGroups = $this->normalizeGroups($item->allowed_customer_groups ?? null);

        // Restriction hi nahi hai -> sab allowed
        if (empty($allowedGroups)) {
            return true;
        }

        if (empty($user)) {
            return false; // guest -> restricted item nahi khareed sakta
        }

        // User ke active customer-family roles nikaalo
        $userGroups = \App\Models\UserRoleRequest::where('user_id', $user->id)
            ->active()
            ->whereHas('roleCatalog', fn ($q) => $q->where('family', 'customer'))
            ->with('roleCatalog')
            ->get()
            ->pluck('roleCatalog.key')
            ->all();

        return !empty(array_intersect($allowedGroups, $userGroups));
    }

    public function denialMessage($item): string
    {
        $allowedGroups = $this->normalizeGroups($item->allowed_customer_groups ?? null);

        $labels = \App\Models\RoleCatalog::whereIn('key', $allowedGroups)
            ->pluck('label')
            ->implode(' or ');

        return "You cannot purchase this, you must be {$labels}.";
    }

    private function normalizeGroups($groups): array
    {
        if (empty($groups)) {
            return [];
        }

        if (is_string($groups)) {
            $decoded = json_decode($groups, true);
            $groups = json_last_error() === JSON_ERROR_NONE ? $decoded : [$groups];
        }

        if (!is_array($groups)) {
            return [];
        }

        return array_values(array_filter($groups));
    }
}

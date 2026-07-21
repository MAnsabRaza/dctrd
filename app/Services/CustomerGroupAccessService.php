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
        $allowedGroups = $item->allowed_customer_groups ?? null;

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
        $labels = \App\Models\RoleCatalog::whereIn('key', $item->allowed_customer_groups ?? [])
            ->pluck('label')
            ->implode(' or ');

        return "You cannot purchase this, you must be {$labels}.";
    }
}
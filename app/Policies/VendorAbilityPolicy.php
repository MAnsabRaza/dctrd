<?php

namespace App\Policies;

use App\Models\VendorAbility;
use App\User;

class VendorAbilityPolicy
{
    /**
     * Vendor sirf apni khud ki ability ko manage kar sake — doosre vendor ki nahi
     * (Acceptance Criteria #4)
     */
    public function manage(User $user, VendorAbility $vendorAbility): bool
    {
        return (int) $vendorAbility->vendor_id === (int) $user->id;
    }
}
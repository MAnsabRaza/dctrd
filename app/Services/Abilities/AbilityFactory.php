<?php

namespace App\Services\Abilities;

use App\Models\VendorAbility;
use InvalidArgumentException;

class AbilityFactory
{
    public static function make(VendorAbility $vendorAbility): AbilityInterface
    {
        $driverClass = $vendorAbility->ability->driver_class;

        if (!class_exists($driverClass)) {
            throw new InvalidArgumentException("Ability driver class not found: {$driverClass}");
        }

        $instance = new $driverClass($vendorAbility);

        if (!$instance instanceof AbilityInterface) {
            throw new InvalidArgumentException("{$driverClass} must implement AbilityInterface");
        }

        return $instance;
    }
}
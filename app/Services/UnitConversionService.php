<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class UnitConversionService
{
    protected $config;

    public function __construct()
    {
        $this->config = config('units');
    }

    /**
     * Convert a value from one unit to another
     */
    public function convert(float $value, string $type, string $fromUnit, string $toUnit): float
    {
        if ($fromUnit === $toUnit) {
            return $value;
        }

        if (!isset($this->config['conversions'][$type])) {
            return $value;
        }

        $conversions = $this->config['conversions'][$type];

        if (!isset($conversions[$fromUnit]) || !isset($conversions[$toUnit])) {
            return $value;
        }

        $cacheKey = 'unit_conversion:' . md5($type . '|' . $value . '|' . $fromUnit . '|' . $toUnit);

        return Cache::remember($cacheKey, now()->addDay(), function () use ($value, $type, $conversions, $fromUnit, $toUnit) {
            if ($type === 'temperature') {
                return $this->convertTemperatureValue($value, $fromUnit, $toUnit);
            }

            $baseValue = $value / $conversions[$fromUnit];

            return round($baseValue * $conversions[$toUnit], 2);
        });
    }

    private function convertTemperatureValue(float $value, string $fromUnit, string $toUnit): float
    {
        if ($fromUnit === 'f') {
            $celsius = ($value - 32) * 5 / 9;
        } elseif ($fromUnit === 'k') {
            $celsius = $value - 273.15;
        } else {
            $celsius = $value;
        }

        if ($toUnit === 'f') {
            return round(($celsius * 9 / 5) + 32, 2);
        }

        if ($toUnit === 'k') {
            return round($celsius + 273.15, 2);
        }

        return round($celsius, 2);
    }

    /**
     * Convert length
     */
    public function convertLength(float $value, string $from, string $to): float
    {
        return $this->convert($value, 'length', $from, $to);
    }

    /**
     * Convert mass
     */
    public function convertMass(float $value, string $from, string $to): float
    {
        return $this->convert($value, 'mass', $from, $to);
    }

    /**
     * Convert area
     */
    public function convertArea(float $value, string $from, string $to): float
    {
        return $this->convert($value, 'area', $from, $to);
    }

    /**
     * Convert a stored base-unit value for a user's preference.
     */
    public function convertForUser(float $value, string $type, $user = null, ?string $fromUnit = null): float
    {
        $fromUnit = $fromUnit ?? $this->getBaseUnit($type);
        $toUnit = $this->getPreferredUnit($type, $user);

        if (!$fromUnit || !$toUnit) {
            return $value;
        }

        return $this->convert($value, $type, $fromUnit, $toUnit);
    }

    /**
     * Convert and format a stored base-unit value for checkout/report display.
     */
    public function formatForUser(float $value, string $type, $user = null, ?string $fromUnit = null, bool $short = false): string
    {
        $unit = $this->getPreferredUnit($type, $user) ?? $fromUnit ?? $this->getBaseUnit($type);
        $converted = $this->convertForUser($value, $type, $user, $fromUnit);

        return $this->format($converted, $unit, $short);
    }

    /**
     * Get the user's preferred unit for a type or fall back to the base unit.
     */
    public function getPreferredUnit(string $type, $user = null): ?string
    {
        $baseUnit = $this->getBaseUnit($type);

        if (empty($user) && function_exists('auth') && auth()->check()) {
            $user = auth()->user();
        }

        $attribute = "preferred_{$type}_unit";
        $preferredUnit = !empty($user) ? ($user->{$attribute} ?? null) : null;
        $preferredUnit = $preferredUnit ?: session($attribute) ?: request()->cookie($attribute);

        return $this->isValidUnit($type, (string) $preferredUnit) ? $preferredUnit : $baseUnit;
    }

    /**
     * Format value with unit label
     */
    public function format(float $value, string $unit, bool $short = false): string
    {
        $labels = $short ? $this->config['short_labels'] : $this->config['display_labels'];
        $label = $labels[$unit] ?? $unit;
        return number_format($value, 2) . ' ' . $label;
    }

    /**
     * Format value with short label
     */
    public function formatShort(float $value, string $unit): string
    {
        return $this->format($value, $unit, true);
    }

    /**
     * Get available units for a type
     */
    public function getAvailableUnits(string $type): array
    {
        if (!isset($this->config['conversions'][$type])) {
            return [];
        }

        $units = [];
        foreach (array_keys($this->config['conversions'][$type]) as $unit) {
            $units[$unit] = $this->config['display_labels'][$unit] ?? $unit;
        }

        return $units;
    }

    /**
     * Get base unit for a type
     */
    public function getBaseUnit(string $type): ?string
    {
        return $this->config['base_units'][$type] ?? null;
    }

    /**
     * Get all unit types
     */
    public function getUnitTypes(): array
    {
        return array_keys($this->config['base_units']);
    }

    /**
     * Check if unit conversion is enabled
     */
    public function isEnabled(): bool
    {
        return $this->config['enabled'];
    }

    /**
     * Get display label for a unit
     */
    public function getLabel(string $unit, bool $short = false): string
    {
        $labels = $short ? $this->config['short_labels'] : $this->config['display_labels'];
        return $labels[$unit] ?? $unit;
    }

    /**
     * Validate unit for type
     */
    public function isValidUnit(string $type, string $unit): bool
    {
        return isset($this->config['conversions'][$type][$unit]);
    }
}

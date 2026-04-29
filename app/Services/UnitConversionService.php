<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;

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
            Log::warning("Invalid unit type: {$type}");
            return $value;
        }

        $conversions = $this->config['conversions'][$type];

        if (!isset($conversions[$fromUnit]) || !isset($conversions[$toUnit])) {
            Log::warning("Invalid units for conversion: {$fromUnit} to {$toUnit}");
            return $value;
        }

        // Convert to base unit first
        $baseValue = $value / $conversions[$fromUnit];

        // Convert from base unit to target unit
        return round($baseValue * $conversions[$toUnit], 2);
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

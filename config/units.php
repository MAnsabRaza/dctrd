<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Unit Conversion Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration handles unit conversions for length, mass, and area
    | allowing users to view content in their preferred units.
    |
    */

    'enabled' => env('UNIT_CONVERSION_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Base Units
    |--------------------------------------------------------------------------
    | All values are stored in these base units in the database
    */
    'base_units' => [
        'length' => 'km',
        'mass' => 'kg',
        'area' => 'sqm',
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversion Factors
    |--------------------------------------------------------------------------
    | Conversion factors relative to base units
    */
    'conversions' => [
        'length' => [
            'km' => 1,
            'mi' => 0.621371,
            'm' => 1000,
            'ft' => 3280.84,
            'cm' => 100000,
            'in' => 39370.1,
        ],
        'mass' => [
            'kg' => 1,
            'lbs' => 2.20462,
            'g' => 1000,
            'oz' => 35.274,
            'ton' => 0.001,
        ],
        'area' => [
            'sqm' => 1,
            'sqft' => 10.7639,
            'sqkm' => 0.000001,
            'acre' => 0.000247105,
            'hectare' => 0.0001,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Display Labels
    |--------------------------------------------------------------------------
    | Human-readable labels for each unit
    */
    'display_labels' => [
        // Length
        'km' => 'Kilometers',
        'mi' => 'Miles',
        'm' => 'Meters',
        'ft' => 'Feet',
        'cm' => 'Centimeters',
        'in' => 'Inches',
        
        // Mass
        'kg' => 'Kilograms',
        'lbs' => 'Pounds',
        'g' => 'Grams',
        'oz' => 'Ounces',
        'ton' => 'Metric Tons',
        
        // Area
        'sqm' => 'Square Meters',
        'sqft' => 'Square Feet',
        'sqkm' => 'Square Kilometers',
        'acre' => 'Acres',
        'hectare' => 'Hectares',
    ],

    /*
    |--------------------------------------------------------------------------
    | Short Labels
    |--------------------------------------------------------------------------
    | Abbreviated labels for compact display
    */
    'short_labels' => [
        'km' => 'km',
        'mi' => 'mi',
        'm' => 'm',
        'ft' => 'ft',
        'cm' => 'cm',
        'in' => 'in',
        'kg' => 'kg',
        'lbs' => 'lbs',
        'g' => 'g',
        'oz' => 'oz',
        'ton' => 't',
        'sqm' => 'm²',
        'sqft' => 'ft²',
        'sqkm' => 'km²',
        'acre' => 'ac',
        'hectare' => 'ha',
    ],
];

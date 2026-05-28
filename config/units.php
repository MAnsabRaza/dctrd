<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Unit Conversion Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration handles common unit conversions, allowing users to
    | view content in their preferred units.
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
        'length' => 'cm',
        'area' => 'cm2',
        'mass' => 'kg',
        'speed' => 'km',
        'temperature' => 'c',
        'force' => 'n',
        'volume' => 'l',
        'energy' => 'btu',
        'heat_flow_rate' => 'w',
    ],

    /*
    |--------------------------------------------------------------------------
    | Conversion Factors
    |--------------------------------------------------------------------------
    | Conversion factors relative to base units
    */
    'conversions' => [
        'length' => [
            'cm' => 1,
            'in' => 0.393701,
            'ft' => 0.0328084,
        ],
        'area' => [
            'cm2' => 1,
            'm2' => 0.0001,
            'in2' => 0.155,
        ],
        'mass' => [
            'kg' => 1,
            'lb' => 2.20462,
        ],
        'speed' => [
            'km' => 1,
            'mph' => 0.621371,
        ],
        'temperature' => [
            'c' => 1,
            'f' => 33.8,
            'k' => 274.15,
        ],
        'force' => [
            'n' => 1,
            'lb' => 0.224809,
        ],
        'volume' => [
            'l' => 1,
            'm3' => 0.001,
            'in3' => 61.0237,
            'gal' => 0.264172,
        ],
        'energy' => [
            'btu' => 1,
            'erg' => 10550558526.2,
            'j' => 1055.06,
            'cal' => 252.164,
            'kwh' => 0.000293071,
        ],
        'heat_flow_rate' => [
            'w' => 1,
            'btuh' => 3.41214,
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
        'cm' => 'Centimeters',
        'in' => 'Inches',
        'ft' => 'Feet',

        // Area
        'cm2' => 'Square Centimeters',
        'm2' => 'Square Meters',
        'in2' => 'Square Inches',

        // Mass / Force
        'kg' => 'Kilograms',
        'lb' => 'Pounds',
        'n' => 'Newtons',

        // Speed
        'km' => 'Kilometers',
        'mph' => 'Miles per hour',

        // Temperature
        'c' => 'Celsius',
        'f' => 'Fahrenheit',
        'k' => 'Kelvin',

        // Volume
        'l' => 'Liters',
        'm3' => 'Cubic Meters',
        'in3' => 'Cubic Inches',
        'gal' => 'Gallons',

        // Energy / heat
        'btu' => 'Btu',
        'erg' => 'Erg',
        'j' => 'Joules',
        'cal' => 'Calories',
        'kwh' => 'Kilowatt hours',
        'w' => 'Watts',
        'btuh' => 'Btu/h',
    ],

    /*
    |--------------------------------------------------------------------------
    | Short Labels
    |--------------------------------------------------------------------------
    | Abbreviated labels for compact display
    */
    'short_labels' => [
        'cm' => 'cm',
        'in' => 'in',
        'ft' => 'ft',
        'cm2' => 'cm2',
        'm2' => 'm2',
        'in2' => 'in2',
        'kg' => 'kg',
        'lb' => 'lb',
        'km' => 'km',
        'mph' => 'mph',
        'c' => 'deg C',
        'f' => 'deg F',
        'k' => 'Kelvin',
        'n' => 'N',
        'l' => 'L',
        'm3' => 'm3',
        'in3' => 'in3',
        'gal' => 'Gallons',
        'btu' => 'Btu',
        'erg' => 'erg',
        'j' => 'J',
        'cal' => 'cal',
        'kwh' => 'kW-h',
        'w' => 'W',
        'btuh' => 'Btu/h',
    ],
];

<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Exchange Rate API Configuration
    |--------------------------------------------------------------------------
    |
    | This configuration handles automatic currency exchange rate updates
    | using multiple API providers with fallback support.
    |
    */

    'enabled' => env('EXCHANGE_RATE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Primary API Configuration (exchangerate.host)
    |--------------------------------------------------------------------------
    | Free tier available, no API key required, supports all base currencies
    */
    'primary_api' => [
        'provider' => 'exchangerate_host',
        'url' => 'https://api.exchangerate.host',
        'key' => null,
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup API Configuration (exchangeratesapi.io)
    |--------------------------------------------------------------------------
    | Note: Free tier only supports EUR as base currency
    */
    'backup_api' => [
        'provider' => 'exchangeratesapi',
        'url' => 'https://api.exchangeratesapi.io/v1',
        'key' => env('EXCHANGE_RATES_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Base Currency
    |--------------------------------------------------------------------------
    | The base currency for all exchange rate calculations
    */
    'base_currency' => env('BASE_CURRENCY', 'USD'),

    /*
    |--------------------------------------------------------------------------
    | Supported Currencies
    |--------------------------------------------------------------------------
    | List of currencies to fetch and support
    */
    'supported_currencies' => [
        'USD', 'EUR', 'GBP', 'JPY', 'AUD', 'CAD', 'CHF', 'CNY', 'INR',
        'AED', 'SAR', 'EGP', 'PKR', 'BDT', 'NGN', 'KES', 'ZAR', 'BRL',
        'MXN', 'RUB', 'TRY', 'KRW', 'IDR', 'MYR', 'SGD', 'THB', 'VND',
    ],

    /*
    |--------------------------------------------------------------------------
    | Update Frequency
    |--------------------------------------------------------------------------
    | How often to update exchange rates (in hours)
    */
    'update_frequency' => env('EXCHANGE_RATE_UPDATE_HOURS', 12),

    /*
    |--------------------------------------------------------------------------
    | Cache Duration
    |--------------------------------------------------------------------------
    | How long to cache exchange rates (in seconds)
    */
    'cache_duration' => 3600, // 1 hour

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    */
    'fallback_on_failure' => true,
    'retry_attempts' => 3,
    'timeout' => 10, // seconds
];

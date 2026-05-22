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
        'provider' => env('EXCHANGE_PRIMARY_PROVIDER', 'exchange_rate_api'),
        'url' => env('EXCHANGE_PRIMARY_URL', 'https://open.er-api.com/v6'),
        'key' => env('EXCHANGE_PRIMARY_API_KEY'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Backup API Configuration (exchangeratesapi.io)
    |--------------------------------------------------------------------------
    | Note: Free tier only supports EUR as base currency
    */
    'backup_api' => [
        'provider' => env('EXCHANGE_BACKUP_PROVIDER', 'frankfurter'),
        'url' => env('EXCHANGE_BACKUP_URL', 'https://api.frankfurter.app'),
        'key' => env('EXCHANGE_BACKUP_API_KEY', env('EXCHANGE_RATES_API_KEY')),
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
    'cache_duration' => env('EXCHANGE_RATE_CACHE_SECONDS', 12 * 60 * 60),

    /*
    |--------------------------------------------------------------------------
    | Fallback Configuration
    |--------------------------------------------------------------------------
    */
    'fallback_on_failure' => true,
    'retry_attempts' => 3,
    'timeout' => 10, // seconds
];

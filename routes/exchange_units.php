<?php

/*
|--------------------------------------------------------------------------
| Exchange Rate & Unit Conversion Routes
|--------------------------------------------------------------------------
|
| Routes for managing exchange rates and unit conversions
|
*/

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\SettingsController;

// Admin Routes (require authentication and admin permissions)
Route::group(['middleware' => ['auth', 'admin'], 'prefix' => 'admin'], function () {
    
    // Exchange Rate Management
    Route::post('/exchange-rates/update', [SettingsController::class, 'updateExchangeRates'])
        ->name('admin.exchange_rates.update');
    
    Route::get('/exchange-rates/settings', [SettingsController::class, 'getExchangeRateSettings'])
        ->name('admin.exchange_rates.settings');
});

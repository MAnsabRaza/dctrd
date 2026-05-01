<?php

/**
 * Custom Admin Routes File
 * 
 * This file allows adding custom admin routes without modifying the main admin.php
 * Routes defined here will be loaded by the main admin routes file.
 */

use Illuminate\Support\Facades\Route;

// Get the admin panel prefix from the main application
$prefix = getAdminPanelUrlPrefix();

/**
 * Define your custom admin panel routes here
 * They will be automatically loaded alongside the main admin routes
 * 
 * All routes will be prefixed with your admin panel prefix 
 * and will have 'web' and 'admin' middleware applied automatically
 */

// Example of custom routes:
// 
// Route::group(['prefix' => 'custom-section'], function () {
//     Route::get('/', 'YourCustomController@index')->name('admin.custom.index');
//     Route::get('/create', 'YourCustomController@create')->name('admin.custom.create');
//     Route::post('/store', 'YourCustomController@store')->name('admin.custom.store');
//     Route::get('/{id}/edit', 'YourCustomController@edit')->name('admin.custom.edit');
//     Route::post('/{id}/update', 'YourCustomController@update')->name('admin.custom.update');
//     Route::get('/{id}/delete', 'YourCustomController@delete')->name('admin.custom.delete');
// });

// You can add as many route groups as needed

/**
 * Booking Categories Routes
 */
Route::group(['prefix' => 'booking'], function () {
    Route::group(['prefix' => 'categories'], function () {
        Route::get('/',             'Booking\BookingCategoryController@index');
        Route::post('/store',       'Booking\BookingCategoryController@store');
        Route::get('/{id}/edit',    'Booking\BookingCategoryController@edit');
        Route::post('/{id}/update', 'Booking\BookingCategoryController@update');
        Route::get('/{id}/delete',  'Booking\BookingCategoryController@delete');
    });
});

/**
 * To use these routes, you must have your controller in App\Http\Controllers\Admin namespace
 * or specify the complete namespace like:
 * 
 * Route::get('/custom-page', '\App\Http\Controllers\YourNamespace\YourController@method');
 */ 
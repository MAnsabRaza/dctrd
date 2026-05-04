<?php

/**
 * Custom Admin Routes File
 * 
 * This file allows adding custom admin routes without modifying the main admin.php
 * Routes defined here will be loaded by the main admin routes file.
 */

use App\Http\Controllers\Admin\Booking\BookingCategoryController;
use App\Http\Controllers\Admin\Booking\BookingController;
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

        Route::get('/',             [BookingCategoryController::class,'index']);
        Route::post('/store',       [BookingCategoryController::class,'store']);
        Route::get('/{id}/edit',    [BookingCategoryController::class,'edit']);
        Route::post('/{id}/update', [BookingCategoryController::class,'update']);
        Route::get('/{id}/delete',  [BookingCategoryController::class,'delete']);

    });
    Route::group(['prefix' => 'categories'], function () {

        Route::get('/',             [BookingController::class,'index']);
        // Route::post('/store',       [BookingController::class,'store']);
        // Route::get('/{id}/edit',    [BookingController::class,'edit']);
        // Route::post('/{id}/update', [BookingController::class,'update']);
        // Route::get('/{id}/delete',  [BookingController::class,'delete']);

    });
});

/**
 * To use these routes, you must have your controller in App\Http\Controllers\Admin namespace
 * or specify the complete namespace like:
 * 
 * Route::get('/custom-page', '\App\Http\Controllers\YourNamespace\YourController@method');
 */ 
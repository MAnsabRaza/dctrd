<?php

/**
 * Custom Admin Routes File
 * 
 * This file allows adding custom admin routes without modifying the main admin.php
 * Routes defined here will be loaded by the main admin routes file.
 */

use App\Http\Controllers\Admin\Booking\BookingAvailabilityController;
use App\Http\Controllers\Admin\Booking\BookingBundleController;
use App\Http\Controllers\Admin\Booking\BookingCategoryController;
use App\Http\Controllers\Admin\Booking\BookingCategorySpecificationController;
use App\Http\Controllers\Admin\Booking\BookingController;
use App\Http\Controllers\Admin\Booking\BookingImportController;
use App\Http\Controllers\Admin\Booking\BookingPolicyController;
use App\Http\Controllers\Admin\Booking\BookingRatePlanController;
use App\Http\Controllers\Admin\Booking\BookingResourceController;
use App\Http\Controllers\Admin\Booking\BookingReviewController;
use App\Http\Controllers\Admin\Booking\BookingSeasonController;
use App\Http\Controllers\Admin\Booking\BookingSpecificationController;
use App\Http\Controllers\Admin\Booking\BookingSpecificationValueController;
use App\Http\Controllers\Admin\Booking\BookingVariantController;
use App\Http\Controllers\Admin\Booking\BookingTimeSlotController;
use App\Http\Controllers\Admin\Booking\BookingOrderController;
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

    // Booking index, store, edit, update, delete
    Route::get('/', [BookingController::class, 'index']);
    Route::post('/store', [BookingController::class, 'store']);
    Route::get('/{id}/edit', [BookingController::class, 'edit']);
    Route::post('/{id}/update', [BookingController::class, 'update']);
    Route::get('/{id}/delete', [BookingController::class, 'delete']);

    // Booking Categories
    Route::group(['prefix' => 'categories'], function () {
        Route::get('/', [BookingCategoryController::class, 'index']);
        Route::post('/store', [BookingCategoryController::class, 'store']);
        Route::get('/{id}/edit', [BookingCategoryController::class, 'edit']);
        Route::post('/{id}/update', [BookingCategoryController::class, 'update']);
        Route::get('/{id}/delete', [BookingCategoryController::class, 'delete']);
    });
    Route::group(['prefix' => 'resources'], function () {
        Route::get('/', [BookingResourceController::class, 'index']);
        Route::post('/store', [BookingResourceController::class, 'store']);
        Route::get('/{id}/edit', [BookingResourceController::class, 'edit']);
        Route::post('/{id}/update', [BookingResourceController::class, 'update']);
        Route::get('/{id}/delete', [BookingResourceController::class, 'delete']);
    });
    Route::group(['prefix' => 'rate'], function () {
        Route::get('/', [BookingRatePlanController::class, 'index']);
        Route::post('/store', [BookingRatePlanController::class, 'store']);
        Route::get('/{id}/edit', [BookingRatePlanController::class, 'edit']);
        Route::post('/{id}/update', [BookingRatePlanController::class, 'update']);
        Route::get('/{id}/delete', [BookingRatePlanController::class, 'delete']);
    });
    Route::group(['prefix' => 'season'], function () {
        Route::get('/', [BookingSeasonController::class, 'index']);
        Route::post('/store', [BookingSeasonController::class, 'store']);
        Route::get('/{id}/edit', [BookingSeasonController::class, 'edit']);
        Route::post('/{id}/update', [BookingSeasonController::class, 'update']);
        Route::get('/{id}/delete', [BookingSeasonController::class, 'delete']);
    });
    Route::group(['prefix' => 'availability'], function () {
        Route::get('/', [BookingAvailabilityController::class, 'index']);
        Route::post('/store', [BookingAvailabilityController::class, 'store']);
        Route::get('/{id}/edit', [BookingAvailabilityController::class, 'edit']);
        Route::post('/{id}/update', [BookingAvailabilityController::class, 'update']);
        Route::get('/{id}/delete', [BookingAvailabilityController::class, 'delete']);
    });
    // ✅ CORRECT
    Route::group(['prefix' => 'policy'], function () {
        Route::get('/', [BookingPolicyController::class, 'index']);
        Route::post('/store', [BookingPolicyController::class, 'store']);
        Route::get('/{id}/edit', [BookingPolicyController::class, 'edit']);
        Route::post('/{id}/update', [BookingPolicyController::class, 'update']);
        Route::get('/{id}/delete', [BookingPolicyController::class, 'delete']);
    });

    Route::group(['prefix' => 'variant'], function () {
        Route::get('/', [BookingVariantController::class, 'index']);
        Route::post('/store', [BookingVariantController::class, 'store']);
        Route::get('/{id}/edit', [BookingVariantController::class, 'edit']);
        Route::post('/{id}/update', [BookingVariantController::class, 'update']);
        Route::get('/{id}/delete', [BookingVariantController::class, 'delete']);
    });
    Route::group(['prefix' => 'time-slot'], function () {
        Route::get('/', [BookingTimeSlotController::class, 'index']);
        Route::post('/store', [BookingTimeSlotController::class, 'store']);
        Route::get('/{id}/edit', [BookingTimeSlotController::class, 'edit']);
        Route::post('/{id}/update', [BookingTimeSlotController::class, 'update']);
        Route::get('/{id}/delete', [BookingTimeSlotController::class, 'delete']);
    });
    Route::group(['prefix' => 'order'], function () {
        Route::get('/', [BookingOrderController::class, 'index']);
        Route::post('/store', [BookingOrderController::class, 'store']);
        Route::get('/{id}/edit', [BookingOrderController::class, 'edit']);
        Route::post('/{id}/update', [BookingOrderController::class, 'update']);
        Route::get('/{id}/delete', [BookingOrderController::class, 'delete']);
    });
    // ✅ CORRECT
    Route::group(['prefix' => 'specification'], function () {
        Route::get('/', [BookingSpecificationController::class, 'index']);
        Route::post('/store', [BookingSpecificationController::class, 'store']);
        Route::get('/{id}/edit', [BookingSpecificationController::class, 'edit']);
        Route::post('/{id}/update', [BookingSpecificationController::class, 'update']);
        Route::get('/{id}/delete', [BookingSpecificationController::class, 'delete']);
    });

    Route::group(['prefix' => 'specificationValue'], function () {
        Route::get('/', [BookingSpecificationValueController::class, 'index']);
        Route::post('/store', [BookingSpecificationValueController::class, 'store']);
        Route::get('/{id}/edit', [BookingSpecificationValueController::class, 'edit']);
        Route::post('/{id}/update', [BookingSpecificationValueController::class, 'update']);
        Route::get('/{id}/delete', [BookingSpecificationValueController::class, 'delete']);
    });
    Route::group(['prefix' => 'categorySpecification'], function () {
        Route::get('/', [BookingCategorySpecificationController::class, 'index']);
        Route::post('/store', [BookingCategorySpecificationController::class, 'store']);
        Route::get('/{id}/edit', [BookingCategorySpecificationController::class, 'edit']);
        Route::post('/{id}/update', [BookingCategorySpecificationController::class, 'update']);
        Route::get('/{id}/delete', [BookingCategorySpecificationController::class, 'delete']);
    });

    Route::group(['prefix' => 'bundle'], function () {
        Route::get('/', [BookingBundleController::class, 'index']);
        Route::post('/store', [BookingBundleController::class, 'store']);
        Route::get('/{id}/edit', [BookingBundleController::class, 'edit']);
        Route::post('/{id}/update', [BookingBundleController::class, 'update']);
        Route::get('/{id}/delete', [BookingBundleController::class, 'delete']);
    });

    Route::group(['prefix' => 'import'], function () {
        Route::get('/', [BookingImportController::class, 'index']);
        Route::post('/store', [BookingImportController::class, 'store']);
        Route::get('/{id}/show', [BookingImportController::class, 'show']);
        Route::get('/{id}/delete', [BookingImportController::class, 'delete']);
        Route::get('/sample', [BookingImportController::class, 'downloadSample']);
    });
    Route::group(['prefix' => 'review'], function () {
    Route::get('/', [BookingReviewController::class, 'index']);
    Route::get('/{id}/edit', [BookingReviewController::class, 'edit']);
    Route::post('/{id}/update', [BookingReviewController::class, 'update']);
    Route::get('/{id}/delete', [BookingReviewController::class, 'delete']);
});

});

/**
 * To use these routes, you must have your controller in App\Http\Controllers\Admin namespace
 * or specify the complete namespace like:
 * 
 * Route::get('/custom-page', '\App\Http\Controllers\YourNamespace\YourController@method');
 */
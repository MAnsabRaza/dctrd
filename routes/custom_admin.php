<?php

/**
 * Custom Admin Routes File
 * 
 * This file allows adding custom admin routes without modifying the main admin.php
 * Routes defined here will be loaded by the main admin routes file.
 */

use App\Http\Controllers\Admin\AvailabilitySettingsController;
use App\Http\Controllers\Admin\BookingCategorySettingsController;
use App\Http\Controllers\Admin\Booking\BookingAvailabilityController;
use App\Http\Controllers\Admin\Booking\BookingBundleController;
use App\Http\Controllers\Admin\Booking\BookingCategoryController;
use App\Http\Controllers\Admin\Booking\BookingCommentController;
use App\Http\Controllers\Admin\Booking\BookingController;
use App\Http\Controllers\Admin\Booking\BookingFavoriteController;
use App\Http\Controllers\Admin\Booking\BookingImportController;
use App\Http\Controllers\Admin\Booking\BookingModuleCrudController;
use App\Http\Controllers\Admin\Booking\BookingPolicyController;
use App\Http\Controllers\Admin\Booking\BookingPackageController;
use App\Http\Controllers\Admin\Booking\BookingDiscountController;
use App\Http\Controllers\Admin\Booking\BookingRatePlanController;
use App\Http\Controllers\Admin\Booking\BookingResourceController;
use App\Http\Controllers\Admin\Booking\BookingReviewController;
use App\Http\Controllers\Admin\Booking\BookingSeasonController;
use App\Http\Controllers\Admin\Booking\BookingSpecificationController;
use App\Http\Controllers\Admin\Booking\BookingVariantController;
use App\Http\Controllers\Admin\Booking\BookingTimeSlotController;
use App\Http\Controllers\Admin\Booking\BookingOrderController;
use App\Http\Controllers\Admin\Booking\BookingSellerController;
use App\Http\Controllers\Admin\Booking\BookingFilterController;
use App\Http\Controllers\Admin\Booking\BookingContentSettingsController;
use App\Http\Controllers\Admin\Booking\BookingFeaturedController;
use App\Http\Controllers\Admin\Booking\BookingTopCategoryController;
use App\Http\Controllers\Admin\Booking\BookingFeatureCategoryController;
use App\Http\Controllers\Admin\SaleController;
use App\Http\Controllers\Admin\UserController;
use Illuminate\Support\Facades\Route;

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
    Route::get('/list', [BookingController::class, 'list']);
    Route::get('/excel', [BookingController::class, 'exportExcel']);
    Route::post('/store', [BookingController::class, 'store']);
    Route::get('/{id}/edit', [BookingController::class, 'edit']);
    Route::post('/{id}/update', [BookingController::class, 'update']);
    Route::get('/{id}/delete', [BookingController::class, 'delete']);
    Route::get('/in-house-bookings', [BookingController::class, 'inHouseBookings'])->name('admin.booking.in-house');

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

    Route::get('/resources', [BookingTimeSlotController::class, 'getResources']);

    Route::get('/', [BookingTimeSlotController::class, 'index']);

    Route::post('/store', [BookingTimeSlotController::class, 'store']);

    Route::get('/{id}/edit', [BookingTimeSlotController::class, 'edit']);

    Route::post('/{id}/update', [BookingTimeSlotController::class, 'update']);

    Route::get('/{id}/delete', [BookingTimeSlotController::class, 'delete']);

});
   Route::group(['prefix' => 'order'], function () {
    Route::get('/', [BookingOrderController::class, 'index']);
    Route::get('/in-house-orders', [BookingOrderController::class, 'inHouseOrders']);
    Route::get('/excel', [BookingOrderController::class, 'exportExcel']);
    Route::get('/{id}/invoice', [BookingOrderController::class, 'invoice']);
    Route::get('/{id}/refund', [BookingOrderController::class, 'refund']);
});
    
    // Booking sellers list
    Route::get('/sellers', [BookingSellerController::class, 'index']);
    // ✅ CORRECT
    Route::group(['prefix' => 'specification'], function () {
        Route::get('/', [BookingSpecificationController::class, 'index']);
        Route::post('/store', [BookingSpecificationController::class, 'store']);
        Route::get('/{id}/edit', [BookingSpecificationController::class, 'edit']);
        Route::post('/{id}/update', [BookingSpecificationController::class, 'update']);
        Route::get('/{id}/delete', [BookingSpecificationController::class, 'delete']);
    });

    // specificationValue and categorySpecification routes consolidated into specification group

    Route::group(['prefix' => 'bundle'], function () {
        Route::get('/', [BookingBundleController::class, 'index']);
        Route::post('/store', [BookingBundleController::class, 'store']);
        Route::get('/{id}/edit', [BookingBundleController::class, 'edit']);
        Route::post('/{id}/update', [BookingBundleController::class, 'update']);
        Route::get('/{id}/delete', [BookingBundleController::class, 'delete']);
    });

    Route::group(['prefix' => 'package'], function () {
        Route::get('/', [BookingPackageController::class, 'index']);
        Route::post('/store', [BookingPackageController::class, 'store']);
        Route::get('/{id}/edit', [BookingPackageController::class, 'edit']);
        Route::post('/{id}/update', [BookingPackageController::class, 'update']);
        Route::get('/{id}/delete', [BookingPackageController::class, 'delete']);
    });

    Route::group(['prefix' => 'import'], function () {
        Route::get('/', [BookingImportController::class, 'index']);
        Route::post('/store', [BookingImportController::class, 'store']);
        Route::get('/{id}/show', [BookingImportController::class, 'show']);
        Route::get('/{id}/delete', [BookingImportController::class, 'delete']);
        Route::get('/sample', [BookingImportController::class, 'downloadSample']);
    });
   Route::group(['prefix' => 'review'], function () {
    Route::get('/',                              [BookingReviewController::class, 'index']);
    Route::get('/{id}/toggleStatus',            [BookingReviewController::class, 'toggleStatus']);
    Route::get('/{id}/reply',                   [BookingReviewController::class, 'reply']);
    Route::get('/{id}/delete',                  [BookingReviewController::class, 'delete']);
});

    Route::group(['prefix' => 'favorite'], function () {
        Route::get('/', [BookingFavoriteController::class,'index']);
        Route::post('/store', [BookingFavoriteController::class,'store']);
        Route::get('/{id}/edit', [BookingFavoriteController::class,'edit']);
        Route::post('/{id}/update', [BookingFavoriteController::class,'update']);
        Route::get('/{id}/delete', [BookingFavoriteController::class,'delete']);

    });

    Route::group(['prefix' => 'comment'], function () {
        Route::get('/', [BookingCommentController::class,'index']);
        Route::post('/store', [BookingCommentController::class,'store']);
        Route::get('/{id}/edit', [BookingCommentController::class,'edit']);
        Route::post('/{id}/update', [BookingCommentController::class,'update']);
        Route::get('/{id}/delete', [BookingCommentController::class,'delete']);

    });

    Route::group(['prefix' => 'discounts'], function () {
        Route::get('/', [BookingDiscountController::class, 'index']);
        Route::post('/store', [BookingDiscountController::class, 'store']);
        Route::get('/{id}/edit', [BookingDiscountController::class, 'edit']);
        Route::post('/{id}/update', [BookingDiscountController::class, 'update']);
        Route::get('/{id}/delete', [BookingDiscountController::class, 'delete']);
    });

    Route::group(['prefix' => 'modules/{resource}'], function () {
        Route::get('/', [BookingModuleCrudController::class, 'index']);
        Route::post('/store', [BookingModuleCrudController::class, 'store']);
        Route::get('/{id}/edit', [BookingModuleCrudController::class, 'edit']);
        Route::post('/{id}/update', [BookingModuleCrudController::class, 'update']);
        Route::get('/{id}/delete', [BookingModuleCrudController::class, 'delete']);
    });

    // Booking Filters (custom dedicated controller)
    Route::group(['prefix' => 'filters'], function () {
        Route::get('/', [BookingFilterController::class, 'index']);
        Route::post('/store', [BookingFilterController::class, 'store']);
        Route::get('/{id}/edit', [BookingFilterController::class, 'edit']);
        Route::post('/{id}/update', [BookingFilterController::class, 'update']);
        Route::get('/{id}/delete', [BookingFilterController::class, 'destroy']);
        Route::get('/by-category/{id}', [BookingFilterController::class, 'getByCategoryId']);
    });

        // Booking Featured (custom controller)
        Route::group(['prefix' => 'featured'], function () {
            Route::get('/', [BookingFeaturedController::class, 'index']);
            Route::get('/create', [BookingFeaturedController::class, 'create']);
            Route::post('/store', [BookingFeaturedController::class, 'store']);
            Route::get('/{id}/edit', [BookingFeaturedController::class, 'edit']);
            Route::post('/{id}/update', [BookingFeaturedController::class, 'update']);
            Route::get('/{id}/delete', [BookingFeaturedController::class, 'destroy']);
        });

        // Booking Top Categories
        Route::group(['prefix' => 'top-categories'], function () {
            Route::get('/', [BookingTopCategoryController::class, 'index']);
            Route::get('/create', [BookingTopCategoryController::class, 'create']);
            Route::post('/store', [BookingTopCategoryController::class, 'store']);
            Route::get('/{id}/edit', [BookingTopCategoryController::class, 'edit']);
            Route::post('/{id}/update', [BookingTopCategoryController::class, 'update']);
            Route::get('/{id}/delete', [BookingTopCategoryController::class, 'delete']);
        });

        // Booking Feature Categories
        Route::group(['prefix' => 'feature-categories'], function () {
            Route::get('/', [BookingFeatureCategoryController::class, 'index']);
            Route::get('/create', [BookingFeatureCategoryController::class, 'create']);
            Route::post('/store', [BookingFeatureCategoryController::class, 'store']);
            Route::get('/{id}/edit', [BookingFeatureCategoryController::class, 'edit']);
            Route::post('/{id}/update', [BookingFeatureCategoryController::class, 'update']);
            Route::get('/{id}/delete', [BookingFeatureCategoryController::class, 'delete']);
        });

        Route::get('/featured-bookings', [BookingContentSettingsController::class, 'featuredBookings']);
        Route::post('/featured-bookings', [BookingContentSettingsController::class, 'storeFeaturedBookings']);

        Route::get('/settings', [BookingContentSettingsController::class, 'settings']);
        Route::post('/settings', [BookingContentSettingsController::class, 'storeSettings']);

});

Route::post('/users/{id}/booking-orders/store', [UserController::class, 'storeManualBookingOrder'])
    ->name('admin.users.booking_orders.store');

// Remove a manually-added booking/bundle order (soft delete -> "Manually Removed" list)
Route::post('/users/{id}/booking-orders/{orderId}/delete', [UserController::class, 'removeManualBookingOrder'])
    ->name('admin.users.booking_orders.delete');

Route::post('/users/{id}/booking-options-update', [UserController::class, 'bookingOptionsUpdate'])
    ->name('admin.users.booking_options.update');

Route::post('/users/{id}/checkout-options-update', [UserController::class, 'checkoutOptionsUpdate'])
    ->name('admin.users.checkout_options.update');

Route::group(['prefix' => 'financial/location-sales'], function () {
    Route::get('/city', [SaleController::class, 'salesByCity'])->name('admin.sales.reports.city');
    Route::get('/country', [SaleController::class, 'salesByCountry'])->name('admin.sales.reports.country');
});

/**
 * ═════════════════════════════════════════════════════════════════
 * AVAILABILITY SETTINGS ROUTES (Admin Panel)
 * ═════════════════════════════════════════════════════════════════
 */
Route::group(['prefix' => 'users/{id}/availability'], function () {
    Route::get('/', [AvailabilitySettingsController::class, 'adminIndex'])
        ->name('admin.users.availability.index');
    Route::post('/save', [AvailabilitySettingsController::class, 'adminSave'])
        ->name('admin.users.availability.save');
    Route::post('/row/delete/{rowId}', [AvailabilitySettingsController::class, 'deleteRow'])
        ->name('admin.users.availability.deleteRow');
    Route::post('/row/add', [AvailabilitySettingsController::class, 'addRow'])
        ->name('admin.users.availability.addRow');
});

Route::group(['prefix' => 'users/{id}/booking-settings'], function () {
    Route::get('/', [BookingCategorySettingsController::class, 'index'])
        ->name('admin.users.booking_settings.index');
    Route::post('/save', [BookingCategorySettingsController::class, 'save'])
        ->name('admin.users.booking_settings.save');
});

/**
 * ═════════════════════════════════════════════════════════════════
 * CHECKOUT MODULES ROUTES (Admin Panel)
 * ═════════════════════════════════════════════════════════════════
 */
Route::resource('checkout-modules', \App\Http\Controllers\Admin\CheckoutModuleController::class)
    ->except(['show', 'destroy']);
 
// GET delete route (used by blade delete button)
Route::get('checkout-modules/{id}/delete', [\App\Http\Controllers\Admin\CheckoutModuleController::class, 'destroy'])
    ->name('checkout-modules.delete');
 
// AJAX toggle active/inactive
Route::post('checkout-modules/{id}/toggle', [\App\Http\Controllers\Admin\CheckoutModuleController::class, 'toggle'])
    ->name('admin.checkout-modules.toggle');
 
// Redirect show → edit
Route::get('checkout-modules/{id}', [\App\Http\Controllers\Admin\CheckoutModuleController::class, 'show'])
    ->name('admin.checkout-modules.show');

/**
 * To use these routes, you must have your controller in App\Http\Controllers\Admin namespace
 * or specify the complete namespace like:
 * 
 * Route::get('/custom-page', '\App\Http\Controllers\YourNamespace\YourController@method');
 */
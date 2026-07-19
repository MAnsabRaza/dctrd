<?php

use Illuminate\Support\Facades\Route;

Route::group(['prefix' => '/development'], function () {

    Route::get('/', function () {
        return response()->json([
            'code' => 200,
            'message' => 'OK, API test'
        ]);
    });

    Route::middleware('api') ->group(base_path('routes/api/auth.php'));

    Route::namespace('Web')->group(base_path('routes/api/guest.php'));

    Route::prefix('panel')->namespace('Panel')->group(base_path('routes/api/user.php'));

    Route::group(['namespace' => 'Config', 'middleware' => []], function () {
        Route::get('/config', ['uses' => 'ConfigController@list']);
        Route::get('/config/register/{type}', ['uses' => 'ConfigController@getRegisterConfig']);
    });

    Route::prefix('instructor')->middleware(['api.auth', 'api.level-access:teacher'])->namespace('Instructor')->group(base_path('routes/api/instructor.php'));

});

Route::prefix('v1')->group(function () {
    Route::get('/courses', [\App\Http\Controllers\LocationController::class, 'courses']);
    Route::get('/products', [\App\Http\Controllers\LocationController::class, 'products']);
    Route::get('/bookings', [\App\Http\Controllers\LocationController::class, 'bookings']);
    Route::post('/checkin/{code}', [\App\Http\Controllers\Api\CheckinController::class, '__invoke'])
        ->name('api.checkin');
});

// Checkout Address APIs
Route::prefix('location')->group(function () {
    Route::get('/suggestions', [\App\Http\Controllers\Api\LocationController::class, 'suggestions']);
});

Route::prefix('user')->middleware('auth:api')->group(function () {
    Route::get('/address', [\App\Http\Controllers\Api\UserController::class, 'getAddress']);
});

<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Event;
//use App\Services\ThemeBuilderLicenseService;
//use App\Services\LicenseService;
//use App\Models\PurchaseCode;
use Illuminate\Routing\Events\RouteMatched;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Route;

class ThemeBuilderLicenseServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
  

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        return;
    }
} 
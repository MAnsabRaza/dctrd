<?php

namespace App\Providers;

use App\Models\Order;
use App\Models\Booking;
use App\Models\Payment;
use App\Models\Product;
use App\Observers\BookingObserver;
use App\Observers\OrderObserver;
use App\Observers\ProductObserver;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\ServiceProvider;

use App\Models\BookingCategory;
use Illuminate\Support\Facades\View;


class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
         

        Validator::extend('check_price', function ($attribute, $value, $parameters, $validator) {
            return preg_match('/^\d*\.?\d*$/', $value);
        });


        Paginator::defaultView('pagination::default');

                View::composer('partials._search_bar', function ($view) {
            // Agar already controller ne pass kiya hai to override na karo
            if (!$view->offsetExists('bookingCategories')) {
                $view->with('bookingCategories',
                    BookingCategory::query()
                        ->whereNull('parent_id')
                        ->where('status', true)
                        ->orderBy('order')
                        ->with(['children' => fn($q) => $q->where('status', true)->orderBy('order')])
                        ->get()
                );
            }
        });
    }
}

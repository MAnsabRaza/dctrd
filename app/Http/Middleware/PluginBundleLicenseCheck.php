<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
//use App\Services\PluginBundleLicenseService;
use Illuminate\Support\Facades\Log;
use App\Models\PurchaseCode;
use Throwable;

class PluginBundleLicenseCheck
{
   
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        return $next($request);
    }
} 
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
//use App\Services\LicenseService;
//use App\Models\PurchaseCode;
use Illuminate\Support\Facades\Log;

class LicenseCheck
{
    

    public function handle(Request $request, Closure $next)
    {
        
        return $next($request);
    }
}
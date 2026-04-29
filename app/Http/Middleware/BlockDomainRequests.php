<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class BlockDomainRequests
{
    public function handle(Request $request, Closure $next)
    {
        $domain = $request->header('origin') ?: $request->header('referer');

        if ($domain && strpos($domain, 'crm.rocket-soft.org') !== false) {
            abort(403, 'Requests from this domain are not allowed.');
        }

        return $next($request);
    }
}
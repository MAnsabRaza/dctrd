<?php

namespace App\Http\Middleware;

use App\Models\ErpCredential;
use Closure;
use Illuminate\Http\Request;

/**
 * Route middleware — usage: ->middleware('erp.key:import_export')  ya  ->middleware('erp.key:dropshipping')
 * Header: X-API-KEY
 */
class VerifyErpApiKey
{
    public function handle(Request $request, Closure $next, string $type = 'import_export')
    {
        $key = $request->header('X-API-KEY');

        if (empty($key)) {
            return response()->json(['message' => 'Missing X-API-KEY header'], 401);
        }

        // api_key encrypted store hoti hai — chunk se scan karna padega (small vendor count ke liye theek hai;
        // bade scale par ek "key_lookup_hash" column add karo aur usay index karo).
        $credential = ErpCredential::where('type', $type)
            ->where('is_active', true)
            ->get()
            ->first(fn($c) => hash_equals((string) $c->api_key, (string) $key));

        if (empty($credential)) {
            return response()->json(['message' => 'Invalid or inactive API key'], 403);
        }

        $request->attributes->set('erp_credential', $credential);
        $request->attributes->set('erp_vendor_id', $credential->vendor_id);

        return $next($request);
    }
}

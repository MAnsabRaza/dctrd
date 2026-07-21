<?php

namespace App\Http\Middleware;

use App\Services\CustomerGroupAccessService;
use Closure;
use Illuminate\Http\Request;

class CheckCustomerGroupAccess
{
    public function handle(Request $request, Closure $next)
    {
        $accessService = app(CustomerGroupAccessService::class);
        $user = auth()->user();

        // Cart mein jo bhi items hain unko check karo
        $cartItems = \App\Models\Cart::where('creator_id', $user->id ?? 0)
            ->with(['product', 'webinar', 'booking', 'bundle'])
            ->get();

        foreach ($cartItems as $cartItem) {
            $item = $cartItem->product ?? $cartItem->webinar ?? $cartItem->booking ?? $cartItem->bundle ?? null;

            if (!empty($item) && !$accessService->canPurchase($item, $user)) {
                $toastData = [
                    'title'  => 'Request Failed',
                    'msg'    => $accessService->denialMessage($item),
                    'status' => 'error',
                ];

                if ($request->ajax()) {
                    return response()->json(['toast_alert' => $toastData], 422);
                }

                return redirect('/cart')->with(['toast' => $toastData]);
            }
        }

        return $next($request);
    }
}
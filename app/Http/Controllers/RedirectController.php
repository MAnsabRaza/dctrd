<?php

namespace App\Http\Controllers;

use App\Models\QrScanLog;
use Illuminate\Http\Request;

class RedirectController extends Controller
{
    /**
     * Route: GET /r/{code}
     * Scan ko log karo aur item ke asli (public) URL par redirect kar do.
     */
    public function __invoke(Request $request, string $code)
    {
        $item = $this->findItemByShortCode($code);

        if (!$item) {
            abort(404, 'Link not found or expired.');
        }

        if ($item->qr_revoked_at) {
            abort(410, 'This link has been disabled.');
        }

        QrScanLog::create([
            'short_code' => $code,
            'item_type'  => get_class($item),
            'item_id'    => $item->id,
            'user_id'    => auth()->id(),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'referrer'   => $request->headers->get('referer'),
        ]);

        return redirect()->away($item->public_url);
    }

    /**
     * Har QR-enabled model (HasQrCode trait use karne wale) mein short_code dhoondo.
     */
    protected function findItemByShortCode(string $code)
    {
        $models = [
            \App\Models\Product::class,
            \App\Models\Webinar::class,
            \App\Models\Booking::class,
            \App\Models\Bundle::class,
        ];

        foreach ($models as $modelClass) {
            if (!class_exists($modelClass)) {
                continue;
            }

            $item = $modelClass::where('short_code', $code)->first();
            if ($item) {
                return $item;
            }
        }

        return null;
    }
}
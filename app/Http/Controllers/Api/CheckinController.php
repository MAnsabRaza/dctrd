<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QrScanLog;
use Illuminate\Http\Request;

class CheckinController extends Controller
{
    /**
     * Route: POST /api/v1/checkin/{code}
     * Is code ke sab se recent scan record ko check-in mark karo.
     */
    public function __invoke(Request $request, string $code)
    {
        $log = QrScanLog::where('short_code', $code)->latest()->first();

        if (!$log) {
            $log = QrScanLog::create([
                'short_code' => $code,
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id'    => auth()->id(),
            ]);
        }

        $log->update([
            'is_checkin'    => true,
            'checked_in_at' => now(),
        ]);

        return response()->json([
            'success'       => true,
            'checked_in_at' => $log->checked_in_at,
        ]);
    }
}
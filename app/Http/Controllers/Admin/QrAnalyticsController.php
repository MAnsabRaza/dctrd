<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\QrScanLog;
use Illuminate\Http\Request;

class QrAnalyticsController extends Controller
{
    /**
     * Route: GET /admin/qr-analytics
     * Filters (sab optional query params): item_type, vendor_id, date_from, date_to
     */
    public function index(Request $request)
    {
        $this->authorize('admin_qr_analytics'); // apne Role/permission seeder mein yeh key add karo

        $query = QrScanLog::query();

        if ($request->filled('item_type')) {
            $query->where('item_type', $request->item_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('vendor_id')) {
            // Vendor ownership column apne User/Organ schema ke mutabiq adjust karo
            // (yahan farz kiya gaya hai ke item par 'user_id' ya 'organ_id' column hai).
            $query->whereHasMorph('item', ['*'], function ($q) use ($request) {
                $q->where('user_id', $request->vendor_id)
                  ->orWhere('organ_id', $request->vendor_id);
            });
        }

        $stats = [
            'total_scans' => (clone $query)->count(),
            'unique_ips'  => (clone $query)->distinct('ip_address')->count('ip_address'),
            'checkins'    => (clone $query)->where('is_checkin', true)->count(),
        ];

        $topReferrers = (clone $query)
            ->selectRaw('referrer, count(*) as total')
            ->whereNotNull('referrer')
            ->groupBy('referrer')
            ->orderByDesc('total')
            ->limit(10)
            ->get();

        $recentScans = (clone $query)
            ->with('item')
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.qr.analytics', compact('stats', 'topReferrers', 'recentScans'));
    }
}

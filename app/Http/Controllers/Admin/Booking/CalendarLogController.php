<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\CalendarLog;
use Illuminate\Http\Request;

class CalendarLogController extends Controller
{
    // GET admin/calendar/logs
    public function index(Request $request)
    {
        $logs = CalendarLog::with('user:id,name,email')
            ->when($request->filled('provider'), fn ($q) => $q->where('provider', $request->get('provider')))
            ->when($request->filled('status'), fn ($q) => $q->where('status', $request->get('status')))
            ->when($request->filled('user_id'), fn ($q) => $q->where('user_id', $request->get('user_id')))
            ->latest()
            ->paginate(30)
            ->withQueryString();

        return view('admin.calendar.logs', compact('logs'));
    }
}

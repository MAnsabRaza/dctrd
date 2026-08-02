<?php

namespace App\Http\Controllers;

use App\Models\CalendarIntegration;
use App\Models\CalendarSetting;
use App\Services\Calendar\GoogleCalendarService;
use App\Services\Calendar\OutlookCalendarService;
use App\Services\Calendar\ICalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

class CalendarController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | GOOGLE
    |--------------------------------------------------------------------------
    */
   public function googleRedirect(Request $request)
{
    $returnTo = $request->get('return_to', route('panel.setting.external-connections'));
    
    // Fix: use target user_id from request/route, not just auth()->id()
    $userId = $request->get('user_id', auth()->id());

    $url = app(GoogleCalendarService::class)->getAuthUrl($userId, $returnTo);

    return redirect($url);
}

    public function googleCallback(Request $request)
    {
        [$userId, $returnTo] = $this->resolveState($request->get('state'));

        if (!$userId) {
            return redirect()->route('panel.setting.external-connections')
                ->with('error', trans('calendar.invalid_state'));
        }

        $success = app(GoogleCalendarService::class)->handleCallback($request->code, $userId);

        return redirect($returnTo ?: route('panel.setting.external-connections'))
            ->with($success ? 'success' : 'error', $success
                ? trans('calendar.google_connected')
                : trans('calendar.connection_failed'));
    }

    /*
    |--------------------------------------------------------------------------
    | OUTLOOK
    |--------------------------------------------------------------------------
    */
    public function outlookRedirect(Request $request)
    {
        $returnTo = $request->get('return_to', route('panel.setting.external-connections'));
        $userId = $request->get('user_id', auth()->id());

        $url = app(OutlookCalendarService::class)->getAuthUrl($userId, $returnTo);

        return redirect($url);
    }

    public function outlookCallback(Request $request)
    {
        [$userId, $returnTo] = $this->resolveState($request->get('state'));

        if (!$userId) {
            return redirect()->route('panel.setting.external-connections')
                ->with('error', trans('calendar.invalid_state'));
        }

        $success = app(OutlookCalendarService::class)->handleCallback($request->code, $userId);

        return redirect($returnTo ?: route('panel.setting.external-connections'))
            ->with($success ? 'success' : 'error', $success
                ? trans('calendar.outlook_connected')
                : trans('calendar.connection_failed'));
    }

    /*
    |--------------------------------------------------------------------------
    | DISCONNECT
    |--------------------------------------------------------------------------
    */
    public function disconnect(Request $request, string $provider)
    {
        CalendarIntegration::where([
            'user_id'  => auth()->id(),
            'provider' => $provider,
        ])->update([
            'status'        => 'disconnected',
            'access_token'  => null,
            'refresh_token' => null,
        ]);

        return back()->with('success', ucfirst($provider) . ' ' . trans('calendar.disconnected'));
    }

    /*
    |--------------------------------------------------------------------------
    | ICAL FEED (public, signed URL)
    |--------------------------------------------------------------------------
    */
    public function icalFeed(string $token)
    {
        $setting = CalendarSetting::where('provider', 'ical')
            ->where('ical_token', $token)
            ->firstOrFail();

        if (!$setting->ical_export_enabled) {
            abort(410);
        }

        $icsContent = app(ICalService::class)->generateIcs($setting->user_id);

        return response($icsContent, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="bookings.ics"',
            'Cache-Control'       => 'no-store, max-age=0',
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Helpers
    |--------------------------------------------------------------------------
    */
    private function resolveState(?string $state): array
    {
        if (!$state) {
            return [null, null];
        }

        try {
            $payload = json_decode(Crypt::decryptString($state), true);
            return [$payload['user_id'] ?? null, $payload['return_to'] ?? null];
        } catch (\Throwable $e) {
            return [null, null];
        }
    }
}

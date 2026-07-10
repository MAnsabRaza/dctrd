<?php

namespace App\Http\Controllers\Panel\Booking;

use App\Http\Controllers\Controller;
use App\Models\CalendarIntegration;
use App\Models\CalendarLog;
use App\Models\CalendarSetting;
use App\Services\Calendar\GoogleCalendarService;
use App\Services\Calendar\ICalService;
use App\Services\Calendar\OutlookCalendarService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Crypt;

class CalendarController extends Controller
{
    // Renders the "External Connections" settings page for the logged-in instructor/org
    public function index()
    {
        $userId = auth()->id();
        $providers = ['google', 'outlook', 'ical'];

        $calendarIntegrations = CalendarIntegration::where('user_id', $userId)->get()->keyBy('provider');

        $calendarSettings = CalendarSetting::where('user_id', $userId)->get()->keyBy('provider');
        foreach ($providers as $provider) {
            if (!$calendarSettings->has($provider)) {
                $calendarSettings->put($provider, new CalendarSetting(['user_id' => $userId, 'provider' => $provider]));
            }
        }

        $icalSetting = $calendarSettings->get('ical');
        $calendarIcalUrl = ($icalSetting && $icalSetting->ical_token && $icalSetting->ical_export_enabled)
            ? route('calendar.ical.feed', $icalSetting->ical_token)
            : null;

        $calendarLogs = CalendarLog::where('user_id', $userId)->latest()->limit(25)->get();

        return view('panel.setting.external_connections', compact(
            'calendarIntegrations', 'calendarSettings', 'calendarIcalUrl', 'calendarLogs'
        ));
    }

    // Save Google/Outlook OAuth app credentials
    public function saveCredentials(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'outlook'], true)) {
            abort(422, 'Unsupported provider.');
        }

        $data = $request->validate([
            'client_id'     => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
        ]);

        CalendarIntegration::updateOrCreate(
            ['user_id' => auth()->id(), 'provider' => $provider],
            [
                'client_id'     => $data['client_id'],
                'client_secret' => $data['client_secret'],
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => trans('calendar.credentials_saved')]);
        }

        return back()->with('success', trans('calendar.credentials_saved'));
    }

    // Save event template + sync options for a provider
    public function saveSettings(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'outlook'], true)) {
            abort(422, 'Unsupported provider.');
        }

        $data = $request->validate([
            'event_title_template'       => ['nullable', 'string', 'max:255'],
            'event_description_template' => ['nullable', 'string'],
            'sync_status_filter'         => ['nullable', 'array'],
        ]);

        CalendarSetting::updateOrCreate(
            ['user_id' => auth()->id(), 'provider' => $provider],
            [
                'event_title_template'       => $data['event_title_template'] ?? '{CUSTOMER_NAME} - {PRODUCT_NAME}',
                'event_description_template' => $data['event_description_template'] ?? null,
                'add_customer_as_attendee'   => $request->boolean('add_customer_as_attendee'),
                'debug_mode'                 => $request->boolean('debug_mode'),
                'sync_status_filter'         => $data['sync_status_filter'] ?? null,
            ]
        );

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => trans('calendar.settings_saved')]);
        }

        return back()->with('success', trans('calendar.settings_saved'));
    }

    // Toggle iCal export on/off, generating a signed token the first time it's enabled.
    // Turning it OFF does NOT delete the token - the feed route below will simply
    // start returning 404 for it, matching the spec ("token active=false, not deleted").
    public function toggleIcal(Request $request)
    {
        $userId  = auth()->id();
        $enabled = $request->boolean('ical_export_enabled');

        $setting = CalendarSetting::firstOrCreate(
            ['user_id' => $userId, 'provider' => 'ical'],
            ['ical_export_enabled' => false]
        );

        if ($enabled && empty($setting->ical_token)) {
            app(ICalService::class)->generateToken($userId);
        }

        $setting->update(['ical_export_enabled' => $enabled]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => trans('calendar.settings_saved')]);
        }

        return back()->with('success', trans('calendar.settings_saved'));
    }

    // Regenerate the signed iCal token (invalidates the old feed URL immediately,
    // since ICalService::generateToken() overwrites ical_token on the same row)
    public function regenerateIcal()
    {
        app(ICalService::class)->generateToken(auth()->id());

        return back()->with('success', trans('calendar.ical_token_regenerated'));
    }

    // ── PUBLIC iCal feed endpoint ────────────────────────────────────────
    // GET /ical/{token}  — no auth, unguessable token acts as the credential.
    // This was referenced everywhere (blade "Download .ics", index() above)
    // but had no route/action wired up yet - added here.
    public function icalFeed(string $token)
    {
        $setting = CalendarSetting::where('provider', 'ical')
            ->where('ical_token', $token)
            ->first();

        // Unknown token, or export turned OFF -> the feed is gone (410) rather
        // than a generic 404, so calendar apps know the subscription was revoked.
        if (!$setting) {
            abort(404);
        }

        if (!$setting->ical_export_enabled) {
            abort(410);
        }

        $ics = app(ICalService::class)->generateIcs($setting->user_id);

        return response($ics, 200, [
            'Content-Type'        => 'text/calendar; charset=utf-8',
            'Content-Disposition' => 'inline; filename="calendar.ics"',
            'Cache-Control'       => 'no-store, max-age=0',
        ]);
    }

    // Save credentials, then redirect user to Google/Outlook consent screen
    public function saveCredentialsAndConnect(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'outlook'], true)) {
            abort(422, 'Unsupported provider.');
        }

        $data = $request->validate([
            'client_id'     => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
            'return_to'     => ['nullable', 'string'],
        ]);

        CalendarIntegration::updateOrCreate(
            ['user_id' => auth()->id(), 'provider' => $provider],
            [
                'client_id'     => $data['client_id'],
                'client_secret' => $data['client_secret'],
            ]
        );

        $service = $provider === 'google'
            ? app(GoogleCalendarService::class)
            : app(OutlookCalendarService::class);

        $authUrl = $service->getAuthUrl(auth()->id(), $data['return_to'] ?? url()->previous());

        if ($request->wantsJson()) {
            return response()->json([
                'success'  => true,
                'redirect' => $authUrl,
            ]);
        }

        return redirect($authUrl);
    }

    // Disconnect a provider - clear tokens, keep past events on the provider side
    // and keep the calendar_settings row (templates, toggles) untouched.
    public function disconnect(Request $request, string $provider)
    {
        if (!in_array($provider, ['google', 'outlook'], true)) {
            abort(422, 'Unsupported provider.');
        }

        CalendarIntegration::where('user_id', auth()->id())
            ->where('provider', $provider)
            ->update([
                'access_token'     => null,
                'refresh_token'    => null,
                'token_expires_at' => null,
                'sync_token'       => null,
                'status'           => 'disconnected',
                'last_error'       => null,
            ]);

        CalendarLog::create([
            'user_id'  => auth()->id(),
            'provider' => $provider,
            'action'   => 'disconnect',
            'status'   => 'success',
        ]);

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => trans('calendar.credentials_saved')]);
        }

        return back()->with('success', trans('calendar.credentials_saved'));
    }

    // ── Google OAuth callback ──────────────────────────────────────────
    public function googleCallback(Request $request)
    {
        return $this->handleProviderCallback($request, 'google', GoogleCalendarService::class);
    }

    // ── Outlook OAuth callback ──────────────────────────────────────────
    public function outlookCallback(Request $request)
    {
        return $this->handleProviderCallback($request, 'outlook', OutlookCalendarService::class);
    }

    // ── Shared logic: decrypt state, run handleCallback(), save token, redirect ──
    private function handleProviderCallback(Request $request, string $provider, string $serviceClass)
    {
        $code  = $request->query('code');
        $state = $request->query('state');

        // If user cancelled consent on Google/Outlook screen
        if ($request->filled('error')) {
            return redirect('/panel/setting/step/external_connections')->with('toast', [
                'title'  => trans('public.error'),
                'msg'    => trans('calendar.oauth_failed'),
                'status' => 'error',
            ]);
        }

        if (empty($code) || empty($state)) {
            return redirect('/panel/setting/step/external_connections')->with('toast', [
                'title'  => trans('public.error'),
                'msg'    => trans('calendar.oauth_failed'),
                'status' => 'error',
            ]);
        }

        $returnTo = '/panel/setting/step/external_connections';

        try {
            $payload  = json_decode(Crypt::decryptString($state), true);
            $userId   = $payload['user_id'] ?? null;
            $returnTo = $payload['return_to'] ?? $returnTo;

            if (empty($userId)) {
                throw new \Exception('Invalid state payload: missing user_id.');
            }

            /** @var GoogleCalendarService|OutlookCalendarService $service */
            $service = app($serviceClass);
            $success = $service->handleCallback($code, (int) $userId);

            return redirect($returnTo)->with('toast', [
                'title'  => $success ? trans('public.request_success') : trans('public.error'),
                'msg'    => $success ? trans('calendar.credentials_saved') : trans('calendar.oauth_failed'),
                'status' => $success ? 'success' : 'error',
            ]);
        } catch (\Throwable $e) {
            CalendarLog::create([
                'user_id'       => auth()->id(),
                'provider'      => $provider,
                'action'        => 'token_refresh',
                'status'        => 'failed',
                'error_message' => $e->getMessage(),
            ]);

            return redirect($returnTo)->with('toast', [
                'title'  => trans('public.error'),
                'msg'    => trans('calendar.oauth_failed'),
                'status' => 'error',
            ]);
        }
    }
}
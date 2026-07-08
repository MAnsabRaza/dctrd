<?php

namespace App\Http\Controllers\Panel;

use App\Http\Controllers\Controller;
use App\Models\CalendarIntegration;
use App\Models\CalendarLog;
use App\Models\CalendarSetting;
use App\Services\Calendar\GoogleCalendarService;
use App\Services\Calendar\ICalService;
use App\Services\Calendar\OutlookCalendarService;
use Illuminate\Http\Request;

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
        $calendarIcalUrl = $icalSetting && $icalSetting->ical_token
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

        return back()->with('success', trans('calendar.settings_saved'));
    }

    // Toggle iCal export on/off, generating a signed token the first time it's enabled
    public function toggleIcal(Request $request)
    {
        $userId = auth()->id();
        $enabled = $request->boolean('ical_export_enabled');

        $setting = CalendarSetting::firstOrCreate(
            ['user_id' => $userId, 'provider' => 'ical'],
            ['ical_export_enabled' => false]
        );

        if ($enabled && empty($setting->ical_token)) {
            app(ICalService::class)->generateToken($userId);
        }

        $setting->update(['ical_export_enabled' => $enabled]);

        return back()->with('success', trans('calendar.settings_saved'));
    }

    // Regenerate the signed iCal token (invalidates the old feed URL)
    public function regenerateIcal()
    {
        app(ICalService::class)->generateToken(auth()->id());

        return back()->with('success', trans('calendar.ical_token_regenerated'));
    }
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

    if ($request->wantsJson()) {
    return response()->json([
        'success'  => true,
        'redirect' => $service->getAuthUrl(auth()->id(), $data['return_to'] ?? url()->previous()),
    ]);
}

return redirect($service->getAuthUrl(auth()->id(), $data['return_to'] ?? url()->previous()));
}
}

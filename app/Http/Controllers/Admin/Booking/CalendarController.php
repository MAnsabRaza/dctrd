<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\CalendarIntegration;
use App\Models\CalendarLog;
use App\Models\CalendarSetting;
use App\User;
use App\Services\Calendar\ICalService;
use Illuminate\Http\Request;

class CalendarController extends Controller
{
    // Save Google/Outlook OAuth app credentials for a user (client_id/client_secret)
    public function saveCredentials(Request $request, int $id, string $provider)
    {
        User::findOrFail($id);

        if (!in_array($provider, ['google', 'outlook'], true)) {
            abort(422, 'Unsupported provider.');
        }

        $data = $request->validate([
            'client_id'     => ['required', 'string', 'max:255'],
            'client_secret' => ['required', 'string', 'max:255'],
        ]);

        CalendarIntegration::updateOrCreate(
            ['user_id' => $id, 'provider' => $provider],
            [
                'client_id'     => $data['client_id'],
                'client_secret' => $data['client_secret'],
            ]
        );

        return back()->with('success', trans('calendar.credentials_saved'));
    }

    // Save event template + sync options for a provider
    public function saveSettings(Request $request, int $id, string $provider)
    {
        User::findOrFail($id);

        if (!in_array($provider, ['google', 'outlook'], true)) {
            abort(422, 'Unsupported provider.');
        }

        $data = $request->validate([
            'event_title_template'       => ['nullable', 'string', 'max:255'],
            'event_description_template' => ['nullable', 'string'],
            'sync_status_filter'         => ['nullable', 'array'],
        ]);

        CalendarSetting::updateOrCreate(
            ['user_id' => $id, 'provider' => $provider],
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
    public function toggleIcal(Request $request, int $id)
    {
        User::findOrFail($id);

        $enabled = $request->boolean('ical_export_enabled');

        $setting = CalendarSetting::firstOrCreate(
            ['user_id' => $id, 'provider' => 'ical'],
            ['ical_export_enabled' => false]
        );

        if ($enabled && empty($setting->ical_token)) {
            app(ICalService::class)->generateToken($id);
        }

        $setting->update(['ical_export_enabled' => $enabled]);

        return back()->with('success', trans('calendar.settings_saved'));
    }

    // Regenerate the signed iCal token (invalidates the old feed URL)
    public function regenerateIcal(int $id)
    {
        User::findOrFail($id);

        app(ICalService::class)->generateToken($id);

        return back()->with('success', trans('calendar.ical_token_regenerated'));
    }

    public function disconnect(Request $request, int $id, string $provider)
    {
        User::findOrFail($id);

        if (!in_array($provider, ['google', 'outlook'], true)) {
            abort(422, 'Unsupported provider.');
        }

        CalendarIntegration::where('user_id', $id)
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
            'user_id'  => $id,
            'provider' => $provider,
            'action'   => 'disconnect',
            'status'   => 'success',
        ]);

        return back()->with('success', ucfirst($provider) . ' ' . trans('calendar.disconnected'));
    }
}

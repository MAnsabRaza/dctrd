<?php

namespace App\Services\Calendar;

use App\Models\CalendarIntegration;
use App\Models\CalendarLog;
use App\Models\CalendarSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class OutlookCalendarService
{
    private const AUTH_URL  = 'https://login.microsoftonline.com/common/oauth2/v2.0/authorize';
    private const TOKEN_URL = 'https://login.microsoftonline.com/common/oauth2/v2.0/token';
    private const API_URL   = 'https://graph.microsoft.com/v1.0';

    // METHOD 1: Get OAuth URL
    public function getAuthUrl(int $userId, ?string $returnTo = null): string
    {
        $integration = $this->integration($userId);

        $state = Crypt::encryptString(json_encode([
            'user_id'   => $userId,
            'return_to' => $returnTo,
            'nonce'     => Str::random(16),
        ]));

        return self::AUTH_URL . '?' . http_build_query([
            'client_id'     => $integration->client_id,
            'redirect_uri'  => route('calendar.outlook.callback'),
            'response_type' => 'code',
            'response_mode' => 'query',
            'scope'         => 'offline_access Calendars.ReadWrite',
            'state'         => $state,
        ]);
    }

    // METHOD 2: Handle OAuth Callback
    public function handleCallback(string $code, int $userId): bool
    {
        $integration = $this->integration($userId);
        $debug       = $this->settingsFor($userId)->debug_mode;

        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id'     => $integration->client_id,
                'client_secret' => $integration->client_secret,
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => route('calendar.outlook.callback'),
                'scope'         => 'offline_access Calendars.ReadWrite',
            ]);

            if (!$response->successful()) {
                $integration->update(['status' => 'error', 'last_error' => $response->body()]);
                $this->log($integration, 'token_refresh', 'failed', [], $response->json(), $response->body(), $debug);
                return false;
            }

            $payload = $response->json();

            $integration->update([
                'access_token'     => $payload['access_token'] ?? null,
                'refresh_token'    => $payload['refresh_token'] ?? $integration->refresh_token,
                'token_expires_at' => now()->addSeconds(max((int) ($payload['expires_in'] ?? 3600) - 60, 60)),
                'calendar_id'      => $integration->calendar_id ?: 'default',
                'status'           => 'connected',
                'last_error'       => null,
                'last_sync_at'     => now(),
            ]);

            $this->log($integration->fresh(), 'token_refresh', 'success', [], ['message' => 'Outlook Calendar connected.'], null, $debug);
            return true;
        } catch (Throwable $exception) {
            $integration->update(['status' => 'error', 'last_error' => $exception->getMessage()]);
            $this->log($integration, 'token_refresh', 'failed', [], [], $exception->getMessage(), $debug);
            return false;
        }
    }

    // METHOD 3: Refresh Token
    public function refreshToken(CalendarIntegration $integration): bool
    {
        if (empty($integration->refresh_token)) {
            return false;
        }

        $debug = $this->settingsFor($integration->user_id)->debug_mode;

        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id'     => $integration->client_id,
                'client_secret' => $integration->client_secret,
                'refresh_token' => $integration->refresh_token,
                'grant_type'    => 'refresh_token',
                'scope'         => 'offline_access Calendars.ReadWrite',
            ]);

            if (!$response->successful()) {
                $integration->update([
                    'status'     => 'error',
                    'last_error' => 'Token revoked or invalid, please reconnect Outlook Calendar.',
                ]);
                $this->log($integration, 'token_refresh', 'failed', [], $response->json(), $response->body(), $debug);
                return false;
            }

            $payload = $response->json();

            $integration->update([
                'access_token'     => $payload['access_token'] ?? null,
                'refresh_token'    => $payload['refresh_token'] ?? $integration->refresh_token,
                'token_expires_at' => now()->addSeconds(max((int) ($payload['expires_in'] ?? 3600) - 60, 60)),
                'status'           => 'connected',
                'last_error'       => null,
            ]);

            $this->log($integration->fresh(), 'token_refresh', 'success', [], ['message' => 'Token refreshed.'], null, $debug);
            return true;
        } catch (Throwable $exception) {
            $integration->update(['status' => 'error', 'last_error' => $exception->getMessage()]);
            $this->log($integration, 'token_refresh', 'failed', [], [], $exception->getMessage(), $debug);
            return false;
        }
    }

    // METHOD 4: Create Event
    public function createEvent(CalendarIntegration $integration, array $booking): ?string
    {
        $this->ensureFreshToken($integration);

        $settings = $this->settingsFor($integration->user_id);

        $event = $this->buildEventData($booking, $settings);
        $bookingId = $booking['id'] ?? null;

        try {
            $response = Http::withToken($integration->access_token)
                ->post($this->calendarEventsUrl($integration), $event);

            if (!$response->successful()) {
                $this->log($integration, 'create', 'failed', $event, $response->json(), $response->body(), $settings->debug_mode, $bookingId);
                return null;
            }

            $eventId = $response->json('id');
            $this->log($integration, 'create', 'success', $event, $response->json(), null, $settings->debug_mode, $bookingId);

            return $eventId;
        } catch (Throwable $exception) {
            $this->log($integration, 'create', 'failed', $event, [], $exception->getMessage(), $settings->debug_mode, $bookingId);
            return null;
        }
    }

    // METHOD 5: Update Event
    public function updateEvent(CalendarIntegration $integration, string $eventId, array $booking): bool
    {
        $this->ensureFreshToken($integration);

        $settings = $this->settingsFor($integration->user_id);

        $event = $this->buildEventData($booking, $settings);
        $bookingId = $booking['id'] ?? null;

        try {
            $response = Http::withToken($integration->access_token)
                ->patch($this->calendarEventUrl($integration, $eventId), $event);

            if (!$response->successful()) {
                $this->log($integration, 'update', 'failed', $event, $response->json(), $response->body(), $settings->debug_mode, $bookingId);
                return false;
            }

            $this->log($integration, 'update', 'success', $event, $response->json(), null, $settings->debug_mode, $bookingId);
            return true;
        } catch (Throwable $exception) {
            $this->log($integration, 'update', 'failed', $event, [], $exception->getMessage(), $settings->debug_mode, $bookingId);
            return false;
        }
    }

    // METHOD 6: Cancel Event
    public function cancelEvent(CalendarIntegration $integration, string $eventId): bool
    {
        $this->ensureFreshToken($integration);
        $debug = $this->settingsFor($integration->user_id)->debug_mode;

        try {
            $response = Http::withToken($integration->access_token)
                ->delete($this->calendarEventUrl($integration, $eventId));

            if (!$response->successful() && $response->status() !== 404) {
                $this->log($integration, 'cancel', 'failed', ['event_id' => $eventId], $response->json(), $response->body(), $debug);
                return false;
            }

            $this->log($integration, 'cancel', 'success', ['event_id' => $eventId], ['status' => $response->status()], null, $debug);
            return true;
        } catch (Throwable $exception) {
            $this->log($integration, 'cancel', 'failed', ['event_id' => $eventId], [], $exception->getMessage(), $debug);
            return false;
        }
    }

    // METHOD 7: Fetch Changes (Inbound sync) - uses Graph delta query
    public function fetchChanges(CalendarIntegration $integration, ?string $syncToken = null): array
    {
        $this->ensureFreshToken($integration);
        $debug = $this->settingsFor($integration->user_id)->debug_mode;

        $url = $syncToken ?: self::API_URL . '/me/calendarView/delta?' . http_build_query([
            'startDateTime' => now()->subDays(1)->toAtomString(),
            'endDateTime'   => now()->addYears(1)->toAtomString(),
        ]);

        try {
            $response = Http::withToken($integration->access_token)->get($url);

            if (!$response->successful()) {
                $this->log($integration, 'sync', 'failed', ['url' => $url], $response->json(), $response->body(), $debug);
                return ['items' => [], 'next_sync_token' => null, 'full_resync_required' => $response->status() === 410];
            }

            $body = $response->json();
            $nextLink = $body['@odata.deltaLink'] ?? $body['@odata.nextLink'] ?? null;

            if ($nextLink) {
                $integration->update(['sync_token' => $nextLink]);
            }

            $this->log($integration, 'sync', 'success', ['url' => $url], ['count' => count($body['value'] ?? [])], null, $debug);

            return [
                'items'                => $body['value'] ?? [],
                'next_sync_token'      => $nextLink,
                'full_resync_required' => false,
            ];
        } catch (Throwable $exception) {
            $this->log($integration, 'sync', 'failed', ['url' => $url], [], $exception->getMessage(), $debug);
            return ['items' => [], 'next_sync_token' => null, 'full_resync_required' => false];
        }
    }

    // PRIVATE: get or create the integration row
    private function integration(int $userId): CalendarIntegration
    {
        return CalendarIntegration::firstOrCreate(
            ['user_id' => $userId, 'provider' => 'outlook'],
            ['status' => 'disconnected']
        );
    }

    private function ensureFreshToken(CalendarIntegration $integration): void
    {
        if ($integration->isTokenExpired()) {
            $this->refreshToken($integration);
            $integration->refresh();
        }
    }

    private function settingsFor(int $userId): CalendarSetting
    {
        return CalendarSetting::where('user_id', $userId)
            ->where('provider', 'outlook')
            ->first() ?? new CalendarSetting([
                'user_id'                  => $userId,
                'provider'                 => 'outlook',
                'debug_mode'               => false,
                'add_customer_as_attendee' => false,
            ]);
    }

    private function calendarEventsUrl(CalendarIntegration $integration): string
    {
        return $integration->calendar_id && $integration->calendar_id !== 'default'
            ? self::API_URL . "/me/calendars/{$integration->calendar_id}/events"
            : self::API_URL . '/me/events';
    }

    private function calendarEventUrl(CalendarIntegration $integration, string $eventId): string
    {
        return $integration->calendar_id && $integration->calendar_id !== 'default'
            ? self::API_URL . "/me/calendars/{$integration->calendar_id}/events/{$eventId}"
            : self::API_URL . "/me/events/{$eventId}";
    }

    // PRIVATE: Build event from booking + template (Graph event schema)
    private function buildEventData(array $booking, CalendarSetting $settings): array
    {
        $title       = $this->replacePlaceholders($settings->event_title_template ?: '{CUSTOMER_NAME} - {PRODUCT_NAME}', $booking);
        $description = $this->replacePlaceholders($settings->event_description_template ?? '', $booking);

        $event = [
            'subject' => $title,
            'body'    => [
                'contentType' => 'Text',
                'content'     => $description,
            ],
            'start' => ['dateTime' => $booking['start_at'], 'timeZone' => 'UTC'],
            'end'   => ['dateTime' => $booking['end_at'], 'timeZone' => 'UTC'],
        ];

        if ($settings->add_customer_as_attendee && !empty($booking['customer_email'])) {
            $event['attendees'] = [[
                'emailAddress' => [
                    'address' => $booking['customer_email'],
                    'name'    => $booking['customer_name'] ?? $booking['customer_email'],
                ],
                'type' => 'required',
            ]];
        }

        if (!empty($booking['location'])) {
            $event['location'] = [
                'displayName' => $booking['location'],
            ];
        }

        return $event;
    }

    // PRIVATE: Replace template placeholders
    private function replacePlaceholders(string $template, array $booking): string
    {
        $replacements = [
            '{CUSTOMER_NAME}'  => $booking['customer_name'] ?? '',
            '{BOOKING_STATUS}' => $booking['status'] ?? '',
            '{PRODUCT_NAME}'   => $booking['title'] ?? '',
            '{BOOKING_ID}'     => $booking['id'] ?? '',
            '{START_AT}'       => $booking['start_at'] ?? '',
            '{END_AT}'         => $booking['end_at'] ?? '',
            '{RESOURCE_NAME}'  => $booking['resource_name'] ?? '',
        ];

        return str_replace(array_keys($replacements), array_values($replacements), $template);
    }

    private function log(
        CalendarIntegration $integration,
        string $action,
        string $status,
        array $request = [],
        array $response = [],
        ?string $error = null,
        bool $debug = false,
        $bookingOrderId = null
    ): void
    {
        CalendarLog::create([
            'user_id'          => $integration->user_id,
            'provider'         => 'outlook',
            'action'           => $action,
            'status'           => $status,
            'booking_order_id' => $bookingOrderId ?? ($request['id'] ?? null),
            'request_data'     => $debug ? $request : $this->minimal($request),
            'response_data'    => $debug ? $response : $this->minimal($response),
            'error_message'    => $error,
        ]);
    }

    private function minimal(array $data): array
    {
        $allowed = ['id', 'event_id', 'status', 'count', 'message'];

        return array_intersect_key($data, array_flip($allowed));
    }
}

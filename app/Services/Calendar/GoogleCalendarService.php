<?php

namespace App\Services\Calendar;

use App\Models\CalendarIntegration;
use App\Models\CalendarLog;
use App\Models\CalendarSetting;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;
use Throwable;

class GoogleCalendarService
{
    private const AUTH_URL  = 'https://accounts.google.com/o/oauth2/v2/auth';
    private const TOKEN_URL = 'https://oauth2.googleapis.com/token';
    private const API_URL   = 'https://www.googleapis.com/calendar/v3';

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
            'redirect_uri'  => route('calendar.google.callback'),
            'response_type' => 'code',
            'scope'         => 'https://www.googleapis.com/auth/calendar.events',
            'access_type'   => 'offline',
            'prompt'        => 'consent',
            'state'         => $state,
        ]);
    }

    // METHOD 2: Handle OAuth Callback
    public function handleCallback(string $code, int $userId): bool
    {
        $integration = $this->integration($userId);

        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id'     => $integration->client_id,
                'client_secret' => $integration->client_secret,
                'code'          => $code,
                'grant_type'    => 'authorization_code',
                'redirect_uri'  => route('calendar.google.callback'),
            ]);

            if (!$response->successful()) {
                $integration->update(['status' => 'error', 'last_error' => $response->body()]);
                $this->log($integration, 'token_refresh', 'failed', [], $response->json(), $response->body());
                return false;
            }

            $payload = $response->json();

            $integration->update([
                'access_token'     => $payload['access_token'] ?? null,
                'refresh_token'    => $payload['refresh_token'] ?? $integration->refresh_token,
                'token_expires_at' => now()->addSeconds(max((int) ($payload['expires_in'] ?? 3600) - 60, 60)),
                'calendar_id'      => $integration->calendar_id ?: 'primary',
                'status'           => 'connected',
                'last_error'       => null,
                'last_sync_at'     => now(),
            ]);

            $this->log($integration->fresh(), 'token_refresh', 'success', [], ['message' => 'Google Calendar connected.']);
            return true;
        } catch (Throwable $exception) {
            $integration->update(['status' => 'error', 'last_error' => $exception->getMessage()]);
            $this->log($integration, 'token_refresh', 'failed', [], [], $exception->getMessage());
            return false;
        }
    }

    // METHOD 3: Refresh Token
    public function refreshToken(CalendarIntegration $integration): bool
    {
        if (empty($integration->refresh_token)) {
            return false;
        }

        try {
            $response = Http::asForm()->post(self::TOKEN_URL, [
                'client_id'     => $integration->client_id,
                'client_secret' => $integration->client_secret,
                'refresh_token' => $integration->refresh_token,
                'grant_type'    => 'refresh_token',
            ]);

            if (!$response->successful()) {
                $integration->update(['status' => 'error', 'last_error' => $response->body()]);
                $this->log($integration, 'token_refresh', 'failed', [], $response->json(), $response->body());
                return false;
            }

            $payload = $response->json();

            $integration->update([
                'access_token'     => $payload['access_token'] ?? null,
                'token_expires_at' => now()->addSeconds(max((int) ($payload['expires_in'] ?? 3600) - 60, 60)),
                'status'           => 'connected',
                'last_error'       => null,
            ]);

            $this->log($integration->fresh(), 'token_refresh', 'success', [], ['message' => 'Token refreshed.']);
            return true;
        } catch (Throwable $exception) {
            $integration->update(['status' => 'error', 'last_error' => $exception->getMessage()]);
            $this->log($integration, 'token_refresh', 'failed', [], [], $exception->getMessage());
            return false;
        }
    }

    // METHOD 4: Create Event
    public function createEvent(CalendarIntegration $integration, array $booking): ?string
    {
        $this->ensureFreshToken($integration);

        $settings = CalendarSetting::where('user_id', $integration->user_id)
            ->where('provider', 'google')
            ->first() ?? new CalendarSetting();

        $event = $this->buildEventData($booking, $settings);
        $calendarId = $integration->calendar_id ?: 'primary';

        try {
            $response = Http::withToken($integration->access_token)
                ->post(self::API_URL . "/calendars/{$calendarId}/events", $event);

            if (!$response->successful()) {
                $this->log($integration, 'create', 'failed', $event, $response->json(), $response->body());
                return null;
            }

            $eventId = $response->json('id');
            $this->log($integration, 'create', 'success', $event, $response->json());

            return $eventId;
        } catch (Throwable $exception) {
            $this->log($integration, 'create', 'failed', $event, [], $exception->getMessage());
            return null;
        }
    }

    // METHOD 5: Update Event
    public function updateEvent(CalendarIntegration $integration, string $providerEventId, array $booking): bool
    {
        $this->ensureFreshToken($integration);

        $settings = CalendarSetting::where('user_id', $integration->user_id)
            ->where('provider', 'google')
            ->first() ?? new CalendarSetting();

        $event = $this->buildEventData($booking, $settings);
        $calendarId = $integration->calendar_id ?: 'primary';

        try {
            $response = Http::withToken($integration->access_token)
                ->put(self::API_URL . "/calendars/{$calendarId}/events/{$providerEventId}", $event);

            if (!$response->successful()) {
                $this->log($integration, 'update', 'failed', $event, $response->json(), $response->body());
                return false;
            }

            $this->log($integration, 'update', 'success', $event, $response->json());
            return true;
        } catch (Throwable $exception) {
            $this->log($integration, 'update', 'failed', $event, [], $exception->getMessage());
            return false;
        }
    }

    // METHOD 6: Cancel Event
    public function cancelEvent(CalendarIntegration $integration, string $providerEventId): bool
    {
        $this->ensureFreshToken($integration);
        $calendarId = $integration->calendar_id ?: 'primary';

        try {
            $response = Http::withToken($integration->access_token)
                ->delete(self::API_URL . "/calendars/{$calendarId}/events/{$providerEventId}");

            // Google returns 410 if event was already deleted remotely - treat as success
            if (!$response->successful() && $response->status() !== 410 && $response->status() !== 404) {
                $this->log($integration, 'cancel', 'failed', ['event_id' => $providerEventId], $response->json(), $response->body());
                return false;
            }

            $this->log($integration, 'cancel', 'success', ['event_id' => $providerEventId], ['status' => $response->status()]);
            return true;
        } catch (Throwable $exception) {
            $this->log($integration, 'cancel', 'failed', ['event_id' => $providerEventId], [], $exception->getMessage());
            return false;
        }
    }

    // METHOD 7: Fetch Changes (Inbound sync)
    public function fetchChanges(CalendarIntegration $integration, ?string $syncToken = null): array
    {
        $this->ensureFreshToken($integration);
        $calendarId = $integration->calendar_id ?: 'primary';

        $query = ['singleEvents' => 'true'];
        if ($syncToken) {
            $query['syncToken'] = $syncToken;
        } else {
            $query['timeMin'] = now()->subDays(1)->toRfc3339String();
        }

        try {
            $response = Http::withToken($integration->access_token)
                ->get(self::API_URL . "/calendars/{$calendarId}/events", $query);

            if (!$response->successful()) {
                // 410 Gone means the syncToken expired - caller should do a full resync
                $this->log($integration, 'sync', 'failed', $query, $response->json(), $response->body());
                return ['items' => [], 'next_sync_token' => null, 'full_resync_required' => $response->status() === 410];
            }

            $body = $response->json();
            $integration->update(['sync_token' => $body['nextSyncToken'] ?? $integration->sync_token]);

            $this->log($integration, 'sync', 'success', $query, ['count' => count($body['items'] ?? [])]);

            return [
                'items'                 => $body['items'] ?? [],
                'next_sync_token'       => $body['nextSyncToken'] ?? null,
                'full_resync_required'  => false,
            ];
        } catch (Throwable $exception) {
            $this->log($integration, 'sync', 'failed', $query, [], $exception->getMessage());
            return ['items' => [], 'next_sync_token' => null, 'full_resync_required' => false];
        }
    }

    // PRIVATE: get or create the integration row (holds per-user client credentials)
    private function integration(int $userId): CalendarIntegration
    {
        return CalendarIntegration::firstOrCreate(
            ['user_id' => $userId, 'provider' => 'google'],
            ['status' => 'disconnected']
        );
    }

    // PRIVATE: refresh token proactively if it is expired or about to expire
    private function ensureFreshToken(CalendarIntegration $integration): void
    {
        if ($integration->isTokenExpired()) {
            $this->refreshToken($integration);
            $integration->refresh();
        }
    }

    // PRIVATE: Build event from booking + template
    private function buildEventData(array $booking, CalendarSetting $settings): array
    {
        $title       = $this->replacePlaceholders($settings->event_title_template ?: '{CUSTOMER_NAME} - {PRODUCT_NAME}', $booking);
        $description = $this->replacePlaceholders($settings->event_description_template ?? '', $booking);

        $event = [
            'summary'     => $title,
            'description' => $description,
            'start'       => ['dateTime' => $booking['start_at'], 'timeZone' => 'UTC'],
            'end'         => ['dateTime' => $booking['end_at'], 'timeZone' => 'UTC'],
        ];

        if ($settings->add_customer_as_attendee && !empty($booking['customer_email'])) {
            $event['attendees'] = [['email' => $booking['customer_email']]];
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

    // PRIVATE: write a calendar_logs row
    private function log(CalendarIntegration $integration, string $action, string $status, array $request = [], array $response = [], ?string $error = null): void
    {
        CalendarLog::create([
            'user_id'          => $integration->user_id,
            'provider'         => 'google',
            'action'           => $action,
            'status'           => $status,
            'booking_order_id' => $request['id'] ?? null,
            'request_data'     => $request,
            'response_data'    => $response,
            'error_message'    => $error,
        ]);
    }
}

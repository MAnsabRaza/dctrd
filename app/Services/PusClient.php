<?php

namespace App\Services;

use App\Models\QrScanLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * PUS = Premium URL Shortener (subdomain, e.g. s.rocket-lms.com), config/pus.php se configure hota hai.
 */
class PusClient
{
    protected string $baseUrl;
    protected ?string $token;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('pus.base_url'), '/');
        $this->token   = config('pus.api_token');
    }

    /**
     * Naya short link + QR image banao aur model par save karo.
     */
    public function createLink($model): bool
    {
        $destinationUrl = $model->public_url ?? url('/');

        try {
            $response = Http::withToken($this->token)
                ->post("{$this->baseUrl}/api/links", [
                    'destination_url' => $destinationUrl,
                    'label'           => class_basename($model) . '#' . $model->id,
                ]);

            if (!$response->successful()) {
                Log::warning('PusClient::createLink failed', [
                    'model' => class_basename($model),
                    'id'    => $model->id,
                    'body'  => $response->body(),
                ]);
                return false;
            }

            $body = $response->json();
            $code = $body['code'] ?? Str::random(7);

            $model->forceFill([
                'short_code'           => $code,
                'short_url'            => $body['short_url'] ?? "{$this->baseUrl}/{$code}",
                'qr_image_path'        => $this->storeQrImage($model, $body['qr_image_base64'] ?? null),
                'qr_last_refreshed_at' => now(),
                'qr_revoked_at'        => null,
            ])->saveQuietly(); // saveQuietly -> observer/trait dobara trigger na ho (loop se bacha)

            return true;
        } catch (\Throwable $e) {
            Log::error('PusClient::createLink exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * PUS side link disable/delete karo aur local record par revoke timestamp lagao.
     */
    public function deleteOrDisable($model): bool
    {
        if (!$model->short_code) {
            return false;
        }

        try {
            Http::withToken($this->token)
                ->delete("{$this->baseUrl}/api/links/{$model->short_code}");

            $model->forceFill([
                'qr_revoked_at' => now(),
            ])->saveQuietly();

            return true;
        } catch (\Throwable $e) {
            Log::error('PusClient::deleteOrDisable exception', ['error' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Invoice / email / certificate mein embed karne ke liye QR image path do.
     * Agar missing/revoked ho to dobara generate karne ki koshish karta hai.
     */
    public function getQrImage($model): ?string
    {
        if ($model->qr_image_path && $model->qr_revoked_at === null) {
            return $model->qr_image_path;
        }

        $this->createLink($model);

        return $model->fresh()->qr_image_path;
    }

    /**
     * Ek short_code ke scan stats (local qr_scan_logs table se calculate).
     */
    public function getStats(string $shortCode): array
    {
        $base = QrScanLog::where('short_code', $shortCode);

        return [
            'total_scans'   => (clone $base)->count(),
            'unique_ips'    => (clone $base)->distinct('ip_address')->count('ip_address'),
            'checkins'      => (clone $base)->where('is_checkin', true)->count(),
            'top_referrers' => (clone $base)
                ->selectRaw('referrer, count(*) as total')
                ->whereNotNull('referrer')
                ->groupBy('referrer')
                ->orderByDesc('total')
                ->limit(5)
                ->get(),
        ];
    }

    /**
     * Base64 QR image ko public disk par save karke relative path return karta hai.
     */
    protected function storeQrImage($model, ?string $base64): ?string
    {
        if (!$base64) {
            return $model->qr_image_path; // kuch naya na mile to jo pehle se hai wahi rakho
        }

        $folder   = 'qr-codes/' . Str::plural(Str::snake(class_basename($model)));
        $filename = "{$folder}/{$model->id}.png";

        Storage::disk('public')->put($filename, base64_decode($base64));

        return $filename;
    }
}

<?php

namespace App\Services;

use App\Models\QrScanLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * PusClient — ab poori tarah LOCAL generation karta hai:
 *  - short_code + short_url apne hi /r/{code} redirect route se banta hai
 *    (koi external subdomain/API token ki zaroorat nahi).
 *  - QR image ek free public QR API (api.qrserver.com) se generate hoti hai
 *    aur phir apne server ke storage/public disk par save hoti hai.
 *
 * Agar kabhi aap real Premium URL Shortener app (alag subdomain) use karna
 * chahen, config/pus.php mein 'base_url'/'api_token' set kar dena aur
 * neeche wale methods mein wapas Http::withToken(...) wala external-API
 * approach use kar lena — filhal yeh sab kuch locally hi karta hai.
 */
class PusClient
{
    /**
     * Naya short link + QR image banao aur model par save karo.
     */
    public function createLink($model): bool
    {
        try {
            $code = $this->generateUniqueCode($model);
            $shortUrl = url('/r/' . $code);

            $qrImagePath = $this->generateAndStoreQrImage($model, $shortUrl);

            $model->forceFill([
                'short_code'           => $code,
                'short_url'            => $shortUrl,
                'qr_image_path'        => $qrImagePath,
                'qr_last_refreshed_at' => now(),
                'qr_revoked_at'        => null,
            ])->saveQuietly(); // saveQuietly -> observer/trait dobara trigger na ho (loop se bacha)

            return true;
        } catch (\Throwable $e) {
            Log::error('PusClient::createLink exception', [
                'model' => class_basename($model),
                'id'    => $model->id ?? null,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Link disable karo — local record par revoke timestamp lagao.
     * (Local generation hai, isliye kisi external API ko delete call
     * bhejne ki zaroorat nahi — bas revoke timestamp save karo.)
     */
    public function deleteOrDisable($model): bool
    {
        if (!$model->short_code) {
            return false;
        }

        try {
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
     * Random 7-char code banao jo kisi bhi QR-enabled table (products, webinars,
     * bookings, bundles) mein short_code column se clash na kare.
     */
    protected function generateUniqueCode($model): string
    {
        $tables = ['products', 'webinars', 'bookings', 'bundles'];

        do {
            $code = Str::random(7);
            $exists = false;

            foreach ($tables as $table) {
                if (\Illuminate\Support\Facades\Schema::hasTable($table)
                    && \Illuminate\Support\Facades\DB::table($table)->where('short_code', $code)->exists()) {
                    $exists = true;
                    break;
                }
            }
        } while ($exists);

        return $code;
    }

    /**
     * Free public QR API se PNG image banwao aur public disk par save karke
     * relative path return karo.
     */
    protected function generateAndStoreQrImage($model, string $shortUrl): ?string
    {
        $size = (int) config('pus.qr_size', 400);

        $response = Http::timeout(15)->get('https://api.qrserver.com/v1/create-qr-code/', [
            'size' => "{$size}x{$size}",
            'data' => $shortUrl,
        ]);

        if (!$response->successful()) {
            Log::warning('PusClient::generateAndStoreQrImage failed', [
                'model'  => class_basename($model),
                'id'     => $model->id ?? null,
                'status' => $response->status(),
            ]);
            return null;
        }

        $folder   = 'qr-codes/' . Str::plural(Str::snake(class_basename($model)));
        $filename = "{$folder}/{$model->id}.png";

        Storage::disk('public')->put($filename, $response->body());

        return $filename;
    }
}
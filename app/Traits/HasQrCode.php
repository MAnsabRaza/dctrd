<?php

namespace App\Traits;

use App\Models\QrScanLog;
use App\Services\PusClient;

/**
 * Usage: Product / Course / Booking / Bundle model mein:
 *
 *     use App\Traits\HasQrCode;
 *
 *     class Product extends Model
 *     {
 *         use HasQrCode;
 *         ...
 *     }
 *
 * Trait khud "qr_enabled" ki value dekh kar save() ke baad
 * short link + QR auto create/disable kar deta hai.
 */
trait HasQrCode
{
    /**
     * Is item ke sare scan logs.
     */
    public function qrScanLogs()
    {
        return $this->morphMany(QrScanLog::class, 'item');
    }

    /**
     * Model boot hote hi Laravel yeh khud call kar leta hai (trait naming convention).
     */
    public static function bootHasQrCode(): void
    {
        static::saved(function ($model) {
            $pus = app(PusClient::class);

            // qr_enabled on hua aur abhi tak koi short_code nahi bana -> naya link banao
            if ($model->qr_enabled && !$model->short_code) {
                $pus->createLink($model);
            }

            // qr_enabled off hua lekin link pehle se maujood hai -> disable karo
            if (!$model->qr_enabled && $model->short_code && !$model->qr_revoked_at) {
                $pus->deleteOrDisable($model);
            }
        });
    }

    /**
     * Blade mein $item->qr_image_url use karne ke liye.
     */
    public function getQrImageUrlAttribute(): ?string
    {
        return $this->qr_image_path
            ? asset('storage/' . $this->qr_image_path)
            : null;
    }

    /**
     * Har model apna "asli" public URL is method se batayega
     * (redirect isi URL par hoga jab koi QR/short-link scan kare).
     * Model mein override kar sakte ho agar route different ho.
     */
    public function getPublicUrlAttribute(): string
    {
        return url('/');
    }
}

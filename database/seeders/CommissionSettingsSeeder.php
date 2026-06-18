<?php

namespace Database\Seeders;

use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Database\Seeder;

class CommissionSettingsSeeder extends Seeder
{
    public function run()
    {
        $defaults = [
            'booking_commission_type' => 'percent',
            'booking_commission_value' => 30,
            'commission_real_estate_type' => 'percent',
            'commission_real_estate_value' => 20,
            'commission_lifestyle_type' => 'percent',
            'commission_lifestyle_value' => 20,
            'commission_healthcare_type' => 'percent',
            'commission_healthcare_value' => 20,
            'commission_automotive_type' => 'percent',
            'commission_automotive_value' => 20,
            'commission_tutoring_type' => 'percent',
            'commission_tutoring_value' => 20,
            'commission_consulting_type' => 'percent',
            'commission_consulting_value' => 30,
        ];

        $setting = Setting::updateOrCreate(
            ['name' => Setting::$commissionSettingsName],
            ['page' => 'financial', 'updated_at' => time()]
        );

        $locale = mb_strtolower(Setting::$defaultSettingsLocale);
        $translation = SettingTranslation::query()
            ->where('setting_id', $setting->id)
            ->where('locale', $locale)
            ->first();

        $values = [];

        if (!empty($translation) and !empty($translation->value)) {
            $values = json_decode($translation->value, true) ?: [];
        }

        foreach ($defaults as $key => $value) {
            if (!array_key_exists($key, $values)) {
                $values[$key] = $value;
            }
        }

        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $setting->id,
                'locale' => $locale,
            ],
            [
                'value' => json_encode($values),
            ]
        );

        cache()->forget('settings.' . Setting::$commissionSettingsName);
    }
}

<?php

namespace App\Http\Controllers\Admin\Booking;

use App\Http\Controllers\Controller;
use App\Models\Booking;
use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingContentSettingsController extends Controller
{
    public function featuredBookings()
    {
        $this->authorize('admin_booking_featured_bookings');
        removeContentLocale();

        $bookings = Booking::query()
            ->orderBy('title')
            ->get(['id', 'title']);

        $settingValues = $this->getSettingValues(Setting::$bookingFeaturedBookingsSettingsName);

        return view('admin.booking.featured_bookings.index', [
            'pageTitle' => 'Featured Bookings',
            'bookings' => $bookings,
            'settingValues' => $settingValues,
        ]);
    }

    public function storeFeaturedBookings(Request $request)
    {
        $this->authorize('admin_booking_featured_bookings');

        $data = $request->all();

        Validator::make($data, [
            'value.featured_bookings' => 'nullable|array',
            'value.featured_bookings.*' => 'integer|exists:bookings,id',
            'value.background_image' => 'nullable|string|max:255',
            'value.overlay_image' => 'nullable|string|max:255',
        ])->validate();

        $newValues = $data['value'] ?? [];
        $newValues['featured_bookings'] = $request->input('value.featured_bookings', []);

        return $this->saveSetting(
            Setting::$bookingFeaturedBookingsSettingsName,
            $newValues,
            $request->get('locale', Setting::$defaultSettingsLocale)
        );
    }

    public function settings()
    {
        $this->authorize('admin_booking_settings');
        removeContentLocale();

        return view('admin.booking.settings', [
            'pageTitle' => 'Booking Settings',
            'itemValue' => $this->getSettingValues(Setting::$bookingSettingsName),
        ]);
    }

    public function storeSettings(Request $request)
    {
        $this->authorize('admin_booking_settings');

        $data = $request->all();

        Validator::make($data, [
            'value.status' => 'nullable|boolean',
            'value.rental_commission' => 'nullable|numeric|min:0',
            'value.lifestyle_events_commission' => 'nullable|numeric|min:0',
            'value.activate_automation' => 'nullable|boolean',
            'value.enable_tutoring' => 'nullable|boolean',
            'value.enable_counselling' => 'nullable|boolean',
            'value.optional_address' => 'nullable|boolean',
            'value.activate_comments' => 'nullable|boolean',
        ])->validate();

        return $this->saveSetting(
            Setting::$bookingSettingsName,
            $data['value'] ?? [],
            $request->get('locale', Setting::$defaultSettingsLocale)
        );
    }

    private function getSettingValues(string $name): ?array
    {
        $setting = Setting::where('page', 'general')
            ->where('name', $name)
            ->first();

        if (empty($setting) or empty($setting->value)) {
            return null;
        }

        return json_decode($setting->value, true);
    }

    private function saveSetting(string $name, array $newValues, string $locale)
    {
        $page = 'general';
        $values = [];
        $settings = Setting::where('name', $name)->first();

        if (!empty($settings) and !empty($settings->value)) {
            $values = json_decode($settings->value);
        }

        if (!empty($newValues) and !empty($values)) {
            foreach ($newValues as $newKey => $newValue) {
                foreach ($values as $key => $value) {
                    if ($key == $newKey) {
                        $values->$key = $newValue;
                        unset($newValues[$key]);
                    }
                }
            }
        }

        if (!empty($newValues)) {
            $values = array_merge((array) $values, $newValues);
        }

        $settings = Setting::updateOrCreate(
            ['name' => $name],
            [
                'page' => $page,
                'updated_at' => time(),
            ]
        );

        SettingTranslation::updateOrCreate(
            [
                'setting_id' => $settings->id,
                'locale' => mb_strtolower($locale),
            ],
            [
                'value' => json_encode($values),
            ]
        );

        cache()->forget('settings.' . $name);

        return back();
    }
}

<?php

use App\Models\Setting;
use App\Models\Translation\SettingTranslation;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private array $templates = [
        'role_request_created' => [
            'title' => 'New role request #[request.id]',
            'template' => '[u.name] requested the [role.name] role. Review it here: [link]',
        ],
        'role_request_approved' => [
            'title' => 'Role request approved #[request.id]',
            'template' => 'Your request for the [role.name] role has been approved. View your roles: [link]',
        ],
        'role_request_rejected' => [
            'title' => 'Role request rejected #[request.id]',
            'template' => 'Your request for the [role.name] role was rejected. Reason: [reason]',
        ],
    ];

    public function up(): void
    {
        $notificationSettings = $this->getNotificationSettings();

        foreach ($this->templates as $key => $data) {
            $templateId = DB::table('notification_templates')
                ->where('title', $data['title'])
                ->value('id');

            if (empty($templateId)) {
                $templateId = DB::table('notification_templates')->insertGetId($data);
            }

            $notificationSettings[$key] = $templateId;
        }

        $this->saveNotificationSettings($notificationSettings);
    }

    public function down(): void
    {
        $notificationSettings = $this->getNotificationSettings();

        foreach ($this->templates as $key => $data) {
            unset($notificationSettings[$key]);

            DB::table('notification_templates')
                ->where('title', $data['title'])
                ->delete();
        }

        $this->saveNotificationSettings($notificationSettings);
    }

    private function getNotificationSettings(): array
    {
        $setting = DB::table('settings')->where('name', Setting::$notificationTemplatesName)->first();

        if (empty($setting)) {
            return [];
        }

        $value = null;

        if (Schema::hasColumn('settings', 'value') and !empty($setting->value)) {
            $value = $setting->value;
        }

        if (empty($value) and Schema::hasTable('setting_translations')) {
            $value = DB::table('setting_translations')
                ->where('setting_id', $setting->id)
                ->where('locale', Setting::$defaultSettingsLocale)
                ->value('value');
        }

        return !empty($value) ? (json_decode($value, true) ?: []) : [];
    }

    private function saveNotificationSettings(array $values): void
    {
        $payload = json_encode(array_filter($values));

        $settingData = [
            'updated_at' => time(),
        ];

        if (Schema::hasColumn('settings', 'page')) {
            $settingData['page'] = 'notifications';
        }

        if (Schema::hasColumn('settings', 'value')) {
            $settingData['value'] = $payload;
        }

        $setting = Setting::updateOrCreate(
            ['name' => Setting::$notificationTemplatesName],
            $settingData
        );

        if (Schema::hasTable('setting_translations')) {
            SettingTranslation::updateOrCreate(
                [
                    'setting_id' => $setting->id,
                    'locale' => Setting::$defaultSettingsLocale,
                ],
                [
                    'value' => $payload,
                ]
            );
        }

        cache()->forget('settings.' . Setting::$notificationTemplatesName);
    }
};

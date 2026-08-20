<?php

namespace App\Http\Controllers\Panel;

use App\Bitwise\UserLevelOfTraining;
use App\Http\Controllers\Controller;
use App\Http\Controllers\Web\traits\UserFormFieldsTrait;
use App\Mixins\Geo\Geo;
use App\Mixins\RegistrationPackage\UserPackage;
use App\Models\CalendarIntegration;
use App\Models\CalendarLog;
use App\Models\CalendarSetting;
use App\Models\Category;
use App\Models\BookingCategory;
use App\Models\DeleteAccountRequest;
use App\Models\Newsletter;
use App\Models\OrgAvailabilityRange;
use App\Models\OrgAvailabilityRule;
use App\Models\Region;
use App\Models\RegulatoryFormSubmission;
use App\Models\RegulatoryFormTemplate;
use App\Models\ReserveMeeting;
use App\Models\Reward;
use App\Models\RewardAccounting;
use App\Models\Role;
use App\Models\UserBank;
use App\Models\UserLoginHistory;
use App\Models\UserMeta;
use App\Models\UserOccupation;
use App\Models\UserSelectedBank;
use App\Models\UserSelectedBankSpecification;
use App\Models\UserZoomApi;
use App\Services\CheckoutModuleService;
use App\Services\UnitConversionService;
use App\Services\LocationService;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    use UserFormFieldsTrait;

    public function setting(Request $request, $step = "basic_information")
    {
        $this->authorize("panel_others_profile_setting");

        $user = auth()->user();

        $data = [
            'pageTitle' => trans('panel.settings'),
            'user' => $user,
        ];
        $data = array_merge($data, $this->getUserEditPageData($request, $user, $step));

        return view('design_1.panel.settings.index', $data);
    }

    private function makeRegulatoryViewData($user): array
{
    $userRoles = \App\Models\UserRoleRequest::where('user_id', $user->id)
        ->where('status', \App\Models\UserRoleRequest::STATUS_ACTIVE)
        ->with('roleCatalog')
        ->get();

    $stacks = [];

    foreach ($userRoles as $userRole) {
        $roleCatalogId = $userRole->role_catalog_id;

        // ab RegulatoryFormTemplate ki jagah admin Forms builder se aa raha hai
        $primaryForm = \App\Models\Form::where('connect_regulatory', true)
            ->where('regulatory_role_catalog_id', $roleCatalogId)
            ->where('regulatory_level', 'primary')
            ->where('enable', true)
            ->with(['fields.options']) // fields + unke dropdown/checkbox/radio options
            ->first();

        if (empty($primaryForm)) {
            continue; // is role ke liye koi connected form hi nahi bana admin ne
        }

        $primarySubmission = \App\Models\RegulatoryFormSubmission::where('user_id', $user->id)
            ->where('role_catalog_id', $roleCatalogId)
            ->where('level', 'primary')
            ->first();

        $extraForms = \App\Models\Form::where('connect_regulatory', true)
            ->where('regulatory_role_catalog_id', $roleCatalogId)
            ->whereIn('regulatory_level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
            ->where('enable', true)
            ->with(['fields.options'])
            ->get();

        $extraSubmissions = \App\Models\RegulatoryFormSubmission::where('user_id', $user->id)
            ->where('role_catalog_id', $roleCatalogId)
            ->whereIn('level', ['secondary', 'tertiary', 'quaternary', 'extra1'])
            ->get()
            ->groupBy('form_id'); // template_id ki jagah form_id se group

        $stacks[] = [
            'role'              => $userRole->roleCatalog,
            'roleRequestStatus' => $userRole->status,
            'primaryForm'       => $primaryForm,
            'primarySubmission' => $primarySubmission,
            'extraForms'        => $extraForms,
            'extraSubmissions'  => $extraSubmissions,
        ];
    }

    $countries = \App\Models\Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
        ->where('type', \App\Models\Region::$country)
        ->get();

    $userCountry = $user->country;

    if (!empty($user->country_id)) {
        $country = \App\Models\Region::where('id', $user->country_id)->first();
        $userCountry = $country->title ?? $userCountry;
    }

    return compact('stacks', 'countries', 'userCountry');
}

    public function getUserEditPageData(Request $request, $user, $step): array
{
    $categories = Category::getCategories();

    $userMetas = $user->userMetas;

    if (!empty($userMetas)) {
        foreach ($userMetas as $meta) {
            $user->{$meta->name} = $meta->value;
        }
    }

    $occupations = $user->occupations->pluck('category_id')->toArray();

    $userLanguages = getGeneralSettings('user_languages');
    if (!empty($userLanguages) and is_array($userLanguages)) {
        $userLanguages = getLanguages($userLanguages);
    } else {
        $userLanguages = [];
    }

    $countries = null;
    $provinces = null;
    $cities = null;
    $districts = null;
    $attachments = null;
    $userLoginHistories = null;
    $moduleSettings = null;
    $formFieldsHtml = null;
    $bookingSettingsData = [];
    $availabilitySettingsData = [];
    $calendarConnectionsData = [];
        $abilitiesSettingsData = [];
    $erpSettingsData = [];
    $regulatorySettingsData = [];
    $rolesSettingsData = [];

    // ===== ERP DATA — HAMESHA COMPUTE HO (step-independent) =====
    if ($user->isOrganization() or $user->isTeacher()) {
        $erpSettingsData = app(\App\Http\Controllers\Panel\ErpSettingsController::class)->viewData($user->id);
    }
    // ================================================================

    if ($step == "extra_information") {
        $countries = Region::select(DB::raw('*, ST_AsText(geo_center) as geo_center'))
            ->where('type', Region::$country)
            ->get();

        $userType = "organization";
        if ($user->isTeacher()) {
            $userType = "teacher";
        } elseif ($user->isUser()) {
            $userType = "user";
        }

        $formFieldsHtml = $this->getFormFieldsByUserType($request, $userType, true, $user);

    } elseif ($step == "about") {
        $attachments = $user->profileAttachments;
    } elseif ($step == "login_history") {
        $userLoginHistories = UserLoginHistory::query()->where('user_id', $user->id)
            ->orderBy('created_at', 'desc')
            ->get();
    } elseif ($step == "checkout_options") {
        if (!($user->isOrganization() or $user->isTeacher())) {
            abort(404);
        }
        $moduleSettings = app(CheckoutModuleService::class)->getOrgModuleSettings($user->id);
    } elseif ($step == "booking_settings") {
        if (!($user->isOrganization() or $user->isTeacher())) {
            abort(404);
        }
        $bookingSettingsData = $this->makeBookingSettingsViewData($user->id);
    } elseif ($step == "external_connections") {
        if (!($user->isOrganization() or $user->isTeacher())) {
            abort(404);
        }
        $calendarConnectionsData = $this->makeExternalConnectionsViewData($user->id);
    } elseif ($step == "abilities") {
        if (!($user->isOrganization() or $user->isTeacher())) {
            abort(404);
        }
        $abilitiesSettingsData = $this->makeAbilitiesViewData($user->id);
    } elseif ($step == "availability") {
        if (!($user->isOrganization() or $user->isTeacher())) {
            abort(404);
        }
        $availabilitySettingsData = $this->makeAvailabilitySettingsViewData($user->id);
        } elseif ($step == "regulatory") {
    $regulatorySettingsData = $this->makeRegulatoryViewData($user);
} elseif ($step == "roles") {
    if (!$user->can('panel_roles')) {
        abort(404);
    }
    $rolesSettingsData = $this->makeRolesViewData($user);
}

    // ⚠️ "erp" step ka alag elseif ab yahan NAHI hai — upar unconditional ho chuka hai

    $userBanks = UserBank::query()
        ->with(['specifications'])
        ->orderBy('created_at', 'desc')
        ->get();

    return array_merge([
        'categories' => $categories,
        'educations' => $userMetas->where('name', 'education'),
        'experiences' => $userMetas->where('name', 'experience'),
        'occupations' => $occupations,
        'userLanguages' => $userLanguages,
        'currentStep' => $step,
        'countries' => $countries,
        'provinces' => $provinces,
        'cities' => $cities,
        'districts' => $districts,
        'userBanks' => $userBanks,
        'unitPreferences' => $this->getUnitPreferencesData(),
        'formFieldsHtml' => $formFieldsHtml,
        'attachments' => $attachments,
        'userLoginHistories' => $userLoginHistories,
        'moduleSettings' => $moduleSettings,
    ], $bookingSettingsData, $availabilitySettingsData, $calendarConnectionsData, $abilitiesSettingsData, $erpSettingsData, $regulatorySettingsData, $rolesSettingsData);
}
    private function makeRolesViewData($user): array
{
    $eligibilityService = app(\App\Services\RoleEligibilityService::class);

    $users = collect([$user]);
    $roleCatalogs = $eligibilityService->eligibleRoles($user);

    $roleRequests = \App\Models\UserRoleRequest::where('user_id', $user->id)
        ->with(['user', 'roleCatalog'])
        ->orderByDesc('requested_at')
        ->orderByDesc('id')
        ->paginate(15);

    return compact('users', 'roleCatalogs', 'roleRequests');
}

    private function makeAbilitiesViewData(int $vendorId): array
{
    $abilities = app(\App\Services\AbilityService::class)->getAvailableAbilitiesForVendor($vendorId);

    return compact('abilities');
}




    private function makeExternalConnectionsViewData(int $userId): array
{
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

    return compact('calendarIntegrations', 'calendarSettings', 'calendarIcalUrl', 'calendarLogs');
}
    private function makeBookingSettingsViewData(int $userId): array
    {
        $categories = BookingCategory::query()
            ->select(['id', 'parent_id', 'title', 'status', 'order'])
            ->orderBy('order')
            ->orderBy('id')
            ->get();

        $grouped = $categories->groupBy(fn($item) => $item->parent_id ?: 0);

        $buildNode = function ($category, bool $parentEnabled = true) use (&$buildNode, $grouped) {
            $rawEnabled = (bool) $category->status;
            $effectiveEnabled = $parentEnabled && $rawEnabled;

            $children = collect($grouped->get((int) $category->id, []))
                ->map(fn($child) => $buildNode($child, $effectiveEnabled))
                ->values()
                ->all();

            return [
                'id' => (int) $category->id,
                'title' => $category->title,
                'enabled' => $effectiveEnabled,
                'children' => $children,
            ];
        };

        return [
            'categoryTree' => collect($grouped->get(0, []))
                ->map(fn($category) => $buildNode($category, true))
                ->values()
                ->all(),
            'bookingSettingsSaveUrl' => route('panel.setting.booking_settings.save'),
            'bookingSettingsUserId' => $userId,
        ];
    }

    private function makeAvailabilitySettingsViewData(int $userId): array
    {
        return [
            'orgId' => $userId,
            'rule' => OrgAvailabilityRule::firstOrNew(
                ['org_id' => $userId],
                [
                    'availability_mode' => 'available_by_default',
                    'product_specific_takes_precedence' => false,
                    'make_all_unavailable_by_default' => false,
                ]
            ),
            'ranges' => OrgAvailabilityRange::where('org_id', $userId)->orderBy('id')->get(),
            'assets' => collect(),
            'assetRanges' => collect(),
            'availabilitySaveUrl' => route('panel.setting.availability.save'),
            'availabilityDeleteRowUrl' => url('/panel/setting/availability/row/delete'),
        ];
    }

    private function getUnitPreferencesData(): array
    {
        $unitService = app(UnitConversionService::class);
        $unitPreferences = [];

        foreach ($unitService->getUnitTypes() as $type) {
            $unitPreferences[$type] = $unitService->getAvailableUnits($type);
        }

        return $unitPreferences;
    }


public function update(Request $request)
{
    $data = $request->all();

    $organization = null;
    if (!empty($data['organization_id']) and !empty($data['user_id'])) {
        $organization = auth()->user();
        $user = User::where('id', $data['user_id'])
            ->where('organ_id', $organization->id)
            ->first();
    } else {
        $user = auth()->user();
    }

    $step = $data['step'] ?? "basic_information";

    $rules = [];

    if ($step == "basic_information") {
        $registerMethod = getGeneralSettings('register_method') ?? 'mobile';
        $unitService = app(UnitConversionService::class);

        $rules = [
            'full_name'                      => 'required|string',
            'email'                          => (($registerMethod == 'email') ? 'required' : 'nullable') . '|email|max:255|unique:users,email,' . $user->id,
            'mobile'                         => (($registerMethod == 'mobile') ? 'required' : 'nullable') . '|numeric|unique:users,mobile,' . $user->id,
            'currency'                       => 'nullable|string|max:3',
            'preferred_date_format'          => 'nullable|string|max:30',
            'preferred_custom_date_format'   => 'nullable|string|max:30',
            'preferred_time_format'          => 'nullable|string|max:30',
            'preferred_custom_time_format'   => 'nullable|string|max:30',
            'preferred_week_start'           => 'nullable|string|max:10',
            'booking_default_currency'       => 'nullable|string|max:3',
            'booking_default_price_unit'     => 'nullable|string|max:64',
            'booking_auto_publish'           => 'nullable|in:on,1,true',
            'booking_location_enabled'       => 'nullable|in:on,1,true',
            'address'                        => 'nullable|string|max:255',
            'city'                           => 'nullable|string|max:100',
            'state'                          => 'nullable|string|max:100',
            'country'                        => 'nullable|string|max:100',
            'postal_code'                    => 'nullable|string|max:20',
            'lat'                            => 'nullable|numeric',
            'lng'                            => 'nullable|numeric',
        ];

        foreach ($unitService->getUnitTypes() as $type) {
            $rules["preferred_{$type}_unit"] = 'nullable|in:' . implode(',', array_keys(config("units.conversions.{$type}", [])));
        }
    }

    $this->validate($request, $rules);

    if (!empty($user)) {

        // ── Password update ──────────────────────────────────────────
        if (!empty($data['password'])) {
            $this->validate($request, [
                'password' => 'required|confirmed|min:6',
            ]);

            $user->update([
                'password' => User::generatePassword($data['password'])
            ]);
        }

        $updateData    = [];
        $updateUserMeta = [];

        // ── STEP: basic_information ──────────────────────────────────
        if ($step == "basic_information") {

            $joinNewsletter = (!empty($data['join_newsletter']) and $data['join_newsletter'] == 'on');
            $unitService    = app(UnitConversionService::class);

            $dateFormat = (($data['preferred_date_format'] ?? null) === 'custom')
                ? ($data['preferred_custom_date_format'] ?? null)
                : ($data['preferred_date_format'] ?? null);

            $timeFormat = (($data['preferred_time_format'] ?? null) === 'custom')
                ? ($data['preferred_custom_time_format'] ?? null)
                : ($data['preferred_time_format'] ?? null);

            $updateData = [
                'full_name'                 => $data['full_name'],
                'email'                     => $data['email'],
                'mobile'                    => $data['mobile'],
                'language'                  => $data['language'] ?? null,
                'timezone'                  => $data['timezone'] ?? null,
                'currency'                  => !empty($data['currency']) ? strtoupper($data['currency']) : null,
                'preferred_currency'        => !empty($data['currency']) ? strtoupper($data['currency']) : config('exchange.base_currency', 'USD'),
                'preferred_date_format'     => $dateFormat ?: 'F j, Y',
                'preferred_time_format'     => $timeFormat ?: 'g:i a',
                'preferred_week_start'      => $data['preferred_week_start'] ?? 'Monday',
                'offline'                   => (!empty($data['offline']) and $data['offline'] == "on"),
                'offline_message'           => (!empty($data['offline_message'])) ? $data['offline_message'] : null,
                'newsletter'                => $joinNewsletter,
                'public_message'            => (!empty($data['public_message']) and $data['public_message'] == 'on'),
                'enable_profile_statistics' => (!empty($data['enable_profile_statistics']) and $data['enable_profile_statistics'] == 'on'),
                'auto_renew_subscription'   => (!empty($data['auto_renew_subscription']) and $data['auto_renew_subscription'] == 'on'),

                // ── FIX 1: location fields — clean fallback to existing DB value ──
                // Agar form se koi value aayi hai toh woh use karo,
                // warna purani DB value raho — NULL mat karo
                'address'     => (isset($data['address'])     && $data['address']     !== '') ? $data['address']     : $user->address,
                'city'        => (isset($data['city'])        && $data['city']        !== '') ? $data['city']        : $user->city,
                'state'       => (isset($data['state'])       && $data['state']       !== '') ? $data['state']       : $user->state,
                'country'     => (isset($data['country'])     && $data['country']     !== '') ? $data['country']     : $user->country,
                // ── FIX 2: postal_code SIRF EK BAAR — duplicate key hata diya ──
                'postal_code' => (isset($data['postal_code']) && $data['postal_code'] !== '') ? $data['postal_code'] : $user->postal_code,
                // ── lat/lng: agar form se aayi toh save, warna DB wali rakho ──
                'lat'         => (isset($data['lat'])         && $data['lat']         !== '') ? $data['lat']         : $user->lat,
                'lng'         => (isset($data['lng'])         && $data['lng']         !== '') ? $data['lng']         : $user->lng,
            ];

            foreach ($unitService->getUnitTypes() as $type) {
                $key = "preferred_{$type}_unit";
                $updateData[$key] = $data[$key] ?? config("units.base_units.{$type}");
            }

            $updateUserMeta = [
                'booking_default_currency'   => !empty($data['booking_default_currency']) ? strtoupper($data['booking_default_currency']) : null,
                'booking_default_price_unit' => $data['booking_default_price_unit'] ?? null,
                'booking_auto_publish'       => (!empty($data['booking_auto_publish']) and in_array($data['booking_auto_publish'], ['on', '1', 'true'], true)) ? '1' : '0',
                'booking_location_enabled'   => (!empty($data['booking_location_enabled']) and in_array($data['booking_location_enabled'], ['on', '1', 'true'], true)) ? '1' : '0',
            ];

            $this->handleNewsletter($data['email'], $user->id, $joinNewsletter);

        // ── STEP: extra_information ──────────────────────────────────
        } elseif ($step == "extra_information") {

            $updateData = [
                "meeting_type"     => $data['meeting_type'] ?? null,
                "level_of_training"=> !empty($data['level_of_training']) ? (new UserLevelOfTraining())->getValue($data['level_of_training']) : null,
                "country_id"       => $data['country_id'] ?? null,
                "province_id"      => $data['province_id'] ?? null,
                "city_id"          => $data['city_id'] ?? null,
                "district_id"      => $data['district_id'] ?? null,
                "location"         => (!empty($data['latitude']) and !empty($data['longitude']))
                                        ? DB::raw("POINT(" . $data['latitude'] . "," . $data['longitude'] . ")")
                                        : null,
                "address"          => $data['address'] ?? null,
            ];

            $updateUserMeta = [
                "birthday" => !empty($data['birthday']) ? convertTimeToUTCzone($data['birthday'])->getTimestamp() : null,
                "gender"   => $data['gender'] ?? null,
            ];

            $updateUserMeta['socials'] = (!empty($data['socials']) and is_array($data['socials']))
                ? json_encode($data['socials'])
                : null;

            $this->handleUserExtraForm($request, $user);

        // ── STEP: financial ──────────────────────────────────────────
        } elseif ($step == "financial") {

            if (!empty($data['bank_id'])) {
                $this->handleUserBankAccount($user, $data);
            }

            $updateData = [
                'identity_scan' => $this->handleUploadImagesAndFiles($request, $user, "identity_scan"),
                'certificate'   => $this->handleUploadImagesAndFiles($request, $user, "certificate"),
            ];

        // ── STEP: images ─────────────────────────────────────────────
        } elseif ($step == "images") {

            $updateData = [
                'avatar'                  => $this->handleUploadImagesAndFiles($request, $user, "avatar"),
                'profile_video'           => $this->handleUploadImagesAndFiles($request, $user, "profile_video"),
                'cover_img'               => $this->handleUploadImagesAndFiles($request, $user, "cover_img"),
                'profile_secondary_image' => $this->handleUploadImagesAndFiles($request, $user, "profile_secondary_image"),
            ];

            if (!empty($request->file("signature_img"))) {
                $signatureImgPath = $this->handleUploadImagesAndFiles($request, $user, "signature_img");
                $updateUserMeta = [
                    'signature' => $signatureImgPath
                ];
            }

        // ── STEP: about ──────────────────────────────────────────────
        } elseif ($step == "about") {

            $updateData = [
                'about' => $data['about'] ?? null,
                'bio'   => $data['bio'] ?? null,
            ];

            if (!$user->isUser()) {
                UserOccupation::where('user_id', $user->id)->delete();

                if (!empty($data['occupations'])) {
                    foreach ($data['occupations'] as $category_id) {
                        UserOccupation::create([
                            'user_id'     => $user->id,
                            'category_id' => $category_id
                        ]);
                    }
                }
            }

        // ── STEP: zoom ───────────────────────────────────────────────
        } elseif ($step == "zoom") {

            if (!empty($data['zoom_api_key']) and !empty($data['zoom_api_secret'])) {
                UserZoomApi::updateOrCreate(
                    ['user_id' => $user->id],
                    [
                        'api_key'    => $data['zoom_api_key'] ?? null,
                        'api_secret' => $data['zoom_api_secret'] ?? null,
                        'account_id' => $data['zoom_account_id'] ?? null,
                        'created_at' => time()
                    ]
                );
            } else {
                UserZoomApi::where('user_id', $user->id)->delete();
            }

        // ── STEP: checkout_options ───────────────────────────────────
        } elseif ($step == "checkout_options") {

            if (!($user->isOrganization() or $user->isTeacher())) {
                abort(404);
            }

            $this->validate($request, [
                'modules'   => 'required|array',
                'modules.*' => 'nullable|boolean',
                'required_modules' => 'nullable|array',
                'required_modules.*' => 'nullable|boolean',
            ]);

            app(CheckoutModuleService::class)->saveOrgModuleSettings(
                $user->id,
                $data['modules'] ?? [],
                $data['required_modules'] ?? []
            );
        } elseif ($step == "regulatory") {
            $this->saveRegulatorySettings($request, $user);
        }

        // ── DB update ────────────────────────────────────────────────
        if (!empty($updateData)) {
            $user->update($updateData);
            Log::info('After User Update', [
                 'address' => $user->fresh()->address,
            ]);
        }
        

        // ── FIX 3: LocationService — har baar basic_information par call karo ──
        // Pehle sirf lat/lng change hone par call hoti thi — ab har save par hogi
        // Taki address, city, state, country bhi LocationService mein save ho sakein
        if ($step == "basic_information") {
            app(LocationService::class)->saveLocation($user, $data);
        }

        // ── UserMeta save ────────────────────────────────────────────
        if (!empty($updateUserMeta)) {
            foreach ($updateUserMeta as $metaName => $metaValue) {
                UserMeta::query()
                    ->where('user_id', $user->id)
                    ->where('name', $metaName)
                    ->delete();

                if (!empty($metaValue)) {
                    UserMeta::query()->create([
                        'user_id' => $user->id,
                        'name'    => $metaName,
                        'value'   => $metaValue
                    ]);
                }
            }
        }

        // ── Redirect ─────────────────────────────────────────────────
        $url = "/panel/setting/step/{$step}";
        if (!empty($organization)) {
            $userType = $user->isTeacher() ? 'instructors' : 'students';
            $url = "/panel/manage/{$userType}/{$user->id}/edit";
        }

        $toastData = [
            'title'  => trans('public.request_success'),
            'msg'    => trans('panel.user_setting_success'),
            'status' => 'success'
        ];

        return redirect($url)->with(['toast' => $toastData]);
    }

    abort(404);
}

    private function handleUserBankAccount($user, $data)
    {
        UserSelectedBank::query()->where('user_id', $user->id)->delete();

        $userSelectedBank = UserSelectedBank::query()->create([
            'user_id' => $user->id,
            'user_bank_id' => $data['bank_id']
        ]);

        if (!empty($data['bank_specifications'])) {
            $specificationInsert = [];

            foreach ($data['bank_specifications'] as $specificationId => $specificationValue) {
                if (!empty($specificationValue)) {
                    $specificationInsert[] = [
                        'user_selected_bank_id' => $userSelectedBank->id,
                        'user_bank_specification_id' => $specificationId,
                        'value' => $specificationValue
                    ];
                }
            }

            UserSelectedBankSpecification::query()->insert($specificationInsert);
        }
    }
private function saveRegulatorySettings(Request $request, User $user): void
{
    $forms = $request->input('regulatory_forms', []);

    if (empty($forms) or !is_array($forms)) {
        return;
    }

    foreach ($forms as $formInput) {
        $formId = !empty($formInput['form_id']) ? (int) $formInput['form_id'] : null;

        if (empty($formId)) {
            continue;
        }

        $form = \App\Models\Form::where('id', $formId)
            ->where('connect_regulatory', true)
            ->first();

        if (empty($form)) {
            continue;
        }

        // Server-side check: is Form ke role par user ka ACTIVE role hona chahiye
        $hasActiveRole = \App\Models\UserRoleRequest::where('user_id', $user->id)
            ->where('role_catalog_id', $form->regulatory_role_catalog_id)
            ->where('status', \App\Models\UserRoleRequest::STATUS_ACTIVE)
            ->exists();

        if (!$hasActiveRole) {
            continue; // koi galat form_id bhej de tab bhi backend se ignore ho jayega
        }

        $submissionId = !empty($formInput['submission_id']) ? (int) $formInput['submission_id'] : null;
        $fields = $formInput['fields'] ?? [];

        if (!is_array($fields)) {
            $fields = [];
        }

        $submission = null;

        if (!empty($submissionId)) {
            $submission = RegulatoryFormSubmission::where('id', $submissionId)
                ->where('user_id', $user->id)
                ->first();
        }

        if (empty($submission)) {
            $submission = RegulatoryFormSubmission::where('user_id', $user->id)
                ->where('form_id', $form->id)
                ->first();
        }

        if (empty($submission)) {
            $submission = new RegulatoryFormSubmission();
            $submission->user_id = $user->id;
        }

        $submission->role_catalog_id = $form->regulatory_role_catalog_id;
        $submission->form_id         = $form->id;
        $submission->level           = $form->regulatory_level;
        $submission->data = array_filter($fields, fn($value) => $value !== null and $value !== '');
        $submission->status = 'draft';
        $submission->save();
    }
}

    private function handleUploadImagesAndFiles(Request $request, $user, $name)
    {
        $path = $user->{$name};

        if (!empty($request->file($name))) {
            if (!empty($path)) {
                $this->removeFile($path);
            }

            $path = $this->uploadFile($request->file($name), "setting", $name, $user->id);
        }

        return $path;
    }

    private function handleUserExtraForm(Request $request, $user)
    {
        $userType = "organization";
        if ($user->isTeacher()) {
            $userType = "teacher";
        } elseif ($user->isUser()) {
            $userType = "user";
        }

        $form = $this->getFormFieldsByType($userType);

        if (!empty($form)) {
            $errors = $this->checkFormRequiredFields($request, $form);

            if (count($errors)) {
                return redirect()->back()->withErrors($errors);
            }

            $this->storeFormFields($request->all(), $user);
        }

        return "ok";
    }

    private function handleNewsletter($email, $user_id, $joinNewsletter)
    {
        $check = Newsletter::where('email', $email)->first();

        if ($joinNewsletter) {
            if (empty($check)) {
                Newsletter::create([
                    'user_id' => $user_id,
                    'email' => $email,
                    'created_at' => time()
                ]);
            } else {
                $check->update([
                    'user_id' => $user_id,
                ]);
            }

            $newsletterReward = RewardAccounting::calculateScore(Reward::NEWSLETTERS);
            RewardAccounting::makeRewardAccounting($user_id, $newsletterReward, Reward::NEWSLETTERS, $user_id, true);
        } elseif (!empty($check)) {
            $reward = RewardAccounting::where('user_id', $user_id)
                ->where('item_id', $user_id)
                ->where('type', Reward::NEWSLETTERS)
                ->where('status', RewardAccounting::ADDICTION)
                ->first();

            if (!empty($reward)) {
                $reward->delete();
            }

            $check->delete();
        }
    }

    public function storeMetas(Request $request)
    {
        $data = $request->all();

        if (!empty($data['name']) and !empty($data['value'])) {

            if (!empty($data['user_id'])) {
                $organization = auth()->user();
                $user = User::where('id', $data['user_id'])
                    ->where('organ_id', $organization->id)
                    ->first();
            } else {
                $user = auth()->user();
            }

            UserMeta::create([
                'user_id' => $user->id,
                'name' => $data['name'],
                'value' => $data['value'],
            ]);

            return response()->json([
                'code' => 200
            ], 200);
        }

        return response()->json([], 422);
    }

    public function updateMeta(Request $request, $meta_id)
    {
        $data = $request->all();
        $user = auth()->user();

        if (!empty($data['user_id'])) {
            $checkUser = User::find($data['user_id']);

            if ((!empty($checkUser) and ($data['user_id'] == $user->id) or $checkUser->organ_id == $user->id)) {
                $meta = UserMeta::where('id', $meta_id)
                    ->where('user_id', $data['user_id'])
                    ->where('name', $data['name'])
                    ->first();

                if (!empty($meta)) {
                    $meta->update([
                        'value' => $data['value']
                    ]);

                    return response()->json([
                        'code' => 200
                    ], 200);
                }

                return response()->json([
                    'code' => 403
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function deleteMeta(Request $request, $meta_id)
    {
        $data = $request->all();
        $user = auth()->user();

        if (!empty($data['user_id'])) {
            $checkUser = User::find($data['user_id']);

            if (!empty($checkUser) and ($data['user_id'] == $user->id or $checkUser->organ_id == $user->id)) {
                $meta = UserMeta::where('id', $meta_id)
                    ->where('user_id', $data['user_id'])
                    ->first();

                $meta->delete();

                return response()->json([
                    'code' => 200
                ], 200);
            }
        }

        return response()->json([], 422);
    }

    public function offlineToggle(Request $request)
    {
        $user = auth()->user();

        $message = $request->get('message');
        $toggle = $request->get('toggle');
        $toggle = (!empty($toggle) and $toggle == 'true');

        $user->offline = $toggle;
        $user->offline_message = $message;

        $user->save();

        return response()->json([
            'code' => 200
        ], 200);
    }

    public function deleteAccount(Request $request)
    {
        $user = auth()->user();

        if (!empty($user)) {
            DeleteAccountRequest::updateOrCreate([
                'user_id' => $user->id,
            ], [
                'created_at' => time()
            ]);

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'text' => trans('update.delete_account_request_stored_msg'),
                'dont_reload' => true
            ]);
        }

        abort(403);
    }

    public function getUserInfo($id)
    {
        $user = User::query()->select('id', 'username', 'full_name', 'role_id', 'role_name', 'avatar', 'avatar_settings')
            ->where('id', $id)
            ->first();

        if (!empty($user)) {
            $user->avatar = $user->getAvatar(40);
            $user->profile_url = $user->getProfileUrl();

            return response()->json([
                'user' => $user
            ]);
        }

        return response()->json([], 422);
    }

    public function deleteUserMedia($type)
    {
        $user = auth()->user();
        $items = ['avatar', 'cover_img', 'profile_secondary_image', 'profile_video', 'signature_img'];

        if (in_array($type, $items)) {
            if ($type == 'signature_img') {
                $user->userMetas()->where('name', 'signature')->delete();
            } else {
                $user->update([
                    "{$type}" => null,
                ]);
            }

            return response()->json([
                'code' => 200,
                'title' => trans('public.request_success'),
                'msg' => trans("update.delete_account_{$type}_msg"),
            ]);
        }

        return response()->json([], 422);
    }

    public function saveBookingSettings(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!($user->isOrganization() or $user->isTeacher())) {
            abort(404);
        }

        $validated = $request->validate([
            'categories' => ['required', 'array'],
            'categories.*.id' => ['required', 'integer', 'exists:booking_categories,id'],
            'categories.*.enabled' => ['nullable'],
        ]);

        $allCategories = BookingCategory::query()
            ->select(['id', 'parent_id'])
            ->orderBy('order')
            ->orderBy('id')
            ->get()
            ->keyBy('id');

        $submitted = collect($validated['categories'])
            ->mapWithKeys(fn($row) => [(int) $row['id'] => (bool) ($row['enabled'] ?? false)])
            ->all();

        $resolved = [];
        foreach ($allCategories as $catId => $category) {
            $resolved[$catId] = (bool) ($submitted[$catId] ?? false);
        }

        foreach ($resolved as $catId => $enabled) {
            if (!$enabled) {
                continue;
            }

            $parentId = $allCategories[$catId]->parent_id ?? null;

            while (!empty($parentId)) {
                $resolved[(int) $parentId] = true;
                $parentId = $allCategories[$parentId]->parent_id ?? null;
            }
        }

        DB::transaction(function () use ($resolved) {
            foreach ($resolved as $catId => $enabled) {
                BookingCategory::where('id', $catId)->update([
                    'status' => (int) $enabled,
                ]);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Booking categories saved successfully.',
        ]);
    }

    public function saveAvailabilitySettings(Request $request): JsonResponse
    {
        $user = auth()->user();

        if (!($user->isOrganization() or $user->isTeacher())) {
            abort(404);
        }

        $validated = $request->validate([
            'availability_mode' => ['required', Rule::in(['available_by_default', 'unavailable_by_default'])],
            'make_all_unavailable_by_default' => ['nullable', 'boolean'],
            'product_specific_takes_precedence' => ['nullable', 'boolean'],
            'ranges' => ['nullable', 'array'],
            'ranges.*.range_type' => ['required_with:ranges', Rule::in(['custom', 'daily', 'weekly', 'monthly', 'date_range'])],
            'ranges.*.from_date' => ['required_with:ranges', 'date'],
            'ranges.*.to_date' => ['required_with:ranges', 'date'],
            'ranges.*.bookable' => ['nullable', 'boolean'],
        ]);

        DB::transaction(function () use ($user, $validated) {
            OrgAvailabilityRule::updateOrCreate(
                ['org_id' => $user->id],
                [
                    'availability_mode' => $validated['availability_mode'],
                    'make_all_unavailable_by_default' => (bool) ($validated['make_all_unavailable_by_default'] ?? false),
                    'product_specific_takes_precedence' => (bool) ($validated['product_specific_takes_precedence'] ?? false),
                ]
            );

            OrgAvailabilityRange::where('org_id', $user->id)->delete();

            if (!empty($validated['ranges'])) {
                $rows = collect($validated['ranges'])
                    ->filter(fn($range) => !empty($range['from_date']) and !empty($range['to_date']))
                    ->map(fn($range) => [
                        'org_id' => $user->id,
                        'range_type' => $range['range_type'],
                        'from_date' => $range['from_date'],
                        'to_date' => $range['to_date'],
                        'bookable' => (bool) ($range['bookable'] ?? true),
                        'created_at' => now(),
                        'updated_at' => now(),
                    ])
                    ->values()
                    ->all();

                if (!empty($rows)) {
                    OrgAvailabilityRange::insert($rows);
                }
            }
        });

        return response()->json([
            'success' => true,
            'message' => trans('booking.availability_saved'),
        ]);
    }

    public function deleteAvailabilityRow(int $rowId): JsonResponse
    {
        $user = auth()->user();

        if (!($user->isOrganization() or $user->isTeacher())) {
            abort(404);
        }

        $deleted = OrgAvailabilityRange::where('id', $rowId)
            ->where('org_id', $user->id)
            ->delete();

        if (!$deleted) {
            return response()->json(['success' => false, 'message' => 'Row not found.'], 404);
        }

        return response()->json(['success' => true]);
    }

}

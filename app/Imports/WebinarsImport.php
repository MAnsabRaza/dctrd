<?php
namespace App\Imports;

use App\Models\Webinar;
use App\Models\Translation\WebinarTranslation;
use App\Models\Tag;
use App\Models\WebinarPartnerTeacher;
use App\Models\WebinarFilterOption;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Validator;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class WebinarsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        // Log the row for debugging
        Log::info('Processing row:', $row);

        // Get authenticated user and check authorization
        $user = auth()->user();
        if (!$user)
        {
            Log::error('Skipping row: No authenticated user found', $row);
            throw new \Exception('You must be logged in to import webinars.');
        }

        if (!$user->can('admin_webinars_create'))
        {
            Log::error('Skipping row: User lacks permission to create webinars', $row);
            throw new \Exception('You do not have permission to import webinars.');
        }

        // Check required fields
        $requiredFields = [
            'type', 'locale', 'title', 'thumbnail', 'image_cover',
            'description', 'teacher_id', 'category_id', 'duration'
        ];
        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $row) || (empty($row[$field]) && $row[$field] !== '0')) {
                Log::warning("Skipping row due to missing or empty field: {$field}", $row);
                return null; // Skip this row silently for the user
            }
        }

        $validator = Validator::make($row, [
            'type' => 'required|in:webinar,course,text_lesson',
            'locale' => 'required|string',
            'title' => 'required|string',
            'slug' => 'nullable|max:255|unique:webinars,slug',
            'thumbnail' => 'required|string',
            'image_cover' => 'required|string',
            'description' => 'required|string',
            'teacher_id' => 'required|exists:users,id',
            'category_id' => 'required|exists:categories,id',
            'duration' => 'required|numeric',
            'start_date' => 'required_if:type,webinar|nullable',
            'capacity' => 'nullable|numeric|min:0',
            'price' => 'nullable|numeric|min:0',
            'filters' => 'nullable|string',
            'partners' => 'nullable|string',
            'seo_description' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            $errors = implode(', ', $validator->errors()->all());
            Log::error("Validation failed for row: {$errors}", $row);
            return null; // Skip this row silently, errors are logged for debugging
        }

        $data = $row;

        // Parse locale and translatable fields
        $locales = array_map('trim', explode(',', $data['locale']));
        $titles = array_map('trim', explode('|', $data['title']));
        $descriptions = array_map('trim', explode('|', $data['description']));
        $seo_descriptions = !empty($data['seo_description']) ? array_map('trim', explode('|', $data['seo_description'])) : array_fill(0, count($locales), null);

        if (count($locales) !== count($titles) || count($locales) !== count($descriptions) || count($locales) !== count($seo_descriptions)) {
            Log::error("Skipping row: Locale, title, description, and seo_description must have matching lengths", $row);
            return null;
        }

        foreach ($locales as $locale) {
            if (!in_array($locale, ['en', 'ar'])) {
                Log::error("Skipping row: Unsupported locale: {$locale}", $row);
                return null;
            }
        }
        foreach ($titles as $title) {
            if (strlen($title) > 255) {
                Log::error("Skipping row: Title exceeds 255 characters: {$title}", $row);
                return null;
            }
        }

        if (!empty($data['capacity']) && !empty($data['sales_count_number']) && $data['sales_count_number'] > $data['capacity']) {
            Log::error("Skipping row: Sales count exceeds capacity", $row);
            return null;
        }

        if ($data['type'] !== 'webinar') {
            $data['start_date'] = null;
        } elseif (!empty($data['start_date'])) {
            $timezone = !empty($data['timezone']) ? $data['timezone'] : getTimezone();
            try {
                if (is_numeric($data['start_date'])) {
                    $startDate = Carbon::createFromTimestampUTC(($data['start_date'] - 25569) * 86400)
                        ->setTimezone($timezone);
                } else {
                    $startDate = Carbon::createFromFormat('m/d/Y h:i:s A', $data['start_date'], $timezone)
                        ->setTimezone('UTC');
                    if (!$startDate) {
                        $startDate = Carbon::parse($data['start_date'], $timezone)->setTimezone('UTC');
                    }
                }
                $data['start_date'] = $startDate->getTimestamp();
            } catch (\Exception $e) {
                Log::error("Skipping row: Invalid start_date format: {$data['start_date']}", $row);
                return null;
            }
        }

        if (empty($data['slug'])) {
            $data['slug'] = Webinar::makeSlug($titles[array_search('en', $locales)] ?? $titles[0]);
        }

        $data['price'] = !empty($data['price']) ? convertPriceToDefaultCurrency($data['price']) : null;
        $data['organization_price'] = !empty($data['organization_price']) ? convertPriceToDefaultCurrency($data['organization_price']) : null;

        $webinar = Webinar::create([
            'type' => $data['type'],
            'slug' => $data['slug'],
            'teacher_id' => $data['teacher_id'],
            'creator_id' => $user->id, // Fixed: Use $user->id instead of auth()->id
            'thumbnail' => $data['thumbnail'],
            'image_cover' => $data['image_cover'],
            'video_demo' => $data['video_demo'] ?? null,
            'video_demo_source' => !empty($data['video_demo']) ? ($data['video_demo_source'] ?? null) : null,
            'sales_count_number' => $data['sales_count_number'] ?? null,
            'capacity' => $data['capacity'] ?? null,
            'start_date' => $data['start_date'],
            'timezone' => $data['timezone'] ?? null,
            'duration' => $data['duration'],
            'support' => !empty($data['support']) ? (bool)$data['support'] : false,
            'certificate' => !empty($data['certificate']) ? (bool)$data['certificate'] : false,
            'downloadable' => !empty($data['downloadable']) ? (bool)$data['downloadable'] : false,
            'partner_instructor' => !empty($data['partner_instructor']) ? (bool)$data['partner_instructor'] : false,
            'subscribe' => !empty($data['subscribe']) ? (bool)$data['subscribe'] : false,
            'private' => !empty($data['private']) ? (bool)$data['private'] : false,
            'forum' => !empty($data['forum']) ? (bool)$data['forum'] : false,
            'enable_waitlist' => !empty($data['enable_waitlist']) ? (bool)$data['enable_waitlist'] : false,
            'access_days' => $data['access_days'] ?? null,
            'price' => $data['price'],
            'organization_price' => $data['organization_price'] ?? null,
            'points' => $data['points'] ?? null,
            'category_id' => $data['category_id'],
            'message_for_reviewer' => $data['message_for_reviewer'] ?? null,
            'status' => Webinar::$pending,
            'created_at' => time(),
            'updated_at' => time(),
        ]);

        if ($webinar) {
            foreach ($locales as $index => $locale) {
                WebinarTranslation::updateOrCreate(
                    [
                        'webinar_id' => $webinar->id,
                        'locale' => mb_strtolower($locale),
                    ],
                    [
                        'title' => $titles[$index],
                        'description' => $descriptions[$index],
                        'seo_description' => $seo_descriptions[$index],
                    ]
                );
            }

            if (!empty($data['filters'])) {
                $filtersString = (string)$data['filters'];
                $filterIds = json_decode($filtersString, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($filterIds)) {
                    $filterIds = array_map('trim', explode(',', $filtersString));
                }

                foreach ($filterIds as $filterId) {
                    if (!is_numeric($filterId) || $filterId > 2147483647 || $filterId < 0) {
                        Log::error("Skipping row: Invalid filter ID: {$filterId}", $row);
                        return null;
                    }
                }

                WebinarFilterOption::where('webinar_id', $webinar->id)->delete();
                foreach ($filterIds as $filterId) {
                    WebinarFilterOption::create([
                        'webinar_id' => $webinar->id,
                        'filter_option_id' => (int)$filterId,
                    ]);
                }
            }

            if (!empty($data['tags'])) {
                $tags = explode(',', $data['tags']);
                Tag::where('webinar_id', $webinar->id)->delete();
                foreach ($tags as $tag) {
                    Tag::create([
                        'webinar_id' => $webinar->id,
                        'title' => trim($tag),
                    ]);
                }
            }

            if (!empty($data['partner_instructor']) && !empty($data['partners'])) {
                $partnersString = (string)$data['partners'];
                $partners = json_decode($partnersString, true);
                if (json_last_error() !== JSON_ERROR_NONE || !is_array($partners)) {
                    $partners = array_map('trim', explode(',', $partnersString));
                }

                foreach ($partners as $partnerId) {
                    if (!is_numeric($partnerId) || $partnerId > 2147483647 || $partnerId < 0) {
                        Log::error("Skipping row: Invalid partner ID: {$partnerId}", $row);
                        return null;
                    }
                }

                WebinarPartnerTeacher::where('webinar_id', $webinar->id)->delete();
                foreach ($partners as $partnerId) {
                    WebinarPartnerTeacher::create([
                        'webinar_id' => $webinar->id,
                        'teacher_id' => (int)$partnerId,
                    ]);
                }
            }
        }

        return $webinar;
    }
}

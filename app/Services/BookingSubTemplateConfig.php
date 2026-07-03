<?php

namespace App\Services;

/**
 * BookingSubTemplateConfig
 *
 * Ye class "Booking Type" (7 parent types) ke ANDAR wale 23 specific
 * templates/subcategories ki config rakhti hai — jaise Doctors/Clinics
 * ke andar: Doctor Appointment, Clinic Visit, Medical Test/Diagnostic,
 * Therapy/Rehabilitation.
 *
 * MATCHING RULE: Har template ka "key" us category ke `slug` se match
 * hota hai. Matlab jab Admin category banaye "Doctor Appointment" naam
 * se, uska slug "doctor-appointment" hona chahiye — bas yehi convention
 * follow karni hai, koi naya DB column nahi chahiye.
 *
 * Is config se 3 cheezen milti hain (jaise aapke screenshots mein tha):
 *   - required fields  (ye field hamesha filled hone chahiye)
 *   - optional fields  (ye field dikhein lekin required na hon)
 *   - price unit       (per consultation, per night, per ticket, etc.)
 *   - checkout modules (sirf informational note ke liye)
 *
 * NOTE: Field keys wahi hain jo already form (blade) mein input names
 * ke tor par maujood hain — koi naya input field add nahi karna para,
 * sirf existing fields ko required/optional/hidden ke darmiyan switch
 * kiya jata hai.
 *
 * IMPORTANT: "Beauty / Spa" ke 4 templates (salon-appointment,
 * spa-treatment, group-fitness-class, beauty-package-membership) aapke
 * screenshots mein nahi thay (sirf Doctors/Clinics, Events,
 * Accommodation, Automotive, Professional Services, Education ki
 * tables share hui thin — total 19). 23 tak pohanchne ke liye maine
 * existing "beauty-spa" parent config se logically 4 templates khud
 * propose kiye hain. Agar aapke paas Beauty/Spa ki actual table hai to
 * bas neeche BEAUTY_SPA section ki values replace kar dein — baqi sab
 * kaam automatically waise hi chalega.
 */
class BookingSubTemplateConfig
{
    /**
     * Master list: category-slug => config array
     */
    private const TEMPLATES = [

        // ── Doctors / Clinics ───────────────────────────────────────
        'doctor-appointment' => [
            'label'             => 'Doctor Appointment',
            'parent_type'       => 'doctors-clinics',
            'required'          => ['staff_id', 'meta.appointment_type', 'duration_minutes', 'price'],
            'optional'          => ['description', 'meta.payment_option', 'meta.online_link'],
            'price_unit'        => 'per consultation',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],
        'clinic-visit' => [
            'label'             => 'Clinic Visit',
            'parent_type'       => 'doctors-clinics',
            'required'          => ['category_id', 'staff_id', 'duration_minutes'],
            'optional'          => ['meta.room_type', 'capacity', 'meta.prerequisites', 'location_enabled'],
            'price_unit'        => 'per appointment',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Persons/Guests', 'Cancellation Policy'],
        ],
        'medical-test-diagnostic' => [
            'label'             => 'Medical Test / Diagnostic',
            'parent_type'       => 'doctors-clinics',
            'required'          => ['location_enabled', 'duration_minutes', 'price'],
            'optional'          => ['requirements', 'meta.required_notes'],
            'price_unit'        => 'per test',
            'checkout_modules'  => ['Hours', 'Cancellation Policy', 'Message'],
        ],
        'therapy-rehabilitation' => [
            'label'             => 'Therapy / Rehabilitation',
            'parent_type'       => 'doctors-clinics',
            'required'          => ['staff_id', 'duration_minutes', 'price'],
            'optional'          => ['meta.prerequisites', 'requirements'],
            'price_unit'        => 'per session',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        // ── Events ───────────────────────────────────────────────────
        'event-tickets' => [
            'label'             => 'Event Tickets',
            'parent_type'       => 'events',
            'required'          => ['location_enabled', 'capacity', 'price'],
            'optional'          => ['sub_type', 'meta.specifications', 'inventory', 'requirements'],
            'price_unit'        => 'per ticket / per person',
            'checkout_modules'  => ['Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],
        'venue-booking' => [
            'label'             => 'Venue Booking',
            'parent_type'       => 'events',
            'required'          => ['location_enabled', 'capacity', 'price'],
            'optional'          => ['deposit_enabled', 'meta.specifications', 'requirements'],
            'price_unit'        => 'per hour / per day',
            'checkout_modules'  => ['Days or Hours', 'Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],
        'entertainment-activity' => [
            'label'             => 'Entertainment / Activity',
            'parent_type'       => 'events',
            'required'          => ['duration_minutes', 'capacity', 'staff_id'],
            'optional'          => ['meta.specifications', 'requirements'],
            'price_unit'        => 'per activity / per person',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Persons/Guests', 'Extra Services'],
        ],
        'tour-experience' => [
            'label'             => 'Tour / Experience',
            'parent_type'       => 'events',
            'required'          => ['location_enabled', 'meta.pickup_location', 'staff_id', 'capacity'],
            'optional'          => ['requirements', 'meta.specifications', 'meta.prerequisites'],
            'price_unit'        => 'per tour / per person',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Persons/Guests', 'Cancellation Policy'],
        ],

        // ── Accommodation / Hotel ───────────────────────────────────
        'hotel-room-booking' => [
            'label'             => 'Hotel Room Booking',
            'parent_type'       => 'accommodation',
            'required'          => ['meta.check_in_date', 'meta.check_out_date', 'meta.room_type', 'max_persons', 'price'],
            'optional'          => ['meta.amenities', 'requirements', 'max_children'],
            'price_unit'        => 'per night',
            'checkout_modules'  => ['Days', 'Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],
        'short-term-rental' => [
            'label'             => 'Short-term Rental',
            'parent_type'       => 'accommodation',
            'required'          => ['meta.room_type', 'meta.check_in_date', 'meta.check_out_date', 'max_persons', 'price'],
            'optional'          => ['deposit_enabled', 'requirements', 'location_enabled'],
            'price_unit'        => 'per night / per day',
            'checkout_modules'  => ['Days', 'Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],
        'bnb-guesthouse' => [
            'label'             => 'B&B / Guesthouse',
            'parent_type'       => 'accommodation',
            'required'          => ['meta.room_type', 'meta.check_in_date', 'meta.check_out_date', 'max_persons', 'price'],
            'optional'          => ['meta.amenities', 'requirements'],
            'price_unit'        => 'per night',
            'checkout_modules'  => ['Days', 'Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],

        // ── Automotive / Mechanics ──────────────────────────────────
        'mechanic-repair-appointment' => [
            'label'             => 'Mechanic / Repair Appointment',
            'parent_type'       => 'automotive',
            'required'          => ['meta.service_type', 'meta.vehicle_type', 'duration_minutes', 'staff_id'],
            'optional'          => ['meta.required_notes', 'meta.pickup_location', 'meta.dropoff_location'],
            'price_unit'        => 'per service / per appointment',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],
        'vehicle-rental' => [
            'label'             => 'Vehicle Rental',
            'parent_type'       => 'automotive',
            'required'          => ['meta.vehicle_type', 'meta.pickup_location', 'meta.dropoff_location', 'price'],
            'optional'          => ['deposit_enabled', 'meta.vehicle_specs'],
            'price_unit'        => 'per day / per hour',
            'checkout_modules'  => ['Days or Hours', 'Extra Services', 'Cancellation Policy'],
        ],
        'technical-support-inspection' => [
            'label'             => 'Technical Support / Inspection',
            'parent_type'       => 'automotive',
            'required'          => ['meta.vehicle_type', 'location_enabled', 'staff_id', 'duration_minutes'],
            'optional'          => ['meta.required_notes'],
            'price_unit'        => 'per inspection / per service',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        // ── Professional Services / Consulting ───────────────────────
        'consulting-session' => [
            'label'             => 'Consulting Session',
            'parent_type'       => 'professional-services',
            'required'          => ['staff_id', 'meta.service_type', 'price'],
            'optional'          => ['sub_type', 'meta.prerequisites', 'meta.online_link'],
            'price_unit'        => 'per consultation',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],
        'legal-appointment' => [
            'label'             => 'Legal Appointment',
            'parent_type'       => 'professional-services',
            'required'          => ['staff_id', 'meta.service_type', 'sub_type', 'duration_minutes'],
            'optional'          => ['meta.required_docs'],
            'price_unit'        => 'per consultation',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],
        'accounting-finance-insurance' => [
            'label'             => 'Accounting / Finance / Insurance',
            'parent_type'       => 'professional-services',
            'required'          => ['meta.service_type', 'staff_id', 'duration_minutes', 'price'],
            'optional'          => ['meta.required_docs'],
            'price_unit'        => 'per appointment',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        // ── Education / Training ────────────────────────────────────
        'tutoring-private-lesson' => [
            'label'             => 'Tutoring / Private Lesson',
            'parent_type'       => 'education-training',
            'required'          => ['category_id', 'staff_id', 'meta.level', 'duration_minutes', 'price'],
            'optional'          => ['sub_type', 'meta.prerequisites'],
            'price_unit'        => 'per lesson',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Persons/Guests', 'Cancellation Policy'],
        ],
        'training-class-workshop' => [
            'label'             => 'Training Class / Workshop',
            'parent_type'       => 'education-training',
            'required'          => ['staff_id', 'capacity', 'price'],
            'optional'          => ['meta.level', 'meta.prerequisites'],
            'price_unit'        => 'per class / per person',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Persons/Guests', 'Extra Services'],
        ],

        // ── Beauty / Spa ─────────────────────────────────────────────
        // NOTE: proposed by extrapolation — screenshot table wasn't shared for this group.
        'salon-appointment' => [
            'label'             => 'Salon Appointment (Haircut / Styling)',
            'parent_type'       => 'beauty-spa',
            'required'          => ['staff_id', 'duration_minutes', 'price'],
            'optional'          => ['description', 'requirements', 'extras'],
            'price_unit'        => 'per appointment',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Extra Services', 'Cancellation Policy'],
        ],
        'spa-treatment' => [
            'label'             => 'Spa / Massage Treatment',
            'parent_type'       => 'beauty-spa',
            'required'          => ['staff_id', 'duration_minutes', 'price'],
            'optional'          => ['extras', 'requirements', 'capacity'],
            'price_unit'        => 'per session',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Extra Services', 'Cancellation Policy'],
        ],
        'group-fitness-class' => [
            'label'             => 'Group Fitness / Wellness Class',
            'parent_type'       => 'beauty-spa',
            'required'          => ['staff_id', 'duration_minutes', 'capacity', 'price'],
            'optional'          => ['extras', 'requirements', 'meta.level'],
            'price_unit'        => 'per class / per person',
            'checkout_modules'  => ['Hours', 'Staff Member', 'Persons/Guests', 'Extra Services'],
        ],
        'beauty-package-membership' => [
            'label'             => 'Beauty Package / Membership',
            'parent_type'       => 'beauty-spa',
            'required'          => ['price', 'extras'],
            'optional'          => ['staff_id', 'duration_minutes', 'requirements'],
            'price_unit'        => 'per package',
            'checkout_modules'  => ['Extra Services', 'Cancellation Policy', 'Message'],
        ],
    ];

    /**
     * Base validation rule per field-key (regardless of required/optional).
     * "required" ya "nullable" is dynamically prepend hota hai — yahan sirf
     * type/format rule di gayi hai.
     */
    private const FIELD_RULE_MAP = [
        'category_id'            => 'integer',
        'staff_id'                => 'exists:users,id',
        'duration_minutes'        => 'integer|min:5|max:480',
        'price'                   => 'numeric|min:0',
        'capacity'                => 'integer|min:1',
        'inventory'               => 'integer|min:0',
        'min_persons'             => 'integer|min:1',
        'max_persons'             => 'integer|min:1',
        'max_children'            => 'integer|min:0',
        'sub_type'                => 'string|max:50',
        'location_enabled'        => 'boolean',
        'deposit_enabled'         => 'boolean',
        'description'             => 'string',
        'requirements'            => 'string',
        'extras'                  => 'array',
        'meta.appointment_type'   => 'string|max:100',
        'meta.payment_option'     => 'string|max:100',
        'meta.online_link'        => 'url',
        'meta.required_docs'      => 'string|max:1000',
        'meta.venue_type'         => 'string|max:100',
        'meta.organizer'          => 'string|max:255',
        'meta.specifications'     => 'string|max:1000',
        'meta.room_type'          => 'string|max:150',
        'meta.amenities'          => 'array',
        'meta.vehicle_type'       => 'string|max:150',
        'meta.pickup_location'    => 'string|max:255',
        'meta.dropoff_location'   => 'string|max:255',
        'meta.vehicle_specs'      => 'string|max:255',
        'meta.service_type'       => 'string|max:150',
        'meta.required_notes'     => 'string|max:1000',
        'meta.level'              => 'in:beginner,intermediate,advanced,all',
        'meta.prerequisites'      => 'string|max:500',
        'meta.check_in_date'      => 'date',
        'meta.check_out_date'     => 'date|after_or_equal:meta.check_in_date',
    ];

    private string $slug;
    private array $config;

    private function __construct(string $slug, array $config)
    {
        $this->slug   = $slug;
        $this->config = $config;
    }

    /**
     * Lookup by category slug. Returns null if slug doesn't map to a
     * known sub-template (e.g. admin-created custom category) — in that
     * case caller should just fall back to the parent Booking Type
     * behaviour (show everything, no extra filtering).
     */
    public static function forSlug(?string $slug): ?self
    {
        if (empty($slug) || !isset(self::TEMPLATES[$slug])) {
            return null;
        }
        return new self($slug, self::TEMPLATES[$slug]);
    }

    public static function exists(string $slug): bool
    {
        return isset(self::TEMPLATES[$slug]);
    }

    /** All 23 template slugs => label, grouped is not needed, flat list */
    public static function all(): array
    {
        return self::TEMPLATES;
    }

    /** Slugs belonging to one parent booking_type, e.g. 'doctors-clinics' */
    public static function slugsForParentType(string $parentType): array
    {
        return array_keys(array_filter(self::TEMPLATES, fn($cfg) => $cfg['parent_type'] === $parentType));
    }

    public function label(): string
    {
        return $this->config['label'];
    }

    public function parentType(): string
    {
        return $this->config['parent_type'];
    }

    public function required(): array
    {
        return $this->config['required'] ?? [];
    }

    public function optional(): array
    {
        return $this->config['optional'] ?? [];
    }

    /** required + optional = every field relevant to this template */
    public function relevantFields(): array
    {
        return array_values(array_unique(array_merge($this->required(), $this->optional())));
    }

    public function priceUnit(): string
    {
        return $this->config['price_unit'] ?? 'per booking';
    }

    public function checkoutModules(): array
    {
        return $this->config['checkout_modules'] ?? [];
    }

    /**
     * Build Laravel validation rules for this template: required fields
     * become "required|<rule>", optional fields become "nullable|<rule>".
     * Fields not in FIELD_RULE_MAP are skipped (already validated
     * elsewhere, e.g. title/global rules).
     */
    public function rules(): array
    {
        $rules = [];

        foreach ($this->required() as $field) {
            if (isset(self::FIELD_RULE_MAP[$field])) {
                $rules[$field] = 'required|' . self::FIELD_RULE_MAP[$field];
            }
        }

        foreach ($this->optional() as $field) {
            if (isset(self::FIELD_RULE_MAP[$field])) {
                $rules[$field] = 'nullable|' . self::FIELD_RULE_MAP[$field];
            }
        }

        return $rules;
    }

    /** JS-friendly array (used by controller to json_encode for the blade) */
    public function toArray(): array
    {
        return [
            'label'            => $this->label(),
            'parent_type'      => $this->parentType(),
            'required'         => $this->required(),
            'optional'         => $this->optional(),
            'price_unit'       => $this->priceUnit(),
            'checkout_modules' => $this->checkoutModules(),
        ];
    }
}
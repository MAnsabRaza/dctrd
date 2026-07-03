<?php

namespace App\Services;

/**
 * BookingTemplateConfig
 *
 * Single source of truth for the 7 Booking Types (parent categories) and the
 * 23 Templates (subcategories) defined in Milestone 3.5.2.
 *
 * Hierarchy:
 *   Booking Type (parent, e.g. "beauty-spa")
 *     └── Template (subcategory, e.g. "beauty-salon")  <-- this is what actually
 *         drives the form: required fields, optional fields, price unit,
 *         checkout modules.
 *
 * A `BookingCategory` root row stores `booking_type` (one of PARENT_TYPES keys).
 * A `BookingCategory` child row stores `template_key` (one of TEMPLATES keys)
 * and must belong to a parent whose booking_type matches TEMPLATES[$key]['parent'].
 */
class BookingTemplateConfig
{
    protected string $key;
    protected array $definition;

    public function __construct(string $key)
    {
        $this->key        = $key;
        $this->definition = self::TEMPLATES[$key] ?? self::fallbackDefinition();
    }

    // ────────────────────────────────────────────────────────────────
    // 7 Parent Booking Types
    // ────────────────────────────────────────────────────────────────

    public const PARENT_TYPES = [
        'beauty-spa'             => 'Beauty / Spa',
        'doctors-clinics'        => 'Doctors / Clinics',
        'events'                 => 'Events',
        'accommodation'          => 'Accommodation / Hotel',
        'automotive'             => 'Automotive / Mechanics',
        'professional-services'  => 'Professional Services / Consulting',
        'education-training'     => 'Education / Training',
    ];

    // ────────────────────────────────────────────────────────────────
    // 23 Templates (subcategories)
    // Each key = template_key stored on the BookingCategory child row.
    // ────────────────────────────────────────────────────────────────

    public const TEMPLATES = [

        // ── Beauty / Spa (4) ───────────────────────────────────────
        'beauty-salon' => [
            'parent'        => 'beauty-spa',
            'label'         => 'Beauty Salon',
            'required'      => ['service_type', 'staff_id', 'duration_minutes', 'price', 'time_slot'],
            'optional'      => ['extras', 'resource_room', 'buffer_before', 'buffer_after', 'gallery', 'location_enabled'],
            'checkout'      => ['time_slot', 'staff', 'extras', 'cancellation_policy', 'message'],
            'price_unit'    => 'per service',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => true,
            'pricing_mode'  => 'flat',
        ],
        'spa-massage' => [
            'parent'        => 'beauty-spa',
            'label'         => 'Spa / Massage',
            'required'      => ['treatment_type', 'staff_id', 'duration_minutes', 'time_slot', 'price'],
            'optional'      => ['resource_room', 'contraindications', 'extras', 'package_option', 'location_enabled'],
            'checkout'      => ['time_slot', 'staff', 'extras', 'cancellation_policy'],
            'price_unit'    => 'per treatment',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => true,
            'pricing_mode'  => 'flat',
        ],
        'wellness-therapy' => [
            'parent'        => 'beauty-spa',
            'label'         => 'Wellness / Therapy',
            'required'      => ['session_type', 'staff_id', 'duration_minutes', 'time_slot', 'price'],
            'optional'      => ['sub_type', 'prerequisites', 'recurring_sessions', 'notes'],
            'checkout'      => ['time_slot', 'staff', 'cancellation_policy', 'message'],
            'price_unit'    => 'per session',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],
        'fitness-trainer' => [
            'parent'        => 'beauty-spa',
            'label'         => 'Fitness Trainer / Class',
            'required'      => ['class_type', 'staff_id', 'duration_minutes', 'capacity', 'time_slot', 'price'],
            'optional'      => ['level', 'equipment', 'recurring_schedule', 'children_allowed', 'location_enabled'],
            'checkout'      => ['time_slot', 'staff', 'persons', 'cancellation_policy'],
            'price_unit'    => 'per person',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'per_person',
        ],

        // ── Doctors / Clinics (4) ──────────────────────────────────
        'doctor-appointment' => [
            'parent'        => 'doctors-clinics',
            'label'         => 'Doctor Appointment',
            'required'      => ['specialty', 'consultation_type', 'duration_minutes', 'time_slot', 'price'],
            'optional'      => ['patient_note', 'payment_option', 'online_link', 'required_docs'],
            'checkout'      => ['time_slot', 'staff', 'cancellation_policy', 'message'],
            'price_unit'    => 'per consultation',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],
        'clinic-visit' => [
            'parent'        => 'doctors-clinics',
            'label'         => 'Clinic Visit',
            'required'      => ['department', 'procedure', 'time_slot', 'duration_minutes', 'staff_id'],
            'optional'      => ['resource_room', 'capacity', 'prerequisites', 'location_enabled'],
            'checkout'      => ['time_slot', 'staff', 'persons', 'cancellation_policy'],
            'price_unit'    => 'per appointment',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],
        'medical-test' => [
            'parent'        => 'doctors-clinics',
            'label'         => 'Medical Test / Diagnostic',
            'required'      => ['test_type', 'lab_location', 'time_slot', 'duration_minutes', 'price'],
            'optional'      => ['preparation_instructions', 'fasting_required', 'result_delivery_method'],
            'checkout'      => ['time_slot', 'cancellation_policy', 'message'],
            'price_unit'    => 'per test',
            'has_staff'     => false,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],
        'therapy-rehabilitation' => [
            'parent'        => 'doctors-clinics',
            'label'         => 'Therapy / Rehabilitation',
            'required'      => ['therapy_type', 'staff_id', 'duration_minutes', 'time_slot', 'price'],
            'optional'      => ['package_series', 'recurring_sessions', 'prerequisites', 'progress_notes'],
            'checkout'      => ['time_slot', 'staff', 'cancellation_policy', 'message'],
            'price_unit'    => 'per session',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],

        // ── Events (4) ─────────────────────────────────────────────
        'event-tickets' => [
            'parent'        => 'events',
            'label'         => 'Event Tickets',
            'required'      => ['event_date', 'start_time', 'end_time', 'venue', 'capacity', 'price'],
            'optional'      => ['ticket_types', 'age_restriction', 'seating', 'extras', 'refund_policy'],
            'checkout'      => ['persons', 'extras', 'cancellation_policy'],
            'price_unit'    => 'per ticket',
            'has_staff'     => false,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => true,
            'pricing_mode'  => 'per_person',
        ],
        'venue-booking' => [
            'parent'        => 'events',
            'label'         => 'Venue Booking',
            'required'      => ['venue_resource', 'date_range_or_time_slot', 'capacity', 'price'],
            'optional'      => ['setup_options', 'deposit', 'equipment', 'catering', 'house_rules'],
            'checkout'      => ['date_range', 'persons', 'extras', 'cancellation_policy'],
            'price_unit'    => 'per hour',
            'has_staff'     => false,
            'has_time_slot' => true,
            'has_date_range'=> true,
            'has_extras'    => true,
            'pricing_mode'  => 'per_hour',
        ],
        'entertainment-activity' => [
            'parent'        => 'events',
            'label'         => 'Entertainment / Activity',
            'required'      => ['activity_type', 'duration_minutes', 'time_slot', 'capacity', 'staff_id'],
            'optional'      => ['equipment_assets', 'difficulty_level', 'risk_notes', 'weather_policy'],
            'checkout'      => ['time_slot', 'staff', 'persons', 'extras'],
            'price_unit'    => 'per person',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => true,
            'pricing_mode'  => 'per_person',
        ],
        'tour-experience' => [
            'parent'        => 'events',
            'label'         => 'Tour / Experience',
            'required'      => ['route_location', 'meeting_point', 'start_time', 'end_time', 'staff_id', 'capacity'],
            'optional'      => ['weather_policy', 'included_items', 'transport', 'prerequisites'],
            'checkout'      => ['time_slot', 'staff', 'persons', 'cancellation_policy'],
            'price_unit'    => 'per person',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'per_person',
        ],

        // ── Accommodation / Hotel (3) ──────────────────────────────
        'hotel-room-booking' => [
            'parent'        => 'accommodation',
            'label'         => 'Hotel Room Booking',
            'required'      => ['check_in_date', 'check_out_date', 'room_type', 'max_persons', 'price'],
            'optional'      => ['amenities', 'meal_plan', 'extra_bed', 'seasonal_rates'],
            'checkout'      => ['date_range', 'persons', 'extras', 'cancellation_policy'],
            'price_unit'    => 'per night',
            'has_staff'     => false,
            'has_time_slot' => false,
            'has_date_range'=> true,
            'has_extras'    => true,
            'pricing_mode'  => 'per_night',
        ],
        'short-term-rental' => [
            'parent'        => 'accommodation',
            'label'         => 'Short-term Rental',
            'required'      => ['property_unit', 'check_in_date', 'check_out_date', 'max_persons', 'price'],
            'optional'      => ['cleaning_fee', 'deposit', 'minimum_nights', 'house_rules', 'location_enabled'],
            'checkout'      => ['date_range', 'persons', 'extras', 'cancellation_policy'],
            'price_unit'    => 'per night',
            'has_staff'     => false,
            'has_time_slot' => false,
            'has_date_range'=> true,
            'has_extras'    => true,
            'pricing_mode'  => 'per_night',
        ],
        'bnb-guesthouse' => [
            'parent'        => 'accommodation',
            'label'         => 'B&B / Guesthouse',
            'required'      => ['room_type', 'check_in_date', 'check_out_date', 'max_persons', 'breakfast_option', 'price'],
            'optional'      => ['meal_preferences', 'bath_type', 'seasonal_rates', 'arrival_time'],
            'checkout'      => ['date_range', 'persons', 'extras', 'cancellation_policy'],
            'price_unit'    => 'per night',
            'has_staff'     => false,
            'has_time_slot' => false,
            'has_date_range'=> true,
            'has_extras'    => true,
            'pricing_mode'  => 'per_night',
        ],

        // ── Automotive / Mechanics (3) ─────────────────────────────
        'mechanic-repair' => [
            'parent'        => 'automotive',
            'label'         => 'Mechanic / Repair Appointment',
            'required'      => ['service_type', 'vehicle_info', 'time_slot', 'duration_minutes', 'staff_id'],
            'optional'      => ['issue_description', 'pickup_dropoff', 'parts_estimate', 'photos'],
            'checkout'      => ['time_slot', 'staff', 'cancellation_policy', 'message'],
            'price_unit'    => 'per service',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],
        'vehicle-rental' => [
            'parent'        => 'automotive',
            'label'         => 'Vehicle Rental',
            'required'      => ['vehicle_type', 'pickup_datetime', 'return_datetime', 'price'],
            'optional'      => ['deposit', 'mileage_policy', 'insurance', 'driver_requirements', 'extras'],
            'checkout'      => ['date_range', 'extras', 'cancellation_policy'],
            'price_unit'    => 'per day',
            'has_staff'     => false,
            'has_time_slot' => false,
            'has_date_range'=> true,
            'has_extras'    => true,
            'pricing_mode'  => 'per_day',
        ],
        'technical-support-inspection' => [
            'parent'        => 'automotive',
            'label'         => 'Technical Support / Inspection',
            'required'      => ['device_asset_type', 'service_location', 'staff_id', 'time_slot', 'duration_minutes'],
            'optional'      => ['problem_description', 'photos', 'onsite_remote_option', 'parts_estimate'],
            'checkout'      => ['time_slot', 'staff', 'cancellation_policy', 'message'],
            'price_unit'    => 'per inspection',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],

        // ── Professional Services / Consulting (3) ─────────────────
        'consulting-session' => [
            'parent'        => 'professional-services',
            'label'         => 'Consulting Session',
            'required'      => ['staff_id', 'topic_service_type', 'time_slot', 'duration_minutes', 'price'],
            'optional'      => ['sub_type', 'prerequisites', 'attachments', 'online_link'],
            'checkout'      => ['time_slot', 'staff', 'cancellation_policy', 'message'],
            'price_unit'    => 'per consultation',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],
        'legal-appointment' => [
            'parent'        => 'professional-services',
            'label'         => 'Legal Appointment',
            'required'      => ['staff_id', 'case_type', 'consultation_type', 'time_slot', 'duration_minutes'],
            'optional'      => ['confidentiality_terms', 'required_docs', 'sub_type', 'notes'],
            'checkout'      => ['time_slot', 'staff', 'cancellation_policy', 'message'],
            'price_unit'    => 'per consultation',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],
        'accounting-finance-insurance' => [
            'parent'        => 'professional-services',
            'label'         => 'Accounting / Finance / Insurance',
            'required'      => ['service_type', 'staff_id', 'time_slot', 'duration_minutes', 'price'],
            'optional'      => ['document_checklist', 'fiscal_period', 'company_or_person_type', 'attachments'],
            'checkout'      => ['time_slot', 'staff', 'cancellation_policy', 'message'],
            'price_unit'    => 'per appointment',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],

        // ── Education / Training (2) ───────────────────────────────
        'tutoring-private-lesson' => [
            'parent'        => 'education-training',
            'label'         => 'Tutoring / Private Lesson',
            'required'      => ['subject', 'staff_id', 'level', 'time_slot', 'duration_minutes', 'price'],
            'optional'      => ['sub_type', 'recurring_sessions', 'student_notes', 'material_requirements'],
            'checkout'      => ['time_slot', 'staff', 'persons', 'cancellation_policy'],
            'price_unit'    => 'per lesson',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => false,
            'pricing_mode'  => 'flat',
        ],
        'training-class-workshop' => [
            'parent'        => 'education-training',
            'label'         => 'Training Class / Workshop',
            'required'      => ['workshop_title', 'staff_id', 'time_slot', 'capacity', 'price'],
            'optional'      => ['level', 'materials', 'certificate_option', 'prerequisites', 'recurring_schedule'],
            'checkout'      => ['time_slot', 'staff', 'persons', 'extras'],
            'price_unit'    => 'per person',
            'has_staff'     => true,
            'has_time_slot' => true,
            'has_date_range'=> false,
            'has_extras'    => true,
            'pricing_mode'  => 'per_person',
        ],
    ];

    // Common fields required on EVERY template, regardless of type
    public const COMMON_REQUIRED = [
        'booking_type', 'category_id', 'title', 'slug', 'language', 'status',
        'description', 'thumbnail', 'creator_id', 'price_or_free', 'currency',
        'requirements', 'terms_agreement',
    ];

    public const COMMON_OPTIONAL = [
        'gallery', 'seo_meta_description', 'related_bookings', 'prerequisites',
        'location_enabled', 'notes', 'tags',
    ];

    // ────────────────────────────────────────────────────────────────
    // Factory / lookup helpers
    // ────────────────────────────────────────────────────────────────

    /**
     * Build a config instance for a given template key (subcategory).
     * Falls back to a generic definition if the key is unknown/blank,
     * so the form never hard-crashes on bad data — it just shows common fields only.
     */
    public static function for(string $templateKey): self
    {
        return new self($templateKey);
    }

    /** All 7 parent booking types: slug => label */
    public static function allTypes(): array
    {
        return self::PARENT_TYPES;
    }

    /** All 23 templates: slug => label */
    public static function allTemplates(): array
    {
        return collect(self::TEMPLATES)->map(fn($t) => $t['label'])->all();
    }

    /** Templates belonging to a given parent booking type: slug => label */
    public static function templatesForType(string $parentType): array
    {
        return collect(self::TEMPLATES)
            ->filter(fn($t) => $t['parent'] === $parentType)
            ->map(fn($t) => $t['label'])
            ->all();
    }

    /** The parent booking-type slug that a given template belongs to */
    public static function parentOf(string $templateKey): ?string
    {
        return self::TEMPLATES[$templateKey]['parent'] ?? null;
    }

    private static function fallbackDefinition(): array
    {
        return [
            'parent'         => null,
            'label'          => 'Generic',
            'required'       => [],
            'optional'       => [],
            'checkout'       => ['cancellation_policy'],
            'price_unit'     => 'per booking',
            'has_staff'      => false,
            'has_time_slot'  => false,
            'has_date_range' => false,
            'has_extras'     => false,
            'pricing_mode'   => 'flat',
        ];
    }

    // ────────────────────────────────────────────────────────────────
    // Instance accessors — used by BookingController
    // ────────────────────────────────────────────────────────────────

    public function key(): string
    {
        return $this->key;
    }

    public function label(): string
    {
        return $this->definition['label'];
    }

    public function parentType(): ?string
    {
        return $this->definition['parent'];
    }

    /** All fields (required + optional + common) relevant to this template */
    public function fields(): array
    {
        return array_values(array_unique(array_merge(
            self::COMMON_REQUIRED,
            $this->definition['required'],
            $this->definition['optional'],
            self::COMMON_OPTIONAL
        )));
    }

    /** Template-specific required fields only (common ones handled separately) */
    public function required(): array
    {
        return $this->definition['required'];
    }

    public function optional(): array
    {
        return $this->definition['optional'];
    }

    public function checkoutModules(): array
    {
        return $this->definition['checkout'];
    }

    public function priceUnitLabel(): string
    {
        return $this->definition['price_unit'];
    }

    public function pricingMode(): string
    {
        return $this->definition['pricing_mode'];
    }

    public function availabilityMode(): string
    {
        if ($this->definition['has_date_range']) return 'date-range';
        if ($this->definition['has_time_slot'])   return 'time-slot';
        return 'none';
    }

    public function hasStaff(): bool
    {
        return $this->definition['has_staff'];
    }

    public function hasDateRange(): bool
    {
        return $this->definition['has_date_range'];
    }

    public function hasTimeSlot(): bool
    {
        return $this->definition['has_time_slot'];
    }

    public function hasExtras(): bool
    {
        return $this->definition['has_extras'];
    }

    /** Human-readable labels for fields, used by JS to relabel inputs dynamically */
    public function fieldLabels(): array
    {
        $labels = [
            'staff_id'          => 'Staff / Provider',
            'duration_minutes'  => 'Duration (minutes)',
            'price'             => $this->priceUnitPriceLabel(),
            'time_slot'         => 'Time Slot',
            'check_in_date'     => 'Check-in Date',
            'check_out_date'    => 'Check-out Date',
            'capacity'          => 'Capacity',
            'requirements'      => 'Cancellation / Rescheduling Policy',
        ];
        return $labels;
    }

    private function priceUnitPriceLabel(): string
    {
        return 'Base Price (' . $this->priceUnitLabel() . ')';
    }

    /**
     * Laravel validation rules for this template's required fields.
     * These map template field keys -> the actual request/meta field name.
     * Extend the FIELD_TO_RULE table below as new field keys are introduced.
     */
    public function rules(): array
    {
        $rules = [];
        foreach ($this->required() as $fieldKey) {
            [$requestField, $rule] = self::FIELD_TO_RULE[$fieldKey] ?? ["meta.$fieldKey", 'required'];
            $rules[$requestField] = $rule;
        }
        return $rules;
    }

    private const FIELD_TO_RULE = [
        'staff_id'           => ['staff_id', 'required|exists:users,id'],
        'duration_minutes'   => ['duration_minutes', 'required|integer|min:1'],
        'price'              => ['price', 'required|numeric|min:0'],
        'capacity'           => ['capacity', 'required|integer|min:1'],
        'max_persons'        => ['max_persons', 'required|integer|min:1'],
        'check_in_date'      => ['meta.check_in_date', 'required|date'],
        'check_out_date'     => ['meta.check_out_date', 'required|date|after:meta.check_in_date'],
        'pickup_datetime'    => ['meta.pickup_datetime', 'required|date'],
        'return_datetime'    => ['meta.return_datetime', 'required|date|after:meta.pickup_datetime'],
        'time_slot'          => ['meta.time_slot', 'nullable'], // slots handled by separate BookingTimeSlot flow
    ];
}
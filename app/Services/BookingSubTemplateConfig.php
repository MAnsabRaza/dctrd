<?php

namespace App\Services;

/**
 * BookingSubTemplateConfig  (v2 — fixed)
 *
 * Category-level (23 templates) config. Match hota hai category ke `slug`
 * se — e.g. slug "doctor-appointment" => Doctor Appointment template.
 *
 * IMPORTANT FIX (v2):
 *  - `category_id`, `title`, base `price`, `description`, `requirements`
 *    ab kabhi HIDE nahi hote — ye hamesha visible rehte hain (sirf unka
 *    LABEL/NAAM change hota hai `field_labels` se, agar template mein
 *    diya gaya ho). Sirf woh fields hide/required-toggle hote hain jo
 *    genuinely type-specific hain (staff, duration, meta.* fields, etc.)
 *    — ye `required` / `optional` arrays mein hote hain.
 *  - `field_labels` ab HAR field (chahe wo hide/show hone wala ho ya
 *    hamesha-visible field ho) ka naam change kar sakta hai — bilkul
 *    jaisa screenshots ki "Required fields" / "Optional / conditional
 *    fields" column mein likha hua tha.
 */
class BookingSubTemplateConfig
{
    private const TEMPLATES = [

        // ── Doctors / Clinics ───────────────────────────────────────
        'doctor-appointment' => [
            'label'       => 'Doctor Appointment',
            'parent_type' => 'doctors-clinics',
            'required'    => ['staff_id', 'meta.appointment_type', 'duration_minutes'],
            'optional'    => ['description', 'meta.payment_option', 'meta.online_link', 'meta.required_docs'],
            'field_labels' => [
                'title'                 => 'Doctor / Clinic Name',
                'staff_id'              => 'Doctor / Specialty',
                'meta.appointment_type' => 'Consultation Type',
                'duration_minutes'      => 'Duration (minutes)',
                'price'                 => 'Base Price',
                'description'           => 'Patient Note',
                'meta.payment_option'   => 'Insurance / Payment Option',
                'meta.online_link'      => 'Online Meeting Link',
                'meta.required_docs'    => 'Required Docs',
            ],
            'price_unit'       => 'per consultation',
            'checkout_modules' => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        'clinic-visit' => [
            'label'       => 'Clinic Visit',
            'parent_type' => 'doctors-clinics',
            'required'    => ['meta.service_type', 'staff_id'],
            'optional'    => ['meta.room_type', 'capacity', 'meta.prerequisites', 'location_enabled'],
            'field_labels' => [
                'title'               => 'Clinic / Department Name',
                'meta.service_type'   => 'Service / Procedure',
                'staff_id'            => 'Staff / Resource',
                'meta.room_type'      => 'Room / Resource',
                'capacity'            => 'Patient Capacity',
                'meta.prerequisites'  => 'Prerequisites',
                'location_enabled'    => 'Location',
                'price'               => 'Base Price',
            ],
            'price_unit'       => 'per appointment',
            'checkout_modules' => ['Hours', 'Staff Member', 'Persons/Guests', 'Cancellation Policy'],
        ],

        'medical-test-diagnostic' => [
            'label'       => 'Medical Test / Diagnostic',
            'parent_type' => 'doctors-clinics',
            'required'    => ['meta.service_type', 'location_enabled', 'duration_minutes'],
            'optional'    => ['requirements', 'meta.required_notes', 'meta.payment_option'],
            'field_labels' => [
                'title'                => 'Test / Diagnostic Name',
                'meta.service_type'    => 'Test Type',
                'location_enabled'     => 'Lab / Location',
                'duration_minutes'     => 'Duration (minutes)',
                'price'                => 'Base Price',
                'requirements'         => 'Preparation Instructions',
                'meta.required_notes'  => 'Fasting Requirement',
                'meta.payment_option'  => 'Result Delivery Method',
            ],
            'price_unit'       => 'per test',
            'checkout_modules' => ['Hours', 'Cancellation Policy', 'Message'],
        ],

        'therapy-rehabilitation' => [
            'label'       => 'Therapy / Rehabilitation',
            'parent_type' => 'doctors-clinics',
            'required'    => ['meta.service_type', 'staff_id', 'duration_minutes'],
            'optional'    => ['extras', 'meta.prerequisites', 'requirements'],
            'field_labels' => [
                'title'               => 'Therapy / Program Name',
                'meta.service_type'   => 'Therapy Type',
                'staff_id'            => 'Therapist',
                'duration_minutes'    => 'Session Duration (minutes)',
                'price'               => 'Base Price',
                'extras'              => 'Package / Series',
                'meta.prerequisites'  => 'Prerequisites',
                'requirements'        => 'Progress Notes',
            ],
            'price_unit'       => 'per session',
            'checkout_modules' => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        // ── Events ───────────────────────────────────────────────────
        'event-tickets' => [
            'label'       => 'Event Tickets',
            'parent_type' => 'events',
            'required'    => ['location_enabled', 'capacity'],
            'optional'    => ['sub_type', 'meta.specifications', 'meta.venue_type', 'extras', 'requirements'],
            'field_labels' => [
                'title'                => 'Event Title',
                'location_enabled'     => 'Venue',
                'capacity'             => 'Capacity / Tickets',
                'price'                => 'Price per Ticket',
                'sub_type'             => 'Ticket Type',
                'meta.specifications'  => 'Age Restriction',
                'meta.venue_type'      => 'Seating',
                'extras'               => 'Extras',
                'requirements'         => 'Refund Policy',
            ],
            'price_unit'       => 'per ticket / per person',
            'checkout_modules' => ['Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],

        'venue-booking' => [
            'label'       => 'Venue Booking',
            'parent_type' => 'events',
            'required'    => ['location_enabled', 'capacity'],
            'optional'    => ['meta.specifications', 'deposit_enabled', 'extras', 'requirements'],
            'field_labels' => [
                'title'                => 'Venue Name',
                'location_enabled'     => 'Venue / Resource',
                'capacity'             => 'Capacity',
                'price'                => 'Base Price',
                'meta.specifications'  => 'Setup Options',
                'deposit_enabled'      => 'Deposit',
                'extras'               => 'Equipment / Catering',
                'requirements'         => 'House Rules',
            ],
            'price_unit'       => 'per hour / per day',
            'checkout_modules' => ['Days or Hours', 'Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],

        'entertainment-activity' => [
            'label'       => 'Entertainment / Activity',
            'parent_type' => 'events',
            'required'    => ['meta.service_type', 'duration_minutes', 'capacity', 'staff_id'],
            'optional'    => ['extras', 'meta.level', 'meta.required_notes', 'requirements'],
            'field_labels' => [
                'title'                => 'Activity Name',
                'meta.service_type'    => 'Activity Type',
                'duration_minutes'     => 'Duration',
                'capacity'             => 'Capacity',
                'staff_id'             => 'Staff / Guide',
                'price'                => 'Price per Person',
                'extras'               => 'Equipment / Assets',
                'meta.level'           => 'Difficulty Level',
                'meta.required_notes'  => 'Risk Notes',
                'requirements'         => 'Weather Policy',
            ],
            'price_unit'       => 'per activity / per person',
            'checkout_modules' => ['Hours', 'Staff Member', 'Persons/Guests', 'Extra Services'],
        ],

        'tour-experience' => [
            'label'       => 'Tour / Experience',
            'parent_type' => 'events',
            'required'    => ['location_enabled', 'meta.pickup_location', 'staff_id', 'capacity'],
            'optional'    => ['requirements', 'extras', 'meta.dropoff_location', 'meta.prerequisites'],
            'field_labels' => [
                'title'                  => 'Tour Name',
                'location_enabled'       => 'Route / Location',
                'meta.pickup_location'   => 'Meeting Point',
                'staff_id'               => 'Guide',
                'capacity'               => 'Capacity',
                'price'                  => 'Price per Person',
                'requirements'           => 'Weather Policy',
                'extras'                 => 'Included Items',
                'meta.dropoff_location'  => 'Transport',
                'meta.prerequisites'     => 'Prerequisites',
            ],
            'price_unit'       => 'per tour / per person',
            'checkout_modules' => ['Hours', 'Staff Member', 'Persons/Guests', 'Cancellation Policy'],
        ],

        // ── Accommodation / Hotel ───────────────────────────────────
        'hotel-room-booking' => [
            'label'       => 'Hotel Room Booking',
            'parent_type' => 'accommodation',
            'required'    => ['meta.check_in_date', 'meta.check_out_date', 'meta.room_type', 'max_persons'],
            'optional'    => ['meta.amenities', 'extras', 'max_children', 'requirements'],
            'field_labels' => [
                'title'                => 'Hotel / Room Name',
                'meta.check_in_date'   => 'Check-in Date',
                'meta.check_out_date'  => 'Check-out Date',
                'meta.room_type'       => 'Room Type / Resource',
                'max_persons'          => 'Guests (Adults)',
                'price'                => 'Nightly Price',
                'meta.amenities'       => 'Amenities',
                'extras'               => 'Meal Plan / Extra Bed',
                'max_children'         => 'Children',
                'requirements'         => 'Cancellation Policy',
            ],
            'price_unit'       => 'per night',
            'checkout_modules' => ['Days', 'Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],

        'short-term-rental' => [
            'label'       => 'Short-term Rental',
            'parent_type' => 'accommodation',
            'required'    => ['meta.room_type', 'meta.check_in_date', 'meta.check_out_date', 'max_persons'],
            'optional'    => ['deposit_enabled', 'extras', 'requirements', 'location_enabled'],
            'field_labels' => [
                'title'                => 'Property Name',
                'meta.room_type'       => 'Property / Unit Type',
                'meta.check_in_date'   => 'Check-in Date',
                'meta.check_out_date'  => 'Check-out Date',
                'max_persons'          => 'Max Guests',
                'price'                => 'Nightly / Daily Price',
                'deposit_enabled'      => 'Deposit',
                'extras'               => 'Cleaning Fee',
                'requirements'         => 'House Rules / Minimum Nights',
                'location_enabled'     => 'Location',
            ],
            'price_unit'       => 'per night / per day',
            'checkout_modules' => ['Days', 'Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],

        'bnb-guesthouse' => [
            'label'       => 'B&B / Guesthouse',
            'parent_type' => 'accommodation',
            'required'    => ['meta.room_type', 'meta.check_in_date', 'meta.check_out_date', 'max_persons'],
            'optional'    => ['meta.amenities', 'requirements', 'extras'],
            'field_labels' => [
                'title'                => 'Guesthouse Name',
                'meta.room_type'       => 'Room',
                'meta.check_in_date'   => 'Check-in Date',
                'meta.check_out_date'  => 'Check-out Date',
                'max_persons'          => 'Guests',
                'price'                => 'Base Price',
                'meta.amenities'       => 'Breakfast Option / Meal Preferences',
                'requirements'         => 'Bath Type / Seasonal Rates',
                'extras'               => 'Arrival Time Notes',
            ],
            'price_unit'       => 'per night',
            'checkout_modules' => ['Days', 'Persons/Guests', 'Extra Services', 'Cancellation Policy'],
        ],

        // ── Automotive / Mechanics ──────────────────────────────────
        'mechanic-repair-appointment' => [
            'label'       => 'Mechanic / Repair Appointment',
            'parent_type' => 'automotive',
            'required'    => ['meta.service_type', 'meta.vehicle_type', 'duration_minutes', 'staff_id'],
            'optional'    => ['meta.required_notes', 'meta.pickup_location', 'meta.dropoff_location'],
            'field_labels' => [
                'title'                  => 'Service Title',
                'meta.service_type'      => 'Service Type',
                'meta.vehicle_type'      => 'Vehicle Info',
                'duration_minutes'       => 'Duration',
                'staff_id'               => 'Mechanic / Staff',
                'price'                  => 'Base Price',
                'meta.required_notes'    => 'Issue Description / Parts Estimate',
                'meta.pickup_location'   => 'Pickup Location',
                'meta.dropoff_location'  => 'Drop-off Location',
            ],
            'price_unit'       => 'per service / per appointment',
            'checkout_modules' => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        'vehicle-rental' => [
            'label'       => 'Vehicle Rental',
            'parent_type' => 'automotive',
            'required'    => ['meta.vehicle_type', 'meta.pickup_location', 'meta.dropoff_location'],
            'optional'    => ['deposit_enabled', 'meta.vehicle_specs', 'extras', 'requirements'],
            'field_labels' => [
                'title'                  => 'Vehicle Name',
                'meta.vehicle_type'      => 'Vehicle Type / Resource',
                'meta.pickup_location'   => 'Pickup Date & Location',
                'meta.dropoff_location'  => 'Return Date & Location',
                'price'                  => 'Base Price',
                'deposit_enabled'        => 'Deposit',
                'meta.vehicle_specs'     => 'Mileage Policy / Insurance',
                'extras'                 => 'Driver Requirements / Extras',
                'requirements'           => 'Rental Policy',
            ],
            'price_unit'       => 'per day / per hour',
            'checkout_modules' => ['Days or Hours', 'Extra Services', 'Cancellation Policy'],
        ],

        'technical-support-inspection' => [
            'label'       => 'Technical Support / Inspection',
            'parent_type' => 'automotive',
            'required'    => ['meta.vehicle_type', 'location_enabled', 'staff_id', 'duration_minutes'],
            'optional'    => ['meta.required_notes', 'sub_type'],
            'field_labels' => [
                'title'                => 'Inspection / Support Title',
                'meta.vehicle_type'    => 'Device / Asset Type',
                'location_enabled'     => 'Service Location',
                'staff_id'             => 'Technician',
                'duration_minutes'     => 'Duration',
                'price'                => 'Base Price',
                'meta.required_notes'  => 'Problem Description / Parts Estimate',
                'sub_type'             => 'Onsite / Remote Option',
            ],
            'price_unit'       => 'per inspection / per service',
            'checkout_modules' => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        // ── Professional Services / Consulting ───────────────────────
        'consulting-session' => [
            'label'       => 'Consulting Session',
            'parent_type' => 'professional-services',
            'required'    => ['staff_id', 'meta.service_type'],
            'optional'    => ['sub_type', 'meta.prerequisites', 'meta.online_link', 'meta.required_docs'],
            'field_labels' => [
                'title'               => 'Consulting Service Name',
                'staff_id'            => 'Consultant',
                'meta.service_type'   => 'Topic / Service Type',
                'price'               => 'Base Price',
                'sub_type'            => 'Online / Offline',
                'meta.prerequisites'  => 'Prerequisites',
                'meta.online_link'    => 'Meeting Link',
                'meta.required_docs'  => 'Attachments',
            ],
            'price_unit'       => 'per consultation',
            'checkout_modules' => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        'legal-appointment' => [
            'label'       => 'Legal Appointment',
            'parent_type' => 'professional-services',
            'required'    => ['staff_id', 'meta.service_type', 'sub_type'],
            'optional'    => ['meta.required_docs', 'meta.online_link'],
            'field_labels' => [
                'title'                => 'Legal Service Name',
                'staff_id'             => 'Lawyer',
                'meta.service_type'    => 'Case Type',
                'sub_type'             => 'Consultation Type',
                'price'                => 'Base Price',
                'meta.required_docs'   => 'Confidentiality Terms / Required Docs',
                'meta.online_link'     => 'Online / Offline Notes',
            ],
            'price_unit'       => 'per consultation',
            'checkout_modules' => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        'accounting-finance-insurance' => [
            'label'       => 'Accounting / Finance / Insurance',
            'parent_type' => 'professional-services',
            'required'    => ['meta.service_type', 'staff_id', 'duration_minutes'],
            'optional'    => ['meta.required_docs', 'meta.prerequisites'],
            'field_labels' => [
                'title'                => 'Service Name',
                'meta.service_type'    => 'Service Type',
                'staff_id'             => 'Advisor',
                'duration_minutes'     => 'Duration',
                'price'                => 'Base Price',
                'meta.required_docs'   => 'Document Checklist / Attachments',
                'meta.prerequisites'   => 'Fiscal Period / Company Type',
            ],
            'price_unit'       => 'per appointment',
            'checkout_modules' => ['Hours', 'Staff Member', 'Cancellation Policy', 'Message'],
        ],

        // ── Education / Training ────────────────────────────────────
        'tutoring-private-lesson' => [
            'label'       => 'Tutoring / Private Lesson',
            'parent_type' => 'education-training',
            'required'    => ['staff_id', 'meta.level', 'duration_minutes'],
            'optional'    => ['sub_type', 'requirements', 'meta.prerequisites'],
            'field_labels' => [
                'title'               => 'Subject / Lesson Title',
                'staff_id'            => 'Teacher',
                'meta.level'          => 'Level',
                'duration_minutes'    => 'Duration',
                'price'               => 'Base Price',
                'sub_type'            => 'Online / Offline',
                'requirements'        => 'Student Notes / Recurring Sessions',
                'meta.prerequisites'  => 'Material Requirements',
            ],
            'price_unit'       => 'per lesson',
            'checkout_modules' => ['Hours', 'Staff Member', 'Persons/Guests', 'Cancellation Policy'],
        ],

        'training-class-workshop' => [
            'label'       => 'Training Class / Workshop',
            'parent_type' => 'education-training',
            'required'    => ['staff_id', 'capacity'],
            'optional'    => ['meta.level', 'extras', 'meta.prerequisites', 'requirements'],
            'field_labels' => [
                'title'               => 'Workshop / Class Title',
                'staff_id'            => 'Trainer',
                'capacity'            => 'Capacity',
                'price'               => 'Base Price',
                'meta.level'          => 'Level',
                'extras'              => 'Materials / Certificate Option',
                'meta.prerequisites'  => 'Prerequisites',
                'requirements'        => 'Recurring Schedule',
            ],
            'price_unit'       => 'per class / per person',
            'checkout_modules' => ['Hours', 'Staff Member', 'Persons/Guests', 'Extra Services'],
        ],

        // ── Beauty / Spa ─────────────────────────────────────────────
        // NOTE: proposed by extrapolation — screenshot table wasn't shared for this group.
        'salon-appointment' => [
            'label'       => 'Salon Appointment (Haircut / Styling)',
            'parent_type' => 'beauty-spa',
            'required'    => ['staff_id', 'duration_minutes'],
            'optional'    => ['description', 'requirements', 'extras'],
            'field_labels' => [
                'title'              => 'Service Name',
                'staff_id'           => 'Stylist / Provider',
                'duration_minutes'   => 'Service Duration',
                'price'              => 'Base Price',
                'description'        => 'Service Notes',
                'requirements'       => 'Cancellation Policy',
                'extras'             => 'Add-ons',
            ],
            'price_unit'       => 'per appointment',
            'checkout_modules' => ['Hours', 'Staff Member', 'Extra Services', 'Cancellation Policy'],
        ],

        'spa-treatment' => [
            'label'       => 'Spa / Massage Treatment',
            'parent_type' => 'beauty-spa',
            'required'    => ['staff_id', 'duration_minutes'],
            'optional'    => ['extras', 'requirements', 'capacity'],
            'field_labels' => [
                'title'             => 'Treatment Name',
                'staff_id'          => 'Therapist',
                'duration_minutes'  => 'Treatment Duration',
                'price'             => 'Base Price',
                'extras'            => 'Add-ons',
                'requirements'      => 'Cancellation Policy',
                'capacity'          => 'Group Size',
            ],
            'price_unit'       => 'per session',
            'checkout_modules' => ['Hours', 'Staff Member', 'Extra Services', 'Cancellation Policy'],
        ],

        'group-fitness-class' => [
            'label'       => 'Group Fitness / Wellness Class',
            'parent_type' => 'beauty-spa',
            'required'    => ['staff_id', 'duration_minutes', 'capacity'],
            'optional'    => ['extras', 'meta.level', 'requirements'],
            'field_labels' => [
                'title'             => 'Class Name',
                'staff_id'          => 'Instructor',
                'duration_minutes'  => 'Class Duration',
                'capacity'          => 'Class Capacity',
                'price'             => 'Base Price',
                'extras'            => 'Add-ons',
                'meta.level'        => 'Level',
                'requirements'      => 'Cancellation Policy',
            ],
            'price_unit'       => 'per class / per person',
            'checkout_modules' => ['Hours', 'Staff Member', 'Persons/Guests', 'Extra Services'],
        ],

        'beauty-package-membership' => [
            'label'       => 'Beauty Package / Membership',
            'parent_type' => 'beauty-spa',
            'required'    => ['extras'],
            'optional'    => ['staff_id', 'duration_minutes', 'requirements'],
            'field_labels' => [
                'title'             => 'Package / Membership Name',
                'extras'            => 'Package Items',
                'price'             => 'Base Price',
                'staff_id'          => 'Provider (optional)',
                'duration_minutes'  => 'Duration',
                'requirements'      => 'Terms',
            ],
            'price_unit'       => 'per package',
            'checkout_modules' => ['Extra Services', 'Cancellation Policy', 'Message'],
        ],
    ];

    /** Base validation rule per field-key (type/format only). */
    private const FIELD_RULE_MAP = [
        'staff_id'                => 'exists:users,id',
        'duration_minutes'        => 'integer|min:5|max:480',
        'capacity'                => 'integer|min:1',
        'inventory'               => 'integer|min:0',
        'min_persons'             => 'integer|min:1',
        'max_persons'             => 'integer|min:1',
        'max_children'            => 'integer|min:0',
        'sub_type'                => 'string|max:50',
        'location_enabled'        => 'boolean',
        'deposit_enabled'         => 'boolean',
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

    public static function all(): array
    {
        return self::TEMPLATES;
    }

    public static function slugsForParentType(string $parentType): array
    {
        return array_keys(array_filter(self::TEMPLATES, fn($cfg) => $cfg['parent_type'] === $parentType));
    }

    public function label(): string { return $this->config['label']; }
    public function parentType(): string { return $this->config['parent_type']; }
    public function required(): array { return $this->config['required'] ?? []; }
    public function optional(): array { return $this->config['optional'] ?? []; }
    public function fieldLabels(): array { return $this->config['field_labels'] ?? []; }
    public function priceUnit(): string { return $this->config['price_unit'] ?? 'per booking'; }
    public function checkoutModules(): array { return $this->config['checkout_modules'] ?? []; }

    public function relevantFields(): array
    {
        return array_values(array_unique(array_merge($this->required(), $this->optional())));
    }

    /**
     * Build Laravel validation rules. Only fields that are toggle-eligible
     * (present in FIELD_RULE_MAP) get rules here — universal fields
     * (title/category_id/price/description/requirements) already have
     * their own rules elsewhere and are never hidden.
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

    public function toArray(): array
    {
        return [
            'label'            => $this->label(),
            'parent_type'      => $this->parentType(),
            'required'         => $this->required(),
            'optional'         => $this->optional(),
            'field_labels'     => $this->fieldLabels(),
            'price_unit'       => $this->priceUnit(),
            'checkout_modules' => $this->checkoutModules(),
        ];
    }
}
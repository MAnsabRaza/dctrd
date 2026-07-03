<?php

namespace App\Services;

/**
 * BookingTemplateConfig
 *
 * Ek central class jo har booking type (template) ki puri configuration
 * define karti hai. Jab bhi booking type change ho, yahan se config load ho.
 *
 * Usage:
 *   $config = BookingTemplateConfig::for('beauty-spa');
 *   $config->fields()       // visible fields array
 *   $config->required()     // required fields array
 *   $config->rules()        // Laravel validation rules
 *   $config->filters()      // frontend/admin filter definitions
 *   $config->pricingMode()  // how price is calculated
 *   $config->availabilityMode() // how availability is checked
 *   $config->meta()         // extra config for this type
 */
class BookingTemplateConfig
{
    // ── Supported template slugs ──────────────────────────────────────
    public const BEAUTY_SPA            = 'beauty-spa';
    public const DOCTORS_CLINICS       = 'doctors-clinics';
    public const EVENTS                = 'events';
    public const ACCOMMODATION         = 'accommodation';
    public const AUTOMOTIVE            = 'automotive';
    public const PROFESSIONAL_SERVICES = 'professional-services';
    public const EDUCATION_TRAINING    = 'education-training';

    // Pricing modes
    public const PRICE_PER_APPOINTMENT = 'per_appointment'; // Beauty, Doctor, Professional
    public const PRICE_PER_TICKET      = 'per_ticket';      // Events
    public const PRICE_PER_NIGHT       = 'per_night';       // Accommodation
    public const PRICE_PER_DAY         = 'per_day';         // Automotive rental
    public const PRICE_PER_SERVICE     = 'per_service';     // Automotive service, Education
    public const PRICE_QUOTE_BASED     = 'quote_based';     // Consulting

    // Availability modes
    public const AVAIL_TIME_SLOT       = 'time_slot';       // Beauty, Doctor, Professional, Education
    public const AVAIL_DATE_RANGE      = 'date_range';      // Accommodation, Automotive rental
    public const AVAIL_TICKET_COUNT    = 'ticket_count';    // Events
    public const AVAIL_APPOINTMENT     = 'appointment';     // Automotive service

    private string $type;
    private array $config;

    // ─────────────────────────────────────────────────────────────────

    private function __construct(string $type)
    {
        $this->type   = $type;
        $this->config = $this->buildConfig($type);
    }

    public static function for(string $bookingType): static
    {
        return new static($bookingType);
    }

    /**
     * All supported template types with labels.
     */
    public static function allTypes(): array
    {
        return [
            self::BEAUTY_SPA            => 'Beauty / Spa',
            self::DOCTORS_CLINICS       => 'Doctors / Clinics',
            self::EVENTS                => 'Events',
            self::ACCOMMODATION         => 'Accommodation / Hotel',
            self::AUTOMOTIVE            => 'Automotive / Mechanics',
            self::PROFESSIONAL_SERVICES => 'Professional Services / Consulting',
            self::EDUCATION_TRAINING    => 'Education / Training',
        ];
    }

    // ── Public API ────────────────────────────────────────────────────

    /** Fields visible on the booking form / detail page */
    public function fields(): array
    {
        return $this->config['fields'] ?? [];
    }

    /** Field labels for display */
    public function fieldLabels(): array
    {
        return $this->config['field_labels'] ?? [];
    }

    /** Fields that are required */
    public function required(): array
    {
        return $this->config['required'] ?? [];
    }

    /** Laravel validation rules (merged with global rules in controller) */
    public function rules(): array
    {
        return $this->config['rules'] ?? [];
    }

    /** Filter definitions for frontend + admin list */
    public function filters(): array
    {
        return $this->config['filters'] ?? [];
    }

    /** How price is calculated for this type */
    public function pricingMode(): string
    {
        return $this->config['pricing_mode'] ?? self::PRICE_PER_APPOINTMENT;
    }

    /** How availability is checked for this type */
    public function availabilityMode(): string
    {
        return $this->config['availability_mode'] ?? self::AVAIL_TIME_SLOT;
    }

    /** Extra type-specific configuration */
    public function meta(): array
    {
        return $this->config['meta'] ?? [];
    }

    /** Human-readable label for this type */
    public function label(): string
    {
        return self::allTypes()[$this->type] ?? ucwords(str_replace('-', ' ', $this->type));
    }

    /** Price unit label for display ("per appointment", "per night", etc.) */
    public function priceUnitLabel(): string
    {
        return $this->config['price_unit_label'] ?? 'per booking';
    }

    /** Whether this type supports staff/provider selection */
    public function hasStaff(): bool
    {
        return $this->config['has_staff'] ?? false;
    }

    /** Whether this type supports date range (check-in/check-out) */
    public function hasDateRange(): bool
    {
        return $this->availabilityMode() === self::AVAIL_DATE_RANGE;
    }

    /** Whether this type supports single date + time slot */
    public function hasTimeSlot(): bool
    {
        return in_array($this->availabilityMode(), [
            self::AVAIL_TIME_SLOT,
            self::AVAIL_APPOINTMENT,
        ]);
    }

    /** Whether extras/add-ons are supported */
    public function hasExtras(): bool
    {
        return $this->config['has_extras'] ?? false;
    }

    // ── Config builder ────────────────────────────────────────────────

    private function buildConfig(string $type): array
    {
        return match ($type) {
            self::BEAUTY_SPA            => $this->beautySpaConfig(),
            self::DOCTORS_CLINICS       => $this->doctorsClinicsConfig(),
            self::EVENTS                => $this->eventsConfig(),
            self::ACCOMMODATION         => $this->accommodationConfig(),
            self::AUTOMOTIVE            => $this->automotiveConfig(),
            self::PROFESSIONAL_SERVICES => $this->professionalServicesConfig(),
            self::EDUCATION_TRAINING    => $this->educationTrainingConfig(),
            default                     => $this->defaultConfig(),
        };
    }

    // ── 1. Beauty / Spa ───────────────────────────────────────────────

    private function beautySpaConfig(): array
    {
        return [
            'fields' => [
                'title',
                'category_id',
                'staff_id',          // meta -> staff_id (BookingResource ya custom)
                'duration_minutes',  // existing model field
                'date',              // via BookingAvailability
                'time_slot',         // via BookingTimeSlot
                'price',
                'extras',            // meta -> extras (JSON)
                'location_enabled',
                'address_line', 'city', 'state', 'country',
                'capacity',          // group service ke liye
                'description',
                'requirements',      // cancellation/rescheduling policy
                'thumbnail', 'cover',
                'status',
                'language',
            ],

            'field_labels' => [
                'title'            => 'Service Name',
                'category_id'      => 'Service Category',
                'staff_id'         => 'Staff / Provider',
                'duration_minutes' => 'Duration (minutes)',
                'price'            => 'Price per Appointment',
                'extras'           => 'Extras / Add-ons',
                'capacity'         => 'Capacity (group service)',
                'requirements'     => 'Cancellation / Rescheduling Policy',
            ],

            'required' => [
                'title',
                'duration_minutes',
                'price',
            ],

            'rules' => [
                'title'            => 'required|string|max:255',
                'duration_minutes' => 'required|integer|min:5|max:480',
                'price'            => 'required|numeric|min:0',
                'capacity'         => 'nullable|integer|min:1',
                'meta.staff_id'    => 'nullable|exists:users,id',
                'meta.extras'      => 'nullable|array',
            ],

            'filters' => [
                'location'       => ['type' => 'text',   'label' => 'Location'],
                'category_id'    => ['type' => 'select', 'label' => 'Service Category', 'source' => 'categories'],
                'staff_id'       => ['type' => 'select', 'label' => 'Staff / Provider', 'source' => 'staff'],
                'date'           => ['type' => 'date',   'label' => 'Date'],
                'time'           => ['type' => 'time',   'label' => 'Time'],
                'price_min'      => ['type' => 'number', 'label' => 'Min Price'],
                'price_max'      => ['type' => 'number', 'label' => 'Max Price'],
                'duration_max'   => ['type' => 'number', 'label' => 'Max Duration (min)'],
                'has_extras'     => ['type' => 'toggle', 'label' => 'Has Extras'],
            ],

            'pricing_mode'      => self::PRICE_PER_APPOINTMENT,
            'availability_mode' => self::AVAIL_TIME_SLOT,
            'price_unit_label'  => 'per appointment',
            'has_staff'         => true,
            'has_extras'        => true,

            'meta' => [
                'supports_group_booking' => true,
                'slot_duration_required' => true,
                'filter_note' => 'Staff unavailability and time slot conflicts must be excluded from results.',
            ],
        ];
    }

    // ── 2. Doctors / Clinics ──────────────────────────────────────────

    private function doctorsClinicsConfig(): array
    {
        return [
            'fields' => [
                'title',
                'category_id',       // Specialty category
                'staff_id',          // Doctor / provider (meta)
                'location_enabled',
                'address_line', 'city', 'state', 'country',
                'date',
                'time_slot',
                'duration_minutes',
                'sub_type',          // existing field: "physical" / "online"
                'price',
                'description',       // Patient notes field (reused)
                'requirements',      // Cancellation policy
                'thumbnail', 'cover',
                'status',
                'language',
                'meta.appointment_type', // consultation / diagnostic / therapy
                'meta.payment_option',   // per appointment / quote based
                'meta.online_link',      // online meeting link if online
            ],

            'field_labels' => [
                'title'                   => 'Doctor / Clinic Name',
                'category_id'             => 'Specialty',
                'staff_id'                => 'Doctor / Provider',
                'city'                    => 'Clinic Location / City',
                'sub_type'                => 'Appointment Type (Online / Physical)',
                'duration_minutes'        => 'Appointment Duration (minutes)',
                'price'                   => 'Consultation Fee',
                'description'             => 'Patient Notes / Info',
                'requirements'            => 'Cancellation Policy',
                'meta.appointment_type'   => 'Service Type',
                'meta.payment_option'     => 'Payment Option',
                'meta.online_link'        => 'Online Meeting Link',
            ],

            'required' => [
                'title',
                'price',
            ],

            'rules' => [
                'title'                 => 'required|string|max:255',
                'price'                 => 'required|numeric|min:0',
                'sub_type'              => 'nullable|in:physical,online,both',
                'duration_minutes'      => 'nullable|integer|min:5|max:240',
                'meta.appointment_type' => 'nullable|in:consultation,diagnostic,therapy,checkup',
                'meta.payment_option'   => 'nullable|in:per_appointment,quote_based,insurance',
                'meta.online_link'      => 'nullable|url',
            ],

            'filters' => [
                'category_id'    => ['type' => 'select', 'label' => 'Specialty',          'source' => 'categories'],
                'staff_id'       => ['type' => 'select', 'label' => 'Doctor / Provider',   'source' => 'staff'],
                'city'           => ['type' => 'text',   'label' => 'Clinic Location'],
                'date'           => ['type' => 'date',   'label' => 'Appointment Date'],
                'time'           => ['type' => 'time',   'label' => 'Appointment Time'],
                'sub_type'       => ['type' => 'select', 'label' => 'Online / Physical',
                                     'options' => ['online' => 'Online', 'physical' => 'Physical', 'both' => 'Both']],
                'price_min'      => ['type' => 'number', 'label' => 'Min Price'],
                'price_max'      => ['type' => 'number', 'label' => 'Max Price'],
                'language'       => ['type' => 'select', 'label' => 'Language',            'source' => 'languages'],
                'rating_min'     => ['type' => 'number', 'label' => 'Min Rating'],
            ],

            'pricing_mode'      => self::PRICE_PER_APPOINTMENT,
            'availability_mode' => self::AVAIL_TIME_SLOT,
            'price_unit_label'  => 'per consultation',
            'has_staff'         => true,
            'has_extras'        => false,

            'meta' => [
                'filter_note' => 'Show only doctors/clinics available on selected date+time+specialty+location.',
                'sub_type_options' => ['physical', 'online', 'both'],
            ],
        ];
    }

    // ── 3. Events ─────────────────────────────────────────────────────

    private function eventsConfig(): array
    {
        return [
            'fields' => [
                'title',
                'category_id',
                'date',              // via BookingAvailability single date
                'time_slot',
                'location_enabled',
                'address_line', 'city', 'state', 'country',
                'capacity',          // total tickets/seats
                'inventory',         // available tickets (existing field)
                'price',             // per ticket / per person
                'sub_type',          // ticket type
                'description',
                'requirements',      // refund/cancellation policy
                'thumbnail', 'cover',
                'status',
                'language',
                'duration_minutes',
                'meta.ticket_types', // JSON: [{name, price, count}]
                'meta.venue_type',   // indoor / outdoor
                'meta.organizer',
                'meta.specifications', // family-friendly, age-limit, etc.
            ],

            'field_labels' => [
                'title'               => 'Event Title',
                'category_id'         => 'Event Category',
                'capacity'            => 'Total Capacity',
                'inventory'           => 'Available Tickets',
                'price'               => 'Price per Ticket / Person',
                'sub_type'            => 'Ticket Type',
                'duration_minutes'    => 'Event Duration (minutes)',
                'requirements'        => 'Refund / Cancellation Policy',
                'meta.ticket_types'   => 'Ticket Types (if multiple)',
                'meta.venue_type'     => 'Venue Type',
                'meta.organizer'      => 'Organizer / Provider',
                'meta.specifications' => 'Specifications',
            ],

            'required' => [
                'title',
                'price',
                'capacity',
            ],

            'rules' => [
                'title'            => 'required|string|max:255',
                'price'            => 'required|numeric|min:0',
                'capacity'         => 'required|integer|min:1',
                'inventory'        => 'nullable|integer|min:0',
                'meta.venue_type'  => 'nullable|in:indoor,outdoor,hybrid,online',
                'meta.ticket_types'=> 'nullable|array',
                'meta.organizer'   => 'nullable|string|max:255',
            ],

            'filters' => [
                'city'           => ['type' => 'text',   'label' => 'Location'],
                'category_id'    => ['type' => 'select', 'label' => 'Event Category',   'source' => 'categories'],
                'date'           => ['type' => 'date',   'label' => 'Event Date'],
                'time'           => ['type' => 'time',   'label' => 'Event Time'],
                'price_min'      => ['type' => 'number', 'label' => 'Min Price'],
                'price_max'      => ['type' => 'number', 'label' => 'Max Price'],
                'has_tickets'    => ['type' => 'toggle', 'label' => 'Available Only'],
                'meta.venue_type'=> ['type' => 'select', 'label' => 'Venue Type',
                                     'options' => ['indoor'=>'Indoor','outdoor'=>'Outdoor','hybrid'=>'Hybrid','online'=>'Online']],
                'meta.organizer' => ['type' => 'text',   'label' => 'Organizer'],
            ],

            'pricing_mode'      => self::PRICE_PER_TICKET,
            'availability_mode' => self::AVAIL_TICKET_COUNT,
            'price_unit_label'  => 'per ticket',
            'has_staff'         => false,
            'has_extras'        => false,

            'meta' => [
                'inventory_tracked'  => true,
                'show_sold_out_badge'=> true,
                'filter_note'        => 'Fully booked events should show as Sold Out or be excluded from available filter.',
            ],
        ];
    }

    // ── 4. Accommodation / Hotel ──────────────────────────────────────

    private function accommodationConfig(): array
    {
        return [
            'fields' => [
                'title',
                'category_id',       // accommodation type: hotel/villa/hostel/apartment
                'location_enabled',
                'address_line', 'city', 'state', 'country', 'postal_code',
                'lat', 'lng',
                // Date range — stored in BookingAvailability or meta
                'meta.check_in_date',
                'meta.check_out_date',
                // Guests
                'min_persons',       // existing
                'max_persons',       // existing
                'max_children',      // existing
                'children_allowed',  // existing
                'capacity',          // max room capacity
                // Rooms / resources
                'meta.room_type',
                // Pricing
                'price',             // base price per night
                'price_per',         // existing — price per extra person
                'currency',
                'deposit_enabled', 'deposit_amount', 'deposit_type',
                // Extras
                'meta.amenities',    // JSON: wifi, pool, parking etc.
                'meta.extra_fees',   // JSON: cleaning fee, city tax etc.
                // Policy
                'requirements',      // cancellation policy
                'description',
                'thumbnail', 'cover',
                'status',
                'language',
                'instant_booking',
                'requires_approval',
                'allow_reschedule',
                'waitlist_enabled',
            ],

            'field_labels' => [
                'title'               => 'Accommodation Title',
                'category_id'         => 'Accommodation Type',
                'price'               => 'Price per Night',
                'price_per'           => 'Price per Extra Person (per night)',
                'meta.check_in_date'  => 'Check-in Date',
                'meta.check_out_date' => 'Check-out Date',
                'min_persons'         => 'Minimum Guests',
                'max_persons'         => 'Maximum Guests (Adults)',
                'max_children'        => 'Maximum Children',
                'capacity'            => 'Room Capacity',
                'meta.room_type'      => 'Room / Unit Type',
                'meta.amenities'      => 'Amenities',
                'meta.extra_fees'     => 'Extra Fees',
                'requirements'        => 'Cancellation Policy',
            ],

            'required' => [
                'title',
                'price',
                'max_persons',
            ],

            'rules' => [
                'title'               => 'required|string|max:255',
                'price'               => 'required|numeric|min:0',
                'max_persons'         => 'required|integer|min:1',
                'capacity'            => 'nullable|integer|min:1',
                'meta.check_in_date'  => 'nullable|date',
                'meta.check_out_date' => 'nullable|date|after:meta.check_in_date',
                'meta.room_type'      => 'nullable|string|max:100',
                'meta.amenities'      => 'nullable|array',
                'meta.extra_fees'     => 'nullable|array',
            ],

            'filters' => [
                'city'              => ['type' => 'text',   'label' => 'Location'],
                'check_in'          => ['type' => 'date',   'label' => 'Check-in Date'],
                'check_out'         => ['type' => 'date',   'label' => 'Check-out Date'],
                'guests'            => ['type' => 'number', 'label' => 'Number of Guests'],
                'category_id'       => ['type' => 'select', 'label' => 'Accommodation Type', 'source' => 'categories'],
                'price_min'         => ['type' => 'number', 'label' => 'Min Price per Night'],
                'price_max'         => ['type' => 'number', 'label' => 'Max Price per Night'],
                'meta.amenities'    => ['type' => 'multi_select', 'label' => 'Amenities',
                                        'options' => ['wifi','pool','parking','gym','spa','breakfast','ac','kitchen']],
                'rating_min'        => ['type' => 'number', 'label' => 'Min Rating'],
                'creator_id'        => ['type' => 'select', 'label' => 'Provider / Host', 'source' => 'creators'],
            ],

            'pricing_mode'      => self::PRICE_PER_NIGHT,
            'availability_mode' => self::AVAIL_DATE_RANGE,
            'price_unit_label'  => 'per night',
            'has_staff'         => false,
            'has_extras'        => true,

            'meta' => [
                'nights_calculation'  => true,
                'seasonal_pricing'    => true,
                'filter_note'         => 'Availability must be checked for the FULL date range. Price = nights × rate + seasonal adjustments.',
                'date_range_required' => true,
            ],
        ];
    }

    // ── 5. Automotive / Mechanics ─────────────────────────────────────

    private function automotiveConfig(): array
    {
        return [
            'fields' => [
                'title',
                'category_id',
                'sub_type',          // "rental" or "service"

                // Rental fields
                'meta.vehicle_type',
                'meta.pickup_location',
                'meta.dropoff_location',
                'meta.pickup_datetime',
                'meta.return_datetime',
                'price',
                'price_per',         // per hour (if hourly)
                'price_unit',        // "per day" / "per hour"
                'deposit_enabled', 'deposit_amount', 'deposit_type',
                'meta.vehicle_specs', // seats, transmission, fuel type
                'meta.mileage_limit',

                // Service / mechanic fields
                'meta.service_type',
                'meta.vehicle_type_service',
                'staff_id',          // mechanic
                'duration_minutes',
                'date',
                'time_slot',
                'meta.required_notes',
                'meta.estimated_price', // quote

                'description',
                'requirements',
                'thumbnail', 'cover',
                'location_enabled',
                'address_line', 'city', 'state', 'country',
                'status',
                'language',
            ],

            'field_labels' => [
                'title'                      => 'Service / Vehicle Name',
                'category_id'                => 'Service Category',
                'sub_type'                   => 'Type (Rental / Mechanic Service)',
                'meta.vehicle_type'          => 'Vehicle Type',
                'meta.pickup_location'       => 'Pickup Location',
                'meta.dropoff_location'      => 'Drop-off Location',
                'meta.pickup_datetime'       => 'Pickup Date & Time',
                'meta.return_datetime'       => 'Return Date & Time',
                'price'                      => 'Price per Day / Service',
                'price_unit'                 => 'Price Unit',
                'meta.vehicle_specs'         => 'Vehicle Specifications',
                'meta.service_type'          => 'Service Type',
                'staff_id'                   => 'Mechanic / Resource',
                'duration_minutes'           => 'Estimated Duration (minutes)',
                'meta.required_notes'        => 'Required Notes / Vehicle Details',
            ],

            'required' => [
                'title',
                'sub_type',
                'price',
            ],

            'rules' => [
                'title'                => 'required|string|max:255',
                'sub_type'             => 'required|in:rental,service',
                'price'                => 'required|numeric|min:0',
                'meta.vehicle_type'    => 'nullable|string|max:100',
                'meta.pickup_datetime' => 'nullable|date',
                'meta.return_datetime' => 'nullable|date|after:meta.pickup_datetime',
                'meta.service_type'    => 'nullable|string|max:150',
                'duration_minutes'     => 'nullable|integer|min:15',
            ],

            'filters' => [
                'city'                 => ['type' => 'text',   'label' => 'Location'],
                'meta.vehicle_type'    => ['type' => 'select', 'label' => 'Vehicle Type',
                                           'options' => ['car','suv','van','truck','motorcycle','bus']],
                'sub_type'             => ['type' => 'select', 'label' => 'Type',
                                           'options' => ['rental' => 'Rental', 'service' => 'Service']],
                'pickup_date'          => ['type' => 'date',   'label' => 'Pickup Date'],
                'return_date'          => ['type' => 'date',   'label' => 'Return Date'],
                'appointment_date'     => ['type' => 'date',   'label' => 'Appointment Date'],
                'staff_id'             => ['type' => 'select', 'label' => 'Mechanic / Resource', 'source' => 'staff'],
                'price_min'            => ['type' => 'number', 'label' => 'Min Price'],
                'price_max'            => ['type' => 'number', 'label' => 'Max Price'],
                'meta.vehicle_specs'   => ['type' => 'text',   'label' => 'Specs (e.g. automatic, diesel)'],
            ],

            'pricing_mode'      => self::PRICE_PER_DAY,   // changes to PRICE_PER_SERVICE for mechanic
            'availability_mode' => self::AVAIL_DATE_RANGE, // changes to AVAIL_APPOINTMENT for mechanic
            'price_unit_label'  => 'per day',
            'has_staff'         => true,
            'has_extras'        => false,

            'meta' => [
                'has_sub_types'       => true,
                'sub_type_options'    => ['rental', 'service'],
                'filter_note'         => 'Rental: exclude vehicles booked in selected period. Service: show available mechanic slots only.',
                'sub_type_switches_mode' => true, // rental=date_range, service=appointment
            ],
        ];
    }

    // ── 6. Professional Services / Consulting ─────────────────────────

    private function professionalServicesConfig(): array
    {
        return [
            'fields' => [
                'title',
                'category_id',       // specialty: law, accounting, coaching, etc.
                'staff_id',          // professional
                'sub_type',          // online / in-person
                'date',
                'time_slot',
                'duration_minutes',
                'price',
                'price_unit',        // per hour / per session
                'location_enabled',
                'address_line', 'city', 'state', 'country',
                'meta.online_link',
                'meta.required_docs',
                'requirements',      // cancellation policy
                'description',
                'language',
                'thumbnail', 'cover',
                'status',
                'instant_booking',
                'requires_approval',
                'allow_reschedule',
                'reviews_enabled',
            ],

            'field_labels' => [
                'title'             => 'Professional / Service Name',
                'category_id'       => 'Specialty / Category',
                'staff_id'          => 'Professional / Provider',
                'sub_type'          => 'Appointment Type (Online / In-person)',
                'price'             => 'Service Fee',
                'price_unit'        => 'Pricing Unit',
                'meta.online_link'  => 'Online Meeting Link',
                'meta.required_docs'=> 'Required Notes / Documents',
                'requirements'      => 'Cancellation Policy',
            ],

            'required' => [
                'title',
                'price',
                'sub_type',
            ],

            'rules' => [
                'title'              => 'required|string|max:255',
                'price'              => 'required|numeric|min:0',
                'sub_type'           => 'required|in:online,in-person,both',
                'duration_minutes'   => 'nullable|integer|min:15|max:480',
                'meta.online_link'   => 'nullable|url',
                'meta.required_docs' => 'nullable|string|max:500',
            ],

            'filters' => [
                'category_id' => ['type' => 'select', 'label' => 'Specialty / Category', 'source' => 'categories'],
                'staff_id'    => ['type' => 'select', 'label' => 'Professional',          'source' => 'staff'],
                'city'        => ['type' => 'text',   'label' => 'Location'],
                'sub_type'    => ['type' => 'select', 'label' => 'Online / In-person',
                                  'options' => ['online'=>'Online','in-person'=>'In-person','both'=>'Both']],
                'date'        => ['type' => 'date',   'label' => 'Preferred Date'],
                'time'        => ['type' => 'time',   'label' => 'Preferred Time'],
                'duration_min'=> ['type' => 'number', 'label' => 'Min Duration (min)'],
                'price_min'   => ['type' => 'number', 'label' => 'Min Price'],
                'price_max'   => ['type' => 'number', 'label' => 'Max Price'],
                'language'    => ['type' => 'select', 'label' => 'Language', 'source' => 'languages'],
                'rating_min'  => ['type' => 'number', 'label' => 'Min Rating'],
            ],

            'pricing_mode'      => self::PRICE_PER_APPOINTMENT,
            'availability_mode' => self::AVAIL_TIME_SLOT,
            'price_unit_label'  => 'per session',
            'has_staff'         => true,
            'has_extras'        => false,

            'meta' => [
                'filter_note'    => 'Show only professionals who offer the selected sub_type (online/in-person) + specialty + have available slots.',
                'sub_type_options' => ['online', 'in-person', 'both'],
            ],
        ];
    }

    // ── 7. Education / Training ───────────────────────────────────────

    private function educationTrainingConfig(): array
    {
        return [
            'fields' => [
                'title',
                'category_id',       // subject
                'staff_id',          // instructor
                'sub_type',          // online / in-person
                'meta.level',        // beginner / intermediate / advanced
                'date',
                'time_slot',
                'duration_minutes',
                'price',
                'capacity',          // group class
                'inventory',         // available seats
                'language',
                'location_enabled',
                'address_line', 'city', 'state', 'country',
                'meta.online_link',
                'meta.prerequisites',
                'description',
                'requirements',
                'thumbnail', 'cover',
                'status',
                'instant_booking',
                'allow_reschedule',
                'reviews_enabled',
                'waitlist_enabled',
            ],

            'field_labels' => [
                'title'              => 'Class / Course Title',
                'category_id'        => 'Subject / Category',
                'staff_id'           => 'Instructor',
                'sub_type'           => 'Delivery (Online / In-person)',
                'meta.level'         => 'Level',
                'capacity'           => 'Class Capacity',
                'inventory'          => 'Available Seats',
                'meta.online_link'   => 'Online Meeting Link',
                'meta.prerequisites' => 'Prerequisites',
                'requirements'       => 'Cancellation Policy',
            ],

            'required' => [
                'title',
                'price',
                'sub_type',
            ],

            'rules' => [
                'title'              => 'required|string|max:255',
                'price'              => 'required|numeric|min:0',
                'sub_type'           => 'required|in:online,in-person,both',
                'meta.level'         => 'nullable|in:beginner,intermediate,advanced,all',
                'capacity'           => 'nullable|integer|min:1',
                'inventory'          => 'nullable|integer|min:0',
                'duration_minutes'   => 'nullable|integer|min:15',
                'meta.online_link'   => 'nullable|url',
                'meta.prerequisites' => 'nullable|string|max:500',
            ],

            'filters' => [
                'category_id'   => ['type' => 'select', 'label' => 'Subject / Category', 'source' => 'categories'],
                'staff_id'      => ['type' => 'select', 'label' => 'Instructor',          'source' => 'staff'],
                'meta.level'    => ['type' => 'select', 'label' => 'Level',
                                    'options' => ['beginner'=>'Beginner','intermediate'=>'Intermediate','advanced'=>'Advanced']],
                'date'          => ['type' => 'date',   'label' => 'Date'],
                'time'          => ['type' => 'time',   'label' => 'Time'],
                'sub_type'      => ['type' => 'select', 'label' => 'Online / In-person',
                                    'options' => ['online'=>'Online','in-person'=>'In-person']],
                'price_min'     => ['type' => 'number', 'label' => 'Min Price'],
                'price_max'     => ['type' => 'number', 'label' => 'Max Price'],
                'language'      => ['type' => 'select', 'label' => 'Language', 'source' => 'languages'],
                'has_seats'     => ['type' => 'toggle', 'label' => 'Available Seats Only'],
                'rating_min'    => ['type' => 'number', 'label' => 'Min Rating'],
            ],

            'pricing_mode'      => self::PRICE_PER_SERVICE,
            'availability_mode' => self::AVAIL_TIME_SLOT,
            'price_unit_label'  => 'per session',
            'has_staff'         => true,
            'has_extras'        => false,

            'meta' => [
                'inventory_tracked' => true,
                'level_options'     => ['beginner', 'intermediate', 'advanced', 'all'],
                'filter_note'       => 'Show only classes matching subject + delivery type + date. Full classes shown as "Full" or excluded.',
            ],
        ];
    }

    // ── Default (fallback) ────────────────────────────────────────────

    private function defaultConfig(): array
    {
        return [
            'fields'            => ['title', 'category_id', 'price', 'description', 'status', 'language'],
            'field_labels'      => [],
            'required'          => ['title', 'price'],
            'rules'             => ['title' => 'required|string|max:255', 'price' => 'required|numeric|min:0'],
            'filters'           => ['city' => ['type' => 'text', 'label' => 'Location']],
            'pricing_mode'      => self::PRICE_PER_APPOINTMENT,
            'availability_mode' => self::AVAIL_TIME_SLOT,
            'price_unit_label'  => 'per booking',
            'has_staff'         => false,
            'has_extras'        => false,
            'meta'              => [],
        ];
    }
}
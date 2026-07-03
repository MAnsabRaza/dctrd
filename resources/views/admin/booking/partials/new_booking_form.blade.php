{{--
  resources/views/admin/booking/partials/new_booking_form.blade.php

  Dynamic form — booking_type select ke change par:
    1. Template-specific field sections show/hide ho jaate hain
    2. Required attributes dynamically set hote hain
    3. Field labels update hote hain
    4. Category dropdown SIRF selected Booking Type (parent) ke children/subcategories
       se filter ho ke populate hoti hai — ye is fix ka core hissa hai.
    5. Form puri tarah reset hoti hai naye booking pe

  templateConfigs (JSON) aur categoriesByParent (JSON) controller se pass hote hain.
--}}

@php
    $booking = $editBooking ?? null;
    $meta    = old('meta', $booking->meta ?? []);
    $tags    = old('tags', !empty($meta['tags']) ? implode(', ', (array)$meta['tags']) : '');
    $currentType = old('booking_type', $booking->booking_type ?? '');
    $currentCategoryId = old('category_id', $booking->category_id ?? '');

    $field = fn($name, $default = null) => old($name, !empty($booking) ? $booking->{$name} : $default);
    $metaField = fn($key, $default = null) => old("meta.$key", $meta[$key] ?? $default);
    $checked = function($name, $default = false) use ($booking) {
        return old($name, !empty($booking) ? (bool) $booking->{$name} : $default) ? 'checked' : '';
    };
@endphp

<form id="bookingAdminForm"
      action="{{ getAdminPanelUrl() }}/booking/{{ !empty($booking) ? $booking->id . '/update' : 'store' }}"
      method="POST"
      class="booking-admin-form">
    {{ csrf_field() }}
    <input type="hidden" name="creator_id" value="{{ auth()->id() }}">

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 1 — Booking Type Selection (always visible, loads first)
         Ye dropdown sirf PARENT categories / booking types dikhata hai.
         ══════════════════════════════════════════════════════════════ --}}
    <div class="booking-section">
        <h3 class="booking-section-title">Booking Type</h3>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label">Booking Type <span class="text-danger">*</span></label>
                    <select id="bookingTypeSelect"
                            name="booking_type"
                            data-plugin-selectTwo
                            class="form-control @error('booking_type') is-invalid @enderror">
                        <option value="">— Select Booking Type —</option>
                        @foreach($bookingTypeLabels ?? [] as $slug => $label)
                            <option value="{{ $slug }}"
                                {{ $currentType === $slug ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('booking_type')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                    <div class="text-muted text-small mt-1" id="bookingTypeNote"></div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label">Language</label>
                    <select name="language" data-plugin-selectTwo class="form-control @error('language') is-invalid @enderror">
                        @foreach($userLanguages ?? ['en' => 'English'] as $lang => $language)
                            <option value="{{ $lang }}" {{ $field('language', 'en') == $lang ? 'selected' : '' }}>
                                {{ $language }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 2 — Basic Information (always visible)
         ══════════════════════════════════════════════════════════════ --}}
    <div class="booking-section" id="section-basic">
        <h3 class="booking-section-title">Basic Information</h3>
        <div class="row">
            <div class="col-12 col-lg-6">

                <div class="form-group">
                    <label class="input-label js-field-label" data-field="title">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="newBookingTitle"
                           value="{{ $field('title') }}"
                           class="form-control @error('title') is-invalid @enderror"
                           required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="input-label">Slug</label>
                    <input type="text" name="slug" id="newBookingSlug"
                           value="{{ $field('slug') }}"
                           class="form-control @error('slug') is-invalid @enderror">
                    <div class="text-gray-500 text-small mt-1">Auto-generated from title. Must be unique.</div>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="input-label">Category (Subcategory / Template) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        {{--
                            NAYA BEHAVIOUR:
                            Ye select PEHLE khali/disabled hai. Jaise hi upar Booking Type
                            select hoga, JS isko usi parent ke children (subcategories) se
                            populate kar dega. Isliye yahan par server se sirf currently
                            saved value (edit mode mein) ek option ki tarah pre-inject ki
                            jaati hai, JS load hote hi ise repopulate/validate kar dega.
                        --}}
                        <select id="bookingCategorySelect" name="category_id" data-plugin-selectTwo
                                class="form-control @error('category_id') is-invalid @enderror"
                                {{ empty($currentType) ? 'disabled' : '' }}>
                            <option value="">
                                {{ empty($currentType) ? 'Pehle Booking Type select karein' : 'Select a Category' }}
                            </option>
                            @if(!empty($booking) && $booking->category)
                                <option value="{{ $booking->category->id }}" selected>
                                    {{ $booking->category->title }}
                                </option>
                            @endif
                        </select>
                        <div class="input-group-append">
                            <a href="{{ getAdminPanelUrl('/booking/categories') }}" class="btn btn-primary add-button">
                                <i class="fa fa-plus"></i>
                            </a>
                        </div>
                    </div>
                    <div class="text-gray-500 text-small mt-1">
                        Sirf usi Booking Type ki subcategories yahan dikhengi jo upar select ki gayi hai.
                    </div>
                    @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="input-label">Status <span class="text-danger">*</span></label>
                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                        <option value="">Select Status</option>
                        <option value="draft"     {{ $field('status', 'draft') == 'draft'     ? 'selected' : '' }}>Draft</option>
                        <option value="pending"   {{ $field('status') == 'pending'   ? 'selected' : '' }}>Pending</option>
                        <option value="published" {{ $field('status') == 'published' ? 'selected' : '' }}>Published</option>
                        <option value="rejected"  {{ $field('status') == 'rejected'  ? 'selected' : '' }}>Rejected</option>
                        <option value="inactive"  {{ $field('status') == 'inactive'  ? 'selected' : '' }}>Inactive</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="input-label">Thumbnail / Featured Image</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager"
                                    data-input="booking_thumbnail" data-preview="holder">
                                <i class="fa fa-upload"></i>
                            </button>
                        </div>
                        <input type="text" name="thumbnail" id="booking_thumbnail"
                               value="{{ $field('thumbnail') }}" class="form-control">
                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view"
                                    data-input="booking_thumbnail"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label">Cover Image</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager"
                                    data-input="booking_cover" data-preview="holder">
                                <i class="fa fa-upload"></i>
                            </button>
                        </div>
                        <input type="text" name="cover" id="booking_cover"
                               value="{{ $field('cover') }}" class="form-control">
                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view"
                                    data-input="booking_cover"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="requirements">
                        Cancellation / Policy
                    </label>
                    <textarea name="requirements" class="form-control" rows="4"
                              placeholder="Cancellation or rescheduling policy...">{{ $field('requirements') }}</textarea>
                </div>

                <div class="form-group">
                    <label class="input-label">Description</label>
                    <textarea name="description" class="summernote form-control"
                              placeholder="Detailed description (min 300 words)">{{ $field('description') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 3 — Location (toggle)
         ══════════════════════════════════════════════════════════════ --}}
    <div class="booking-section" id="section-location">
        <h3 class="booking-section-title">Location</h3>
        <div class="form-group d-flex align-items-center">
            <div class="custom-control custom-switch">
                <input type="checkbox" name="location_enabled" id="newBookingLocationSwitch"
                       value="on" class="custom-control-input"
                       {{ (old('location_enabled') == 'on' || (!empty($booking) && $booking->location_enabled)) ? 'checked' : '' }}>
                <label class="custom-control-label" for="newBookingLocationSwitch"></label>
            </div>
            <label for="newBookingLocationSwitch" class="mb-0 ml-2">Enable Location</label>
        </div>

        <div id="newBookingLocationPanel"
             style="{{ (old('location_enabled') == 'on' || (!empty($booking) && $booking->location_enabled)) ? '' : 'display:none' }}">
            @php $locationModel = $booking ?? null; @endphp
            @include('partials._location_picker', [
                'locationModel' => $locationModel,
                'addressName'   => 'address_line',
                'showAjaxSave'  => false,
                'pickerId'      => 'adminBookingLocationPicker'
            ])
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 4 — Template-specific fields (dynamic sections)
         Each section is hidden by default. JS reveals the right one.
         ══════════════════════════════════════════════════════════════ --}}

    {{-- ─── 4a: Staff / Provider (Beauty, Doctors, Professional, Automotive, Education) ─── --}}
    <div class="booking-section booking-type-section" data-template-section="staff" style="display:none">
        <h3 class="booking-section-title js-section-title" data-section="staff">Staff / Provider</h3>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="staff_id">Staff / Provider</label>
                    <select name="staff_id" data-plugin-selectTwo class="form-control">
                        <option value="">Select Staff / Provider</option>
                        @foreach($instructors ?? [] as $instructor)
                            <option value="{{ $instructor->id }}"
                                {{ $metaField('staff_id') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->full_name }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4b: Sub-type selector (Doctors, Professional, Automotive, Education) ─── --}}
    <div class="booking-section booking-type-section" data-template-section="sub-type" style="display:none">
        <h3 class="booking-section-title js-section-title" data-section="sub-type">Appointment / Service Type</h3>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="sub_type">Type</label>
                    <select name="sub_type" class="form-control js-sub-type-select">
                        <option value="">Select Type</option>
                        {{-- Options populated by JS based on template config --}}
                    </select>
                </div>
            </div>

            {{-- Online meeting link (shown when sub_type=online) --}}
            <div class="col-12 col-md-6 js-online-link-field" style="display:none">
                <div class="form-group">
                    <label class="input-label">Online Meeting Link</label>
                    <input type="url" name="meta[online_link]"
                           value="{{ $metaField('online_link') }}"
                           class="form-control" placeholder="https://meet.example.com/...">
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4c: Availability — Time Slot (Beauty, Doctor, Professional, Education) ─── --}}
    <div class="booking-section booking-type-section" data-template-section="time-slot" style="display:none">
        <h3 class="booking-section-title">Schedule & Availability</h3>
        <p class="text-muted text-small mb-3">
            Time slots are managed separately after saving. Set duration and buffer times here.
        </p>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="duration_minutes">
                        Duration (minutes) <span class="text-danger js-required-star" style="display:none">*</span>
                    </label>
                    <input type="number" name="duration_minutes" min="5" max="480"
                           value="{{ $field('duration_minutes') }}" class="form-control"
                           placeholder="e.g. 60">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Buffer Before (minutes)</label>
                    <input type="number" name="buffer_before" min="0"
                           value="{{ $field('buffer_before', 0) }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Buffer After (minutes)</label>
                    <input type="number" name="buffer_after" min="0"
                           value="{{ $field('buffer_after', 0) }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Lead Time (hours)</label>
                    <input type="number" name="lead_time_hours" min="0"
                           value="{{ $field('lead_time_hours', 0) }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Cutoff Time (hours)</label>
                    <input type="number" name="cutoff_time_hours" min="0"
                           value="{{ $field('cutoff_time_hours', 0) }}" class="form-control">
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4d: Availability — Date Range (Accommodation, Automotive Rental) ─── --}}
    <div class="booking-section booking-type-section" data-template-section="date-range" style="display:none">
        <h3 class="booking-section-title js-section-title" data-section="date-range">Availability Period</h3>
        <p class="text-muted text-small mb-3">
            Set the default date range for this listing. Customers will pick their own dates at checkout.
        </p>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.check_in_date">Check-in / Pickup Date</label>
                    <input type="date" name="meta[check_in_date]"
                           value="{{ $metaField('check_in_date') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.check_out_date">Check-out / Return Date</label>
                    <input type="date" name="meta[check_out_date]"
                           value="{{ $metaField('check_out_date') }}" class="form-control">
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4e: Accommodation — specific fields ─── --}}
    <div class="booking-section booking-type-section" data-template-section="accommodation" style="display:none">
        <h3 class="booking-section-title">Accommodation Details</h3>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Room / Unit Type</label>
                    <input type="text" name="meta[room_type]"
                           value="{{ $metaField('room_type') }}"
                           class="form-control" placeholder="e.g. Deluxe Room, Studio, Villa">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Price per Night <span class="text-danger">*</span></label>
                    <input type="number" name="price" step="0.01" min="0"
                           value="{{ $field('price', '0.00') }}"
                           class="form-control @error('price') is-invalid @enderror">
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Price per Extra Person (per night)</label>
                    <input type="number" name="price_per" step="0.01" min="0"
                           value="{{ $field('price_per') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label">Min Guests</label>
                    <input type="number" name="min_persons" min="1"
                           value="{{ $field('min_persons', 1) }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label">Max Guests (Adults) <span class="text-danger">*</span></label>
                    <input type="number" name="max_persons" min="1"
                           value="{{ $field('max_persons') }}" class="form-control js-required-for-type" data-type="accommodation">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label">Max Children</label>
                    <input type="number" name="max_children" min="0"
                           value="{{ $field('max_children') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label">Room Capacity</label>
                    <input type="number" name="capacity" min="1"
                           value="{{ $field('capacity') }}" class="form-control">
                </div>
            </div>
            <div class="col-12">
                <div class="form-group">
                    <label class="input-label">Amenities</label>
                    <div class="d-flex flex-wrap gap-2" id="amenitiesCheckboxes">
                        @php
                            $amenityOptions = ['wifi','pool','parking','gym','spa','breakfast','ac','kitchen','tv','washer'];
                            $selectedAmenities = (array)($meta['amenities'] ?? []);
                        @endphp
                        @foreach($amenityOptions as $amenity)
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" name="meta[amenities][]"
                                       id="amenity_{{ $amenity }}" value="{{ $amenity }}"
                                       class="custom-control-input"
                                       {{ in_array($amenity, $selectedAmenities) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="amenity_{{ $amenity }}">
                                    {{ ucfirst($amenity) }}
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4f: Events — specific fields ─── --}}
    <div class="booking-section booking-type-section" data-template-section="events" style="display:none">
        <h3 class="booking-section-title">Event Details</h3>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Total Capacity <span class="text-danger">*</span></label>
                    <input type="number" name="capacity" min="1"
                           value="{{ $field('capacity') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Available Tickets</label>
                    <input type="number" name="inventory" min="0"
                           value="{{ $field('inventory') }}" class="form-control"
                           placeholder="Leave blank = same as capacity">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Event Duration (minutes)</label>
                    <input type="number" name="duration_minutes" min="0"
                           value="{{ $field('duration_minutes') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Venue Type</label>
                    <select name="meta[venue_type]" class="form-control">
                        <option value="">Select</option>
                        <option value="indoor"   {{ $metaField('venue_type') == 'indoor'   ? 'selected' : '' }}>Indoor</option>
                        <option value="outdoor"  {{ $metaField('venue_type') == 'outdoor'  ? 'selected' : '' }}>Outdoor</option>
                        <option value="hybrid"   {{ $metaField('venue_type') == 'hybrid'   ? 'selected' : '' }}>Hybrid</option>
                        <option value="online"   {{ $metaField('venue_type') == 'online'   ? 'selected' : '' }}>Online</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Organizer / Provider</label>
                    <input type="text" name="meta[organizer]"
                           value="{{ $metaField('organizer') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Specifications</label>
                    <input type="text" name="meta[specifications]"
                           value="{{ $metaField('specifications') }}"
                           class="form-control" placeholder="e.g. Family-friendly, 18+, Outdoor shoes required">
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4g: Automotive — specific fields ─── --}}
    <div class="booking-section booking-type-section" data-template-section="automotive" style="display:none">
        <h3 class="booking-section-title">Automotive Details</h3>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Vehicle Type</label>
                    <input type="text" name="meta[vehicle_type]"
                           value="{{ $metaField('vehicle_type') }}"
                           class="form-control" placeholder="e.g. Sedan, SUV, Motorcycle">
                </div>
            </div>

            {{-- Rental fields --}}
            <div class="col-12 js-automotive-rental" style="display:none">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label">Pickup Location</label>
                            <input type="text" name="meta[pickup_location]"
                                   value="{{ $metaField('pickup_location') }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label">Drop-off Location</label>
                            <input type="text" name="meta[dropoff_location]"
                                   value="{{ $metaField('dropoff_location') }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label class="input-label">Vehicle Specs</label>
                            <input type="text" name="meta[vehicle_specs]"
                                   value="{{ $metaField('vehicle_specs') }}"
                                   class="form-control" placeholder="e.g. 5 seats, automatic, petrol">
                        </div>
                    </div>
                </div>
            </div>

            {{-- Service / mechanic fields --}}
            <div class="col-12 js-automotive-service" style="display:none">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label">Service Type</label>
                            <input type="text" name="meta[service_type]"
                                   value="{{ $metaField('service_type') }}"
                                   class="form-control" placeholder="e.g. Oil change, Brake service, AC repair">
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label class="input-label">Required Notes / Vehicle Details</label>
                            <input type="text" name="meta[required_notes]"
                                   value="{{ $metaField('required_notes') }}"
                                   class="form-control" placeholder="License plate, model year, issue description">
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4h: Education — specific fields ─── --}}
    <div class="booking-section booking-type-section" data-template-section="education" style="display:none">
        <h3 class="booking-section-title">Education / Training Details</h3>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Level</label>
                    <select name="meta[level]" class="form-control">
                        <option value="">Select Level</option>
                        <option value="beginner"     {{ $metaField('level') == 'beginner'     ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ $metaField('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced"     {{ $metaField('level') == 'advanced'     ? 'selected' : '' }}>Advanced</option>
                        <option value="all"          {{ $metaField('level') == 'all'          ? 'selected' : '' }}>All Levels</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Class Capacity</label>
                    <input type="number" name="capacity" min="1"
                           value="{{ $field('capacity') }}" class="form-control"
                           placeholder="Leave blank for unlimited">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Available Seats</label>
                    <input type="number" name="inventory" min="0"
                           value="{{ $field('inventory') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="input-label">Prerequisites</label>
                    <input type="text" name="meta[prerequisites]"
                           value="{{ $metaField('prerequisites') }}"
                           class="form-control" placeholder="e.g. Basic English required">
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4i: Professional Services — specific fields ─── --}}
    <div class="booking-section booking-type-section" data-template-section="professional" style="display:none">
        <h3 class="booking-section-title">Professional Service Details</h3>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label class="input-label">Required Notes / Documents</label>
                    <textarea name="meta[required_docs]" class="form-control" rows="3"
                              placeholder="e.g. Please bring your last tax return, NDA required">{{ $metaField('required_docs') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4j: Doctors — specific fields ─── --}}
    <div class="booking-section booking-type-section" data-template-section="doctors" style="display:none">
        <h3 class="booking-section-title">Doctor / Clinic Details</h3>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Service Type</label>
                    <select name="meta[appointment_type]" class="form-control">
                        <option value="">Select</option>
                        <option value="consultation" {{ $metaField('appointment_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                        <option value="diagnostic"   {{ $metaField('appointment_type') == 'diagnostic'   ? 'selected' : '' }}>Diagnostic</option>
                        <option value="therapy"      {{ $metaField('appointment_type') == 'therapy'      ? 'selected' : '' }}>Therapy</option>
                        <option value="checkup"      {{ $metaField('appointment_type') == 'checkup'      ? 'selected' : '' }}>Check-up</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Payment Option</label>
                    <select name="meta[payment_option]" class="form-control">
                        <option value="">Select</option>
                        <option value="per_appointment" {{ $metaField('payment_option') == 'per_appointment' ? 'selected' : '' }}>Per Appointment</option>
                        <option value="quote_based"     {{ $metaField('payment_option') == 'quote_based'     ? 'selected' : '' }}>Quote Based</option>
                        <option value="insurance"       {{ $metaField('payment_option') == 'insurance'       ? 'selected' : '' }}>Insurance</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    {{-- ─── 4k: Beauty/Spa — extras ─── --}}
    <div class="booking-section booking-type-section" data-template-section="beauty-extras" style="display:none">
        <h3 class="booking-section-title">Extras / Add-ons</h3>
        <p class="text-muted text-small mb-3">
            Add optional extras customers can select at checkout.
        </p>
        <div id="extrasContainer">
            @php $extras = $meta['extras'] ?? []; @endphp
            @foreach($extras as $i => $extra)
                <div class="extra-row d-flex gap-2 mb-2">
                    <input type="text"   name="extras[{{ $i }}][name]"  value="{{ $extra['name']  ?? '' }}" class="form-control" placeholder="Extra name">
                    <input type="number" name="extras[{{ $i }}][price]" value="{{ $extra['price'] ?? '' }}" class="form-control" placeholder="Price" min="0" step="0.01">
                    <button type="button" class="btn btn-sm btn-danger remove-extra">✕</button>
                </div>
            @endforeach
        </div>
        <button type="button" class="btn btn-sm btn-outline-primary mt-2" id="addExtraBtn">
            + Add Extra
        </button>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 5 — Pricing (always visible, labels change per type)
         ══════════════════════════════════════════════════════════════ --}}
    <div class="booking-section" id="section-pricing">
        <h3 class="booking-section-title">Pricing</h3>
        <div class="row">
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="price">
                        Base Price <span class="text-danger">*</span>
                    </label>
                    <input type="number" name="price" step="0.01" min="0"
                           value="{{ $field('price', '0.00') }}"
                           class="form-control @error('price') is-invalid @enderror" required>
                    <div class="text-muted text-small mt-1 js-price-unit-label">per booking</div>
                    @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label">Discount / Display Price</label>
                    <input type="number" name="discount_price" step="0.01" min="0"
                           value="{{ $field('discount_price') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label">Price Unit</label>
                    <input type="text" name="price_unit"
                           value="{{ $field('price_unit') }}"
                           class="form-control js-price-unit-input"
                           placeholder="e.g. per night">
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label">Currency</label>
                    <select name="currency" data-plugin-selectTwo class="form-control">
                        @foreach(['USD','EUR','GBP','PKR','AED','SAR','INR'] as $cur)
                            <option value="{{ $cur }}" {{ $field('currency', 'USD') == $cur ? 'selected' : '' }}>
                                {{ $cur }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-3">
                <div class="form-group">
                    <label class="input-label">Tax (%)</label>
                    <input type="number" name="tax" step="0.01" min="0"
                           value="{{ $field('tax', '0.00') }}" class="form-control">
                </div>
            </div>

            {{-- Deposit --}}
            <div class="col-12 col-md-3">
                <div class="form-group d-flex align-items-center mt-4">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="deposit_enabled" id="booking_deposit_enabled"
                               value="1" class="custom-control-input" {{ $checked('deposit_enabled', false) }}>
                        <label class="custom-control-label" for="booking_deposit_enabled"></label>
                    </div>
                    <label for="booking_deposit_enabled" class="mb-0 ml-2">Deposit Required</label>
                </div>
            </div>
        </div>

        <div id="bookingDepositPanel" style="{{ $checked('deposit_enabled') ? '' : 'display:none' }}">
            <div class="row">
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label class="input-label">Deposit Amount</label>
                        <input type="number" name="deposit_amount" step="0.01" min="0"
                               value="{{ $field('deposit_amount') }}" class="form-control">
                    </div>
                </div>
                <div class="col-12 col-md-3">
                    <div class="form-group">
                        <label class="input-label">Deposit Type</label>
                        <select name="deposit_type" class="form-control">
                            <option value="percent" {{ $field('deposit_type', 'percent') == 'percent' ? 'selected' : '' }}>Percent</option>
                            <option value="fixed"   {{ $field('deposit_type') == 'fixed'   ? 'selected' : '' }}>Fixed Amount</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- ══════════════════════════════════════════════════════════════
         SECTION 6 — Booking Options (always visible)
         ══════════════════════════════════════════════════════════════ --}}
    <div class="booking-section" id="section-options">
        <h3 class="booking-section-title">Booking Options</h3>
        <div class="row">
            @foreach([
                'instant_booking'  => ['Instant Booking',   true],
                'requires_approval'=> ['Requires Approval', false],
                'allow_reschedule' => ['Allow Reschedule',  true],
                'waitlist_enabled' => ['Waitlist Enabled',  false],
                'children_allowed' => ['Children Allowed',  true],
                'forum_enabled'    => ['Forum Enabled',     false],
                'comments_enabled' => ['Comments Enabled',  true],
                'reviews_enabled'  => ['Reviews Enabled',   true],
                'featured'         => ['Featured',          false],
            ] as $name => [$label, $default])
                <div class="col-12 col-md-4">
                    <div class="form-group d-flex align-items-center">
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="{{ $name }}" id="booking_{{ $name }}"
                                   value="1" class="custom-control-input" {{ $checked($name, $default) }}>
                            <label class="custom-control-label" for="booking_{{ $name }}"></label>
                        </div>
                        <label for="booking_{{ $name }}" class="mb-0 ml-2">{{ $label }}</label>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="form-group mt-2">
            <label class="input-label">Checkout Message</label>
            <textarea name="checkout_message" class="form-control" rows="3">{{ $field('checkout_message') }}</textarea>
        </div>
        <div class="form-group">
            <label class="input-label">Message to Reviewer</label>
            <textarea name="reviewer_message" class="form-control" rows="4">{{ $field('reviewer_message') }}</textarea>
        </div>
        <div class="form-group">
            <label class="input-label">Tags</label>
            <input type="text" name="tags" value="{{ $tags }}"
                   class="form-control" placeholder="tag1, tag2, tag3">
        </div>
    </div>

    {{-- Submit --}}
    <div class="d-flex align-items-center gap-3 mt-4">
        <button type="submit" class="btn btn-success px-5">
            <i class="fa fa-save mr-2"></i> Save Booking
        </button>
        <a href="{{ getAdminPanelUrl('/booking/list') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

{{-- ══════════════════════════════════════════════════════════════════
     JavaScript — Dynamic form behavior
     ══════════════════════════════════════════════════════════════════ --}}
<script>
(function () {
    'use strict';

    // Template configs passed from controller as JSON
    var TEMPLATE_CONFIGS = {!! $templateConfigs ?? '{}' !!};

    // NAYA: Booking Type (slug) => parent category id map, aur
    // parent category id => uske children [{id,title}] map.
    // Ye dono milke Category dropdown ko dynamically filter karte hain.
    var TYPE_CATEGORY_MAP    = {!! json_encode($bookingTypeCategoryMap ?? []) !!};
    var CATEGORIES_BY_PARENT = {!! $categoriesByParent ?? '{}' !!};
    var CURRENT_CATEGORY_ID  = {!! !empty($currentCategoryId) ? json_encode((string) $currentCategoryId) : 'null' !!};

    // Which sections to show per template type
    var TYPE_SECTIONS = {
        'beauty-spa': ['staff', 'time-slot', 'beauty-extras'],
        'doctors-clinics': ['staff', 'sub-type', 'time-slot', 'doctors'],
        'events': ['time-slot', 'events'],
        'accommodation': ['date-range', 'accommodation'],
        'automotive': ['sub-type', 'automotive'],
        'professional-services': ['staff', 'sub-type', 'time-slot', 'professional'],
        'education-training': ['staff', 'sub-type', 'time-slot', 'education'],
    };

    // Sub-type options per template
    var SUB_TYPE_OPTIONS = {
        'doctors-clinics':        [['physical','Physical'],['online','Online'],['both','Both']],
        'automotive':             [['rental','Rental / Car Hire'],['service','Mechanic / Service Appointment']],
        'professional-services':  [['in-person','In-person'],['online','Online'],['both','Both']],
        'education-training':     [['in-person','In-person'],['online','Online'],['both','Both']],
    };

    // Section heading overrides per template
    var SECTION_TITLES = {
        'accommodation': { 'date-range': 'Availability Period (Check-in / Check-out)' },
        'automotive':    { 'date-range': 'Pickup & Return Period',
                           'sub-type':   'Booking Sub-type (Rental or Service)' },
    };

    // ─── Category dropdown — filtered by selected Booking Type ────────

    function populateCategoryOptions(type, selectedCategoryId) {
        var select = document.getElementById('bookingCategorySelect');
        if (!select) return;

        var parentId = TYPE_CATEGORY_MAP[type];
        var children = (parentId && CATEGORIES_BY_PARENT[parentId]) ? CATEGORIES_BY_PARENT[parentId] : [];

        select.innerHTML = '';

        if (!type) {
            select.disabled = true;
            select.appendChild(makeOption('', 'Pehle Booking Type select karein'));
            triggerSelectTwoRefresh(select);
            return;
        }

        select.disabled = false;

        if (!children.length) {
            select.appendChild(makeOption('', 'Is Booking Type ke liye koi subcategory nahi mili'));
            triggerSelectTwoRefresh(select);
            return;
        }

        select.appendChild(makeOption('', 'Select a Category'));
        children.forEach(function (cat) {
            var isSelected = selectedCategoryId && String(selectedCategoryId) === String(cat.id);
            select.appendChild(makeOption(cat.id, cat.title, isSelected));
        });

        triggerSelectTwoRefresh(select);
    }

    function makeOption(value, label, selected) {
        var opt = document.createElement('option');
        opt.value = value;
        opt.textContent = label;
        if (selected) opt.selected = true;
        return opt;
    }

    function triggerSelectTwoRefresh(select) {
        // Agar select2 (data-plugin-selectTwo) is field par init ho chuka hai,
        // to naye options ke baad usko refresh karna zaroori hai.
        if (window.jQuery) {
            var $select = window.jQuery(select);
            if ($select.data('select2')) {
                $select.trigger('change.select2');
            } else {
                $select.trigger('change');
            }
        }
    }

    // ─── Main switch function ─────────────────────────────────────────

    function applyTemplate(type) {
        if (!type) {
            hideAllSections();
            populateCategoryOptions('', null);
            return;
        }

        var config   = TEMPLATE_CONFIGS[type] || {};
        var sections = TYPE_SECTIONS[type]    || [];

        // 1. Hide all type-specific sections
        hideAllSections();

        // 2. Show relevant sections for this type
        sections.forEach(function (sectionKey) {
            var el = document.querySelector('[data-template-section="' + sectionKey + '"]');
            if (el) el.style.display = '';
        });

        // 3. Update price unit label
        var priceLabel = config.price_unit_label || 'per booking';
        document.querySelectorAll('.js-price-unit-label').forEach(function (el) {
            el.textContent = priceLabel;
        });
        var priceUnitInput = document.querySelector('.js-price-unit-input');
        if (priceUnitInput && !priceUnitInput.value) {
            priceUnitInput.value = priceLabel;
        }

        // 4. Update field labels from config
        if (config.field_labels) {
            document.querySelectorAll('.js-field-label').forEach(function (el) {
                var fieldKey = el.dataset.field;
                if (config.field_labels[fieldKey]) {
                    // Keep the asterisk span if it exists
                    var star = el.querySelector('.text-danger');
                    el.textContent = config.field_labels[fieldKey] + ' ';
                    if (star) el.appendChild(star);
                }
            });
        }

        // 5. Build sub-type select options
        buildSubTypeOptions(type);

        // 6. NAYA: Category dropdown ko sirf isi parent ke children se filter karo
        populateCategoryOptions(type, CURRENT_CATEGORY_ID);

        // 7. Apply section title overrides
        var titleOverrides = SECTION_TITLES[type] || {};
        Object.keys(titleOverrides).forEach(function (sectionKey) {
            var el = document.querySelector('[data-template-section="' + sectionKey + '"] .booking-section-title');
            if (el) el.textContent = titleOverrides[sectionKey];
        });

        // 8. Update the type note under the select
        var note = config.meta && config.meta.filter_note ? config.meta.filter_note : '';
        var noteEl = document.getElementById('bookingTypeNote');
        if (noteEl) noteEl.textContent = note;
    }

    function hideAllSections() {
        document.querySelectorAll('[data-template-section]').forEach(function (el) {
            el.style.display = 'none';
        });
    }

    // ─── Sub-type options ─────────────────────────────────────────────

    function buildSubTypeOptions(type) {
        var select  = document.querySelector('.js-sub-type-select');
        var options = SUB_TYPE_OPTIONS[type];

        if (!select || !options) return;

        // Clear existing options except blank
        select.innerHTML = '<option value="">Select Type</option>';
        options.forEach(function (opt) {
            var option    = document.createElement('option');
            option.value  = opt[0];
            option.textContent = opt[1];
            select.appendChild(option);
        });

        // Show/hide online link when online selected
        select.addEventListener('change', function () {
            toggleOnlineLink(this.value);
            // Automotive: show rental vs service fields
            if (type === 'automotive') {
                toggleAutomotiveFields(this.value);
            }
        });
    }

    function toggleOnlineLink(val) {
        document.querySelectorAll('.js-online-link-field').forEach(function (el) {
            el.style.display = (val === 'online' || val === 'both') ? '' : 'none';
        });
    }

    function toggleAutomotiveFields(val) {
        document.querySelectorAll('.js-automotive-rental').forEach(function (el) {
            el.style.display = val === 'rental' ? '' : 'none';
        });
        document.querySelectorAll('.js-automotive-service').forEach(function (el) {
            el.style.display = val === 'service' ? '' : 'none';
        });
    }

    // ─── Slug auto-generation ─────────────────────────────────────────

    function slugify(str) {
        return str.toLowerCase().trim()
            .replace(/[^a-z0-9\s-]/g, '')
            .replace(/\s+/g, '-')
            .replace(/-+/g, '-');
    }

    var titleInput = document.getElementById('newBookingTitle');
    var slugInput  = document.getElementById('newBookingSlug');

    if (titleInput && slugInput) {
        titleInput.addEventListener('input', function () {
            if (!slugInput.value || slugInput.dataset.autoGenerated === 'true') {
                slugInput.value = slugify(this.value);
                slugInput.dataset.autoGenerated = 'true';
            }
        });
        slugInput.addEventListener('input', function () {
            this.dataset.autoGenerated = 'false';
        });
    }

    // ─── Location panel toggle ────────────────────────────────────────

    var locationSwitch = document.getElementById('newBookingLocationSwitch');
    var locationPanel  = document.getElementById('newBookingLocationPanel');

    if (locationSwitch && locationPanel) {
        locationSwitch.addEventListener('change', function () {
            locationPanel.style.display = this.checked ? '' : 'none';
        });
    }

    // ─── Deposit panel toggle ─────────────────────────────────────────

    var depositSwitch = document.getElementById('booking_deposit_enabled');
    var depositPanel  = document.getElementById('bookingDepositPanel');

    if (depositSwitch && depositPanel) {
        depositSwitch.addEventListener('change', function () {
            depositPanel.style.display = this.checked ? '' : 'none';
        });
    }

    // ─── Extras (Beauty/Spa) ──────────────────────────────────────────

    var extrasContainer = document.getElementById('extrasContainer');
    var addExtraBtn     = document.getElementById('addExtraBtn');
    var extraIndex      = {{ count($meta['extras'] ?? []) }};

    if (addExtraBtn && extrasContainer) {
        addExtraBtn.addEventListener('click', function () {
            var row = document.createElement('div');
            row.className = 'extra-row d-flex gap-2 mb-2';
            row.innerHTML =
                '<input type="text"   name="extras[' + extraIndex + '][name]"  class="form-control" placeholder="Extra name">' +
                '<input type="number" name="extras[' + extraIndex + '][price]" class="form-control" placeholder="Price" min="0" step="0.01">' +
                '<button type="button" class="btn btn-sm btn-danger remove-extra">✕</button>';
            extrasContainer.appendChild(row);
            extraIndex++;
        });

        extrasContainer.addEventListener('click', function (e) {
            if (e.target.classList.contains('remove-extra')) {
                e.target.closest('.extra-row').remove();
            }
        });
    }

    // ─── Booking type change ──────────────────────────────────────────

    var typeSelect = document.getElementById('bookingTypeSelect');

    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            // Naya Booking Type select hote hi purani category selection clear
            // karo (kyunki wo purane parent ki thi, naye parent se match nahi karti).
            CURRENT_CATEGORY_ID = null;
            applyTemplate(this.value);
        });

        // On load (create ya edit dono par) — apply saved/selected type
        applyTemplate(typeSelect.value);

        // Restore sub_type if editing
        @if(!empty($booking) && $booking->sub_type)
            var subTypeSelect = document.querySelector('.js-sub-type-select');
            if (subTypeSelect) {
                subTypeSelect.value = '{{ $booking->sub_type }}';
                toggleOnlineLink('{{ $booking->sub_type }}');
                toggleAutomotiveFields('{{ $booking->sub_type }}');
            }
        @endif
    }

    // ─── Tags: auto-comma on Enter ────────────────────────────────────

    var tagsInput = document.querySelector('input[name="tags"]');
    if (tagsInput) {
        tagsInput.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                var val = this.value.trim();
                if (val && !val.endsWith(',')) this.value = val + ', ';
            }
        });
    }

})();
</script>
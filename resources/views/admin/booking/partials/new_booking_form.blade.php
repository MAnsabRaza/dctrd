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

            <div class="col-12 col-lg-6">
                <div class="form-group" data-field-key="category_id">
                    <label class="input-label js-field-label" data-field="category_id">Category (Subcategory / Template) <span class="text-danger">*</span></label>
                    <div class="input-group">
                        <select id="bookingCategorySelect" name="category_id" data-plugin-selectTwo
                                class="form-control @error('category_id') is-invalid @enderror"
                                {{ empty($currentType) ? 'disabled' : '' }}>
                            <option value="">
                                {{ empty($currentType) ? 'Pehle Booking Type select karein' : 'Select a Category' }}
                            </option>
                            @if(!empty($booking) && $booking->category)
                                <option value="{{ $booking->category->id }}" data-slug="{{ $booking->category->slug }}" selected>
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
                    <div class="text-primary text-small mt-1" id="subTemplateNote"></div>
                    @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section" id="section-basic">
        <h3 class="booking-section-title">Basic Information</h3>
        <div class="row">
            <div class="col-12 col-lg-6">

                <div class="form-group" data-field-key="title">
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
                <div class="form-group" data-field-key="requirements">
                    <label class="input-label js-field-label" data-field="requirements">
                        Cancellation / Policy <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <textarea name="requirements" class="form-control" rows="4"
                              placeholder="Cancellation or rescheduling policy...">{{ $field('requirements') }}</textarea>
                </div>

                <div class="form-group" data-field-key="description">
                    <label class="input-label js-field-label" data-field="description">Description <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <textarea name="description" class="summernote form-control"
                              placeholder="Detailed description (min 300 words)">{{ $field('description') }}</textarea>
                </div>
            </div>
        </div>
    </div>

   
    <div class="booking-section" id="section-location" data-field-key="location_enabled">
        <h3 class="booking-section-title">Location <span class="text-danger js-dynamic-required" style="display:none">*</span></h3>
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
                <div class="form-group" data-field-key="staff_id">
                    <label class="input-label js-field-label" data-field="staff_id">Staff / Provider <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
                <div class="form-group" data-field-key="sub_type">
                    <label class="input-label js-field-label" data-field="sub_type">Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="sub_type" class="form-control js-sub-type-select">
                        <option value="">Select Type</option>
                        {{-- Options populated by JS based on template config --}}
                    </select>
                </div>
            </div>

            {{-- Online meeting link (shown when sub_type=online) --}}
            <div class="col-12 col-md-6 js-online-link-field" style="display:none" data-field-key="meta.online_link">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.online_link">Online Meeting Link <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-4" data-field-key="duration_minutes">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="duration_minutes">
                        Duration (minutes) <span class="text-danger js-dynamic-required" style="display:none">*</span>
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
            <div class="col-12 col-md-4" data-field-key="meta.check_in_date">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.check_in_date">Check-in / Pickup Date <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="date" name="meta[check_in_date]"
                           value="{{ $metaField('check_in_date') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="meta.check_out_date">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.check_out_date">Check-out / Return Date <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-4" data-field-key="meta.room_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.room_type">Room / Unit Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="text" name="meta[room_type]"
                           value="{{ $metaField('room_type') }}"
                           class="form-control" placeholder="e.g. Deluxe Room, Studio, Villa">
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="price">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="price">Price per Night <span class="text-danger">*</span></label>
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
            <div class="col-12 col-md-3" data-field-key="min_persons">
                <div class="form-group">
                    <label class="input-label">Min Guests</label>
                    <input type="number" name="min_persons" min="1"
                           value="{{ $field('min_persons', 1) }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-3" data-field-key="max_persons">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="max_persons">Max Guests (Adults) <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="number" name="max_persons" min="1"
                           value="{{ $field('max_persons') }}" class="form-control js-required-for-type" data-type="accommodation">
                </div>
            </div>
            <div class="col-12 col-md-3" data-field-key="max_children">
                <div class="form-group">
                    <label class="input-label">Max Children</label>
                    <input type="number" name="max_children" min="0"
                           value="{{ $field('max_children') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-3" data-field-key="capacity">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="capacity">Room Capacity <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="number" name="capacity" min="1"
                           value="{{ $field('capacity') }}" class="form-control">
                </div>
            </div>
            <div class="col-12" data-field-key="meta.amenities">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.amenities">Amenities <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-4" data-field-key="inventory">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="inventory">Available Tickets <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-4" data-field-key="meta.venue_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.venue_type">Venue Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="meta[venue_type]" class="form-control">
                        <option value="">Select</option>
                        <option value="indoor"   {{ $metaField('venue_type') == 'indoor'   ? 'selected' : '' }}>Indoor</option>
                        <option value="outdoor"  {{ $metaField('venue_type') == 'outdoor'  ? 'selected' : '' }}>Outdoor</option>
                        <option value="hybrid"   {{ $metaField('venue_type') == 'hybrid'   ? 'selected' : '' }}>Hybrid</option>
                        <option value="online"   {{ $metaField('venue_type') == 'online'   ? 'selected' : '' }}>Online</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="meta.organizer">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.organizer">Organizer / Provider <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="text" name="meta[organizer]"
                           value="{{ $metaField('organizer') }}" class="form-control">
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="meta.specifications">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.specifications">Specifications <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-4" data-field-key="meta.vehicle_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.vehicle_type">Vehicle Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="text" name="meta[vehicle_type]"
                           value="{{ $metaField('vehicle_type') }}"
                           class="form-control" placeholder="e.g. Sedan, SUV, Motorcycle">
                </div>
            </div>

            {{-- Rental fields --}}
            <div class="col-12 js-automotive-rental" style="display:none">
                <div class="row">
                    <div class="col-md-4" data-field-key="meta.pickup_location">
                        <div class="form-group">
                            <label class="input-label js-field-label" data-field="meta.pickup_location">Pickup Location <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                            <input type="text" name="meta[pickup_location]"
                                   value="{{ $metaField('pickup_location') }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4" data-field-key="meta.dropoff_location">
                        <div class="form-group">
                            <label class="input-label js-field-label" data-field="meta.dropoff_location">Drop-off Location <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                            <input type="text" name="meta[dropoff_location]"
                                   value="{{ $metaField('dropoff_location') }}" class="form-control">
                        </div>
                    </div>
                    <div class="col-md-4" data-field-key="meta.vehicle_specs">
                        <div class="form-group">
                            <label class="input-label js-field-label" data-field="meta.vehicle_specs">Vehicle Specs <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
                    <div class="col-md-6" data-field-key="meta.service_type">
                        <div class="form-group">
                            <label class="input-label js-field-label" data-field="meta.service_type">Service Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                            <input type="text" name="meta[service_type]"
                                   value="{{ $metaField('service_type') }}"
                                   class="form-control" placeholder="e.g. Oil change, Brake service, AC repair">
                        </div>
                    </div>
                    <div class="col-md-6" data-field-key="meta.required_notes">
                        <div class="form-group">
                            <label class="input-label js-field-label" data-field="meta.required_notes">Required Notes / Vehicle Details <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-4" data-field-key="meta.level">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.level">Level <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-6" data-field-key="meta.prerequisites">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.prerequisites">Prerequisites <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-6" data-field-key="meta.required_docs">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.required_docs">Required Notes / Documents <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
            <div class="col-12 col-md-4" data-field-key="meta.appointment_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.appointment_type">Service Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="meta[appointment_type]" class="form-control">
                        <option value="">Select</option>
                        <option value="consultation" {{ $metaField('appointment_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                        <option value="diagnostic"   {{ $metaField('appointment_type') == 'diagnostic'   ? 'selected' : '' }}>Diagnostic</option>
                        <option value="therapy"      {{ $metaField('appointment_type') == 'therapy'      ? 'selected' : '' }}>Therapy</option>
                        <option value="checkup"      {{ $metaField('appointment_type') == 'checkup'      ? 'selected' : '' }}>Check-up</option>
                    </select>
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="meta.payment_option">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.payment_option">Payment Option <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
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
    <div class="booking-section booking-type-section" data-template-section="beauty-extras" style="display:none" data-field-key="extras">
        <h3 class="booking-section-title">Extras / Add-ons <span class="text-danger js-dynamic-required" style="display:none">*</span></h3>
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
            <div class="col-12 col-md-3" data-field-key="deposit_enabled">
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

    // Template configs passed from controller as JSON (Booking Type level)
    var TEMPLATE_CONFIGS = {!! $templateConfigs ?? '{}' !!};

    // NAYA: Sub-template configs (Category level) — key = category slug.
    // Ye woh 23 templates hain (Doctor Appointment, Clinic Visit, ...).
    var SUB_TEMPLATE_CONFIGS = {!! $subTemplateConfigs ?? '{}' !!};

    // Booking Type (slug) => parent category id map, aur
    // parent category id => uske children [{id,title,slug}] map.
    var TYPE_CATEGORY_MAP    = {!! json_encode($bookingTypeCategoryMap ?? []) !!};
    var CATEGORIES_BY_PARENT = {!! $categoriesByParent ?? '{}' !!};
    var CURRENT_CATEGORY_ID  = {!! !empty($currentCategoryId) ? json_encode((string) $currentCategoryId) : 'null' !!};
    var CURRENT_SUB_TYPE     = {!! json_encode(old('sub_type', $booking->sub_type ?? '')) !!};

    // ── FIX: ye fields kabhi hide nahi hoti — chahe kisi bhi sub-template
    // (category level) ke required/optional array mein likhi hon ya na hon.
    // Pehle bug ye tha ke jaise hi category select hoti thi, "category_id"
    // field khud apne aap chip jaati thi kyunki wo kisi sub-template ki
    // required/optional list mein nahi hoti — sirf uska naam/label change
    // hona chahiye, hide nahi honi chahiye.
    var ALWAYS_VISIBLE_FIELD_KEYS = ['category_id', 'title', 'price', 'description', 'requirements'];

    // Currently active Booking-Type level field labels — jab category
    // deselect ho ya kisi aisi category pe switch ho jo kisi 23-template se
    // match nahi karti, to labels wapas isi par reset hote hain (sub-template
    // ka purana overridden label chipka nahi rehta).
    var CURRENT_TYPE_FIELD_LABELS = {};

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
            select.appendChild(makeOption(cat.id, cat.title, isSelected, cat.slug));
        });

        triggerSelectTwoRefresh(select);
    }

    function makeOption(value, label, selected, slug) {
        var opt = document.createElement('option');
        opt.value = value;
        opt.textContent = label;
        if (selected) opt.selected = true;
        if (slug) opt.dataset.slug = slug;
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

    // ── Field label helper ──────────────────────────────────────────
    // Field group ke andar (chahe wo `[data-field-key]` wala element khud
    // ho ya uske andar) `.input-label` dhoondh kar sirf uska text change
    // karta hai, jabke required-star (span) ya icon jaise child elements
    // ko preserve karta hai (unhe wapas label mein append kar deta hai).
    function updateFieldLabel(containerEl, newLabel) {
        if (!newLabel) return;
        var label = containerEl.querySelector('label.input-label');
        if (!label) return;

        var keepEls = Array.prototype.slice.call(label.querySelectorAll('span, i'));
        label.textContent = newLabel + ' ';
        keepEls.forEach(function (node) { label.appendChild(node); });
    }

    // ─── Main switch function (Booking Type level) ─────────────────────

    function applyTemplate(type, options) {
        options = options || {};
        var shouldPopulateCategory = options.populateCategory !== false;
        var shouldResetSubTemplate = options.resetSubTemplate !== false;

        if (!type) {
            hideAllSections();
            if (shouldPopulateCategory) {
                populateCategoryOptions('', null);
            }
            CURRENT_TYPE_FIELD_LABELS = {};
            if (shouldResetSubTemplate) {
                resetSubTemplate();
            }
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

        // 3. Update price unit label (default, category select ye override kar sakta hai)
        var priceLabel = config.price_unit_label || 'per booking';
        document.querySelectorAll('.js-price-unit-label').forEach(function (el) {
            el.textContent = priceLabel;
        });
        var priceUnitInput = document.querySelector('.js-price-unit-input');
        if (priceUnitInput && !priceUnitInput.value) {
            priceUnitInput.value = priceLabel;
        }

        // 4. Update field labels from config (Booking Type level defaults)
        CURRENT_TYPE_FIELD_LABELS = config.field_labels || {};
        if (config.field_labels) {
            document.querySelectorAll('.js-field-label').forEach(function (el) {
                var fieldKey = el.dataset.field;
                if (config.field_labels[fieldKey]) {
                    // Keep the asterisk span(s) if they exist
                    var stars = el.querySelectorAll('.text-danger');
                    el.textContent = config.field_labels[fieldKey] + ' ';
                    stars.forEach(function (star) { el.appendChild(star); });
                }
            });
        }

        // 5. Build sub-type select options
        buildSubTypeOptions(type);

        // 6. Category dropdown ko sirf isi parent ke children se filter karo
        if (shouldPopulateCategory) {
            populateCategoryOptions(type, CURRENT_CATEGORY_ID);
        }

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

        // 9. Booking Type badalte hi purana category-level (sub-template)
        //    filtering reset kar do — jab tak naya category select na ho.
        if (shouldResetSubTemplate) {
            resetSubTemplate();
        }
    }

    function hideAllSections() {
        document.querySelectorAll('[data-template-section]').forEach(function (el) {
            el.style.display = 'none';
        });
    }

    // ─── NAYA: Sub-template (Category level) switch function ───────────
    //
    // Jab admin Category select kare (e.g. "Doctor Appointment"), is
    // function ko us category ke `slug` ke sath call kiya jata hai.
    // Ye SUB_TEMPLATE_CONFIGS se us template ki config nikalta hai aur:
    //   - jo fields us template ke "required" mein hain -> show + required
    //   - jo fields "optional" mein hain               -> show, required nahi
    //   - jo fields dono mein nahi hain (irrelevant)     -> hide kar deta hai
    //   - EXCEPTION: ALWAYS_VISIBLE_FIELD_KEYS (category_id, title, price,
    //     description, requirements) kabhi hide nahi hoti — sirf required
    //     ya optional state set hoti hai.
    //   - price unit ko template ke price_unit se update karta hai
    //   - field_labels ke through har field ka naam bhi update karta hai
    //     (jaisa screenshots ke table mein diya gaya hai)
    //
    // Agar category kisi bhi known template se match na ho (custom
    // category jo 23 wali list mein nahi), to sub-template filtering
    // skip ho jati hai aur sab kuch Booking-Type level par hi chalta hai.

    function applySubTemplate(categorySlug) {
        var allFieldEls = document.querySelectorAll('[data-field-key]');
        var subConfig   = categorySlug ? SUB_TEMPLATE_CONFIGS[categorySlug] : null;

        var noteEl = document.getElementById('subTemplateNote');

        if (!subConfig) {
            // Koi specific sub-template match nahi hua -> sab visible rehne do,
            // koi extra required mat lagao, aur labels wapas Booking-Type
            // level defaults par reset kar do.
            allFieldEls.forEach(function (el) {
                el.style.display = '';
                setDynamicRequired(el, false);
                var key = el.dataset.fieldKey;
                if (CURRENT_TYPE_FIELD_LABELS[key]) {
                    updateFieldLabel(el, CURRENT_TYPE_FIELD_LABELS[key]);
                }
            });
            if (noteEl) noteEl.textContent = '';
            return;
        }

        var required = subConfig.required || [];
        var optional = subConfig.optional || [];
        var labels   = subConfig.field_labels || {};

        allFieldEls.forEach(function (el) {
            var key = el.dataset.fieldKey;

            if (ALWAYS_VISIBLE_FIELD_KEYS.indexOf(key) !== -1) {
                // category_id, title, price, description, requirements —
                // ye kabhi hide nahi hoti, sirf required/optional state
                // sub-template ke hisab se lagti hai.
                el.style.display = '';
                setDynamicRequired(el, required.indexOf(key) !== -1);
            } else if (required.indexOf(key) !== -1) {
                el.style.display = '';
                setDynamicRequired(el, true);
            } else if (optional.indexOf(key) !== -1) {
                el.style.display = '';
                setDynamicRequired(el, false);
            } else {
                el.style.display = 'none';
                setDynamicRequired(el, false);
            }

            // Field label update — agar is sub-template ne is field ke
            // liye custom naam diya hai (jaisa screenshot table mein hai).
            if (labels[key]) {
                updateFieldLabel(el, labels[key]);
            }
        });

        syncDynamicContainers();

        // Price unit: template ki value se override karo
        if (subConfig.price_unit) {
            document.querySelectorAll('.js-price-unit-label').forEach(function (el) {
                el.textContent = subConfig.price_unit;
            });
            var priceUnitInput = document.querySelector('.js-price-unit-input');
            if (priceUnitInput) {
                priceUnitInput.value = subConfig.price_unit;
            }
        }

        // Informational note: label + checkout modules
        if (noteEl) {
            var modules = (subConfig.checkout_modules || []).join(', ');
            noteEl.textContent = 'Template: ' + subConfig.label +
                (modules ? ' — Checkout modules: ' + modules : '');
        }
    }

    function syncDynamicContainers() {
        document.querySelectorAll('[data-field-key]').forEach(function (fieldEl) {
            if (fieldEl.style.display === 'none') return;

            var conditionalWrapper = fieldEl.closest('.js-automotive-rental, .js-automotive-service');
            if (conditionalWrapper) {
                conditionalWrapper.style.display = '';
            }

            var section = fieldEl.closest('[data-template-section]');
            if (section) {
                section.style.display = '';

                if (section.dataset.templateSection === 'automotive') {
                    var typeSelect = document.getElementById('bookingTypeSelect');
                    var title = section.querySelector('.booking-section-title');
                    if (title && typeSelect && typeSelect.value !== 'automotive') {
                        title.textContent = 'Service Details';
                    }
                }
            }
        });
    }

    function resetSubTemplate() {
        document.querySelectorAll('[data-field-key]').forEach(function (el) {
            el.style.display = '';
            setDynamicRequired(el, false);
            var key = el.dataset.fieldKey;
            if (CURRENT_TYPE_FIELD_LABELS[key]) {
                updateFieldLabel(el, CURRENT_TYPE_FIELD_LABELS[key]);
            }
        });
        var noteEl = document.getElementById('subTemplateNote');
        if (noteEl) noteEl.textContent = '';
    }

    function prepareCategoryPicker(type, selectedCategoryId) {
        var config = type ? (TEMPLATE_CONFIGS[type] || {}) : {};
        CURRENT_TYPE_FIELD_LABELS = config.field_labels || {};

        hideAllSections();
        resetSubTemplate();
        populateCategoryOptions(type, selectedCategoryId || null);

        var note = config.meta && config.meta.filter_note ? config.meta.filter_note : '';
        var noteEl = document.getElementById('bookingTypeNote');
        if (noteEl) noteEl.textContent = note;

        var priceLabel = config.price_unit_label || 'per booking';
        document.querySelectorAll('.js-price-unit-label').forEach(function (el) {
            el.textContent = priceLabel;
        });
        var priceUnitInput = document.querySelector('.js-price-unit-input');
        if (priceUnitInput) {
            priceUnitInput.value = priceLabel;
        }
    }

    function selectedCategorySlug() {
        var select = document.getElementById('bookingCategorySelect');
        if (!select) return null;

        var selectedOption = select.options[select.selectedIndex];
        return selectedOption ? (selectedOption.dataset.slug || null) : null;
    }

    function applySelectedCategoryTemplate() {
        var typeSelect = document.getElementById('bookingTypeSelect');
        var categorySelect = document.getElementById('bookingCategorySelect');
        var type = typeSelect ? typeSelect.value : '';
        var slug = selectedCategorySlug();

        CURRENT_CATEGORY_ID = categorySelect && categorySelect.value ? categorySelect.value : null;

        if (!type || !slug) {
            prepareCategoryPicker(type, CURRENT_CATEGORY_ID);
            return;
        }

        applyTemplate(type, {
            populateCategory: false,
            resetSubTemplate: false
        });
        applySubTemplate(slug);
    }

    // Ek field-group ke andar required/optional star show/hide karo, aur
    // agar iske andar koi actual input/select/textarea hai to uska
    // `required` attribute bhi set/remove karo (checkbox/switch fields
    // ko required attribute nahi diya jata).
    function setDynamicRequired(fieldGroupEl, isRequired) {
        var star = fieldGroupEl.querySelector('.js-dynamic-required');
        if (star) star.style.display = isRequired ? '' : 'none';

        var input = fieldGroupEl.querySelector('input, select, textarea');
        if (input && input.type !== 'checkbox' && input.type !== 'hidden') {
            if (isRequired) {
                input.setAttribute('required', 'required');
            } else {
                input.removeAttribute('required');
            }
        }
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

        if (CURRENT_SUB_TYPE) {
            select.value = CURRENT_SUB_TYPE;
            CURRENT_SUB_TYPE = select.value;
            toggleOnlineLink(CURRENT_SUB_TYPE);
            if (type === 'automotive') {
                toggleAutomotiveFields(CURRENT_SUB_TYPE);
            }
        }

        // Show/hide online link when online selected
        if (select.dataset.templateListenerBound === 'true') return;
        select.dataset.templateListenerBound = 'true';
        select.addEventListener('change', function () {
            CURRENT_SUB_TYPE = this.value;
            toggleOnlineLink(this.value);
            // Automotive: show rental vs service fields
            if ((document.getElementById('bookingTypeSelect') || {}).value === 'automotive') {
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
            prepareCategoryPicker(this.value, null);
        });

        // On load (create ya edit dono par) — apply saved/selected type
        prepareCategoryPicker(typeSelect.value, CURRENT_CATEGORY_ID);

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

    // ─── NAYA: Category (subcategory/template) change ──────────────────

    var categorySelect = document.getElementById('bookingCategorySelect');

    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            applySelectedCategoryTemplate();
        });

        // select2 use hone ki wajah se native 'change' kabhi kabhi select2's
        // apne event se replace ho jata hai — is liye select2 ka event bhi sunte hain.
        if (window.jQuery) {
            window.jQuery(categorySelect).on('select2:select', function () {
                applySelectedCategoryTemplate();
            });
        }

        // Page load par (edit mode mein) agar category pehle se selected hai
        // to uska sub-template bhi turant apply karo.
        if (selectedCategorySlug()) {
            applySelectedCategoryTemplate();
        }
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

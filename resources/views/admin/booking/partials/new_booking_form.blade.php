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
    <input type="hidden" name="admin_booking_draft_id" value="{{ $draftId ?? '' }}">

    {{-- FIX: general error summary — pehle koi visible error message nahi tha --}}
    @if ($errors->any())
        <div class="alert alert-danger" id="bookingFormErrorsAlert">
            <strong>{{ $errors->count() }} {{ $errors->count() == 1 ? 'error' : 'errors' }} found. Please check the highlighted fields below:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

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
                    @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                {{ empty($currentType) ? 'Select booking type first' : 'Select a Category' }}
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
                        Only subcategories of the selected booking type will be shown here.
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
                <div class="form-group" data-field-key="description">
                    <label class="input-label js-field-label" data-field="description">Description <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <textarea name="description" class="summernote form-control @error('description') is-invalid @enderror"
                              placeholder="Detailed description (min 300 words)">{{ $field('description') }}</textarea>
                    @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group" data-field-key="requirements">
                    <label class="input-label js-field-label" data-field="requirements">
                        Policy / Requirements <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <textarea name="requirements"
                              class="form-control @error('requirements') is-invalid @enderror"
                              rows="4"
                              placeholder="Cancellation policy, house rules, preparation notes, or booking requirements">{{ $field('requirements') }}</textarea>
                    @error('requirements')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    @include('admin.partials.qr-toggle-section', [
        'item'          => $editBooking ?? null,
        'regenerateUrl' => !empty($editBooking) ? getAdminPanelUrl('/booking/'.$editBooking->id.'/qr/regenerate') : null,
    ])
    @include('admin.partials.customer-group-restriction', ['item' => $editBooking ?? null])

    <div class="booking-section" id="section-shared-meta">
        <h3 class="booking-section-title">Template-Specific Details</h3>
        <div class="row">
            <div class="col-12 col-md-6" data-field-key="meta.service_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.service_type">
                        Service / Type <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="text" name="meta[service_type]"
                           value="{{ $metaField('service_type') }}"
                           class="form-control @error('meta.service_type') is-invalid @enderror"
                           placeholder="e.g. type of service for this template">
                    @error('meta.service_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6" data-field-key="meta.room_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.room_type">
                        Room / Resource <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="text" name="meta[room_type]"
                           value="{{ $metaField('room_type') }}"
                           class="form-control @error('meta.room_type') is-invalid @enderror"
                           placeholder="e.g. room, chair, or resource name">
                    @error('meta.room_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6" data-field-key="meta.pickup_location">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.pickup_location">
                        Pickup / Meeting Location <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="text" name="meta[pickup_location]"
                           value="{{ $metaField('pickup_location') }}"
                           class="form-control @error('meta.pickup_location') is-invalid @enderror">
                    @error('meta.pickup_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6" data-field-key="meta.dropoff_location">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.dropoff_location">
                        Drop-off / Return Location <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="text" name="meta[dropoff_location]"
                           value="{{ $metaField('dropoff_location') }}"
                           class="form-control @error('meta.dropoff_location') is-invalid @enderror">
                    @error('meta.dropoff_location')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6" data-field-key="meta.required_notes">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.required_notes">
                        Required Notes <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="text" name="meta[required_notes]"
                           value="{{ $metaField('required_notes') }}"
                           class="form-control @error('meta.required_notes') is-invalid @enderror">
                    @error('meta.required_notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6" data-field-key="meta.gallery">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.gallery">
                        Gallery Images <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager"
                                    data-input="booking_gallery" data-preview="holder" data-multiple="true">
                                <i class="fa fa-upload"></i>
                            </button>
                        </div>
                        <input type="text" name="meta[gallery]" id="booking_gallery"
                               value="{{ $metaField('gallery') }}"
                               class="form-control @error('meta.gallery') is-invalid @enderror"
                               placeholder="Comma-separated image paths">
                    </div>
                    @error('meta.gallery')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            {{-- capacity aur inventory ek hi jagah maujood hain — label sirf text change hota hai. --}}
            <div class="col-12 col-md-6" data-field-key="capacity">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="capacity">
                        Capacity <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="number" name="capacity" min="1"
                           value="{{ $field('capacity') }}"
                           class="form-control @error('capacity') is-invalid @enderror">
                    @error('capacity')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6" data-field-key="inventory">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="inventory">
                        Inventory / Available Slots <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="number" name="inventory" min="0"
                           value="{{ $field('inventory') }}"
                           class="form-control @error('inventory') is-invalid @enderror"
                           placeholder="Leave blank = unlimited">
                    @error('inventory')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section" id="section-location" data-field-key="location_enabled">
        <h3 class="booking-section-title">Location <span class="text-danger js-dynamic-required" style="display:none">*</span></h3>
        <div class="form-group d-flex align-items-center">
            <div class="custom-control custom-switch">
                {{-- FIX: `location_enabled` "must be true or false" bug.
                     A lone checkbox with value="on" fails Laravel's `boolean` rule
                     (it only accepts true/false/1/0/"1"/"0"), and an unchecked
                     checkbox sends nothing at all. We now always submit a clean
                     0/1: a hidden default of 0 followed by the checkbox itself
                     (same name, value="1"). Duplicate scalar field names resolve
                     to the LAST value in the POST body, so checked -> 1, unchecked -> 0.
                     On top of that, JS below recalculates this value from whether
                     the address panel actually has data right before submit, so
                     the switch is just a UI convenience, not the source of truth. --}}
                <input type="hidden" name="location_enabled" value="0">
                <input type="checkbox" name="location_enabled" id="newBookingLocationSwitch"
                       value="1" class="custom-control-input"
                       {{ (old('location_enabled') === '1' || (!empty($booking) && $booking->location_enabled)) ? 'checked' : '' }}>
                <label class="custom-control-label" for="newBookingLocationSwitch"></label>
            </div>
            <label for="newBookingLocationSwitch" class="mb-0 ml-2">Enable Location</label>
        </div>

        <div id="newBookingLocationPanel"
             style="{{ (old('location_enabled') === '1' || (!empty($booking) && $booking->location_enabled)) ? '' : 'display:none' }}">
            @php $locationModel = $booking ?? null; @endphp
            @include('partials._location_picker', [
                'locationModel' => $locationModel,
                'addressName'   => 'address_line',
                'showAjaxSave'  => false,
                'pickerId'      => 'adminBookingLocationPicker'
            ])
        </div>
    </div>

    <div class="booking-section booking-type-section" data-template-section="staff" style="display:none">
        <h3 class="booking-section-title js-section-title" data-section="staff">Staff / Provider</h3>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group" data-field-key="staff_id">
                    <label class="input-label js-field-label" data-field="staff_id">Staff / Provider <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="staff_id" data-plugin-selectTwo class="form-control @error('staff_id') is-invalid @enderror">
                        <option value="">Select Staff / Provider</option>
                        @foreach($instructors ?? [] as $instructor)
                            <option value="{{ $instructor->id }}"
                                {{ $metaField('staff_id') == $instructor->id ? 'selected' : '' }}>
                                {{ $instructor->full_name }}
                            </option>
                        @endforeach
                    </select>
                    @error('staff_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section booking-type-section" data-template-section="sub-type" style="display:none">
        <h3 class="booking-section-title js-section-title" data-section="sub-type">Appointment / Service Type</h3>
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group" data-field-key="sub_type">
                    <label class="input-label js-field-label" data-field="sub_type">Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="sub_type" class="form-control js-sub-type-select @error('sub_type') is-invalid @enderror">
                        <option value="">Select Type</option>
                    </select>
                    @error('sub_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 col-md-6 js-online-link-field" style="display:none" data-field-key="meta.online_link">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.online_link">Online Meeting Link <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="url" name="meta[online_link]"
                           value="{{ $metaField('online_link') }}"
                           class="form-control @error('meta.online_link') is-invalid @enderror"
                           placeholder="https://meet.example.com/...">
                    @error('meta.online_link')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

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
                           value="{{ $field('duration_minutes') }}"
                           class="form-control @error('duration_minutes') is-invalid @enderror"
                           placeholder="e.g. 60">
                    @error('duration_minutes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="buffer_before">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="buffer_before">
                        Buffer Before (minutes) <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="number" name="buffer_before" min="0"
                           value="{{ $field('buffer_before', 0) }}"
                           class="form-control @error('buffer_before') is-invalid @enderror">
                    @error('buffer_before')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="buffer_after">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="buffer_after">
                        Buffer After (minutes) <span class="text-danger js-dynamic-required" style="display:none">*</span>
                    </label>
                    <input type="number" name="buffer_after" min="0"
                           value="{{ $field('buffer_after', 0) }}"
                           class="form-control @error('buffer_after') is-invalid @enderror">
                    @error('buffer_after')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Lead Time (hours)</label>
                    <input type="number" name="lead_time_hours" min="0"
                           value="{{ $field('lead_time_hours', 0) }}"
                           class="form-control @error('lead_time_hours') is-invalid @enderror">
                    @error('lead_time_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Cutoff Time (hours)</label>
                    <input type="number" name="cutoff_time_hours" min="0"
                           value="{{ $field('cutoff_time_hours', 0) }}"
                           class="form-control @error('cutoff_time_hours') is-invalid @enderror">
                    @error('cutoff_time_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

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
                           value="{{ $metaField('check_in_date') }}"
                           class="form-control @error('meta.check_in_date') is-invalid @enderror">
                    @error('meta.check_in_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="meta.check_out_date">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.check_out_date">Check-out / Return Date <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="date" name="meta[check_out_date]"
                           value="{{ $metaField('check_out_date') }}"
                           class="form-control @error('meta.check_out_date') is-invalid @enderror">
                    @error('meta.check_out_date')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section booking-type-section" data-template-section="accommodation" style="display:none">
        <h3 class="booking-section-title">Accommodation Details</h3>
        <div class="row">
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label class="input-label">Price per Extra Person (per night)</label>
                    <input type="number" name="price_per" step="0.01" min="0"
                           value="{{ $field('price_per') }}"
                           class="form-control @error('price_per') is-invalid @enderror">
                    @error('price_per')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-3" data-field-key="min_persons">
                <div class="form-group">
                    <label class="input-label">Min Guests</label>
                    <input type="number" name="min_persons" min="1"
                           value="{{ $field('min_persons', 1) }}"
                           class="form-control @error('min_persons') is-invalid @enderror">
                    @error('min_persons')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-3" data-field-key="max_persons">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="max_persons">Max Guests (Adults) <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="number" name="max_persons" min="1"
                           value="{{ $field('max_persons') }}"
                           class="form-control js-required-for-type @error('max_persons') is-invalid @enderror" data-type="accommodation">
                    @error('max_persons')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-3" data-field-key="max_children">
                <div class="form-group">
                    <label class="input-label">Max Children</label>
                    <input type="number" name="max_children" min="0"
                           value="{{ $field('max_children') }}"
                           class="form-control @error('max_children') is-invalid @enderror">
                    @error('max_children')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                    @error('meta.amenities')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section booking-type-section" data-template-section="events" style="display:none">
        <h3 class="booking-section-title">Event Details</h3>
        <div class="row">
            <div class="col-12 col-md-4" data-field-key="meta.venue_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.venue_type">Venue Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="meta[venue_type]" class="form-control @error('meta.venue_type') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="indoor"   {{ $metaField('venue_type') == 'indoor'   ? 'selected' : '' }}>Indoor</option>
                        <option value="outdoor"  {{ $metaField('venue_type') == 'outdoor'  ? 'selected' : '' }}>Outdoor</option>
                        <option value="hybrid"   {{ $metaField('venue_type') == 'hybrid'   ? 'selected' : '' }}>Hybrid</option>
                        <option value="online"   {{ $metaField('venue_type') == 'online'   ? 'selected' : '' }}>Online</option>
                    </select>
                    @error('meta.venue_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="meta.organizer">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.organizer">Organizer / Provider <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="text" name="meta[organizer]"
                           value="{{ $metaField('organizer') }}"
                           class="form-control @error('meta.organizer') is-invalid @enderror">
                    @error('meta.organizer')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="meta.specifications">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.specifications">Specifications <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="text" name="meta[specifications]"
                           value="{{ $metaField('specifications') }}"
                           class="form-control @error('meta.specifications') is-invalid @enderror"
                           placeholder="e.g. Family-friendly, 18+, Outdoor shoes required">
                    @error('meta.specifications')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section booking-type-section" data-template-section="automotive" style="display:none">
        <h3 class="booking-section-title">Automotive Details</h3>
        <div class="row">
            <div class="col-12 col-md-4" data-field-key="meta.vehicle_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.vehicle_type">Vehicle Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="text" name="meta[vehicle_type]"
                           value="{{ $metaField('vehicle_type') }}"
                           class="form-control @error('meta.vehicle_type') is-invalid @enderror"
                           placeholder="e.g. Sedan, SUV, Motorcycle">
                    @error('meta.vehicle_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="col-12 js-automotive-rental" style="display:none">
                <div class="row">
                    <div class="col-md-6" data-field-key="meta.vehicle_specs">
                        <div class="form-group">
                            <label class="input-label js-field-label" data-field="meta.vehicle_specs">Vehicle Specs <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                            <input type="text" name="meta[vehicle_specs]"
                                   value="{{ $metaField('vehicle_specs') }}"
                                   class="form-control @error('meta.vehicle_specs') is-invalid @enderror"
                                   placeholder="e.g. 5 seats, automatic, petrol">
                            @error('meta.vehicle_specs')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 js-automotive-service" style="display:none">
                <div class="row"></div>
            </div>
        </div>
    </div>

    <div class="booking-section booking-type-section" data-template-section="education" style="display:none">
        <h3 class="booking-section-title">Education / Training Details</h3>
        <div class="row">
            <div class="col-12 col-md-4" data-field-key="meta.level">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.level">Level <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="meta[level]" class="form-control @error('meta.level') is-invalid @enderror">
                        <option value="">Select Level</option>
                        <option value="beginner"     {{ $metaField('level') == 'beginner'     ? 'selected' : '' }}>Beginner</option>
                        <option value="intermediate" {{ $metaField('level') == 'intermediate' ? 'selected' : '' }}>Intermediate</option>
                        <option value="advanced"     {{ $metaField('level') == 'advanced'     ? 'selected' : '' }}>Advanced</option>
                        <option value="all"          {{ $metaField('level') == 'all'          ? 'selected' : '' }}>All Levels</option>
                    </select>
                    @error('meta.level')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-6" data-field-key="meta.prerequisites">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.prerequisites">Prerequisites <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <input type="text" name="meta[prerequisites]"
                           value="{{ $metaField('prerequisites') }}"
                           class="form-control @error('meta.prerequisites') is-invalid @enderror"
                           placeholder="e.g. Basic English required">
                    @error('meta.prerequisites')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section booking-type-section" data-template-section="professional" style="display:none">
        <h3 class="booking-section-title">Professional Service Details</h3>
        <div class="row">
            <div class="col-12 col-md-6" data-field-key="meta.required_docs">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.required_docs">Required Notes / Documents <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <textarea name="meta[required_docs]" class="form-control @error('meta.required_docs') is-invalid @enderror" rows="3"
                              placeholder="e.g. Please bring your last tax return, NDA required">{{ $metaField('required_docs') }}</textarea>
                    @error('meta.required_docs')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section booking-type-section" data-template-section="doctors" style="display:none">
        <h3 class="booking-section-title">Doctor / Clinic Details</h3>
        <div class="row">
            <div class="col-12 col-md-4" data-field-key="meta.appointment_type">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.appointment_type">Service Type <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="meta[appointment_type]" class="form-control @error('meta.appointment_type') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="consultation" {{ $metaField('appointment_type') == 'consultation' ? 'selected' : '' }}>Consultation</option>
                        <option value="diagnostic"   {{ $metaField('appointment_type') == 'diagnostic'   ? 'selected' : '' }}>Diagnostic</option>
                        <option value="therapy"      {{ $metaField('appointment_type') == 'therapy'      ? 'selected' : '' }}>Therapy</option>
                        <option value="checkup"      {{ $metaField('appointment_type') == 'checkup'      ? 'selected' : '' }}>Check-up</option>
                    </select>
                    @error('meta.appointment_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-4" data-field-key="meta.payment_option">
                <div class="form-group">
                    <label class="input-label js-field-label" data-field="meta.payment_option">Payment Option <span class="text-danger js-dynamic-required" style="display:none">*</span></label>
                    <select name="meta[payment_option]" class="form-control @error('meta.payment_option') is-invalid @enderror">
                        <option value="">Select</option>
                        <option value="per_appointment" {{ $metaField('payment_option') == 'per_appointment' ? 'selected' : '' }}>Per Appointment</option>
                        <option value="quote_based"     {{ $metaField('payment_option') == 'quote_based'     ? 'selected' : '' }}>Quote Based</option>
                        <option value="insurance"       {{ $metaField('payment_option') == 'insurance'       ? 'selected' : '' }}>Insurance</option>
                    </select>
                    @error('meta.payment_option')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>
        </div>
    </div>

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
                           value="{{ $field('discount_price') }}"
                           class="form-control @error('discount_price') is-invalid @enderror">
                    @error('discount_price')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                           value="{{ $field('tax', '0.00') }}"
                           class="form-control @error('tax') is-invalid @enderror">
                    @error('tax')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
            </div>

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
                               value="{{ $field('deposit_amount') }}"
                               class="form-control @error('deposit_amount') is-invalid @enderror">
                        @error('deposit_amount')<div class="invalid-feedback">{{ $message }}</div>@enderror
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

    <div class="d-flex align-items-center gap-3 mt-4">
        <button type="submit" class="btn btn-success px-5">
            <i class="fa fa-save mr-2"></i> Save Booking
        </button>
        <a href="{{ getAdminPanelUrl('/booking/list') }}" class="btn btn-secondary">Cancel</a>
    </div>
</form>

<script>
(function () {
    'use strict';

    var TEMPLATE_CONFIGS = {!! $templateConfigs ?? '{}' !!};
    var SUB_TEMPLATE_CONFIGS = {!! $subTemplateConfigs ?? '{}' !!};
    var TYPE_CATEGORY_MAP    = {!! json_encode($bookingTypeCategoryMap ?? []) !!};
    var CATEGORIES_BY_PARENT = {!! $categoriesByParent ?? '{}' !!};
    var CURRENT_CATEGORY_ID  = {!! !empty($currentCategoryId) ? json_encode((string) $currentCategoryId) : 'null' !!};
    var CURRENT_SUB_TYPE     = {!! json_encode(old('sub_type', $booking->sub_type ?? '')) !!};
    var IS_REFRESHING_CATEGORY_SELECT = false;
    var IS_EDIT_MODE = {{ !empty($booking) ? 'true' : 'false' }};
    var CATEGORY_BY_TYPE_URL = '{{ getAdminPanelUrl('/booking/categories-by-type') }}';
    var CATEGORY_REQUEST_TOKEN = 0;
    var CLEAR_RESTORED_FIELD_VALUES = false;
    var CLEAR_RESTORED_EXCEPT_FIELD_KEYS = [];

    var ALWAYS_VISIBLE_FIELD_KEYS = ['category_id', 'title', 'price', 'description', 'requirements'];

    var CURRENT_TYPE_FIELD_LABELS = {};
    var ORIGINAL_FIELD_GROUP_HTML = {};

    // FIX: original, server-rendered label text for every field, captured once
    // before any template logic runs. Every subsequent label update is
    // rebuilt from this base layer so leftover labels from a previously
    // selected template can never "stick" on a field that the new template
    // doesn't happen to override.
    var ORIGINAL_FIELD_LABELS = {};

    function captureOriginalLabels() {
        document.querySelectorAll('.js-field-label').forEach(function (labelEl) {
            var key = labelEl.dataset.field;
            var clone = labelEl.cloneNode(true);
            clone.querySelectorAll('span, i').forEach(function (n) { n.remove(); });
            ORIGINAL_FIELD_LABELS[key] = clone.textContent.trim();
        });
    }
    captureOriginalLabels();

    function captureOriginalFieldGroups() {
        document.querySelectorAll('[data-field-key]').forEach(function (fieldEl) {
            ORIGINAL_FIELD_GROUP_HTML[fieldEl.dataset.fieldKey] = fieldEl.innerHTML;
        });
    }
    captureOriginalFieldGroups();

    function setLabelText(labelEl, newLabel) {
        if (!newLabel) return;
        var keepEls = Array.prototype.slice.call(labelEl.querySelectorAll('span, i'));
        labelEl.textContent = newLabel + ' ';
        keepEls.forEach(function (node) { labelEl.appendChild(node); });
    }

    // Layers: sub-template label (most specific) > type-level label > original default.
    function applyLabelLayers(typeLabels, subLabels) {
        document.querySelectorAll('.js-field-label').forEach(function (labelEl) {
            var key = labelEl.dataset.field;
            var label = (subLabels && subLabels[key])
                || (typeLabels && typeLabels[key])
                || ORIGINAL_FIELD_LABELS[key];
            setLabelText(labelEl, label);
        });
    }

    var TYPE_SECTIONS = {
        'beauty-spa': ['staff', 'time-slot', 'beauty-extras'],
        'doctors-clinics': ['staff', 'sub-type', 'time-slot', 'doctors'],
        'events': ['time-slot', 'events'],
        'accommodation': ['date-range', 'accommodation'],
        'automotive': ['staff', 'sub-type', 'automotive'],
        'professional-services': ['staff', 'sub-type', 'time-slot', 'professional'],
        'education-training': ['staff', 'sub-type', 'time-slot', 'education'],
    };

    var SUB_TYPE_OPTIONS = {
        'doctors-clinics':        [['physical','Physical'],['online','Online'],['both','Both']],
        'automotive':             [['rental','Rental / Car Hire'],['service','Mechanic / Service Appointment']],
        'professional-services':  [['in-person','In-person'],['online','Online'],['both','Both']],
        'education-training':     [['in-person','In-person'],['online','Online'],['both','Both']],
    };

    var SECTION_TITLES = {
        'accommodation': { 'date-range': 'Availability Period (Check-in / Check-out)' },
        'automotive':    { 'date-range': 'Pickup & Return Period',
                           'sub-type':   'Booking Sub-type (Rental or Service)' },
    };

    function populateCategoryOptions(type, selectedCategoryId, childrenOverride) {
        var select = document.getElementById('bookingCategorySelect');
        if (!select) return;

        var children = childrenOverride || getCategoryChildrenForType(type);

        select.innerHTML = '';

        if (!type) {
            select.disabled = true;
            select.appendChild(makeOption('', 'Select booking template first'));
            triggerSelectTwoRefresh(select);
            return;
        }

        select.disabled = false;

        if (!children.length) {
            select.appendChild(makeOption('', 'No subcategories were found for this booking type'));
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

    function resetCategorySelectLoading(type) {
        var select = document.getElementById('bookingCategorySelect');
        if (!select) return;

        select.innerHTML = '';
        select.disabled = true;
        select.appendChild(makeOption('', type ? 'Loading categories...' : 'Select booking type first'));
        triggerSelectTwoRefresh(select);
    }

    function fetchCategoryOptions(type, selectedCategoryId) {
        var requestToken = ++CATEGORY_REQUEST_TOKEN;

        if (!type) {
            populateCategoryOptions('', null);
            return Promise.resolve([]);
        }

        resetCategorySelectLoading(type);

        return fetch(CATEGORY_BY_TYPE_URL + '?booking_type=' + encodeURIComponent(type), {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(function (response) {
                if (!response.ok) throw new Error('Category request failed');
                return response.json();
            })
            .then(function (payload) {
                if (requestToken !== CATEGORY_REQUEST_TOKEN) return [];
                var children = payload.categories || [];
                populateCategoryOptions(type, selectedCategoryId || null, children);
                return children;
            })
            .catch(function () {
                if (requestToken !== CATEGORY_REQUEST_TOKEN) return [];
                populateCategoryOptions(type, selectedCategoryId || null);
                return getCategoryChildrenForType(type);
            });
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
        if (window.jQuery) {
            var $select = window.jQuery(select);
            IS_REFRESHING_CATEGORY_SELECT = true;
            if ($select.data('select2')) {
                $select.trigger('change');
            } else {
                $select.trigger('change');
            }
            window.setTimeout(function () {
                IS_REFRESHING_CATEGORY_SELECT = false;
            }, 0);
        }
    }

    function normalizeTemplateSlug(value) {
        return String(value || '').toLowerCase().trim()
            .replace(/[^a-z0-9]+/g, '-')
            .replace(/^-+|-+$/g, '');
    }

    function findSubTemplateConfig(categorySlug) {
        var slug = normalizeTemplateSlug(categorySlug);
        return SUB_TEMPLATE_CONFIGS[slug] || null;
    }

    function getCategoryChildrenForType(type) {
        if (!type) return [];

        var matched = [];
        Object.keys(CATEGORIES_BY_PARENT || {}).forEach(function (parentId) {
            (CATEGORIES_BY_PARENT[parentId] || []).forEach(function (cat) {
                var subConfig = findSubTemplateConfig(cat.slug);
                if (subConfig && subConfig.parent_type === type) {
                    matched.push(cat);
                }
            });
        });

        if (matched.length) {
            return matched;
        }

        var parentId = TYPE_CATEGORY_MAP[type];
        return (parentId && CATEGORIES_BY_PARENT[parentId]) ? CATEGORIES_BY_PARENT[parentId] : [];
    }

    // Template-specific values are removed from the submitted DOM when the
    // field does not belong to the selected category. This prevents stale
    // hidden inputs from being posted.
    function restoreFieldGroupControls(fieldEl) {
        var key = fieldEl.dataset.fieldKey;
        if (!key || ORIGINAL_FIELD_GROUP_HTML[key] === undefined) return;
        if (!fieldEl.querySelector('input, select, textarea, button')) {
            fieldEl.innerHTML = ORIGINAL_FIELD_GROUP_HTML[key];
            initializeRestoredControls(fieldEl);
        }
    }

    function removeFieldGroupControls(fieldEl) {
        fieldEl.querySelectorAll('.select2-container, .js-custom-invalid-feedback, .js-server-invalid-feedback').forEach(function (node) {
            node.remove();
        });

        clearFieldGroupValue(fieldEl);

        fieldEl.querySelectorAll('input, select, textarea, button').forEach(function (input) {
            input.remove();
        });
    }

    function setFieldGroupVisible(fieldEl, isVisible, isRequired) {
        if (isVisible) {
            restoreFieldGroupControls(fieldEl);
            if (CLEAR_RESTORED_FIELD_VALUES && CLEAR_RESTORED_EXCEPT_FIELD_KEYS.indexOf(fieldEl.dataset.fieldKey) === -1) {
                clearFieldGroupValue(fieldEl);
            }
            fieldEl.style.display = '';
            setDynamicRequired(fieldEl, !!isRequired);
            return;
        }

        setDynamicRequired(fieldEl, false);
        removeFieldGroupControls(fieldEl);
        fieldEl.style.display = 'none';
    }

    function initializeRestoredControls(rootEl) {
        if (window.jQuery) {
            window.jQuery(rootEl).find('[data-plugin-selectTwo]').each(function () {
                var $select = window.jQuery(this);
                if (!$select.data('select2') && window.jQuery.fn.select2) {
                    $select.select2();
                }
            });
        }

        bindRestoredDynamicControls();
    }

    // Clear values before removing a stale field from the DOM.
    function clearFieldGroupValue(fieldEl) {
        fieldEl.querySelectorAll('input, select, textarea').forEach(function (input) {
            if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else if (input.type !== 'hidden' || input.name !== 'location_enabled') {
                input.value = '';
            }
            if (window.jQuery && window.jQuery(input).data('select2')) {
                window.jQuery(input).val(null).trigger('change');
            }
        });

        // Extras are managed as dynamic rows, not just plain inputs — drop the rows entirely.
        var extrasContainer = fieldEl.id === 'extrasContainer'
            ? fieldEl
            : fieldEl.querySelector('#extrasContainer');
        if (extrasContainer) {
            extrasContainer.innerHTML = '';
        }
    }

    function clearAllDynamicFieldValues() {
        document.querySelectorAll('[data-field-key]').forEach(function (fieldEl) {
            var key = fieldEl.dataset.fieldKey;
            if (ALWAYS_VISIBLE_FIELD_KEYS.indexOf(key) !== -1) return;
            clearFieldGroupValue(fieldEl);
        });
    }

    function clearBookingFormValues(keepNames) {
        keepNames = keepNames || [];

        var form = document.getElementById('bookingAdminForm');
        if (!form) return;

        form.querySelectorAll('input, select, textarea').forEach(function (input) {
            var name = input.name || '';

            if (!name || keepNames.indexOf(name) !== -1) return;
            if (name === '_token' || name === 'creator_id' || name === 'admin_booking_draft_id') return;

            if (input.type === 'checkbox' || input.type === 'radio') {
                input.checked = false;
            } else if (input.type === 'hidden') {
                input.value = name === 'location_enabled' ? '0' : '';
            } else {
                input.value = '';
            }

            input.classList.remove('is-invalid');

            if (window.jQuery) {
                var $input = window.jQuery(input);

                if ($input.data('select2')) {
                    if (input.id === 'bookingCategorySelect') {
                        $input.val('').trigger('change.select2');
                    } else {
                        $input.val('').trigger('change');
                    }
                }

                if ($input.hasClass('summernote') && typeof $input.summernote === 'function') {
                    $input.summernote('code', '');
                }
            }
        });

        form.querySelectorAll('.js-custom-invalid-feedback, .js-server-invalid-feedback').forEach(function (node) {
            node.remove();
        });

        var errorAlert = document.getElementById('bookingFormErrorsAlert');
        if (errorAlert) errorAlert.remove();

        var locationSwitchEl = document.getElementById('newBookingLocationSwitch');
        var locationPanelEl = document.getElementById('newBookingLocationPanel');
        if (locationSwitchEl) locationSwitchEl.checked = false;
        if (locationPanelEl) locationPanelEl.style.display = 'none';

        extraIndex = 0;
    }

    function removeAllTemplateSpecificControls() {
        document.querySelectorAll('[data-field-key]').forEach(function (fieldEl) {
            var key = fieldEl.dataset.fieldKey;
            if (ALWAYS_VISIBLE_FIELD_KEYS.indexOf(key) !== -1) return;
            removeFieldGroupControls(fieldEl);
            fieldEl.style.display = 'none';
        });
    }

    function updateFieldLabel(containerEl, newLabel) {
        var label = containerEl.querySelector('label.input-label.js-field-label');
        if (label) setLabelText(label, newLabel);
    }

    function applyTemplate(type, options) {
        options = options || {};
        var shouldPopulateCategory = options.populateCategory !== false;
        var shouldResetSubTemplate = options.resetSubTemplate !== false;

        if (!type) {
            hideAllSections();
            if (shouldPopulateCategory) {
                fetchCategoryOptions('', null);
            }
            CURRENT_TYPE_FIELD_LABELS = {};
            applyLabelLayers(null, null);
            if (shouldResetSubTemplate) {
                resetSubTemplate();
            }
            return;
        }

        var config   = TEMPLATE_CONFIGS[type] || {};
        var sections = TYPE_SECTIONS[type]    || [];

        hideAllSections();

        sections.forEach(function (sectionKey) {
            var el = document.querySelector('[data-template-section="' + sectionKey + '"]');
            if (el) el.style.display = '';
        });

        var priceLabel = config.price_unit_label || 'per booking';
        document.querySelectorAll('.js-price-unit-label').forEach(function (el) {
            el.textContent = priceLabel;
        });
        var priceUnitInput = document.querySelector('.js-price-unit-input');
        if (priceUnitInput && !priceUnitInput.value) {
            priceUnitInput.value = priceLabel;
        }

        CURRENT_TYPE_FIELD_LABELS = config.field_labels || {};
        applyLabelLayers(CURRENT_TYPE_FIELD_LABELS, null);

        buildSubTypeOptions(type);

        if (shouldPopulateCategory) {
            fetchCategoryOptions(type, CURRENT_CATEGORY_ID);
        }

        var titleOverrides = SECTION_TITLES[type] || {};
        Object.keys(titleOverrides).forEach(function (sectionKey) {
            var el = document.querySelector('[data-template-section="' + sectionKey + '"] .booking-section-title');
            if (el) el.textContent = titleOverrides[sectionKey];
        });

        var note = config.meta && config.meta.filter_note ? config.meta.filter_note : '';
        var noteEl = document.getElementById('bookingTypeNote');
        if (noteEl) noteEl.textContent = note;

        if (shouldResetSubTemplate) {
            resetSubTemplate();
        }
    }

    function hideAllSections() {
        document.querySelectorAll('[data-template-section]').forEach(function (el) {
            el.style.display = 'none';
        });
    }

    function applySubTemplate(categorySlug) {
        var allFieldEls = document.querySelectorAll('[data-field-key]');
        var subConfig   = categorySlug ? findSubTemplateConfig(categorySlug) : null;

        var noteEl = document.getElementById('subTemplateNote');

        if (!subConfig) {
            allFieldEls.forEach(function (el) {
                var key = el.dataset.fieldKey;
                setFieldGroupVisible(el, ALWAYS_VISIBLE_FIELD_KEYS.indexOf(key) !== -1, false);
            });
            applyLabelLayers(CURRENT_TYPE_FIELD_LABELS, null);
            if (noteEl) noteEl.textContent = '';
            bindRestoredDynamicControls();
            return;
        }

        var required = subConfig.required || [];
        var optional = subConfig.optional || [];

        allFieldEls.forEach(function (el) {
            var key = el.dataset.fieldKey;

            if (ALWAYS_VISIBLE_FIELD_KEYS.indexOf(key) !== -1) {
                setFieldGroupVisible(el, true, required.indexOf(key) !== -1);
            } else if (required.indexOf(key) !== -1) {
                setFieldGroupVisible(el, true, true);
            } else if (optional.indexOf(key) !== -1) {
                setFieldGroupVisible(el, true, false);
            } else {
                setFieldGroupVisible(el, false, false);
            }
        });

        // FIX: labels are now always rebuilt in layered order — original
        // default -> type-level override -> sub-template override — so a
        // field that this sub-template doesn't mention falls back to the
        // type-level (or original) label instead of keeping whatever the
        // previously-selected sub-template last wrote into it.
        applyLabelLayers(CURRENT_TYPE_FIELD_LABELS, subConfig.field_labels || {});

        var currentType = (document.getElementById('bookingTypeSelect') || {}).value || '';
        syncDynamicContainers(currentType);

        if (subConfig.price_unit) {
            document.querySelectorAll('.js-price-unit-label').forEach(function (el) {
                el.textContent = subConfig.price_unit;
            });
            var priceUnitInput = document.querySelector('.js-price-unit-input');
            if (priceUnitInput) {
                priceUnitInput.value = subConfig.price_unit;
            }
        }

        if (noteEl) {
            var modules = (subConfig.checkout_modules || []).join(', ');
            noteEl.textContent = 'Template: ' + subConfig.label +
                (modules ? ' — Checkout modules: ' + modules : '');
        }

        bindRestoredDynamicControls();
    }

    function syncDynamicContainers(currentType) {
        var allowedSections = TYPE_SECTIONS[currentType] || [];

        document.querySelectorAll('[data-template-section]').forEach(function (section) {
            var sectionKey = section.dataset.templateSection;
            var hasVisibleTemplateField = false;

            section.querySelectorAll('[data-field-key]').forEach(function (fieldEl) {
                if (fieldEl.style.display !== 'none' && fieldEl.querySelector('input, select, textarea')) {
                    hasVisibleTemplateField = true;
                }
            });

            if (allowedSections.indexOf(sectionKey) !== -1 || hasVisibleTemplateField) {
                section.style.display = '';
            } else {
                section.style.display = 'none';
                section.querySelectorAll('[required]').forEach(function (input) {
                    input.removeAttribute('required');
                });
            }
        });

        document.querySelectorAll('[data-field-key]').forEach(function (fieldEl) {
            if (fieldEl.style.display === 'none') return;

            var conditionalWrapper = fieldEl.closest('.js-automotive-rental, .js-automotive-service');
            if (conditionalWrapper) {
                conditionalWrapper.style.display = '';
            }

            var section = fieldEl.closest('[data-template-section]');
            if (section) {
                var sectionKey = section.dataset.templateSection;
                section.style.display = '';

                if (sectionKey === 'automotive') {
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
            var key = el.dataset.fieldKey;
            setFieldGroupVisible(el, ALWAYS_VISIBLE_FIELD_KEYS.indexOf(key) !== -1, false);
        });
        applyLabelLayers(CURRENT_TYPE_FIELD_LABELS, null);
        var noteEl = document.getElementById('subTemplateNote');
        if (noteEl) noteEl.textContent = '';
    }

    function prepareCategoryPicker(type, selectedCategoryId) {
        var config = type ? (TEMPLATE_CONFIGS[type] || {}) : {};
        CURRENT_TYPE_FIELD_LABELS = config.field_labels || {};

        hideAllSections();
        resetSubTemplate();
        fetchCategoryOptions(type, selectedCategoryId || null).then(function () {
            if (selectedCategoryId && selectedCategorySlug()) {
                applySelectedCategoryTemplate();
            }
        });
        applyLabelLayers(CURRENT_TYPE_FIELD_LABELS, null);

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

    function buildSubTypeOptions(type) {
        var select  = document.querySelector('.js-sub-type-select');
        var options = SUB_TYPE_OPTIONS[type];

        if (!select || !options) return;

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

        if (select.dataset.templateListenerBound === 'true') return;
        select.dataset.templateListenerBound = 'true';
        select.addEventListener('change', function () {
            CURRENT_SUB_TYPE = this.value;
            toggleOnlineLink(this.value);
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

    var locationSwitch = document.getElementById('newBookingLocationSwitch');
    var locationPanel  = document.getElementById('newBookingLocationPanel');

    function bindLocationSwitch() {
        locationSwitch = document.getElementById('newBookingLocationSwitch');
        locationPanel  = document.getElementById('newBookingLocationPanel');
        if (!locationSwitch || !locationPanel || locationSwitch.dataset.bookingBound === 'true') return;
        locationSwitch.dataset.bookingBound = 'true';
        locationSwitch.addEventListener('change', function () {
            locationPanel.style.display = this.checked ? '' : 'none';
        });
    }
    bindLocationSwitch();

    // FIX: `location_enabled` should be derived from whether address data
    // actually exists, not trusted from the checkbox alone. The switch is
    // kept purely as a UI convenience to reveal the picker; the real value
    // gets recalculated right before submit.
    function hasLocationData() {
        if (!locationPanel) return false;
        var inputs = locationPanel.querySelectorAll('input, select, textarea');
        for (var i = 0; i < inputs.length; i++) {
            var val = (inputs[i].value || '').trim();
            if (val) return true;
        }
        return false;
    }

    var depositSwitch = document.getElementById('booking_deposit_enabled');
    var depositPanel  = document.getElementById('bookingDepositPanel');

    function bindDepositSwitch() {
        depositSwitch = document.getElementById('booking_deposit_enabled');
        depositPanel  = document.getElementById('bookingDepositPanel');
        if (!depositSwitch || !depositPanel || depositSwitch.dataset.bookingBound === 'true') return;
        depositSwitch.dataset.bookingBound = 'true';
        depositSwitch.addEventListener('change', function () {
            depositPanel.style.display = this.checked ? '' : 'none';
        });
    }
    bindDepositSwitch();

    var extrasContainer = document.getElementById('extrasContainer');
    var addExtraBtn     = document.getElementById('addExtraBtn');
    var extraIndex      = {{ count($meta['extras'] ?? []) }};

    function bindExtrasControls() {
        extrasContainer = document.getElementById('extrasContainer');
        addExtraBtn     = document.getElementById('addExtraBtn');
        if (!addExtraBtn || !extrasContainer || addExtraBtn.dataset.bookingBound === 'true') return;
        addExtraBtn.dataset.bookingBound = 'true';
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

    function bindRestoredDynamicControls() {
        bindLocationSwitch();
        bindDepositSwitch();
        bindExtrasControls();
    }
    bindExtrasControls();

    var typeSelect = document.getElementById('bookingTypeSelect');

    if (typeSelect) {
        typeSelect.addEventListener('change', function () {
            CURRENT_CATEGORY_ID = null;
            CURRENT_SUB_TYPE = '';
            CLEAR_RESTORED_FIELD_VALUES = true;
            CLEAR_RESTORED_EXCEPT_FIELD_KEYS = [];
            clearBookingFormValues(['booking_type']);
            removeAllTemplateSpecificControls();
            prepareCategoryPicker(this.value, null);
            CLEAR_RESTORED_FIELD_VALUES = false;
            CLEAR_RESTORED_EXCEPT_FIELD_KEYS = [];
        });

        if (!IS_EDIT_MODE && !typeSelect.value) {
            removeAllTemplateSpecificControls();
        }

        // Initial setup — edit mode keeps existing data, new mode starts fresh.
        prepareCategoryPicker(typeSelect.value, CURRENT_CATEGORY_ID);

        @if(!empty($booking) && $booking->sub_type)
            var subTypeSelect = document.querySelector('.js-sub-type-select');
            if (subTypeSelect) {
                subTypeSelect.value = '{{ $booking->sub_type }}';
                toggleOnlineLink('{{ $booking->sub_type }}');
                toggleAutomotiveFields('{{ $booking->sub_type }}');
            }
        @endif
    }

    var categorySelect = document.getElementById('bookingCategorySelect');

    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            if (IS_REFRESHING_CATEGORY_SELECT) return;
            // FIX: user actually changed the category — clear previously
            // entered template-specific values and remove stale hidden inputs.
            CLEAR_RESTORED_FIELD_VALUES = true;
            CLEAR_RESTORED_EXCEPT_FIELD_KEYS = ['category_id'];
            clearBookingFormValues(['booking_type', 'category_id']);
            removeAllTemplateSpecificControls();
            applySelectedCategoryTemplate();
            CLEAR_RESTORED_FIELD_VALUES = false;
            CLEAR_RESTORED_EXCEPT_FIELD_KEYS = [];
        });

        if (window.jQuery) {
            window.jQuery(categorySelect).on('select2:select', function () {
                CLEAR_RESTORED_FIELD_VALUES = true;
                CLEAR_RESTORED_EXCEPT_FIELD_KEYS = ['category_id'];
                clearBookingFormValues(['booking_type', 'category_id']);
                removeAllTemplateSpecificControls();
                applySelectedCategoryTemplate();
                CLEAR_RESTORED_FIELD_VALUES = false;
                CLEAR_RESTORED_EXCEPT_FIELD_KEYS = [];
            });
        }

        // Initial (edit-mode) setup — must NOT clear the booking's existing data.
        if (selectedCategorySlug()) {
            applySelectedCategoryTemplate();
        }
    }

    var bookingForm = document.getElementById('bookingAdminForm');

    function isSelect2Field(el) {
        return !!(window.jQuery && window.jQuery(el).data('select2'));
    }

    function isInsideHidden(el) {
        return !!el.closest(
            '[data-field-key][style*="display: none"], [data-field-key][style*="display:none"],' +
            '[data-template-section][style*="display: none"], [data-template-section][style*="display:none"]'
        );
    }

    function removeRequiredFromHiddenControls() {
        if (!bookingForm) return;

        bookingForm.querySelectorAll('[required]').forEach(function (el) {
            if (isInsideHidden(el) || !el.offsetParent) {
                el.removeAttribute('required');
                clearFieldInvalid(el);
            }
        });
    }

    function markFieldInvalid(el, message) {
        el.classList.add('is-invalid');
        var group = el.closest('.form-group') || el.parentElement;
        var feedback = group.querySelector('.js-custom-invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback d-block text-danger js-custom-invalid-feedback';
            group.appendChild(feedback);
        }
        feedback.textContent = message;
        if (isSelect2Field(el)) {
            window.jQuery(el).next('.select2-container')
                .find('.selection, .select2-selection')
                .css('border-color', '#dc3545');
        }
    }

    function clearFieldInvalid(el) {
        el.classList.remove('is-invalid');
        var group = el.closest('.form-group') || el.parentElement;
        var feedback = group.querySelector('.js-custom-invalid-feedback');
        if (feedback) feedback.remove();
        if (isSelect2Field(el)) {
            window.jQuery(el).next('.select2-container')
                .find('.selection, .select2-selection')
                .css('border-color', '');
        }
    }

    if (bookingForm) {
        bookingForm.addEventListener('submit', function (e) {
            removeRequiredFromHiddenControls();

            // FIX: compute location_enabled from actual address data right
            // before validation/submit, instead of trusting the switch.
            if (locationSwitch) {
                locationSwitch.checked = hasLocationData();
            }

            var firstInvalid = null;

            bookingForm.querySelectorAll('[required]').forEach(function (el) {
                if (isInsideHidden(el)) {
                    el.removeAttribute('required');
                    clearFieldInvalid(el);
                    return;
                }

                var value = (el.value || '').trim();
                if (!value) {
                    markFieldInvalid(el, 'This field is required.');
                    if (!firstInvalid) firstInvalid = el;
                } else {
                    clearFieldInvalid(el);
                }
            });

            if (firstInvalid) {
                e.preventDefault();
                e.stopPropagation();
                var scrollTarget = isSelect2Field(firstInvalid)
                    ? window.jQuery(firstInvalid).next('.select2-container')[0]
                    : firstInvalid;
                if (scrollTarget) {
                    scrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            }
        });
    }

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

    var SERVER_ERRORS = {!! json_encode($errors->getMessages() ?? []) !!};

    function nameToErrorKey(name) {
        return name.replace(/\[(\w*)\]/g, '.$1').replace(/\.$/, '');
    }

    function showServerErrors() {
        var keys = Object.keys(SERVER_ERRORS);
        if (!keys.length) return;

        var currentType     = (document.getElementById('bookingTypeSelect') || {}).value || '';
        var allowedSections = TYPE_SECTIONS[currentType] || [];

        var firstErrorScrollTarget = null;
        var handledGroups = {};

        document.querySelectorAll('[name]').forEach(function (el) {
            var key = nameToErrorKey(el.name);
            if (!SERVER_ERRORS[key]) return;

            var section = el.closest('[data-template-section]');
            if (section) {
                var sectionKey = section.dataset.templateSection;
                if (allowedSections.indexOf(sectionKey) === -1) {
                    return; // is type ke liye allowed nahi — skip karo
                }
            }

            var fieldGroupKey = el.closest('[data-field-key]');
            if (fieldGroupKey) {
                fieldGroupKey.style.display = '';
                var star = fieldGroupKey.querySelector('.js-dynamic-required');
                if (star) star.style.display = '';
            }
            if (section) section.style.display = '';

            el.classList.add('is-invalid');

            var group = el.closest('.form-group') || el.parentElement;
            var dedupeKey = key + '::' + (group.dataset.errGroupId || Math.random());
            if (!handledGroups[key]) {
                var feedback = document.createElement('div');
                feedback.className = 'invalid-feedback d-block text-danger js-server-invalid-feedback';
                feedback.textContent = SERVER_ERRORS[key][0];
                group.appendChild(feedback);
                handledGroups[key] = true;
            }

            var scrollTarget = el;
            if (window.jQuery && window.jQuery(el).data('select2')) {
                window.jQuery(el).next('.select2-container')
                    .find('.selection, .select2-selection')
                    .css('border-color', '#dc3545');
                scrollTarget = window.jQuery(el).next('.select2-container')[0];
            }

            if (!firstErrorScrollTarget) firstErrorScrollTarget = scrollTarget;
        });

        if (firstErrorScrollTarget) {
            firstErrorScrollTarget.scrollIntoView({ behavior: 'smooth', block: 'center' });
        } else {
            var alertEl = document.getElementById('bookingFormErrorsAlert');
            if (alertEl) alertEl.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    }

    window.setTimeout(showServerErrors, 50);

})();
</script>

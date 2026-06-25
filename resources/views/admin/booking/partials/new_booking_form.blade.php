@php
    $booking = $editBooking ?? null;
    $meta = old('meta', $booking->meta ?? []);
    $tags = old('tags', !empty($meta['tags']) ? implode(', ', (array) $meta['tags']) : '');
    $field = fn($name, $default = null) => old($name, !empty($booking) ? $booking->{$name} : $default);
    $checked = function ($name, $default = false) use ($booking) {
        return old($name, !empty($booking) ? (bool) $booking->{$name} : $default) ? 'checked' : '';
    };
@endphp

<form action="{{ getAdminPanelUrl() }}/booking/{{ !empty($booking) ? $booking->id . '/update' : 'store' }}"
      method="POST"
      class="booking-admin-form">
    {{ csrf_field() }}
    {{-- NOTE: the "status" hidden input that used to sit here was removed.
         It was named "status" — same name as the real <select name="status">
         further below — which is a duplicate-field footgun (two fields with
         the same name conflicting with each other on submit). The select
         below is the single source of truth for status now. --}}
    <input type="hidden" name="creator_id" value="{{ auth()->id() }}">

    <div class="booking-section">
        <h3 class="booking-section-title">Basic Information</h3>

        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label">Language</label>
                    <select name="language" data-plugin-selectTwo class="form-control @error('language') is-invalid @enderror">
                        @foreach($userLanguages ?? ['en' => 'English'] as $lang => $language)
                            <option value="{{ $lang }}" {{ $field('language', 'en') == $lang ? 'selected' : '' }}>{{ $language }}</option>
                        @endforeach
                    </select>
                    @error('language')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="input-label">Booking Type <span class="text-danger">*</span></label>
                    <select id="bookingTypeSelect" name="booking_type" data-plugin-selectTwo class="form-control @error('booking_type') is-invalid @enderror">
                        <option value="">Select Booking Service Type</option>
                        @foreach($childCategories ?? [] as $type)
                            <option value="{{ $type->slug }}" data-parent-id="{{ $type->parent_id }}"
                                {{ $field('booking_type') == $type->slug || $field('booking_type') == $type->title ? 'selected' : '' }}>
                                {{ $type->title }}
                            </option>
                        @endforeach
                    </select>
                    @error('booking_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group d-flex align-items-center">
                    <div class="custom-control custom-switch">
                        <input type="checkbox" name="location_enabled" id="newBookingLocationSwitch" value="on" class="custom-control-input"
                               {{ (old('location_enabled') == 'on' || (!empty($booking) && $booking->location_enabled)) ? 'checked' : '' }}
                               onchange="toggleLocation(this.checked)">
                        <label class="custom-control-label" for="newBookingLocationSwitch"></label>
                    </div>
                    <label for="newBookingLocationSwitch" class="mb-0 ml-2">{{ trans('admin/main.enable_location') }}</label>
                </div>

                <div id="newBookingLocationPanel" class="booking-location-panel mb-3" style="{{ (old('location_enabled') == 'on' || (!empty($booking) && $booking->location_enabled)) ? '' : 'display:none' }}">
                    @php $locationModel = $booking ?? null; @endphp
                    @include('partials._location_picker', [
                        'locationModel' => $locationModel,
                        'addressName' => 'address_line',
                        'showAjaxSave' => false,
                        'pickerId' => 'adminBookingLocationPicker'
                    ])
                </div>

                <div class="form-group">
                    <label class="input-label">Title <span class="text-danger">*</span></label>
                    <input type="text" name="title" id="newBookingTitle" value="{{ $field('title') }}" class="form-control @error('title') is-invalid @enderror" required>
                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="input-label">Slug</label>
                    <input type="text" name="slug" id="newBookingSlug" value="{{ $field('slug') }}" class="form-control @error('slug') is-invalid @enderror">
                    <div class="text-gray-500 text-small mt-1">Must be unique, auto-generated from title.</div>
                    @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="input-label">
                        Status <span class="text-danger">*</span>
                    </label>

                    <select
                        name="status"
                        id="status"
                        class="form-control @error('status') is-invalid @enderror"
                        required
                    >
                        <option value="">Select Status</option>

                        <option value="draft" {{ $field('status', 'draft') == 'draft' ? 'selected' : '' }}>
                            Draft
                        </option>

                        <option value="pending" {{ $field('status') == 'pending' ? 'selected' : '' }}>
                            Pending
                        </option>

                        <option value="published" {{ $field('status') == 'published' ? 'selected' : '' }}>
                            Published
                        </option>

                        <option value="rejected" {{ $field('status') == 'rejected' ? 'selected' : '' }}>
                            Rejected
                        </option>

                        <option value="inactive" {{ $field('status') == 'inactive' ? 'selected' : '' }}>
                            Inactive
                        </option>
                    </select>

                    @error('status')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="input-label">Tax</label>
                            <input type="number" name="tax" step="0.01" min="0" value="{{ $field('tax', '0.00') }}" class="form-control" placeholder="0.00">
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label">Thumbnail / Featured Image</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="booking_thumbnail" data-preview="holder"><i class="fa fa-upload"></i></button>
                        </div>
                        <input type="text" name="thumbnail" id="booking_thumbnail" value="{{ $field('thumbnail') }}" class="form-control">
                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view" data-input="booking_thumbnail"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="input-label">Cover Image</label>
                    <div class="input-group">
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text admin-file-manager" data-input="booking_cover" data-preview="holder"><i class="fa fa-upload"></i></button>
                        </div>
                        <input type="text" name="cover" id="booking_cover" value="{{ $field('cover') }}" class="form-control">
                        <div class="input-group-append">
                            <button type="button" class="input-group-text admin-file-view" data-input="booking_cover"><i class="fa fa-eye"></i></button>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-12 col-lg-6">
                <div class="form-group">
                    <label class="input-label">Summary / Requirements</label>
                    <textarea name="requirements" class="form-control" rows="5" placeholder="A brief summary, ideally between 50 and 160 characters">{{ $field('requirements') }}</textarea>
                </div>
            </div>
        </div>

        <div class="form-group">
            <label class="input-label">Description</label>
            <textarea name="description" class="summernote form-control" placeholder="Minimum 300 words required. HTML and images are supported.">{{ $field('description') }}</textarea>
        </div>
    </div>

    <div class="booking-section">
        <h3 class="booking-section-title">Bookings</h3>
        <div class="row">
            <div class="col-12 col-md-6"><div class="form-group"><label class="input-label">Capacity</label><input type="number" name="capacity" min="0" value="{{ $field('capacity') }}" class="form-control" placeholder="Leave blank for unlimited capacity"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Min Persons</label><input type="number" name="min_persons" min="1" value="{{ $field('min_persons', 1) }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Max Persons</label><input type="number" name="max_persons" min="1" value="{{ $field('max_persons') }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Max Children</label><input type="number" name="max_children" min="0" value="{{ $field('max_children') }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Duration (minutes)</label><input type="number" name="duration_minutes" min="0" value="{{ $field('duration_minutes') }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Buffer Before (minutes)</label><input type="number" name="buffer_before" min="0" value="{{ $field('buffer_before', 0) }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Buffer After (minutes)</label><input type="number" name="buffer_after" min="0" value="{{ $field('buffer_after', 0) }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Lead Time (hours)</label><input type="number" name="lead_time_hours" min="0" value="{{ $field('lead_time_hours', 0) }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Cutoff Time (hours)</label><input type="number" name="cutoff_time_hours" min="0" value="{{ $field('cutoff_time_hours', 0) }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Reschedule Before (hours)</label><input type="number" name="reschedule_before_hours" min="0" value="{{ $field('reschedule_before_hours', 24) }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Inventory</label><input type="number" name="inventory" min="0" value="{{ $field('inventory') }}" class="form-control" placeholder="Leave blank for unlimited"></div></div>
        </div>

        <div class="row">
            @foreach([
                    'children_allowed' => ['Children Allowed', true],
                    'instant_booking' => ['Instant Booking', true],
                    'requires_approval' => ['Requires Approval', false],
                    'allow_reschedule' => ['Allow Reschedule', true],
                    'waitlist_enabled' => ['Waitlist Enabled', false],
                    'forum_enabled' => ['Forum Enabled', false],
                    'comments_enabled' => ['Comments Enabled', true],
                    'reviews_enabled' => ['Reviews Enabled', true],
                    'featured' => ['Featured', false],
                ] as $name => [$label, $default])
                    <div class="col-12 col-md-4">
                        <div class="form-group d-flex align-items-center">
                            <div class="custom-control custom-switch">
                                <input type="checkbox" name="{{ $name }}" id="booking_{{ $name }}" value="1" class="custom-control-input" {{ $checked($name, $default) }}>
                                <label class="custom-control-label" for="booking_{{ $name }}"></label>
                            </div>
                            <label for="booking_{{ $name }}" class="mb-0 ml-2">{{ $label }}</label>
                        </div>
                    </div>
            @endforeach
        </div>
    </div>

    <div class="booking-section">
        <h3 class="booking-section-title">Booking Costs</h3>
        <div class="row">
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Base Cost <span class="info-icon">i</span></label><input type="number" name="price" step="0.01" min="0" value="{{ $field('price', '0.00') }}" class="form-control @error('price') is-invalid @enderror" required>@error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Cost per Block <span class="info-icon">i</span></label><input type="number" name="price_per" step="0.01" min="0" value="{{ $field('price_per') }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Price Unit</label><input type="text" name="price_unit" value="{{ $field('price_unit') }}" class="form-control" placeholder="e.g. per hour, per session"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Display / Discount Price <span class="info-icon">i</span></label><input type="number" name="discount_price" step="0.01" min="0" value="{{ $field('discount_price') }}" class="form-control"></div></div>
            <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Currency</label><select name="currency" data-plugin-selectTwo class="form-control">@foreach(['USD', 'EUR', 'GBP', 'PKR', 'AED', 'SAR', 'INR'] as $cur)<option value="{{ $cur }}" {{ $field('currency', 'USD') == $cur ? 'selected' : '' }}>{{ $cur }}</option>@endforeach</select></div></div>
            <div class="col-12 col-md-3"><div class="form-group d-flex align-items-center mt-4"><div class="custom-control custom-switch"><input type="checkbox" name="deposit_enabled" id="booking_deposit_enabled" value="1" class="custom-control-input" {{ $checked('deposit_enabled', false) }}><label class="custom-control-label" for="booking_deposit_enabled"></label></div><label for="booking_deposit_enabled" class="mb-0 ml-2">Deposit Enabled</label></div></div>
        </div>
        <div id="bookingDepositPanel" class="booking-deposit-panel">
            <div class="row">
                <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Deposit Amount</label><input type="number" name="deposit_amount" step="0.01" min="0" value="{{ $field('deposit_amount') }}" class="form-control"></div></div>
                <div class="col-12 col-md-3"><div class="form-group"><label class="input-label">Deposit Type</label><select name="deposit_type" class="form-control"><option value="percent" {{ $field('deposit_type', 'percent') == 'percent' ? 'selected' : '' }}>Percent</option><option value="fixed" {{ $field('deposit_type') == 'fixed' ? 'selected' : '' }}>Fixed</option></select></div></div>
            </div>
        </div>
    </div>

    <div class="booking-section">
        <h3 class="booking-section-title">Additional Information</h3>

        <div class="form-group">
            <label class="input-label">Category</label>
            <div class="input-group">
                <select id="bookingCategorySelect" name="category_id" data-plugin-selectTwo class="form-control">
                    <option value="">Select a Category</option>
                    @foreach($parentCategories ?? [] as $cat)
                        <option value="{{ $cat->id }}" {{ $field('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->title }}</option>
                    @endforeach
                </select>
                <div class="input-group-append">
                    <a href="{{ getAdminPanelUrl('/booking/categories') }}" class="btn btn-primary add-button"><i class="fa fa-plus"></i></a>
                </div>
            </div>
        </div>
    </div>

    <div class="booking-section">
        <div class="text-gray-500 text-small mb-3">Check this if booking requires admin approval/confirmation.</div>
        <div class="form-group">
            <label class="input-label">Checkout Message</label>
            <textarea name="checkout_message" class="form-control" rows="4">{{ $field('checkout_message') }}</textarea>
        </div>
        <h3 class="booking-section-title">Message to Reviewer</h3>
        <textarea name="reviewer_message" rows="7" class="form-control" placeholder="Message for the reviewer...">{{ $field('reviewer_message') }}</textarea>
    </div>

    <button type="submit" class="btn btn-success mt-4">Save and Continue</button>
</form>
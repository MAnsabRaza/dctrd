{{-- ══════════════════════════════════════════════════════════════
     Inline toggle CSS  — fixes broken custom-switch labels
     ══════════════════════════════════════════════════════════════ --}}
<style>
.booking-switch-row {
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 6px 0;
}
.booking-switch {
    position: relative;
    display: inline-block;
    width: 48px;
    height: 26px;
    flex-shrink: 0;
}
.booking-switch input {
    opacity: 0;
    width: 0;
    height: 0;
    position: absolute;
}
.booking-switch-slider {
    position: absolute;
    inset: 0;
    background: #ccc;
    border-radius: 26px;
    cursor: pointer;
    transition: background .2s;
}
.booking-switch-slider:before {
    content: '';
    position: absolute;
    height: 20px;
    width: 20px;
    left: 3px;
    bottom: 3px;
    background: #fff;
    border-radius: 50%;
    transition: transform .2s;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
}
.booking-switch input:checked + .booking-switch-slider {
    background: #2196F3;
}
.booking-switch input:checked + .booking-switch-slider:before {
    transform: translateX(22px);
}
.booking-switch-label {
    font-size: 14px;
    color: #495057;
    font-weight: 500;
    cursor: pointer;
    user-select: none;
    margin-bottom: 0;
}
.booking-switch-label small {
    display: block;
    font-size: 12px;
    color: #999;
    font-weight: 400;
}
.booking-map-preview {
    width: 100%;
    height: 260px;
    border: 1px solid #e1e5eb;
    border-radius: 12px;
    overflow: hidden;
    background: #f7f8fa;
}
.booking-map-preview iframe {
    width: 100%;
    height: 100%;
    border: 0;
}
</style>

<div class="row">
    <div class="col-12">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @php
        $isEditing = isset($booking) && !is_null($booking);
        $bookingDefaults = $bookingDefaults ?? [];
    @endphp

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('auth.language') }}</label>
            <select name="language" class="form-control">
                @foreach($userLanguages ?? [app()->getLocale() => ucfirst(app()->getLocale())] as $lang => $language)
                    <option value="{{ $lang }}" {{ old('language', $isEditing ? $booking->language : app()->getLocale()) == $lang ? 'selected' : '' }}>
                        {{ $language }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ── Title & Slug ──────────────────────────────────────────────── --}}
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.title') }} <span class="text-danger">*</span></label>
            <input name="title" type="text"
                   class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title', $isEditing ? $booking->title : '') }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.slug') }}</label>
            <input name="slug" type="text"
                   class="form-control @error('slug') is-invalid @enderror"
                   value="{{ old('slug', $isEditing ? $booking->slug : '') }}"
                   placeholder="auto-generated-if-empty">
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- ── Category & Booking Type ──────────────────────────────────── --}}
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.category') }}</label>
            <select name="category_id"
                    class="form-control @error('category_id') is-invalid @enderror">
                <option value="">{{ trans('panel.select_category') }}</option>
                @foreach($allCategoryLists as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $isEditing ? $booking->category_id : '') == $category->id ? 'selected' : '' }}>
                        {{ $category->title }}
                    </option>
                @endforeach
            </select>
            @error('category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.booking_type') }} <span class="text-danger">*</span></label>
            <select name="booking_type"
                    class="form-control @error('booking_type') is-invalid @enderror">
                <option value="">— {{ trans('panel.select_type') }} —</option>
                @foreach(['tour','activity','rental','event','service','accommodation'] as $type)
                    <option value="{{ $type }}"
                        {{ old('booking_type', $isEditing ? $booking->booking_type : '') === $type ? 'selected' : '' }}>
                        {{ ucfirst($type) }}
                    </option>
                @endforeach
            </select>
            @error('booking_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- ── Sub Type & Requirements ──────────────────────────────────── --}}
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.sub_type') }}</label>
            <input name="sub_type" type="text" class="form-control"
                   value="{{ old('sub_type', $isEditing ? $booking->sub_type : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.requirements') }}</label>
            <input name="requirements" type="text" class="form-control"
                   value="{{ old('requirements', $isEditing ? $booking->requirements : '') }}">
        </div>
    </div>

    {{-- ── Price, Discount, Currency ───────────────────────────────── --}}
    <div class="col-12 col-md-4">
        <div class="form-group">
            <label>{{ trans('panel.price') }}</label>
            <input name="price" type="number" step="0.01" min="0"
                   class="form-control @error('price') is-invalid @enderror"
                   value="{{ old('price', $isEditing ? $booking->price : '') }}"
                   placeholder="0.00">
            @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="form-group">
            <label>{{ trans('panel.discount_price') }}</label>
            <input name="discount_price" type="number" step="0.01" min="0"
                   class="form-control"
                   value="{{ old('discount_price', $isEditing ? $booking->discount_price : '') }}"
                   placeholder="0.00">
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="form-group">
            <label>{{ trans('panel.currency') }}</label>
            <select name="currency" class="form-control">
                @foreach(['USD','EUR','GBP','PKR','AED','SAR','INR'] as $cur)
                    <option value="{{ $cur }}"
                        {{ old('currency', $isEditing ? $booking->currency : ($bookingDefaults['currency'] ?? 'USD')) === $cur ? 'selected' : '' }}>
                        {{ $cur }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ── Price Per & Price Unit ───────────────────────────────────── --}}
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.price_per') }} <small class="text-muted">(numeric)</small></label>
            <input name="price_per" type="number" step="0.01" min="0"
                   class="form-control @error('price_per') is-invalid @enderror"
                   value="{{ old('price_per', $isEditing ? $booking->price_per : '') }}"
                   placeholder="e.g. 1.00">
            @error('price_per')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.price_unit_label') }}</label>
            <input name="price_unit" type="text" class="form-control"
                   value="{{ old('price_unit', $isEditing ? $booking->price_unit : ($bookingDefaults['price_unit'] ?? 'booking')) }}"
                   placeholder="e.g. per night, per adult">
        </div>
    </div>

    {{-- ── Capacity, Min/Max Persons, Duration ─────────────────────── --}}
    <div class="col-12 col-md-3">
        <div class="form-group">
            <label>{{ trans('panel.capacity') }}</label>
            <input name="capacity" type="number" min="0" class="form-control"
                   value="{{ old('capacity', $isEditing ? $booking->capacity : '') }}"
                   placeholder="Unlimited">
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="form-group">
            <label>{{ trans('panel.min_persons') }}</label>
            <input name="min_persons" type="number" min="0" class="form-control"
                   value="{{ old('min_persons', $isEditing ? $booking->min_persons : 1) }}">
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="form-group">
            <label>{{ trans('panel.max_persons') }}</label>
            <input name="max_persons" type="number" min="0" class="form-control"
                   value="{{ old('max_persons', $isEditing ? $booking->max_persons : '') }}"
                   placeholder="No limit">
        </div>
    </div>

    <div class="col-12 col-md-3">
        <div class="form-group">
            <label>{{ trans('panel.duration_minutes') }}</label>
            <input name="duration_minutes" type="number" min="0" class="form-control"
                   value="{{ old('duration_minutes', $isEditing ? $booking->duration_minutes : '') }}"
                   placeholder="Minutes">
        </div>
    </div>

    {{-- ── Description ──────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="form-group">
            <label>{{ trans('panel.description') }}</label>
            <textarea name="description" rows="4"
                      class="form-control @error('description') is-invalid @enderror">{{ old('description', $isEditing ? $booking->description : '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- ── Location Toggle ──────────────────────────────────────────── --}}
    @php
        $locationEnabled = old('location_enabled', $isEditing ? !empty($booking->location_enabled) : !empty($bookingDefaults['location_enabled']));
    @endphp

    <div class="col-12 mb-15">
        <div class="booking-switch-row">
            <label class="booking-switch" for="locationSwitch">
                <input type="checkbox"
                       id="locationSwitch"
                       name="location_enabled"
                       value="1"
                       {{ $locationEnabled ? 'checked' : '' }}
                       onchange="toggleLocationFields(this.checked)">
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label" for="locationSwitch">
                Enable location
                <small>Show address &amp; map coordinates</small>
            </label>
        </div>
    </div>

    {{-- ── Location Fields ──────────────────────────────────────────── --}}
    <div id="locationFields"
         class="col-12"
         style="{{ $locationEnabled ? 'display:block' : 'display:none' }}">
        <div class="row">

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Address line</label>
                    <input name="address_line" type="text" class="form-control"
                           value="{{ old('address_line', $isEditing ? $booking->address_line : '') }}">
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>City</label>
                    <input name="city" type="text" class="form-control"
                           value="{{ old('city', $isEditing ? $booking->city : '') }}">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>State / Province</label>
                    <input name="state" type="text" class="form-control"
                           value="{{ old('state', $isEditing ? $booking->state : '') }}">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Country</label>
                    <input name="country" type="text" class="form-control"
                           value="{{ old('country', $isEditing ? $booking->country : '') }}">
                </div>
            </div>

            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Postal code</label>
                    <input name="postal_code" type="text" class="form-control"
                           value="{{ old('postal_code', $isEditing ? $booking->postal_code : '') }}">
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Latitude</label>
                    <input id="bookingLat" name="lat" type="number" step="0.000001" class="form-control"
                           value="{{ old('lat', $isEditing ? $booking->lat : '') }}"
                           placeholder="e.g. 31.5204">
                </div>
            </div>

            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>Longitude</label>
                    <input id="bookingLng" name="lng" type="number" step="0.000001" class="form-control"
                           value="{{ old('lng', $isEditing ? $booking->lng : '') }}"
                           placeholder="e.g. 74.3587">
                </div>
            </div>

            <div class="col-12">
                <div class="booking-map-preview">
                    <iframe id="bookingMapFrame" loading="lazy" referrerpolicy="no-referrer-when-downgrade" src="about:blank"></iframe>
                </div>
            </div>

        </div>
    </div>

    {{-- ── Status Toggle ────────────────────────────────────────────── --}}
    <div class="col-12 col-md-6 mb-15">
        <div class="booking-switch-row">
            <label class="booking-switch" for="bookingStatus">
                <input type="checkbox"
                       id="bookingStatus"
                       name="status"
                       value="1"
                       {{ old('status', $isEditing ? ($booking->status === 'published') : (($bookingDefaults['status'] ?? 'draft') === 'published')) ? 'checked' : '' }}>
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label" for="bookingStatus">
                Active / Published
                <small>Visible to users on the platform</small>
            </label>
        </div>
    </div>

    {{-- ── Featured Toggle ──────────────────────────────────────────── --}}
    <div class="col-12 col-md-6 mb-15">
        <div class="booking-switch-row">
            <label class="booking-switch" for="bookingFeatured">
                <input type="checkbox"
                       id="bookingFeatured"
                       name="featured"
                       value="1"
                       {{ old('featured', $isEditing && $booking->featured ? 1 : 0) ? 'checked' : '' }}>
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label" for="bookingFeatured">
                Featured
                <small>Show in featured / homepage section</small>
            </label>
        </div>
    </div>

    {{-- ── Meta JSON ────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="form-group">
            <label>Meta JSON</label>
            <textarea name="meta" rows="3" class="form-control"
                      placeholder='{"key": "value"}'>{{ old('meta', $isEditing && $booking->meta ? json_encode($booking->meta, JSON_PRETTY_PRINT) : '') }}</textarea>
            <small class="text-muted">Meta JSON data for the booking. Can include amenities, policies, etc.</small>
        </div>
    </div>

    {{-- ── Submit ────────────────────────────────────────────────────── --}}
    <div class="col-12 mt-10">
        <button type="submit" class="btn btn-primary">
            <i class="fa fa-save mr-1"></i>
            {{ $isEditing ? trans('public.update') : trans('public.save') }}
        </button>
        <a href="{{ route('panel.bookings.index') }}" class="btn btn-outline-secondary ml-2">
            <i class="fa fa-times mr-1"></i>
            {{ trans('public.cancel') }}
        </a>
    </div>
</div>

{{-- ── JS ─────────────────────────────────────────────────────────────── --}}
<script>
    function toggleLocationFields(show) {
        document.getElementById('locationFields').style.display = show ? 'block' : 'none';
        if (show) {
            updateBookingMap();
        }
    }

    function updateBookingMap() {
        var latInput = document.getElementById('bookingLat');
        var lngInput = document.getElementById('bookingLng');
        var frame = document.getElementById('bookingMapFrame');

        if (!latInput || !lngInput || !frame || !latInput.value || !lngInput.value) {
            return;
        }

        var latitude = parseFloat(latInput.value);
        var longitude = parseFloat(lngInput.value);

        if (isNaN(latitude) || isNaN(longitude)) {
            return;
        }

        var delta = 0.01;
        var bbox = [
            longitude - delta,
            latitude - delta,
            longitude + delta,
            latitude + delta
        ].join(',');

        frame.src = 'https://www.openstreetmap.org/export/embed.html?bbox=' + bbox + '&layer=mapnik&marker=' + latitude + ',' + longitude;
    }

    document.addEventListener('DOMContentLoaded', function () {
        var latInput = document.getElementById('bookingLat');
        var lngInput = document.getElementById('bookingLng');

        if (latInput && lngInput) {
            latInput.addEventListener('input', updateBookingMap);
            lngInput.addEventListener('input', updateBookingMap);
            updateBookingMap();
        }
    });
</script>

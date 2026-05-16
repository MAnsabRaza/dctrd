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
    @endphp

    {{-- ── Title & Slug ─────────────────────────────────────────────────── --}}
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.title') }} <span class="text-danger">*</span></label>
            <input name="title" type="text" class="form-control @error('title') is-invalid @enderror"
                   value="{{ old('title', $isEditing ? $booking->title : '') }}" required>
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.slug') }}</label>
            <input name="slug" type="text" class="form-control @error('slug') is-invalid @enderror"
                   value="{{ old('slug', $isEditing ? $booking->slug : '') }}"
                   placeholder="auto-generated-if-empty">
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- ── Category & Booking Type (required - NOT NULL in DB) ────────── --}}
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.category') }}</label>
            <select name="category_id" class="form-control @error('category_id') is-invalid @enderror">
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
            <select name="booking_type" class="form-control @error('booking_type') is-invalid @enderror">
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

    {{-- ── Sub Type & Requirements ───────────────────────────────────────── --}}
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

    {{-- ── Price, Discount, Currency ────────────────────────────────────── --}}
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
            <input name="discount_price" type="number" step="0.01" min="0" class="form-control"
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
                        {{ old('currency', $isEditing ? $booking->currency : 'USD') === $cur ? 'selected' : '' }}>
                        {{ $cur }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- ── Price Per & Price Unit ───────────────────────────────────────── --}}
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
                   value="{{ old('price_unit', $isEditing ? $booking->price_unit : '') }}"
                   placeholder="e.g. per night, per adult">
        </div>
    </div>

    {{-- ── Capacity, Min/Max Persons, Duration ─────────────────────────── --}}
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

    {{-- ── Description ─────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="form-group">
            <label>{{ trans('panel.description') }}</label>
            <textarea name="description" rows="4" class="form-control @error('description') is-invalid @enderror">{{ old('description', $isEditing ? $booking->description : '') }}</textarea>
            @error('description')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    {{-- ── Location Toggle ──────────────────────────────────────────────── --}}
    <div class="col-12 mb-15">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="locationSwitch"
                   name="location_enabled" value="1"
                   {{ old('location_enabled', $isEditing && $booking->location_enabled ? 1 : 0) ? 'checked' : '' }}
                   onchange="toggleLocationFields(this.checked)">
            <label class="custom-control-label" for="locationSwitch">{{ trans('panel.enable_location') }}</label>
        </div>
    </div>

    {{-- ── Location Fields ─────────────────────────────────────────────── --}}
    <div id="locationFields" class="col-12"
         style="{{ old('location_enabled', $isEditing && $booking->location_enabled ? 1 : 0) ? '' : 'display:none' }}">
        <div class="row">
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>{{ trans('panel.address_line') }}</label>
                    <input name="address_line" type="text" class="form-control"
                           value="{{ old('address_line', $isEditing ? $booking->address_line : '') }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>{{ trans('panel.city') }}</label>
                    <input name="city" type="text" class="form-control"
                           value="{{ old('city', $isEditing ? $booking->city : '') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>{{ trans('panel.state') }}</label>
                    <input name="state" type="text" class="form-control"
                           value="{{ old('state', $isEditing ? $booking->state : '') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>{{ trans('panel.country') }}</label>
                    <input name="country" type="text" class="form-control"
                           value="{{ old('country', $isEditing ? $booking->country : '') }}">
                </div>
            </div>
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>{{ trans('panel.postal_code') }}</label>
                    <input name="postal_code" type="text" class="form-control"
                           value="{{ old('postal_code', $isEditing ? $booking->postal_code : '') }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>{{ trans('panel.latitude') }}</label>
                    <input name="lat" type="number" step="0.000001" class="form-control"
                           value="{{ old('lat', $isEditing ? $booking->lat : '') }}"
                           placeholder="e.g. 31.5204">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group">
                    <label>{{ trans('panel.longitude') }}</label>
                    <input name="lng" type="number" step="0.000001" class="form-control"
                           value="{{ old('lng', $isEditing ? $booking->lng : '') }}"
                           placeholder="e.g. 74.3587">
                </div>
            </div>
        </div>
    </div>

    {{-- ── Status & Featured ────────────────────────────────────────────── --}}
    <div class="col-12 col-md-6 mb-15">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="bookingStatus"
                   name="status" value="1"
                   {{ old('status', $isEditing && $booking->status === 'published' ? 1 : 0) ? 'checked' : '' }}>
            <label class="custom-control-label" for="bookingStatus">{{ trans('public.active') }}</label>
        </div>
    </div>

    <div class="col-12 col-md-6 mb-15">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="bookingFeatured"
                   name="featured" value="1"
                   {{ old('featured', $isEditing && $booking->featured ? 1 : 0) ? 'checked' : '' }}>
            <label class="custom-control-label" for="bookingFeatured">{{ trans('panel.featured') }}</label>
        </div>
    </div>

    {{-- ── Meta JSON ────────────────────────────────────────────────────── --}}
    <div class="col-12">
        <div class="form-group">
            <label>{{ trans('panel.meta_json') }}</label>
            <textarea name="meta" rows="3" class="form-control" placeholder='{"key": "value"}'>{{ old('meta', $isEditing && $booking->meta ? json_encode($booking->meta, JSON_PRETTY_PRINT) : '') }}</textarea>
            <small class="text-muted">{{ trans('panel.meta_json_hint') }}</small>
        </div>
    </div>

    {{-- ── Submit ───────────────────────────────────────────────────────── --}}
    <div class="col-12 mt-10">
        <button type="submit" class="btn btn-primary">
            {{ $isEditing ? trans('public.update') : trans('public.save') }}
        </button>
        <a href="{{ route('panel.bookings.index') }}" class="btn btn-outline-secondary ml-2">
            {{ trans('public.cancel') }}
        </a>
    </div>
</div>

<script>
    function toggleLocationFields(show) {
        document.getElementById('locationFields').style.display = show ? '' : 'none';
    }
</script>
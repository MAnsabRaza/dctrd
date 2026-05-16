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
        // Safe helper — agar $booking null hai to har jagah ?? '' kaam karega
        $isEditing = isset($booking) && !is_null($booking);
    @endphp

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.title') }} <span class="text-danger">*</span></label>
            <input name="title" type="text" class="form-control"
                   value="{{ old('title', $isEditing ? $booking->title : '') }}" required>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.slug') }}</label>
            <input name="slug" type="text" class="form-control"
                   value="{{ old('slug', $isEditing ? $booking->slug : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.category') }}</label>
            <select name="category_id" class="form-control">
                <option value="">{{ trans('panel.select_category') }}</option>
                @foreach($allCategoryLists as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $isEditing ? $booking->category_id : '') == $category->id ? 'selected' : '' }}>
                        {{ $category->title }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.price') }}</label>
            <input name="price" type="number" step="0.01" min="0" class="form-control"
                   value="{{ old('price', $isEditing ? $booking->price : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.discount_price') }}</label>
            <input name="discount_price" type="number" step="0.01" min="0" class="form-control"
                   value="{{ old('discount_price', $isEditing ? $booking->discount_price : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.capacity') }}</label>
            <input name="capacity" type="number" min="0" class="form-control"
                   value="{{ old('capacity', $isEditing ? $booking->capacity : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="form-group">
            <label>{{ trans('panel.min_persons') }}</label>
            <input name="min_persons" type="number" min="0" class="form-control"
                   value="{{ old('min_persons', $isEditing ? $booking->min_persons : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="form-group">
            <label>{{ trans('panel.max_persons') }}</label>
            <input name="max_persons" type="number" min="0" class="form-control"
                   value="{{ old('max_persons', $isEditing ? $booking->max_persons : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-4">
        <div class="form-group">
            <label>{{ trans('panel.duration_minutes') }}</label>
            <input name="duration_minutes" type="number" min="0" class="form-control"
                   value="{{ old('duration_minutes', $isEditing ? $booking->duration_minutes : '') }}">
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <label>{{ trans('panel.description') }}</label>
            <textarea name="description" rows="4" class="form-control">{{ old('description', $isEditing ? $booking->description : '') }}</textarea>
        </div>
    </div>

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
                   value="{{ old('lat', $isEditing ? $booking->lat : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>{{ trans('panel.longitude') }}</label>
            <input name="lng" type="number" step="0.000001" class="form-control"
                   value="{{ old('lng', $isEditing ? $booking->lng : '') }}">
        </div>
    </div>

    <div class="col-12 col-md-6 mb-15">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="bookingStatus" name="status" value="1"
                {{ old('status', $isEditing && $booking->status === 'published' ? 1 : 0) ? 'checked' : '' }}>
            <label class="custom-control-label" for="bookingStatus">{{ trans('public.active') }}</label>
        </div>
    </div>

    <div class="col-12 col-md-6 mb-15">
        <div class="custom-control custom-switch">
            <input type="checkbox" class="custom-control-input" id="bookingFeatured" name="featured" value="1"
                {{ old('featured', $isEditing && $booking->featured ? 1 : 0) ? 'checked' : '' }}>
            <label class="custom-control-label" for="bookingFeatured">{{ trans('panel.featured') }}</label>
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <label>{{ trans('panel.meta_json') }}</label>
            <textarea name="meta" rows="3" class="form-control" placeholder='{"key": "value"}'>{{ old('meta', $isEditing && $booking->meta ? json_encode($booking->meta, JSON_PRETTY_PRINT) : '') }}</textarea>
            <small class="text-muted">{{ trans('panel.meta_json_hint') }}</small>
        </div>
    </div>

    <div class="col-12">
        <button type="submit" class="btn btn-primary">
            {{ $isEditing ? trans('public.update') : trans('public.save') }}
        </button>
        <a href="{{ route('panel.bookings.index') }}" class="btn btn-outline-secondary ml-2">
            {{ trans('public.cancel') }}
        </a>
    </div>
</div>
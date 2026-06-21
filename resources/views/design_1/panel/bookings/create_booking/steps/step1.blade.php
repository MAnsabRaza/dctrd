{{--
    Step 1 — General Info
--}}
<h5 class="mb-1">General Information</h5>
<p class="text-muted mb-4">Basic details that identify this booking.</p>

<div class="row">

    <div class="col-12 mb-4">
        <label class="font-weight-bold mb-2 d-block">Booking Type <span class="text-danger">*</span></label>
        <div class="row">
            @foreach(['tour' => 'Tour', 'activity' => 'Activity', 'rental' => 'Rental', 'event' => 'Event', 'service' => 'Service', 'accommodation' => 'Accommodation'] as $value => $label)
                <div class="col-6 col-md-4 col-lg-2 mb-3">
                    <label class="border rounded p-3 text-center d-block mb-0" style="cursor:pointer;">
                        <input type="radio" name="booking_type" value="{{ $value }}"
                               {{ old('booking_type', $booking->booking_type ?? '') === $value ? 'checked' : '' }} required>
                        <div class="small font-weight-600 mt-1">{{ $label }}</div>
                    </label>
                </div>
            @endforeach
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>Title <span class="text-danger">*</span></label>
            <input name="title" type="text" class="form-control @error('title') is-invalid @enderror" required
                   value="{{ old('title', $booking->title ?? '') }}">
            @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>Slug</label>
            <input name="slug" type="text" class="form-control @error('slug') is-invalid @enderror"
                   placeholder="auto-generated-if-empty"
                   value="{{ old('slug', $booking->slug ?? '') }}">
            @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>Category</label>
            <select name="category_id" class="form-control">
                <option value="">Select category</option>
                @foreach($allCategoryLists as $category)
                    <option value="{{ $category->id }}"
                        {{ old('category_id', $booking->category_id ?? '') == $category->id ? 'selected' : '' }}>
                        {{ $category->title }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>Language</label>
            <select name="language" class="form-control">
                @foreach($userLanguages as $lang => $language)
                    <option value="{{ $lang }}" {{ old('language', $booking->language ?? app()->getLocale()) == $lang ? 'selected' : '' }}>
                        {{ $language }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>Sub Type</label>
            <input name="sub_type" type="text" class="form-control"
                   value="{{ old('sub_type', $booking->sub_type ?? '') }}">
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>Requirements</label>
            <input name="requirements" type="text" class="form-control"
                   value="{{ old('requirements', $booking->requirements ?? '') }}">
        </div>
    </div>

    <div class="col-12">
        <div class="form-group">
            <label>Description</label>
            <textarea name="description" rows="5" class="form-control">{{ old('description', $booking->description ?? '') }}</textarea>
        </div>
    </div>

</div>

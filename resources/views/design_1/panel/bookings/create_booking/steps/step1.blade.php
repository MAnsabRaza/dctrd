{{--
    Step 1 — General Info
--}}
<div class="section-head">
    <div class="badge-icon"><i class="fa fa-info-circle"></i></div>
    <div>
        <h6>General Information</h6>
        <p class="section-sub">Basic details that identify this booking.</p>
    </div>
</div>

<div class="panel-card">
    <label class="font-weight-bold mb-2 d-block">Booking Type <span class="text-danger">*</span></label>
    <div class="row">
        @foreach(['tour' => ['Tour','fa-map-signs'], 'activity' => ['Activity','fa-bolt'], 'rental' => ['Rental','fa-key'], 'event' => ['Event','fa-calendar'], 'service' => ['Service','fa-wrench'], 'accommodation' => ['Accommodation','fa-bed']] as $value => $opt)
            <div class="col-6 col-md-4 col-lg-2 mb-3">
                <label class="pill-check mb-0">
                    <input type="radio" name="booking_type" value="{{ $value }}"
                           {{ old('booking_type', $booking->booking_type ?? '') === $value ? 'checked' : '' }} required>
                    <span class="pill-box">
                        <i class="fa {{ $opt[1] }} d-block mb-1"></i>
                        {{ $opt[0] }}
                    </span>
                </label>
            </div>
        @endforeach
    </div>
</div>

<div class="panel-card">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-id-card"></i></div>
        <div>
            <h6>Basic Details</h6>
        </div>
    </div>
    <div class="row">
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
                <label>Slug
                    <span class="field-hint" title="Leave blank to auto-generate from the title">?</span>
                </label>
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
    </div>
</div>

<div class="panel-card mb-0">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-align-left"></i></div>
        <div>
            <h6>Description</h6>
        </div>
    </div>
    <div class="form-group mb-0">
        <textarea name="description" rows="5" class="form-control" placeholder="Tell customers what this booking is about...">{{ old('description', $booking->description ?? '') }}</textarea>
    </div>
</div>
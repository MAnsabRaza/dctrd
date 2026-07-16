{{--
    Step 1 — General Info

    booking_type now maps 1:1 to BookingTemplateConfig::allTypes() — whichever
    template is picked here is what every later step (2,3,7) will read fields/
    rules/filters from. $templateOptions comes straight from the config class
    so this view can never drift out of sync with it again.

    NOTE: Category filtering now works two ways:
    1) Server-side (guaranteed, no JS required): the "Filter Categories" button
       resubmits the form as GET to the same URL, so request('booking_type')
       is available on reload and the existing hidden/disabled Blade logic
       filters the <option> list correctly.
    2) Client-side JS (best-effort, instant, no reload): if/when the inline
       script below successfully executes, it does the same filtering live
       without needing a page reload. If it doesn't execute for any reason,
       option (1) still guarantees correct filtering.
--}}
@php
    $templateIcons = [
        \App\Services\BookingTemplateConfig::BEAUTY_SPA            => 'fa-cut',
        \App\Services\BookingTemplateConfig::DOCTORS_CLINICS       => 'fa-stethoscope',
        \App\Services\BookingTemplateConfig::EVENTS                => 'fa-calendar',
        \App\Services\BookingTemplateConfig::ACCOMMODATION         => 'fa-bed',
        \App\Services\BookingTemplateConfig::AUTOMOTIVE            => 'fa-car',
        \App\Services\BookingTemplateConfig::PROFESSIONAL_SERVICES => 'fa-briefcase',
        \App\Services\BookingTemplateConfig::EDUCATION_TRAINING    => 'fa-graduation-cap',
    ];

    // Templates whose sub_type is a real meaningful choice (per the config's own
    // validation rules) instead of free text — values match BookingTemplateConfig::for($type)->rules()['sub_type'].
    $subTypeOptionsMap = [
        \App\Services\BookingTemplateConfig::AUTOMOTIVE            => ['rental' => 'Rental', 'service' => 'Mechanic Service'],
        \App\Services\BookingTemplateConfig::DOCTORS_CLINICS       => ['physical' => 'Physical', 'online' => 'Online', 'both' => 'Both'],
        \App\Services\BookingTemplateConfig::PROFESSIONAL_SERVICES => ['online' => 'Online', 'in-person' => 'In-person', 'both' => 'Both'],
        \App\Services\BookingTemplateConfig::EDUCATION_TRAINING    => ['online' => 'Online', 'in-person' => 'In-person', 'both' => 'Both'],
    ];

    // IMPORTANT CHANGE: we now also check request()/query string, so that
    // after the "Filter Categories" GET-reload button is used, the selected
    // template, title, sub type etc. are all preserved and the category list
    // filters correctly — all without any JS.
    $currentType       = old('booking_type', request('booking_type', $booking->booking_type ?? ''));
    $currentStatus = old('status', request('status', $booking->status ?? 'draft'));
    $currentSubType    = old('sub_type', request('sub_type', $booking->sub_type ?? ''));
    $currentCategoryId = old('category_id', request('category_id', $booking->category_id ?? ''));
    $currentTitle       = old('title', request('title', $booking->title ?? ''));
    $currentSlug        = old('slug', request('slug', $booking->slug ?? ''));
    $currentDescription = old('description', request('description', $booking->description ?? ''));
    $currentRequirements = old('requirements', request('requirements', $booking->requirements ?? ''));
    $currentLanguage     = old('language', request('language', $booking->language ?? app()->getLocale()));

    $bookingCategoryOptions = collect($allCategoryLists ?? [])
        ->whereNotNull('parent_id')
        ->map(function ($category) use ($allCategoryLists) {
            $subTemplate = \App\Services\BookingSubTemplateConfig::forSlug($category->slug);
            $parent = collect($allCategoryLists ?? [])->firstWhere('id', $category->parent_id);

            return [
                'id' => $category->id,
                'title' => $category->title,
                'slug' => $category->slug,
                'parent_id' => $category->parent_id,
                'booking_type' => $subTemplate
                    ? $subTemplate->parentType()
                    : (!empty($parent) ? \Illuminate\Support\Str::slug($parent->slug ?: $parent->title) : ''),
            ];
        })
        ->values();

    // Pre-filter categories server-side for the currently selected type, so
    // the select only ever renders the relevant options (plus we still add
    // hidden/disabled as a belt-and-braces guard in case of stale JS state).
    $filteredCategoryOptions = empty($currentType)
        ? $bookingCategoryOptions
        : $bookingCategoryOptions->filter(fn ($opt) => $opt['booking_type'] === $currentType)->values();
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-info-circle"></i></div>
    <div>
        <h6>General Information</h6>
        <p class="section-sub">Basic details that identify this booking.</p>
    </div>
</div>

<div class="panel-card">
    <label class="font-weight-bold mb-2 d-block">Booking Template <span class="text-danger">*</span></label>
    <div class="row">
        @foreach($templateOptions as $value => $label)
            <div class="col-6 col-md-4 col-lg-3 mb-3">
                <label class="pill-check mb-0">
                    <input type="radio" name="booking_type" value="{{ $value }}" class="booking-type-radio"
                           onchange="window.panelBookingStep1ApplyTemplate && window.panelBookingStep1ApplyTemplate(this.value, false)"
                           {{ $currentType === $value ? 'checked' : '' }} required>
                    <span class="pill-box">
                        <i class="fa {{ $templateIcons[$value] ?? 'fa-bookmark' }} d-block mb-1"></i>
                        {{ $label }}
                    </span>
                </label>
            </div>
        @endforeach
    </div>

    {{--
        SERVER-SIDE FALLBACK (no JS required): clicking this resubmits the
        whole form as a GET request to the exact same URL. All currently
        typed values travel along as query params (thanks to the $current*
        vars above reading from request()), and booking_type becomes
        available server-side so the category <select> below renders
        already filtered correctly on reload.
    --}}
    <button type="submit" formmethod="GET" formaction="{{ url()->current() }}"
            class="btn btn-sm btn-outline-primary mt-2">
        <i class="fa fa-filter mr-1"></i> Apply Template &amp; Filter Categories
    </button>

    @if($isEditingTemplate = !empty($booking) && !empty($booking->id))
        <small class="text-muted d-block mt-1">
            <i class="fa fa-info-circle mr-1"></i> Changing the template after step 2 onward has been filled in may require re-checking those steps' fields.
        </small>
    @endif
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
                       value="{{ $currentTitle }}">
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
                       value="{{ $currentSlug }}">
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label>Category <span class="text-danger">*</span></label>
                <select name="category_id" id="panelBookingCategorySelect" class="form-control @error('category_id') is-invalid @enderror" required
                        {{ empty($currentType) ? 'disabled' : '' }}>
                    <option value="">
                        {{ empty($currentType)
                            ? 'Select booking template first'
                            : ($filteredCategoryOptions->isEmpty() ? 'No category found for this template' : 'Select category') }}
                    </option>
                    @foreach($filteredCategoryOptions as $categoryOption)
                        <option value="{{ $categoryOption['id'] }}"
                                data-slug="{{ $categoryOption['slug'] }}"
                                data-booking-type="{{ $categoryOption['booking_type'] }}"
                                {{ (string) $currentCategoryId === (string) $categoryOption['id'] ? 'selected' : '' }}>
                            {{ $categoryOption['title'] }}
                        </option>
                    @endforeach
                </select>
                @if(empty($currentType))
                    <small class="text-muted d-block mt-1">
                        Booking Template select karke "Apply Template &amp; Filter Categories" button dabao.
                    </small>
                @endif
                <small class="text-muted d-block mt-1" id="panelSubTemplateNote"></small>
                @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label>Language</label>
                <select name="language" class="form-control">
                    @foreach($userLanguages as $lang => $language)
                        <option value="{{ $lang }}" {{ $currentLanguage == $lang ? 'selected' : '' }}>
                            {{ $language }}
                        </option>
                    @endforeach
                </select>
            </div>
        </div>

        {{-- Sub Type — becomes a meaningful select for templates that define
             real sub_type options (automotive rental/service, online/in-person, etc.),
             plain free text otherwise. Server-side rendering picks the right
             input type immediately based on $currentType (no JS needed for this
             either, since $currentType is already known after the filter reload). --}}
        <div class="col-12 col-md-6">
            @php
                $subTypeOptions = $subTypeOptionsMap[$currentType] ?? null;
            @endphp

            @if($subTypeOptions)
                <div class="form-group" id="subTypeSelectWrap">
                    <label id="subTypeSelectLabel">Sub Type</label>
                    <select name="sub_type" id="subTypeSelect" class="form-control">
                        <option value="">Select sub type</option>
                        @foreach($subTypeOptions as $val => $optLabel)
                            <option value="{{ $val }}" {{ $currentSubType === $val ? 'selected' : '' }}>
                                {{ $optLabel }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group" id="subTypeTextWrap" style="display:none;">
                    <label>Sub Type</label>
                    <input type="text" id="subTypeText" class="form-control" value="{{ $currentSubType }}">
                </div>
            @else
                <div class="form-group" id="subTypeSelectWrap" style="display:none;">
                    <label id="subTypeSelectLabel">Sub Type</label>
                    <select name="sub_type" id="subTypeSelect" class="form-control"></select>
                </div>
                <div class="form-group" id="subTypeTextWrap">
                    <label>Sub Type</label>
                    <input name="sub_type" id="subTypeText" type="text" class="form-control"
                           value="{{ $currentSubType }}">
                </div>
            @endif
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label>Requirements</label>
                <input name="requirements" type="text" class="form-control"
                       value="{{ $currentRequirements }}">
            </div>
        </div>
      <div class="col-12 col-md-6">
    <div class="form-group">
        <label class="input-label">Status <span class="text-danger">*</span></label>
        <select name="status" class="form-control @error('status') is-invalid @enderror">
            <option value="">Select Status</option>
            <option value="draft"     {{ $currentStatus == 'draft'     ? 'selected' : '' }}>Draft</option>
            <option value="pending"   {{ $currentStatus == 'pending'   ? 'selected' : '' }}>Pending</option>
            <option value="published" {{ $currentStatus == 'published' ? 'selected' : '' }}>Published</option>
            <option value="rejected"  {{ $currentStatus == 'rejected'  ? 'selected' : '' }}>Rejected</option>
            <option value="inactive"  {{ $currentStatus == 'inactive'  ? 'selected' : '' }}>Inactive</option>
        </select>
        @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
        <textarea name="description" rows="5" class="form-control" placeholder="Tell customers what this booking is about...">{{ $currentDescription }}</textarea>
    </div>
</div>

@push('scripts_bottom')
<script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
<script src="/assets/admin/vendor/bootstrap-colorpicker/bootstrap-colorpicker.min.js"></script>
<script>
{{--
    This JS is a "nice to have" progressive enhancement. It gives instant,
    no-reload filtering IF it successfully executes on your page. The
    server-side "Apply Template & Filter Categories" button above already
    guarantees correct filtering even if this script never runs (e.g. if a
    theme/pjax layer strips inline <script> tags from partial includes) —
    so the feature works either way now.
--}}
;(function () {
function initPanelBookingStep1() {
    var subTypeOptionsMap = @json($subTypeOptionsMap);
    var currentSubType = {{ json_encode($currentSubType) }};
    var currentCategoryId = {{ json_encode((string) $currentCategoryId) }};
    var typeCategoryMap = @json($bookingTypeCategoryMap ?? []);
    var categoriesByParent = @json($categoriesByParent ?? []);
    var subTemplateConfigs = @json($subTemplateConfigs ?? []);

    var subTypeSelectWrap = document.getElementById('subTypeSelectWrap');
    var subTypeTextWrap = document.getElementById('subTypeTextWrap');
    var subTypeSelect = document.getElementById('subTypeSelect');
    var subTypeText = document.getElementById('subTypeText');
    var categorySelect = document.getElementById('panelBookingCategorySelect');
    var subTemplateNote = document.getElementById('panelSubTemplateNote');
    var renderedCategoryOptions = categorySelect
        ? Array.prototype.slice.call(categorySelect.querySelectorAll('option[data-booking-type]')).map(function (option) {
            return {
                id: option.value,
                title: option.textContent,
                slug: option.getAttribute('data-slug') || '',
                booking_type: option.getAttribute('data-booking-type') || ''
            };
        })
        : [];

    function normalizeSlug(value) {
        return String(value || '').toLowerCase().trim().replace(/[^a-z0-9]+/g, '-').replace(/^-+|-+$/g, '');
    }

    function findSubTemplate(slug) {
        slug = normalizeSlug(slug);
        return subTemplateConfigs[slug] || null;
    }

    function categoryMatchesType(category, type) {
        var sub = findSubTemplate(category.slug);
        return sub && sub.parent_type === type;
    }

    function getCategoryChildrenForType(type) {
        if (!type) return [];

        // Priority 1: server-rendered options (Blade already computed the
        // correct booking_type per category) — most reliable source.
        var matched = renderedCategoryOptions.filter(function (cat) {
            return cat.booking_type === type;
        });
        if (matched.length) {
            return matched;
        }

        // Priority 2: categoriesByParent + subTemplateConfigs match
        matched = [];
        Object.keys(categoriesByParent || {}).forEach(function (parentId) {
            (categoriesByParent[parentId] || []).forEach(function (cat) {
                if (categoryMatchesType(cat, type)) {
                    matched.push(cat);
                }
            });
        });
        if (matched.length) {
            return matched;
        }

        // Priority 3: last-resort parent-id lookup
        var normalizedType = normalizeSlug(type);
        var parentId = typeCategoryMap[type]
            || typeCategoryMap[normalizedType]
            || typeCategoryMap[type.toLowerCase()];

        return parentId && categoriesByParent[parentId] ? categoriesByParent[parentId] : [];
    }

    function setSelectOption(select, label, value) {
        var option = document.createElement('option');
        option.value = value || '';
        option.textContent = label;
        select.appendChild(option);
        return option;
    }

    function updateSubTemplateNote() {
        if (!categorySelect || !subTemplateNote) return;

        var selected = categorySelect.options[categorySelect.selectedIndex];
        var sub = selected ? findSubTemplate(selected.getAttribute('data-slug')) : null;

        subTemplateNote.textContent = sub
            ? 'Template: ' + sub.label + ' | Price unit: ' + (sub.price_unit || 'per booking')
            : '';
    }

    function populateCategories(type, selectedId) {
        if (!categorySelect) return;

        categorySelect.innerHTML = '';

        if (!type) {
            categorySelect.disabled = true;
            setSelectOption(categorySelect, 'Select booking template first', '');
            if (subTemplateNote) subTemplateNote.textContent = '';
            return;
        }

        var children = getCategoryChildrenForType(type);
        categorySelect.disabled = false;
        setSelectOption(categorySelect, children.length ? 'Select category' : 'No category found for this template', '');

        children.forEach(function (cat) {
            var option = setSelectOption(categorySelect, cat.title, cat.id);
            option.setAttribute('data-slug', cat.slug || '');

            if (String(cat.id) === String(selectedId || '')) {
                option.selected = true;
            }
        });

        updateSubTemplateNote();
    }

    function applyTemplate(type, isInitial) {
        var options = subTypeOptionsMap[type];

        if (subTypeSelect && subTypeText && subTypeSelectWrap && subTypeTextWrap) {
            if (options) {
                subTypeSelect.innerHTML = '';
                var placeholder = document.createElement('option');
                placeholder.value = '';
                placeholder.textContent = 'Select sub type';
                subTypeSelect.appendChild(placeholder);

                Object.keys(options).forEach(function (val) {
                    var opt = document.createElement('option');
                    opt.value = val;
                    opt.textContent = options[val];
                    if (isInitial && val === currentSubType) opt.selected = true;
                    subTypeSelect.appendChild(opt);
                });

                subTypeSelect.setAttribute('name', 'sub_type');
                subTypeText.removeAttribute('name');
                subTypeSelectWrap.style.display = 'block';
                subTypeTextWrap.style.display = 'none';
            } else {
                subTypeText.setAttribute('name', 'sub_type');
                subTypeSelect.removeAttribute('name');
                subTypeSelectWrap.style.display = 'none';
                subTypeTextWrap.style.display = 'block';
            }
        }

        populateCategories(type, isInitial ? currentCategoryId : null);
    }

    window.panelBookingStep1ApplyTemplate = applyTemplate;

    function applyCheckedTemplate(isInitial) {
        var checkedRadio = document.querySelector('.booking-type-radio:checked');
        applyTemplate(checkedRadio ? checkedRadio.value : '', isInitial);
    }

    document.addEventListener('change', function (event) {
        if (event.target && event.target.classList.contains('booking-type-radio')) {
            applyTemplate(event.target.value, false);
        }
    });

    document.addEventListener('click', function (event) {
        var radio = event.target && event.target.closest ? event.target.closest('.pill-check') : null;
        if (!radio) return;

        var input = radio.querySelector('.booking-type-radio');
        if (input) {
            window.setTimeout(function () {
                applyTemplate(input.value, false);
            }, 0);
        }
    });

    if (categorySelect && categorySelect.dataset.bookingCategoryReady !== 'true') {
        categorySelect.dataset.bookingCategoryReady = 'true';
        categorySelect.addEventListener('change', updateSubTemplateNote);
    }

    applyCheckedTemplate(true);
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', initPanelBookingStep1);
} else {
    initPanelBookingStep1();
}
})();
</script>
@endpush
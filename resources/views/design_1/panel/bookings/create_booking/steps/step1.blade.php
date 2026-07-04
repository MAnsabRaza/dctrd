{{--
    Step 1 — General Info

    booking_type now maps 1:1 to BookingTemplateConfig::allTypes() — whichever
    template is picked here is what every later step (2,3,7) will read fields/
    rules/filters from. $templateOptions comes straight from the config class
    so this view can never drift out of sync with it again.
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

    $currentType    = old('booking_type', $booking->booking_type ?? '');
    $currentSubType = old('sub_type', $booking->sub_type ?? '');
    $currentCategoryId = old('category_id', $booking->category_id ?? '');
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
                           {{ $currentType === $value ? 'checked' : '' }} required>
                    <span class="pill-box">
                        <i class="fa {{ $templateIcons[$value] ?? 'fa-bookmark' }} d-block mb-1"></i>
                        {{ $label }}
                    </span>
                </label>
            </div>
        @endforeach
    </div>
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
                <label>Category <span class="text-danger">*</span></label>
                <select name="category_id" id="panelBookingCategorySelect" class="form-control @error('category_id') is-invalid @enderror" {{ empty($currentType) ? 'disabled' : '' }} required>
                    <option value="">{{ empty($currentType) ? 'Select booking template first' : 'Select category' }}</option>
                </select>
                <small class="text-muted d-block mt-1" id="panelSubTemplateNote"></small>
                @error('category_id')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

        {{-- Sub Type — becomes a meaningful select for templates that define
             real sub_type options (automotive rental/service, online/in-person, etc.),
             plain free text otherwise. JS below swaps which input is "live" the
             moment a template pill is clicked, since the booking isn't saved yet. --}}
        <div class="col-12 col-md-6">
            <div class="form-group" id="subTypeSelectWrap" style="display:none;">
                <label id="subTypeSelectLabel">Sub Type</label>
                <select name="sub_type" id="subTypeSelect" class="form-control"></select>
            </div>
            <div class="form-group" id="subTypeTextWrap">
                <label>Sub Type</label>
                <input name="sub_type" id="subTypeText" type="text" class="form-control"
                       value="{{ $currentSubType }}">
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

<script>
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

        var matched = [];
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

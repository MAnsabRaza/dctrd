{{--
    Step 1 of 8 — General Info
    Maps to Image 1/2 top section: booking type cards + basic fields
--}}
@php
    $booking = $booking ?? null;
@endphp

<form data-wiz-form id="stepGeneralForm">
    <h5 class="mb-1">General Information</h5>
    <p class="text-muted mb-4">Basic details that identify this booking.</p>

    <div class="row">

        {{-- Booking type — card style selector like Image 1 --}}
        <div class="col-12 mb-4">
            <label class="font-weight-bold mb-2 d-block">Booking Type <span class="text-danger">*</span></label>
            <div class="row" id="bookingTypeCards">
                @foreach(['tour' => 'Tour', 'activity' => 'Activity', 'rental' => 'Rental', 'event' => 'Event', 'service' => 'Service', 'accommodation' => 'Accommodation'] as $value => $label)
                    <div class="col-6 col-md-4 col-lg-2 mb-3">
                        <label class="border rounded p-3 text-center d-block mb-0 booking-type-card {{ old('booking_type', $booking->booking_type ?? '') === $value ? 'border-primary' : '' }}" style="cursor:pointer;">
                            <input type="radio" name="booking_type" value="{{ $value }}" class="d-none"
                                   {{ old('booking_type', $booking->booking_type ?? '') === $value ? 'checked' : '' }}>
                            <div class="small font-weight-600">{{ $label }}</div>
                        </label>
                    </div>
                @endforeach
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label>Title <span class="text-danger">*</span></label>
                <input name="title" type="text" class="form-control" required
                       value="{{ old('title', $booking->title ?? '') }}">
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label>Slug</label>
                <input name="slug" type="text" class="form-control"
                       placeholder="auto-generated-if-empty"
                       value="{{ old('slug', $booking->slug ?? '') }}">
            </div>
        </div>

        <div class="col-12 col-md-6">
            <div class="form-group">
                <label>Category</label>
                <select name="category_id" id="categorySelect" class="form-control">
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

        {{-- Category-specific filters preview (populated by AJAX when category changes) --}}
        <div class="col-12" id="categoryFiltersPreview" style="{{ ($booking->category_id ?? null) ? '' : 'display:none' }}">
            <hr>
            <label class="font-weight-bold mb-2 d-block">Category Filters</label>
            <div id="categoryFiltersList" class="row"></div>
            <small class="text-muted">These filters help users find this booking. You can fine-tune them in the Location &amp; Filters step.</small>
        </div>

    </div>
</form>

<script>
(function () {
    document.querySelectorAll('.booking-type-card input[type=radio]').forEach(function (radio) {
        radio.addEventListener('change', function () {
            document.querySelectorAll('.booking-type-card').forEach(c => c.classList.remove('border-primary'));
            this.closest('.booking-type-card').classList.add('border-primary');
        });
    });

    const categorySelect = document.getElementById('categorySelect');
    categorySelect?.addEventListener('change', function () {
        const categoryId = this.value;
        const preview = document.getElementById('categoryFiltersPreview');
        const list = document.getElementById('categoryFiltersList');

        if (!categoryId) {
            preview.style.display = 'none';
            return;
        }

        fetch(`{{ url('panel/bookings/wizard/specifications') }}/${categoryId}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.json())
        .then(data => {
            list.innerHTML = '';
            (data.specifications || []).forEach(function (spec) {
                const col = document.createElement('div');
                col.className = 'col-12 col-md-4 mb-2';
                col.innerHTML = `<strong class="d-block small">${spec.title}</strong>`;
                (spec.booking_values || []).forEach(function (val) {
                    col.innerHTML += `
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="specification_values[]" value="${val.id}" id="spec_${val.id}">
                            <label class="form-check-label small" for="spec_${val.id}">${val.value}</label>
                        </div>`;
                });
                list.appendChild(col);
            });
            preview.style.display = (data.specifications || []).length ? '' : 'none';
        });
    });

    if (categorySelect?.value) {
        categorySelect.dispatchEvent(new Event('change'));
    }
})();
</script>

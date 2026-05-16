<form id="filtersForm" class="px-16 py-12 border-bottom-gray-100">

    <div class="row align-items-end">

        {{-- Search --}}
        <div class="col-12 col-md-3 mb-10 mb-md-0">
            <label class="font-12 text-muted mb-4">{{ trans('panel.search') }}</label>
            <input type="text"
                   name="search"
                   class="form-control form-control-sm"
                   placeholder="{{ trans('panel.search_by_title') }}"
                   value="{{ request('search') }}" />
        </div>

        {{-- Category --}}
        <div class="col-12 col-md-2 mb-10 mb-md-0">
            <label class="font-12 text-muted mb-4">{{ trans('panel.category') }}</label>
            <select name="category_id" class="form-control form-control-sm">
                <option value="">{{ trans('panel.all_categories') }}</option>
                @foreach($allCategoryLists as $category)
                    <option value="{{ $category->id }}"
                        {{ request('category_id') == $category->id ? 'selected' : '' }}>
                        {{ $category->title }}
                    </option>
                @endforeach
            </select>
        </div>

        {{-- Status --}}
        <div class="col-12 col-md-2 mb-10 mb-md-0">
            <label class="font-12 text-muted mb-4">{{ trans('public.status') }}</label>
            <select name="status" class="form-control form-control-sm">
                <option value="">{{ trans('panel.all_statuses') }}</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>{{ trans('public.active') }}</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>{{ trans('public.inactive') }}</option>
            </select>
        </div>

        {{-- Date Range --}}
        <div class="col-12 col-md-3 mb-10 mb-md-0">
            <label class="font-12 text-muted mb-4">{{ trans('public.date') }}</label>
            <div class="input-group input-group-sm">
                <input type="text"
                       name="from"
                       id="filterFrom"
                       class="form-control form-control-sm"
                       placeholder="{{ trans('panel.from') }}"
                       value="{{ request('from') }}"
                       readonly />
                <div class="input-group-prepend input-group-append">
                    <span class="input-group-text px-6">—</span>
                </div>
                <input type="text"
                       name="to"
                       id="filterTo"
                       class="form-control form-control-sm"
                       placeholder="{{ trans('panel.to') }}"
                       value="{{ request('to') }}"
                       readonly />
            </div>
        </div>

        {{-- Sort --}}
        <div class="col-12 col-md-2 mb-10 mb-md-0">
            <label class="font-12 text-muted mb-4">{{ trans('panel.sort') }}</label>
            <select name="sort" class="form-control form-control-sm">
                <option value="">{{ trans('panel.default') }}</option>
                <option value="create_date_desc" {{ request('sort') === 'create_date_desc' ? 'selected' : '' }}>{{ trans('panel.newest') }}</option>
                <option value="create_date_asc"  {{ request('sort') === 'create_date_asc'  ? 'selected' : '' }}>{{ trans('panel.oldest') }}</option>
                <option value="price_asc"         {{ request('sort') === 'price_asc'        ? 'selected' : '' }}>{{ trans('panel.price_low') }}</option>
                <option value="price_desc"        {{ request('sort') === 'price_desc'       ? 'selected' : '' }}>{{ trans('panel.price_high') }}</option>
            </select>
        </div>

    </div>

    <div class="row mt-10">
        <div class="col-12 d-flex align-items-center">
            <button type="submit" class="btn btn-primary btn-sm mr-8">
                {{ trans('public.filter') }}
            </button>
            <a href="{{ route('panel.bookings.index') }}" class="btn btn-outline-secondary btn-sm">
                {{ trans('panel.reset') }}
            </a>
        </div>
    </div>

</form>

@push('scripts_bottom')
<script>
    // Date-range picker for filters
    (function () {
        if (typeof daterangepicker === 'undefined') return;

        const opts = {
            autoUpdateInput: false,
            locale: { cancelLabel: '{{ trans('public.close') }}', applyLabel: '{{ trans('public.apply') }}' },
        };

        $('#filterFrom').daterangepicker({ ...opts, singleDatePicker: true }, function (start) {
            $('#filterFrom').val(start.format('YYYY-MM-DD'));
        });

        $('#filterTo').daterangepicker({ ...opts, singleDatePicker: true }, function (start) {
            $('#filterTo').val(start.format('YYYY-MM-DD'));
        });
    })();
</script>
@endpush
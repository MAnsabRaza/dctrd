<div class="position-relative products-lists-filters">
    <div class="products-lists-filters__mask"></div>

    <div class="position-relative d-flex flex-column flex-lg-row align-items-lg-center justify-content-lg-between gap-20 bg-white px-24 py-12 rounded-24 z-index-2">
        <div class="d-flex align-items-center flex-wrap gap-16 gap-lg-48">
            <div class="form-group mb-0 d-flex align-items-center">
                <div class="custom-switch mr-8">
                    <input id="top_filter_free" type="checkbox" name="free" value="on" class="custom-control-input">
                    <label class="custom-control-label cursor-pointer" for="top_filter_free"></label>
                </div>
                <label class="cursor-pointer mb-0" for="top_filter_free">{{ trans('update.free') }}</label>
            </div>

            <div class="form-group mb-0 d-flex align-items-center">
                <div class="custom-switch mr-8">
                    <input id="top_filter_instant" type="checkbox" name="options[]" value="instant_booking" class="custom-control-input">
                    <label class="custom-control-label cursor-pointer" for="top_filter_instant"></label>
                </div>
                <label class="cursor-pointer mb-0" for="top_filter_instant">{{ trans('update.instant_booking') }}</label>
            </div>
        </div>

        <div class="products-lists-sort-input form-group mb-0">
            <select name="sort" class="form-control select2" data-minimum-results-for-search="Infinity">
                <option disabled selected>{{ trans('public.sort_by') }}</option>
                <option value="">{{ trans('public.all') }}</option>
                @foreach(['newest', 'expensive', 'inexpensive', 'bestsellers', 'best_rates'] as $filterSort)
                    <option value="{{ $filterSort }}">{{ trans("public.{$filterSort}") }}</option>
                @endforeach
            </select>
        </div>
    </div>
</div>
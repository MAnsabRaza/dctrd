@if (!$authUser->up_selling)
    @php abort(403); @endphp
@endif

@push('styles_top')
    <style>
        .radio_style {
            display: flex;
            align-items: flex-start;
            gap: 10px;
            flex-direction: column;
        }

        .radio_style .input-label {
            margin-bottom: 0 !important;
        }

        small {
            font-size: 15px;
            font-weight: bold;
            font-style: italic;
            margin-top: 5px;
            display: block;
            color: #a16800;
        }
    </style>
@endpush

<section class="mt-30">
    <h2 class="section-title after-line">{{ trans('update.Up Selling') }}</h2>

    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'enable',
        'label' => trans('update.Enable'),
        'checked' => old('enable', $upSelling?->enable ?? 1),
        'small' => ''
    ])

    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'hide_on_single_product',
        'label' => trans('update.Hide On Single Product'),
        'checked' => old('hide_on_single_product', $upSelling?->hide_on_single_product ?? 1),
        'small' => ''
    ])

    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'hide_on_cart_page',
        'label' => trans('update.Hide On Cart Page'),
        'checked' => old('hide_on_cart_page', $upSelling?->hide_on_cart_page ?? 1),
        'small' => ''
    ])

    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'hide_on_checkout_page',
        'label' => trans('update.Hide On Checkout Page'),
        'checked' => old('hide_on_checkout_page', $upSelling?->hide_on_checkout_page ?? 1),
        'small' => ''
    ])

    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'hide_out_of_stock',
        'label' => trans('update.Hide out-of-stock products'),
        'checked' => old('hide_out_of_stock', $upSelling?->hide_out_of_stock ?? 1),
        'small' => ''
    ])

    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'hide_products_added_cart',
        'label' => trans('update.Hide Products Added to Cart'),
        'checked' => old('hide_products_added_cart', $upSelling?->hide_products_added_cart ?? 1),
        'small' => ''
    ])

    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'product_same_category',
        'label' => trans('update.Products in category'),
        'checked' => old('product_same_category', $upSelling?->product_category ?? 1),
        'small' => 'Upsell popup will show products in the same category. Upsell products of Upsells page will not use.'
    ])

    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'show_if_empty',
        'label' => trans('update.Show if empty'),
        'checked' => old('show_if_empty', $upSelling?->show_if_empty ?? 1),
        'small' => "Show upsell popup even if there's no upsells"
    ])

    @php
        $excludedUpsell = old('exclude_products_upsell', $upSelling?->exclude_products_upsell ? json_decode($upSelling->exclude_products_upsell) : []);
        $excludedUpsellPopup = old('exclude_products_upsell_popup', $upSelling?->exclude_products_upsell_popup ? json_decode($upSelling->exclude_products_upsell_popup) : []);
        $excludedCategories = old('exclude_categories_upsell', $upSelling?->exclude_categories_upsell ? json_decode($upSelling->exclude_categories_upsell) : []);
        $excludedCategoriesPopup = old('exclude_categories_upsell_popup', $upSelling?->exclude_categories_upsell_popup ? json_decode($upSelling->exclude_categories_upsell_popup) : []);
    @endphp

    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Exclude products to enable upsell') }}</label>
        <select name="exclude_products_upsell[]" id="exclude_products_upsell" class="form-control select2" multiple>
            <option value="">{{ trans('cross.Please Select') }}</option>
            @foreach ($upSellingProducts as $product)
                <option value="{{ $product->id }}" {{ in_array($product->id, $excludedUpsell) ? 'selected' : '' }}>
                    {{ $product->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Exclude products that display in upsell popup') }}</label>
        <select name="exclude_products_upsell_popup[]" id="exclude_products_upsell_popup" class="form-control select2" multiple>
            <option value="">{{ trans('cross.Please Select') }}</option>
            @foreach ($upSellingProducts as $product)
                <option value="{{ $product->id }}" {{ in_array($product->id, $excludedUpsellPopup) ? 'selected' : '' }}>
                    {{ $product->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Exclude categories to enable upsell') }}</label>
        <select name="exclude_categories_upsell[]" id="exclude_categories_upsell" class="form-control select2" multiple>
            <option value="">{{ trans('cross.Please Select') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ in_array($category->id, $excludedCategories) ? 'selected' : '' }}>
                    {{ $category->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Exclude categories that display in upsell popup') }}</label>
        <select name="exclude_categories_upsell_popup[]" id="exclude_categories_upsell_popup" class="form-control select2" multiple>
            <option value="">{{ trans('cross.Please Select') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" {{ in_array($category->id, $excludedCategoriesPopup) ? 'selected' : '' }}>
                    {{ $category->title }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Sort by') }}</label>
        <select name="sort_by" class="form-control">
            @for ($i = 1; $i <= 6; $i++)
                <option value="{{ $i }}" {{ old('sort_by', $crossSelling?->sort_by ?? 1) == $i ? 'selected' : '' }}>
                    {{ trans("update." . [
                        1 => 'Random',
                        2 => 'Title A-Z',
                        3 => 'Title Z-A',
                        4 => 'Price Highest',
                        5 => 'Price Lowest',
                        6 => 'Best Selling'
                    ][$i]) }}
                </option>
            @endfor
        </select>
    </div>
</section>

@push('scripts_bottom')
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="/assets/vendors/leaflet/leaflet.min.js"></script>

    <script>
        var selectProvinceLang = '{{ trans('update.select_province') }}';
        var selectCityLang = '{{ trans('update.select_city') }}';
        var selectDistrictLang = '{{ trans('update.select_district') }}';
        var leafletApiPath = '{{ getLeafletApiPath() }}';
    </script>

    <script src="/assets/default/js/panel/user_settings_tab.min.js"></script>

    {{-- Cross Selling JS --}}
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const displayOnSelect = document.getElementById('display_on');
            const displayTypeWrapper = document.getElementById('display_type_wrapper');
            const displayTypeFallback = document.getElementById('display_type_fallback');

            if (displayOnSelect) {
                displayOnSelect.addEventListener('change', function () {
                    if (this.value === '2') {
                        displayTypeWrapper.style.display = 'block';
                        displayTypeFallback.value = '1';
                    } else {
                        displayTypeWrapper.style.display = 'none';
                        displayTypeFallback.value = '0';
                    }
                });
            }
        });
    </script>
@endpush

@if (!$authUser->cross_selling)
    @php
        abort(403);
    @endphp
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

        small{
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
    <h2 class="section-title after-line">{{ trans('update.Cross Selling') }}</h2>

    {{-- Enable Cross Selling --}}
    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'enable',
        'label' => trans('update.Enable'),
        'checked' => old('enable', $crossSelling->enable ?? 1),
        'small' => ''
    ])

    {{-- Hide on Single Product --}}
    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'hide_on_single_product',
        'label' => trans('update.Hide on Single Product'),
        'checked' => old('hide_on_single_product', $crossSelling->hide_on_single_product ?? 1),
        'small' => ''
    ])

    {{-- Display On Single Product --}}
    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Display On') }}</label>
        <select name="display_on" id="display_on" class="form-control">
            @for($i = 1; $i <= 4; $i++)
                <option value="{{ $i }}" {{ old('display_on', $crossSelling->display_on ?? 1) == $i ? 'selected' : '' }}>
                    {{ trans("update." . [1 => 'Popup', 2 => 'Below Add to cart button', 3 => 'Above Description Tab', 4 => 'Below description'][$i]) }}
                </option>
            @endfor
        </select>
        <small>{{ trans('update.Select how/where you want to show cross sell on single product') }}</small>
    </div>

    {{-- Conditional Display Format --}}
    <div id="display_type_wrapper" style="display: {{ old('display_on', $crossSelling->display_on ?? 1) == 2 ? 'block' : 'none' }};">
        @include('web.default.panel.setting.setting_includes.partials.radio', [
            'name' => 'display_type_on_single_product',
            'label' => trans('update.Slider'),
            'checked' => old('display_type_on_single_product', $crossSelling->display_type_on_single_product ?? 1),
            'small' => ''
        ])
    </div>

    <input type="hidden" name="display_type_on_sinle_product" id="display_type_fallback"
        value="{{ old('display_on', $crossSelling->display_on ?? 1) == 2 ? old('display_type_on_sinle_product', $crossSelling->display_type_on_sinle_product ?? 1) : 0 }}">

    {{-- Show on Cart Page --}}
    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'show_on_cart_page',
        'label' => trans('update.Show on Cart Page'),
        'checked' => old('show_on_cart_page', $crossSelling->show_on_cart_page ?? 1),
        'small' => ''
    ])

    {{-- Product Bundle Type (Cart) --}}
    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Product Bundle Type (Cart)') }}</label>
        <select name="product_bundle_type_cart" class="form-control">
            @for($i = 1; $i <= 3; $i++)
                <option value="{{ $i }}" {{ old('product_bundle_type_cart', $crossSelling->product_bundle_type_cart ?? 1) == $i ? 'selected' : '' }}>
                    {{ trans("update." . [1 => 'The largest quantity in order', 2 => 'Random', 3 => 'The most expensive'][$i]) }}
                </option>
            @endfor
        </select>
        <small>{{ trans('update.Select product bundle type on cart page') }}</small>
    </div>

    {{-- Display On (Cart) --}}
    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Display On (Cart)') }}</label>
        <select name="display_on_cart" class="form-control">
            @for($i = 1; $i <= 4; $i++)
                <option value="{{ $i }}" {{ old('display_on_cart', $crossSelling->display_on_cart ?? 1) == $i ? 'selected' : '' }}>
                    {{ trans("update." . [1 => 'Popup', 2 => 'Below Add to cart button', 3 => 'Above Description Tab', 4 => 'Below description'][$i]) }}
                </option>
            @endfor
        </select>
        <small>{{ trans('update.Select how/where you want to show cross sell on cart page') }}</small>
    </div>

    {{-- Show on Checkout Page --}}
    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'show_on_checkout_page',
        'label' => trans('update.Show on Checkout Page'),
        'checked' => old('show_on_checkout_page', $crossSelling->show_on_checkout_page ?? 1),
        'small' => ''
    ])

    {{-- Product Bundle Type (Checkout) --}}
    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Product Bundle Type (Checkout)') }}</label>
        <select name="product_bundle_type_checkout" class="form-control">
            @for($i = 1; $i <= 3; $i++)
                <option value="{{ $i }}" {{ old('product_bundle_type_checkout', $crossSelling->product_bundle_type_checkout ?? 1) == $i ? 'selected' : '' }}>
                    {{ trans("update." . [1 => 'The largest quantity in order', 2 => 'Random', 3 => 'The most expensive'][$i]) }}
                </option>
            @endfor
        </select>
    </div>

    {{-- Display On (Checkout) --}}
    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Display On (Checkout)') }}</label>
        <select name="display_on_checkout" class="form-control">
            @for($i = 1; $i <= 4; $i++)
                <option value="{{ $i }}" {{ old('display_on_checkout', $crossSelling->display_on_checkout ?? 1) == $i ? 'selected' : '' }}>
                    {{ trans("update." . [1 => 'Popup', 2 => 'Below Add to cart button', 3 => 'Above Description Tab', 4 => 'Below description'][$i]) }}
                </option>
            @endfor
        </select>
    </div>

    {{-- Same Bundle in Cart --}}
    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'same_bundle_in_cart',
        'label' => trans('update.Same Bundle in Cart'),
        'checked' => old('same_bundle_in_cart', $crossSelling->same_bundle_in_cart ?? 1),
        'small' => trans('update.The same bundle can display in cart page and checkout page.')
    ])

    {{-- Description --}}
    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Description') }}</label>
        <textarea name="description" class="form-control">{{ old('description', $crossSelling->description ?? '') }}</textarea>
    </div>

    {{-- Display Saved Price --}}
    <div class="form-group mb-30">
        <label class="input-label">{{ trans('update.Display Saved Price') }}</label>
        <select name="display_saved_price" class="form-control">
            @for($i = 1; $i <= 3; $i++)
                <option value="{{ $i }}" {{ old('display_saved_price', $crossSelling->display_saved_price ?? 1) == $i ? 'selected' : '' }}>
                    {{ trans("update." . [1 => 'Price', 2 => 'Percent', 3 => 'None'][$i]) }}
                </option>
            @endfor
        </select>
    </div>

    {{-- Override Products --}}
    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'override_products',
        'label' => trans('update.Override Products'),
        'checked' => old('override_products', $crossSelling->override_products ?? 1),
        'small' => trans('update.Remove the same products on cart when add combo.')
    ])

    {{-- Hide Out of Stock --}}
    @include('web.default.panel.setting.setting_includes.partials.radio', [
        'name' => 'hide_out_of_stock',
        'label' => trans('update.Hide Out of Stock'),
        'checked' => old('hide_out_of_stock', $crossSelling->hide_out_of_stock ?? 1),
        'small' => trans('update.Do not show crosssell if one of bundle items is out of stock')
    ])
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


    {{-- Cross Selling --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const displayOnSelect = document.getElementById('display_on');
            const displayTypeWrapper = document.getElementById('display_type_wrapper');
            const displayTypeFallback = document.getElementById('display_type_fallback');

            displayOnSelect.addEventListener('change', function() {
                if (this.value === '2') {
                    displayTypeWrapper.style.display = 'block';
                    displayTypeFallback.value = '1'; // or your default
                } else {
                    displayTypeWrapper.style.display = 'none';
                    displayTypeFallback.value = '0';
                }
            });
        });
    </script>
@endpush

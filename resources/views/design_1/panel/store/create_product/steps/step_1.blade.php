@push('styles_top')
    <link rel="stylesheet" href="/assets/vendors/summernote/summernote-bs4.min.css">
@endpush

<div class="bg-white rounded-16 p-16 mt-32">

    <h3 class="font-14 font-weight-bold mb-24">{{ trans('public.basic_information') }}</h3>


    @include('design_1.panel.includes.locale.locale_select',[
        'itemRow' => !empty($product) ? $product : null,
        'withoutReloadLocale' => false,
        'extraClass' => ''
    ])

    <div class="form-group ">
        <label class="form-group-label is-required">{{ trans('public.type') }}</label>

        <select name="type" class="form-control select2 @error('type')  is-invalid @enderror" data-minimum-results-for-search="Infinity">
            @if(!empty(getStoreSettings('possibility_create_physical_product')) and getStoreSettings('possibility_create_physical_product'))
                <option value="physical" @if(!empty($product) and $product->isPhysical()) selected @endif>{{ trans('update.physical') }}</option>
            @endif

            @if(!empty(getStoreSettings('possibility_create_virtual_product')) and getStoreSettings('possibility_create_virtual_product'))
                <option value="virtual" @if(!empty($product) and $product->isVirtual()) selected @endif>{{ trans('update.virtual') }}</option>
            @endif
        </select>

        @error('type')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>


    <div class="form-group">
        <label class="form-group-label is-required">{{ trans('public.title') }}</label>
        <span class="has-translation bg-gray-300 rounded-8 p-8"><x-iconsax-lin-translate class="icons text-gray-500"/></span>
        <input type="text" name="title" class="form-control @error('title')  is-invalid @enderror" value="{{ (!empty($product) and !empty($product->translate($locale))) ? $product->translate($locale)->title : old('title') }}" placeholder=""/>
        @error('title')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="form-group mt-15">
        <label class="form-group-label is-required">{{ trans('public.seo_description') }}</label>
        <span class="has-translation bg-gray-300 rounded-8 p-8"><x-iconsax-lin-translate class="icons text-gray-500"/></span>
        <input type="text" name="seo_description" class="form-control @error('seo_description')  is-invalid @enderror " value="{{ (!empty($product) and !empty($product->translate($locale))) ? $product->translate($locale)->seo_description : old('seo_description') }}" placeholder="{{ trans('forms.50_160_characters_preferred') }}"/>
        @error('seo_description')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    {{-- Course Description --}}
    <h3 class="font-14 font-weight-bold my-24">{{ trans('public.description') }}</h3>

    <div class="form-group">
        <label class="form-group-label is-required">{{ trans('public.summary') }}</label>
        <textarea name="summary" rows="6" class="form-control @error('summary')  is-invalid @enderror " placeholder="{{ trans('update.product_summary_placeholder') }}">{{ (!empty($product) and !empty($product->translate($locale))) ? $product->translate($locale)->summary : old('summary') }}</textarea>
        @error('summary')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="form-group bg-white-editor">
        <label class="form-group-label is-required">{{ trans('public.description') }}</label>
        <textarea name="description" class="main-summernote form-control @error('description')  is-invalid @enderror" data-height="400" placeholder="{{ trans('forms.webinar_description_placeholder') }}">{!! (!empty($product) and !empty($product->translate($locale))) ? $product->translate($locale)->description : old('description')  !!}</textarea>
        @error('description')
        <div class="invalid-feedback">
            {{ $message }}
        </div>
        @enderror
    </div>

    <div class="form-group">
        <div class="d-flex align-items-center">
            <div class="custom-switch mr-8">
                <input id="orderingSwitch" type="checkbox" name="ordering" class="custom-control-input" {{ (!empty($product) and $product->ordering) ? 'checked' :  '' }}>
                <label class="custom-control-label cursor-pointer" for="orderingSwitch"></label>
            </div>

            <div class="">
                <label class="cursor-pointer" for="orderingSwitch">{{ trans('update.enable_ordering') }}</label>
            </div>
        </div>

        <p class="text-gray-500 font-12 mt-6">{{ trans('update.create_product_enable_ordering_hint') }}</p>
    </div>

    @if(!empty($product))
        @php
            $erpStaffIds = old('erp_staff_ids', $product->erp_staff_ids ?: []);
            $erpTaskTemplates = old('erp_task_templates', implode("\n", $product->erp_task_templates ?: []));

            // Category/Subcategory: DIRECT from product_categories table — NO API.
            // $productCategories is already passed to every step by the panel controller
            // (same variable used for the main category dropdown in step_2.blade.php),
            // with subCategories eager-loaded.
            $erpCategoriesMap = [];
            foreach ($productCategories as $erpTopCategory) {
                $erpSubs = [];
                if (!empty($erpTopCategory->subCategories) and count($erpTopCategory->subCategories)) {
                    foreach ($erpTopCategory->subCategories as $erpSub) {
                        $erpSubs[] = ['id' => $erpSub->id, 'title' => $erpSub->title];
                    }
                }
                $erpCategoriesMap[$erpTopCategory->id] = $erpSubs;
            }

            $erpSelectedCategoryId = old('erp_category_id', $product->erp_category_id);
            $erpSelectedSubcategoryId = old('erp_subcategory_id', $product->erp_subcategory_id);
        @endphp

        <div class="border-top pt-24 mt-24">
            <h3 class="font-14 font-weight-bold mb-16">Link Post-Sale Processes with ERP</h3>

            <div class="form-group">
                <div class="d-flex align-items-center">
                    <div class="custom-switch mr-8">
                        <input id="erpPostSaleSwitch" type="checkbox" name="erp_post_sale_enabled" class="custom-control-input" {{ (old('erp_post_sale_enabled') == 'on' || $product->erp_post_sale_enabled) ? 'checked' : '' }}>
                        <label class="custom-control-label cursor-pointer" for="erpPostSaleSwitch"></label>
                    </div>

                    <label class="cursor-pointer mb-0" for="erpPostSaleSwitch">Enable ERP Post-Sale Process</label>
                </div>
            </div>

            <div id="erpPostSaleFields" style="{{ (old('erp_post_sale_enabled') == 'on' || $product->erp_post_sale_enabled) ? '' : 'display:none' }}">
                <input type="hidden" name="erp_category_name" id="erpCategoryName" value="{{ old('erp_category_name', $product->erp_category_name) }}">
                <input type="hidden" name="erp_subcategory_name" id="erpSubcategoryName" value="{{ old('erp_subcategory_name', $product->erp_subcategory_name) }}">

                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="form-group-label">Category Mapping</label>
                            <select id="erpCategorySelect" name="erp_category_id" class="form-control select2">
                                <option value="">Select a category</option>
                                @foreach($productCategories as $erpTopCategory)
                                    <option value="{{ $erpTopCategory->id }}" {{ ((string) $erpSelectedCategoryId === (string) $erpTopCategory->id) ? 'selected' : '' }}>
                                        {{ $erpTopCategory->title }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="form-group-label">Subcategory Mapping</label>
                            <select id="erpSubcategorySelect" name="erp_subcategory_id" class="form-control select2" {{ empty($erpSelectedCategoryId) ? 'disabled' : '' }}>
                                <option value="">{{ empty($erpSelectedCategoryId) ? 'Select a category first' : 'Select a subcategory' }}</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-group-label">Staff to Assign</label>
                    <select id="erpStaffSelect" name="erp_staff_ids[]" class="form-control select2" multiple data-selected='@json($erpStaffIds)'>
                        @foreach($erpStaffIds as $staffId)
                            <option value="{{ $staffId }}" selected>{{ $staffId }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="form-group">
                    <label class="form-group-label">Task Templates</label>
                    <textarea name="erp_task_templates" rows="4" class="form-control" placeholder="One task title per line">{{ $erpTaskTemplates }}</textarea>
                </div>

                <p id="erpPostSaleMessage" class="text-gray-500 font-12 mt-8"></p>
            </div>
        </div>

        <script>
            // Category → Subcategory map built directly from product_categories (no API call)
            var erpCategoriesMap = @json($erpCategoriesMap);
            var erpSelectedSubcategoryId = "{{ $erpSelectedSubcategoryId }}";
        </script>
    @endif

    <div class="form-group mt-20 d-flex align-items-center">
        <div class="custom-switch mr-8">
            <input id="productLocationSwitch" type="checkbox" name="location_enabled" class="custom-control-input" {{ (old('location_enabled') == 'on' || (!empty($product) && $product->location_enabled)) ? 'checked' : '' }}>
            <label class="custom-control-label cursor-pointer" for="productLocationSwitch"></label>
        </div>

        <div class="">
            <label class="cursor-pointer" for="productLocationSwitch">{{ trans('admin/main.enable_location') }}</label>
        </div>
    </div>

  <div id="productLocationFields" style="{{ (old('location_enabled') == 'on' || (!empty($product) && $product->location_enabled)) ? '' : 'display:none' }}">
        @php $locationModel = $product ?? null; @endphp
        @include('partials._location_picker', [
            'locationModel' => $locationModel,
            'pickerId' => 'panelProductLocationPicker'
        ])
    </div>
</div>

@if(!empty($product))
    @include('admin.partials.qr-toggle-section', [
        'item'          => $product,
        'regenerateUrl' => url('/panel/store/products/'.$product->id.'/qr/regenerate'),
    ])
@endif


@push('scripts_bottom')
    <script src="/assets/vendors/summernote/summernote-bs4.min.js"></script>
    <script>
        function toggleProductLocation(show) {
            var container = document.getElementById('productLocationFields');
            if (!container) {
                return;
            }
            container.style.display = show ? '' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function () {
            var locationSwitch = document.getElementById('productLocationSwitch');
            if (locationSwitch) {
                locationSwitch.addEventListener('change', function () {
                    toggleProductLocation(this.checked);
                });
            }

            var erpSwitch = document.getElementById('erpPostSaleSwitch');
            var erpFields = document.getElementById('erpPostSaleFields');
            var erpMessage = document.getElementById('erpPostSaleMessage');
            var categorySelect = document.getElementById('erpCategorySelect');
            var subcategorySelect = document.getElementById('erpSubcategorySelect');
            var staffSelect = document.getElementById('erpStaffSelect');
            var categoryName = document.getElementById('erpCategoryName');
            var subcategoryName = document.getElementById('erpSubcategoryName');

            function setMessage(message) {
                if (erpMessage) {
                    erpMessage.textContent = message || '';
                }
            }

            function toggleErpFields(show) {
                if (erpFields) {
                    erpFields.style.display = show ? '' : 'none';
                }
            }

            function optionLabel(item) {
                return item.name || item.title || item.label || item.text || item.id;
            }

            function optionId(item) {
                return item.id || item.value || optionLabel(item);
            }

            function selectedValues(select) {
                try {
                    return JSON.parse(select.getAttribute('data-selected') || '[]').map(String);
                } catch (e) {
                    return [];
                }
            }

            function fillSelect(select, items, selected) {
                if (!select) {
                    return;
                }

                var selectedList = Array.isArray(selected) ? selected.map(String) : [String(selected || '')];
                select.innerHTML = '';

                items.forEach(function (item) {
                    var id = String(optionId(item));
                    var option = document.createElement('option');
                    option.value = id;
                    option.textContent = optionLabel(item);
                    if (selectedList.indexOf(id) !== -1) {
                        option.selected = true;
                    }
                    select.appendChild(option);
                });

                if (window.$ && $.fn.select2) {
                    $(select).trigger('change.select2');
                }
            }

            // Subcategory options come from the local erpCategoriesMap (built from
            // product_categories server-side) — no fetch, no API call.
            function updateSubcategories(preselectId) {
                if (!categorySelect || !subcategorySelect) {
                    return;
                }

                var categoryId = categorySelect.value;
                subcategorySelect.innerHTML = '';

                var subs = (window.erpCategoriesMap && erpCategoriesMap[categoryId]) || [];

                if (!categoryId || subs.length === 0) {
                    var emptyOpt = document.createElement('option');
                    emptyOpt.value = '';
                    emptyOpt.textContent = categoryId ? 'No subcategories' : 'Select a category first';
                    subcategorySelect.appendChild(emptyOpt);
                    subcategorySelect.disabled = true;
                } else {
                    var placeholderOpt = document.createElement('option');
                    placeholderOpt.value = '';
                    placeholderOpt.textContent = 'Select a subcategory';
                    subcategorySelect.appendChild(placeholderOpt);

                    subs.forEach(function (sub) {
                        var opt = document.createElement('option');
                        opt.value = sub.id;
                        opt.textContent = sub.title;
                        if (preselectId && String(preselectId) === String(sub.id)) {
                            opt.selected = true;
                        }
                        subcategorySelect.appendChild(opt);
                    });

                    subcategorySelect.disabled = false;
                }

                if (window.$ && $.fn.select2) {
                    $(subcategorySelect).trigger('change.select2');
                }

                if (categoryName) {
                    categoryName.value = categorySelect.selectedOptions[0] ? categorySelect.selectedOptions[0].textContent : '';
                }
                if (subcategoryName) {
                    subcategoryName.value = subcategorySelect.selectedOptions[0] ? subcategorySelect.selectedOptions[0].textContent : '';
                }
            }

            // Staff is the ONLY thing still fetched from Perfex (via our own backend route).
            function loadErpStaff() {
                if (!erpSwitch || !erpSwitch.checked || !staffSelect) {
                    return;
                }

                setMessage('Loading staff from Perfex...');

                fetch('/panel/store/products/erp/post-sale/staff')
                    .then(function (response) { return response.json(); })
                    .then(function (response) {
                        fillSelect(staffSelect, response.data || [], selectedValues(staffSelect));
                        setMessage('');
                    })
                    .catch(function () {
                        setMessage('Could not load staff list from Perfex. Saved values will remain.');
                    });
            }

            if (erpSwitch) {
                erpSwitch.addEventListener('change', function () {
                    toggleErpFields(this.checked);
                    loadErpStaff();
                });
            }

            if (categorySelect) {
                categorySelect.addEventListener('change', function () {
                    updateSubcategories(null);
                });
            }

            if (subcategorySelect) {
                subcategorySelect.addEventListener('change', function () {
                    if (subcategoryName) {
                        subcategoryName.value = subcategorySelect.selectedOptions[0] ? subcategorySelect.selectedOptions[0].textContent : '';
                    }
                });
            }

            // Initial state on page load: if a category is already selected
            // (editing an existing product), populate its subcategories immediately.
            if (categorySelect && categorySelect.value) {
                updateSubcategories(typeof erpSelectedSubcategoryId !== 'undefined' ? erpSelectedSubcategoryId : null);
            }

            loadErpStaff();
        });
    </script>
@endpush
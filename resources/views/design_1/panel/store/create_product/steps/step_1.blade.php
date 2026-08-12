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
                            <select id="erpCategorySelect" name="erp_category_id" class="form-control select2" data-selected="{{ old('erp_category_id', $product->erp_category_id) }}" data-selected-name="{{ old('erp_category_name', $product->erp_category_name) }}">
                                @if(!empty($product->erp_category_id))
                                    <option value="{{ $product->erp_category_id }}" selected>{{ $product->erp_category_name ?: $product->erp_category_id }}</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label class="form-group-label">Subcategory Mapping</label>
                            <select id="erpSubcategorySelect" name="erp_subcategory_id" class="form-control select2" data-selected="{{ old('erp_subcategory_id', $product->erp_subcategory_id) }}" data-selected-name="{{ old('erp_subcategory_name', $product->erp_subcategory_name) }}">
                                @if(!empty($product->erp_subcategory_id))
                                    <option value="{{ $product->erp_subcategory_id }}" selected>{{ $product->erp_subcategory_name ?: $product->erp_subcategory_id }}</option>
                                @endif
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
            var categories = [];

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

            function updateSubcategories() {
                if (!categorySelect || !subcategorySelect) {
                    return;
                }

                var selectedCategory = categories.find(function (item) {
                    return String(optionId(item)) === String(categorySelect.value);
                });
                var children = selectedCategory ? (selectedCategory.children || selectedCategory.subcategories || []) : [];
                fillSelect(subcategorySelect, children, subcategorySelect.getAttribute('data-selected'));
                categoryName.value = selectedCategory ? optionLabel(selectedCategory) : (categorySelect.selectedOptions[0] ? categorySelect.selectedOptions[0].textContent : '');
                subcategoryName.value = subcategorySelect.selectedOptions[0] ? subcategorySelect.selectedOptions[0].textContent : '';
            }

            function loadErpOptions() {
                if (!erpSwitch || !erpSwitch.checked) {
                    return;
                }

                setMessage('Loading Perfex ERP options...');

                Promise.all([
                    fetch('/panel/store/products/erp/post-sale/categories').then(function (response) { return response.json(); }),
                    fetch('/panel/store/products/erp/post-sale/staff').then(function (response) { return response.json(); })
                ]).then(function (responses) {
                    categories = responses[0].data || [];
                    fillSelect(categorySelect, categories, categorySelect.getAttribute('data-selected'));
                    updateSubcategories();
                    fillSelect(staffSelect, responses[1].data || [], selectedValues(staffSelect));
                    setMessage('');
                }).catch(function () {
                    setMessage('Perfex ERP options could not be loaded right now. Saved values will remain.');
                });
            }

            if (erpSwitch) {
                erpSwitch.addEventListener('change', function () {
                    toggleErpFields(this.checked);
                    loadErpOptions();
                });
            }

            if (categorySelect) {
                categorySelect.addEventListener('change', updateSubcategories);
            }

            if (subcategorySelect) {
                subcategorySelect.addEventListener('change', function () {
                    subcategoryName.value = subcategorySelect.selectedOptions[0] ? subcategorySelect.selectedOptions[0].textContent : '';
                });
            }

            loadErpOptions();
        });
    </script>
@endpush

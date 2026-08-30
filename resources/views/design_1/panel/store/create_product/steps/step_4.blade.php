@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/sortable/jquery-ui.min.css"/>
    <link rel="stylesheet" href="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.css">
@endpush

<div class="bg-white rounded-16 p-16 mt-32">

    {{-- Specifications --}}
    <div class="d-flex align-items-center justify-content-between p-12 rounded-16 border-gray-300 border-dashed">
        <div class="d-flex align-items-center">
            <div class="d-flex-center size-48 bg-primary-20 rounded-12">
                <x-iconsax-bul-video-tick class="icons text-primary" width="24px" height="24px"/>
            </div>

            <div class="ml-8">
                <h5 class="font-14 font-weight-bold">{{ trans('update.specifications') }}</h5>
                <p class="mt-4 font-12 text-gray-500">{{ trans('update.product_files_hint_1') }}</p>
            </div>
        </div>
    </div>

    <div class="row">
        <div class="col-lg-6">
            @include('design_1.panel.store.create_product.includes.accordions.specification')
        </div>

        <div class="col-lg-6 mt-16">
            @if(!empty($product->selectedSpecifications) and count($product->selectedSpecifications))
                <div class="p-16 rounded-16 border-gray-200">
                    <h3 class="font-14 font-weight-bold">{{ trans('update.specifications') }}</h3>

                    <ul class="draggable-content-lists file-draggable-lists" data-path="" data-drag-class="file-draggable-lists">
                        @foreach($product->selectedSpecifications as $selectedSpecificationRow)
                            @include('design_1.panel.store.create_product.includes.accordions.specification', ['selectedSpecification' => $selectedSpecificationRow])
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="d-flex-center flex-column px-32 py-120 text-center rounded-16 border-gray-200">
                    <div class="d-flex-center size-64 rounded-12 bg-primary-30">
                        <x-iconsax-bul-bill class="icons text-primary" width="32px" height="32px"/>
                    </div>
                    <h3 class="font-16 font-weight-bold mt-12">{{ trans('update.specifications_no_result') }}</h3>
                    <p class="mt-4 font-12 text-gray-500">{!! trans('update.specifications_no_result_hint') !!}</p>
                </div>
            @endif
        </div>
    </div>

    {{-- FAQ --}}
    <div class="d-flex align-items-center justify-content-between mt-32 p-12 rounded-16 border-gray-300 border-dashed">
        <div class="d-flex align-items-center">
            <div class="d-flex-center size-48 bg-primary-20 rounded-12">
                <x-iconsax-bul-bill class="icons text-primary" width="24px" height="24px"/>
            </div>

            <div class="ml-8">
                <h5 class="font-14 font-weight-bold">{{ trans('update.frequently_asked_questions') }}</h5>
                <p class="mt-4 font-12 text-gray-500">{{ trans('update.add_FAQ_and_display_them_on_the_course_page') }}</p>
            </div>
        </div>
    </div>


    <div class="row">
        <div class="col-lg-6">
            @include('design_1.panel.store.create_product.includes.accordions.faq')
        </div>

        <div class="col-lg-6 mt-16">
            @if(!empty($product->faqs) and count($product->faqs))
                <div class="p-16 rounded-16 border-gray-200">
                    <h3 class="font-14 font-weight-bold">{{ trans('update.faqs') }}</h3>

                    <ul class="draggable-content-lists faq-draggable-lists" data-path="" data-drag-class="faq-draggable-lists">
                        @foreach($product->faqs as $faqInfo)
                            @include('design_1.panel.store.create_product.includes.accordions.faq',['faq' => $faqInfo])
                        @endforeach
                    </ul>
                </div>
            @else
                <div class="d-flex-center flex-column px-32 py-120 text-center rounded-16 border-gray-200">
                    <div class="d-flex-center size-64 rounded-12 bg-primary-30">
                        <x-iconsax-bul-message-question class="icons text-primary" width="32px" height="32px"/>
                    </div>
                    <h3 class="font-16 font-weight-bold mt-12">{{ trans('update.product_faq_no_result') }}</h3>
                    <p class="mt-4 font-12 text-gray-500">{!! trans('update.product_faq_no_result_hint') !!}</p>
                </div>
            @endif
                </div>
    </div>

    {{-- ERP Post-Sale Automation --}}
    <div class="d-flex align-items-center justify-content-between mt-32 p-12 rounded-16 border-gray-300 border-dashed">
        <div class="d-flex align-items-center">
            <div class="d-flex-center size-48 bg-primary-20 rounded-12">
                <x-iconsax-bul-video-tick class="icons text-primary" width="24px" height="24px"/>
            </div>

            <div class="ml-8">
                <h5 class="font-14 font-weight-bold">Link Post-Sale Processes with ERP</h5>
                <p class="mt-4 font-12 text-gray-500">Automatically create a Perfex CRM project, tasks, and staff assignment after this product is purchased.</p>
            </div>
        </div>
    </div>

    <div class="p-16 rounded-16 border-gray-200 mt-16">

        {{-- Enable Toggle --}}
        <div class="form-group">
            <div class="d-flex align-items-center">
                <div class="custom-switch mr-8">
                    <input id="erpPostSaleEnabledSwitch" type="checkbox" name="erp_post_sale_enabled" class="custom-control-input" {{ (!empty($product) and $product->erp_post_sale_enabled) ? 'checked' : '' }}>
                    <label class="custom-control-label cursor-pointer" for="erpPostSaleEnabledSwitch"></label>
                </div>

                <div class="">
                    <label class="cursor-pointer" for="erpPostSaleEnabledSwitch">Enable ERP Post-Sale Process</label>
                </div>
            </div>
            <p class="text-gray-500 font-12 mt-8">When enabled, a Perfex project/task will be created automatically after a successful order for this product.</p>
        </div>

        {{-- ERP fields, hidden until toggle is on --}}
        <div id="erpPostSaleFieldsWrapper" class="{{ (!empty($product) and $product->erp_post_sale_enabled) ? '' : 'd-none' }}">

            <div class="row">
                {{-- Category (parent categories only, from product_categories table) --}}
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-group-label">Category</label>

                        <select name="erp_category_id" id="erpCategorySelect" class="select2 form-control @error('erp_category_id') is-invalid @enderror">
                            <option value="">Select a category</option>
                            @foreach($erpParentCategories as $erpParentCategory)
                                <option value="{{ $erpParentCategory->id }}" {{ (!empty($product) and $product->erp_category_id == $erpParentCategory->id) ? 'selected' : '' }}>
                                    {{ $erpParentCategory->title }}
                                </option>
                            @endforeach
                        </select>

                        @error('erp_category_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Subcategory: disabled until a Category is chosen, populated via JS from the map below --}}
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="form-group-label">Subcategory</label>

                        <select name="erp_subcategory_id" id="erpSubcategorySelect" class="select2 form-control @error('erp_subcategory_id') is-invalid @enderror" disabled>
                            <option value="">Select a category first</option>
                        </select>

                        @error('erp_subcategory_id')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                        @enderror
                    </div>
                </div>
            </div>

            {{-- Staff to Assign (from Perfex API) --}}
            <div class="form-group">
                <label class="form-group-label">Staff to Assign</label>

                <select name="erp_staff_ids[]" id="erpStaffSelect" class="select2 form-control @error('erp_staff_ids') is-invalid @enderror" multiple data-placeholder="Select staff members">
                    @if(!empty($erpPerfexStaff))
                        @foreach($erpPerfexStaff as $staffMember)
                            <option value="{{ $staffMember['staffid'] }}" {{ (!empty($product) and !empty($product->erp_staff_ids) and in_array($staffMember['staffid'], $product->erp_staff_ids)) ? 'selected' : '' }}>
                                {{ trim($staffMember['firstname'] . ' ' . $staffMember['lastname']) }}
                            </option>
                        @endforeach
                    @endif
                </select>

                @if(empty($erpPerfexStaff))
                    <p class="text-gray-500 font-12 mt-8">Could not load staff list from Perfex. You can save this product and add staff later.</p>
                @endif

                @error('erp_staff_ids')
                <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>

        </div>
    </div>

</div>

{{-- Category → Subcategory map, built entirely from product_categories (no API) --}}
<script>
    var erpCategoriesMap = @json($erpCategoriesMap);
    var erpSelectedSubcategoryId = "{{ !empty($product) ? $product->erp_subcategory_id : '' }}";
</script>

@push('scripts_bottom')
    <script src="/assets/default/vendors/sortable/jquery-ui.min.js"></script>
    <script src="/assets/default/vendors/bootstrap-tagsinput/bootstrap-tagsinput.min.js"></script>

    <script>
        (function ($) {
            'use strict';

            function toggleErpFields() {
                if ($('#erpPostSaleEnabledSwitch').is(':checked')) {
                    $('#erpPostSaleFieldsWrapper').removeClass('d-none');
                } else {
                    $('#erpPostSaleFieldsWrapper').addClass('d-none');
                }
            }

            function loadSubcategories(categoryId, preselectId) {
                var $sub = $('#erpSubcategorySelect');
                $sub.empty();

                var subs = erpCategoriesMap[categoryId] || [];

                if (!categoryId || subs.length === 0) {
                    $sub.append('<option value="">No subcategories</option>');
                    $sub.prop('disabled', true);
                    $sub.trigger('change.select2');
                    return;
                }

                $sub.append('<option value="">Select a subcategory</option>');
                subs.forEach(function (sub) {
                    var selected = (preselectId && String(preselectId) === String(sub.id)) ? 'selected' : '';
                    $sub.append('<option value="' + sub.id + '" ' + selected + '>' + sub.title + '</option>');
                });

                $sub.prop('disabled', false);
                $sub.trigger('change.select2');
            }

            $(document).on('change', '#erpPostSaleEnabledSwitch', toggleErpFields);

            $(document).on('change', '#erpCategorySelect', function () {
                loadSubcategories($(this).val(), null);
            });

            $(function () {
                toggleErpFields();

                var initialCategoryId = $('#erpCategorySelect').val();
                if (initialCategoryId) {
                    loadSubcategories(initialCategoryId, erpSelectedSubcategoryId);
                } else {
                    $('#erpSubcategorySelect').prop('disabled', true);
                }
            });

        })(jQuery);
    </script>
@endpush

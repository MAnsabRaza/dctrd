@extends('web.default.panel.layouts.panel_layout')

@push('styles_top')
@endpush

@section('content')
    <div class="">

        <form method="post" action="/panel/store/products/{{ !empty($product) ? $product->id . '/update' : 'store' }}"
            id="productForm" class="webinar-form">
            @include('web.default.panel.store.products.create_includes.progress')

            {{ csrf_field() }}
            <input type="hidden" name="current_step" value="{{ !empty($currentStep) ? $currentStep : 1 }}">
            <input type="hidden" name="draft" value="no" id="forDraft" />
            <input type="hidden" name="get_next" value="no" id="getNext" />
            <input type="hidden" name="get_step" value="0" id="getStep" />


            @if ($currentStep == 1)
                @include('web.default.panel.store.products.create_includes.step_1')
            @elseif(!empty($product))
                @include('web.default.panel.store.products.create_includes.step_' . $currentStep)
            @endif

        </form>


        <div
            class="create-webinar-footer d-flex flex-column flex-md-row align-items-center justify-content-between mt-20 pt-15 border-top">
            <div class="d-flex align-items-center">

                @if (!empty($product))
                    <a href="/panel/store/products/{{ $product->id }}/step/{{ $currentStep - 1 }}"
                        class="btn btn-sm btn-primary {{ $currentStep < 2 ? 'disabled' : '' }}">{{ trans('webinars.previous') }}</a>
                @else
                    <a href="" class="btn btn-sm btn-primary disabled">{{ trans('webinars.previous') }}</a>
                @endif

                <button type="button" id="getNextStep" class="btn btn-sm btn-primary ml-15"
                    @if ($currentStep >= 5) disabled @endif>{{ trans('webinars.next') }}</button>
            </div>

            <div class="mt-20 mt-md-0">
                <button type="button" id="sendForReview"
                    class="btn btn-sm btn-primary">{{ trans('public.send_for_review') }}</button>

                <button type="button" id="saveAsDraft"
                    class=" btn btn-sm btn-primary">{{ trans('public.save_as_draft') }}</button>

                @if (!empty($product) and $product->creator_id == $authUser->id)
                    @include('web.default.panel.includes.content_delete_btn', [
                        'deleteContentUrl' => "/panel/store/products/{$product->id}/delete?redirect_to=/panel/store/products",
                        'deleteContentClassName' => 'webinar-actions btn btn-sm btn-danger mt-20 mt-md-0',
                        'deleteContentItem' => $product,
                    ])
                @endif
            </div>
        </div>
    </div>
@endsection

@push('scripts_bottom')
    <script>
        var saveSuccessLang = '{{ trans('webinars.success_store') }}';
        var requestFailedLang = '{{ trans('public.request_failed') }}';
        var maxFourImageCanSelect = '{{ trans('update.max_four_image_can_select') }}';
    </script>

    <script src="/assets/default/js/panel/new_product.min.js"></script>

    {{-- Specifications and Variations --}}
    <script>
        $(window).on("load", function() { // Ensures script runs only after everything is loaded
            let selectedVariants = {};

            $(".variant-selector").select2({
                tags: true,
                createTag: function(params) {
                    return {
                        id: params.term,
                        text: params.term,
                        newOption: true
                    };
                }
            });

            $(document).on("change", ".variant-selector", function() {
                let selectElement = $(this);
                let variant_id = selectElement.data('variant_id');
                let values = selectElement.val() || [];

                if (values.length === 0) return;

                selectedVariants[variant_id] = values;

                saveNewVariant(variant_id, values);
                appendNewVariants();
            });

            function saveNewVariant(variantId, values) {
                return $.ajax({
                    url: "{{ route('save_new_value_variant') }}",
                    type: "POST",
                    data: {
                        variant_id: variantId,
                        values: values,
                        _token: $('meta[name="csrf-token"]').attr("content")
                    },
                    success: function(response) {
                        console.log("New Variant Saved:", values);
                    },
                    error: function(xhr) {
                        console.error("Error saving variant:", xhr.responseText);
                    }
                });
            }

            function appendNewVariants() {
                $('.no_variations').hide();
                $('#newSpecificationForm').removeClass('d-none');
                let allVariants = generateCombinations(Object.values(selectedVariants));

                let tableBody = $("#variantTable tbody");
                let existingVariants = new Set();

                // Collect existing variant names to prevent duplicates
                tableBody.find("input[name='variant_name[]']").each(function() {
                    existingVariants.add($(this).val().trim().toLowerCase());
                });

                if (allVariants.length === 0) {
                    if (!existingVariants.has("default variant")) {
                        appendVariantRow("variant_1", "Default Variant");
                    }
                }

                allVariants.forEach((variantName, index) => {
                    let normalizedVariant = variantName.trim().toLowerCase();
                    if (!existingVariants.has(normalizedVariant)) {
                        let variantId = `variant_${index + 1}`;
                        appendVariantRow(variantId, variantName);
                        existingVariants.add(normalizedVariant); // Add to prevent future duplicates
                    }
                });
            }


            function generateCombinations(arrays) {
                if (arrays.length === 0) return [];
                return arrays.reduce((acc, current) => {
                    let result = [];
                    acc.forEach(a => {
                        current.forEach(b => {
                            result.push(a && b ? `${a}-${b}` : b);
                        });
                    });
                    return result;
                }, [""]);
            }

            function appendVariantRow(variantId, variantName) {
                let tableBody = $("#variantTable tbody");

                let row = `
                    <tr data-variant-id="${variantId}">
                        <td><input type="text" name="variant_name[]" class="form-control" value="${variantName}"></td>
                        <td><input type="number" name="variant_price[]" class="form-control"></td>
                        <td><input type="number" name="variant_stock[]" class="form-control"></td>
                        <td><input type="number" name="variant_initial_price[]" class="form-control"></td>
                        <td><input type="number" name="variant_discount[]" class="form-control"></td>
                        <td><input type="text" name="variant_sku[]" class="form-control"></td>
                        <td>
                            <div class="input-group-prepend">
                                <button type="button" class="input-group-text panel-file-manager" data-input="variant_image_${variantId}" data-preview="holder">
                                    <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 52 52" xml:space="preserve"><path d="M48.5 31h-3c-.8 0-1.5.8-1.5 1.5v10c0 .8-.7 1.5-1.5 1.5h-33c-.8 0-1.5-.7-1.5-1.5v-10c0-.7-.7-1.5-1.5-1.5h-3c-.8 0-1.5.8-1.5 1.5V46c0 2.2 1.8 4 4 4h40c2.2 0 4-1.8 4-4V32.5c0-.7-.7-1.5-1.5-1.5"/><path d="M27 2.4c-.6-.6-1.5-.6-2.1 0L11.4 15.9c-.6.6-.6 1.5 0 2.1l2.1 2.1c.6.6 1.5.6 2.1 0l5.6-5.6c.6-.6 1.8-.2 1.8.7v21.2c0 .8.6 1.5 1.4 1.5h3c.8 0 1.6-.8 1.6-1.5V15.3c0-.9 1-1.3 1.7-.7l5.6 5.6c.6.6 1.5.6 2.1 0l2.1-2.1c.6-.6.6-1.5 0-2.1z"/></svg>
                                </button>
                            </div>
                            <input type="text" name="variant_image[]" id="variant_image_${variantId}" class="form-control" placeholder="Variant Image"/>
                        </td>
                        <td>
                            <button type="button" class="btn btn-danger delete-variant">
                                <svg width="20" height="20" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#fff"><path fill-rule="evenodd" clip-rule="evenodd" d="M10 3h3v1h-1v9l-1 1H4l-1-1V4H2V3h3V2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1zM9 2H6v1h3zM4 13h7V4H4zm2-8H5v7h1zm1 0h1v7H7zm2 0h1v7H9z"/></svg>
                            </button>
                        </td>
                    </tr>
                `;

                tableBody.append(row);
            }

            $(document).on("click", ".delete-variant", function() {
                $(this).closest("tr").remove();
            });

        });
    </script>

    {{-- Specifications --}}
    <script>
        $(document).ready(function() {
            $('#categories').on('change', function() {
                let categoryId = $(this).val();
                var specification_url = "{{ route('get_category_specifications', ['id' => '#id']) }}";
                specification_url = specification_url.replace('#id', categoryId);

                if (categoryId) {
                    $.ajax({
                        url: specification_url,
                        type: 'GET',
                        dataType: 'json',
                        success: function(response) {
                            let specificationsHtml = '';
                            console.log(response);

                            response.forEach(specification => {
                                if (specification.input_type === 'multi_value') {
                                    let optionsHtml = '';

                                    if (specification.multi_values) {
                                        specification.multi_values.forEach(
                                            multiValue => {
                                                optionsHtml +=
                                                    `<option value="${multiValue.title}">${multiValue.title}</option>`;
                                            });
                                    }

                                    specificationsHtml += `
                                <div class="w-100">
                                    <label for="">${specification.title}</label>
                                    <div class="form-group js-multi-values-input multi_value">
                                        <select name="${specification.title}"
                                            class="js-ajax-multi_values form-control select-multi-values-select2 variant-selector"
                                            multiple
                                            data-placeholder="Select Specification"
                                            data-allow-clear="false"
                                            data-search="false"
                                            data-tags="true"
                                            data-variant_id="${specification.id}">
                                            ${optionsHtml}
                                        </select>
                                        <div class="invalid-feedback"></div>
                                    </div>
                                </div>
                            `;
                                }
                            });

                            $('#specificationsList').html(specificationsHtml);

                            // Reinitialize Select2 after appending new elements
                            $('.select-multi-values-select2').select2({
                                width: '100%',
                                placeholder: "Select Specification",
                                allowClear: false
                            });
                        }
                    });
                } else {
                    $('#specificationsList').html('');
                }
            });
        });
    </script>
@endpush

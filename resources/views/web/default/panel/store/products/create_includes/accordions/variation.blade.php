
{{-- <div class="d-flex justify-content-between" style="gap:10px;">
    @foreach ($productSpecifications as $productSpecification)
        @if ($productSpecification->input_type == 'multi_value')
            <div class="w-100">
                <label for="">{{ $productSpecification->title }}</label>
                <div class="form-group js-multi-values-input multi_value">
                    <select name="{{ $productSpecification->title }}"
                        class="js-ajax-multi_values form-control select-multi-values-select2 variant-selector"
                        multiple
                        data-placeholder="{{ trans('update.select_specification_params') }}"
                        data-allow-clear="false"
                        data-search="false"
                        data-tags="true"
                        data-search="false" data-tags="true" data-variant_id="{{ $productSpecification->id }}">
                        @if (!empty($productSpecification) and !empty($productSpecification->multiValues))
                            @foreach ($productSpecification->multiValues as $multiValue)
                                <option value="{{ $multiValue->title }}">{{ $multiValue->title }}</option>
                            @endforeach
                        @endif
                    </select>

                    <div class="invalid-feedback"></div>
                </div>
            </div>
        @endif
    @endforeach
</div> --}}

<!-- Table for Variants -->
<div class="table-responsive">
    <table class="table table-bordered mt-4" id="variantTable">
        <thead>
            <tr>
                <th>Variant</th>
                <th>Variant Price</th>
                <th>Stock</th>
                <th>Initial Price</th>
                <th>Discount %</th>
                <th>SKU No</th>
                <th>Image</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($product->variants as $variant)
                <tr data-variant-id="{{ $variant->id }}">
                    <td>
                        <input type="text" name="variant_name[]" class="form-control" value="{{ $variant->name }}">
                    </td>
                    <td><input type="number" name="variant_price[]" class="form-control" value="{{ $variant->price }}">
                    </td>
                    <td><input type="number" name="variant_stock[]" class="form-control" value="{{ $variant->stock }}">
                    </td>
                    <td><input type="number" name="variant_initial_price[]" class="form-control"
                            value="{{ $variant->initial_price }}"></td>
                    <td><input type="number" name="variant_discount[]" class="form-control"
                            value="{{ $variant->discount }}"></td>
                    <td><input type="text" name="variant_sku[]" class="form-control" value="{{ $variant->sku }}">
                    </td>
                    <td>
                        <div class="input-group-prepend">
                            <button type="button" class="input-group-text panel-file-manager" data-input="variant_image_{{ $variant->id }}" data-preview="holder">
                                <svg fill="#fff" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 52 52" xml:space="preserve"><path d="M48.5 31h-3c-.8 0-1.5.8-1.5 1.5v10c0 .8-.7 1.5-1.5 1.5h-33c-.8 0-1.5-.7-1.5-1.5v-10c0-.7-.7-1.5-1.5-1.5h-3c-.8 0-1.5.8-1.5 1.5V46c0 2.2 1.8 4 4 4h40c2.2 0 4-1.8 4-4V32.5c0-.7-.7-1.5-1.5-1.5"/><path d="M27 2.4c-.6-.6-1.5-.6-2.1 0L11.4 15.9c-.6.6-.6 1.5 0 2.1l2.1 2.1c.6.6 1.5.6 2.1 0l5.6-5.6c.6-.6 1.8-.2 1.8.7v21.2c0 .8.6 1.5 1.4 1.5h3c.8 0 1.6-.8 1.6-1.5V15.3c0-.9 1-1.3 1.7-.7l5.6 5.6c.6.6 1.5.6 2.1 0l2.1-2.1c.6-.6.6-1.5 0-2.1z"/></svg>
                            </button>
                        </div>
                        <input type="text" name="variant_image[]" id="variant_image_{{ $variant->id }}"
                            class="form-control" value="{{ $variant->image }}" placeholder="Variant Image" />
                    </td>
                    <td>
                        <input type="hidden" name="variant_id[]" value="{{ $variant->id }}">
                        <a href="{{ route('delete_product_variants',$variant->id) }}" class="btn btn-danger" onclick="return confirm('Are you sure?')">
                            <svg width="20" height="20" viewBox="0 0 16 16" xmlns="http://www.w3.org/2000/svg" fill="#fff"><path fill-rule="evenodd" clip-rule="evenodd" d="M10 3h3v1h-1v9l-1 1H4l-1-1V4H2V3h3V2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1zM9 2H6v1h3zM4 13h7V4H4zm2-8H5v7h1zm1 0h1v7H7zm2 0h1v7H9z"/></svg>
                        </a>
                    </td>
                </tr>
            @endforeach
        </tbody>

    </table>
</div>

{{-- resources/views/partials/checkout_modules/_staff_member.blade.php --}}
@php
    $itemKey = $itemId ?? '0';
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";

    // Staff options from module config (array of ['id', 'name', 'avatar'])
    $staffOptions = $module->config['staff'] ?? [];
@endphp

<div
    class="checkout-module-card checkout-module-card--staff"
    id="checkout-module-staff-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="none"
>
    {{-- Header --}}
    <div class="font-13 font-weight-bold text-dark mb-4">
        {{ $module->translated_label }}
        @if($module->is_required)
            <span class="text-danger">*</span>
        @endif
    </div>
    @if($module->translated_help_text)
        <p class="checkout-module-helper mb-0 mt-4">{{ $module->translated_help_text }}</p>
    @endif

    {{-- Inline Staff Card (matches client design — green tick + name) --}}
    <div class="checkout-inline-fields-card border border-primary rounded-12 p-10 mt-12">
        <div class="d-flex align-items-center">
            <x-iconsax-lin-profile-2user class="icons text-primary mr-8" width="16px" height="16px"/>

            @if(!empty($staffOptions))
                {{-- Radio-style staff options displayed as styled select --}}
                <select
                    name="{{ $prefix }}"
                    class="checkout-staff-select border-0 bg-transparent font-12 text-dark flex-1 p-0"
                    id="checkout_staff_{{ $itemKey }}"
                    {{ $module->is_required ? 'required' : '' }}
                    style="outline: none; cursor: pointer; -webkit-appearance: none; appearance: none;"
                >
                    <option value="">--- {{ trans('checkout.select_staff') }} ---</option>
                    @foreach($staffOptions as $staff)
                        <option
                            value="{{ $staff['id'] ?? $staff['name'] }}"
                            {{ old('checkout_modules.' . $itemKey . '.' . $module->name) == ($staff['id'] ?? $staff['name']) ? 'selected' : '' }}
                        >
                            {{ $staff['name'] }}
                        </option>
                    @endforeach
                </select>
            @else
                {{-- Fallback: free-text or dynamic load via JS --}}
                <select
                    name="{{ $prefix }}"
                    class="checkout-staff-select border-0 bg-transparent font-12 text-dark flex-1 p-0"
                    id="checkout_staff_{{ $itemKey }}"
                    {{ $module->is_required ? 'required' : '' }}
                    style="outline: none; cursor: pointer;"
                >
                    <option value="">--- {{ trans('checkout.select_staff') }} ---</option>
                </select>
            @endif
        </div>
    </div>

    @error('checkout_modules.' . $itemKey . '.staff_member')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
<script>
    $(document).on('change', '.checkout-staff-select', function () {
        $(document).trigger('checkout:priceUpdate');
    });
</script>
@endpush
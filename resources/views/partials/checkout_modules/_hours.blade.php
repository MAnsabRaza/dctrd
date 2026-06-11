{{-- resources/views/partials/checkout_modules/_hours.blade.php --}}
@php
    $itemKey = $itemId ?? '0';
    $slots = $module->config['slots'] ?? ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
    $prefix = isset($itemId) ? "checkout_modules[{$itemId}][{$module->name}]" : "checkout_modules[{$module->name}]";
    $priceRule = $module->price_rule ?? [];
    $perHourAmount = $priceRule['amount'] ?? 0;

    // Build paired start→end slots for range display (e.g. 06:00 PM - 07:00 PM)
    // Each slot value is stored as "HH:MM" 24-hr; we display in 12-hr format
    function formatHourSlot12($t) {
        try {
            return \Carbon\Carbon::createFromFormat('H:i', $t)->format('h:i A');
        } catch (\Throwable $e) {
            return $t;
        }
    }
@endphp

<div
    class="checkout-module-card checkout-module-card--hours"
    id="checkout-module-hours-{{ $itemKey }}"
    data-module-name="{{ $module->name }}"
    data-price-type="per_hour"
    data-price-amount="{{ $perHourAmount }}"
>
    {{-- Header --}}
    <div class="d-flex align-items-start justify-content-between mb-12">
        <div>
            <div class="font-13 font-weight-bold text-dark">
                {{ $module->translated_label }}
                @if($module->is_required)
                    <span class="text-danger">*</span>
                @endif
            </div>
            @if($module->translated_help_text)
                <p class="checkout-module-helper mb-0 mt-4">{{ $module->translated_help_text }}</p>
            @endif
        </div>
        @if($perHourAmount)
            <span class="checkout-module-price">{{ handlePrice($perHourAmount) }}/hr</span>
        @endif
    </div>

    {{-- Time Slot Grid --}}
    <div class="checkout-time-slot-grid">
        @foreach($slots as $slot)
            @php
                // Calculate end time = start + 1 hour
                try {
                    $startCarbon = \Carbon\Carbon::createFromFormat('H:i', $slot);
                    $endCarbon   = $startCarbon->copy()->addHour();
                    $displayLabel = $startCarbon->format('h:i A') . ' - ' . $endCarbon->format('h:i A');
                } catch (\Throwable $e) {
                    $displayLabel = $slot;
                }
                $slotId = 'checkout_time_' . $itemKey . '_' . str_replace(':', '', $slot);
                $isChecked = old('checkout_modules.' . $itemKey . '.' . $module->name) == $slot;
            @endphp

            <label class="checkout-time-slot-option" for="{{ $slotId }}">
                <input
                    type="radio"
                    name="{{ $prefix }}"
                    value="{{ $slot }}"
                    class="checkout-time-slot"
                    id="{{ $slotId }}"
                    {{ $isChecked ? 'checked' : '' }}
                    {{ $module->is_required ? 'required' : '' }}
                >
                <span>{{ $displayLabel }}</span>
            </label>
        @endforeach
    </div>

    @error('checkout_modules.' . $itemKey . '.hours')
        <div class="text-danger small mt-1">{{ $message }}</div>
    @enderror
</div>

@push('scripts_bottom')
<script>
    $(document).on('change', '.checkout-time-slot', function () {
        $(document).trigger('checkout:priceUpdate');
    });
</script>
@endpush
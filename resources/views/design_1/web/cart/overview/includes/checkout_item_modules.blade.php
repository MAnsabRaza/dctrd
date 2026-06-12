@push('styles_top')
<style>
/* ==================================================
   Checkout Modules — Unified Card (Image 2 design)
   ================================================== */

/* The single green-border unified card wrapper */
.checkout-unified-card {
    display: flex;
    align-items: stretch;
    border: 1.5px solid #22c55e;
    border-radius: 12px;
    overflow: hidden;
    background: #fff;
}

/* Each module column inside the unified card */
.checkout-unified-col {
    flex: 1 1 0;
    min-width: 0;
    padding: 10px 14px;
    position: relative;
}

/* Vertical divider between columns */
.checkout-unified-col + .checkout-unified-col {
    border-left: 1px solid #e2e8f0;
}

/* Column heading label */
.checkout-unified-col__label {
    font-size: 11px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 4px;
}

/* Column value row */
.checkout-unified-col__value {
    display: flex;
    align-items: center;
    gap: 6px;
    font-size: 12px;
    color: #0f172a;
}

/* Green icon color */
.checkout-unified-col__value .icon-green {
    color: #22c55e;
    flex-shrink: 0;
}

/* Date input inside column */
.checkout-col-date-input {
    border: none !important;
    outline: none !important;
    background: transparent !important;
    font-size: 12px;
    color: #0f172a;
    padding: 0 !important;
    width: 100%;
    cursor: pointer;
}

.checkout-col-date-input::-webkit-calendar-picker-indicator {
    opacity: 0.5;
    cursor: pointer;
    margin-left: -2px;
}

/* Staff select inside column */
.checkout-col-staff-select {
    border: none !important;
    outline: none !important;
    background: transparent !important;
    font-size: 12px;
    color: #0f172a;
    padding: 0 !important;
    width: 100%;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
}

/* Nights badge */
.checkout-nights-badge {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    margin-top: 6px;
    padding: 2px 8px;
    border-radius: 999px;
    background: rgba(34, 197, 94, 0.1);
    border: 1px solid rgba(34, 197, 94, 0.3);
    font-size: 11px;
    color: #15803d;
    font-weight: 500;
}

/* Time slot pills */
.checkout-time-pills {
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    margin-top: 4px;
}

.checkout-time-pill {
    padding: 3px 8px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    font-size: 11px;
    color: #334155;
    cursor: pointer;
    transition: all 0.15s;
    display: inline-flex;
    align-items: center;
}

.checkout-time-pill input[type="radio"] { display: none; }

.checkout-time-pill:has(input:checked) {
    border-color: #22c55e;
    background: rgba(34, 197, 94, 0.08);
    color: #15803d;
    font-weight: 600;
}

/* Extra services */
.checkout-extras-list {
    display: flex;
    flex-direction: column;
    gap: 4px;
    margin-top: 4px;
}

.checkout-extra-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 11px;
    cursor: pointer;
}

.checkout-extra-row input[type="checkbox"] {
    margin-right: 5px;
    accent-color: #22c55e;
}

.checkout-extra-price {
    color: #2563eb;
    font-weight: 600;
    font-size: 11px;
}

/* Stepper */
.checkout-stepper-sm {
    display: flex;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 6px;
    overflow: hidden;
    height: 24px;
}

.checkout-stepper-sm__btn {
    width: 22px;
    height: 24px;
    border: none;
    background: #f8fafc;
    font-size: 13px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0f172a;
}

.checkout-stepper-sm__btn:hover { background: #e2e8f0; }

.checkout-stepper-sm__input {
    width: 28px;
    text-align: center;
    border: none;
    border-left: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
    font-size: 11px;
    font-weight: 600;
    color: #0f172a;
    background: #fff;
    padding: 0;
    height: 24px;
    -moz-appearance: textfield;
}

.checkout-stepper-sm__input:focus { outline: none; box-shadow: none; }
.checkout-stepper-sm__input::-webkit-outer-spin-button,
.checkout-stepper-sm__input::-webkit-inner-spin-button { -webkit-appearance: none; }

.checkout-stepper-label {
    font-size: 11px;
    color: #475569;
    flex: 1;
}

/* Policy info */
.checkout-policy-box {
    display: flex;
    align-items: flex-start;
    gap: 6px;
    padding: 7px 10px;
    border-radius: 8px;
    background: rgba(30, 84, 255, 0.05);
    border: 1px solid rgba(30, 84, 255, 0.14);
    margin-bottom: 8px;
}

.checkout-policy-box p {
    font-size: 11px;
    color: #475569;
    margin: 0;
    line-height: 1.5;
}

/* Textarea */
.checkout-textarea-sm {
    font-size: 11px;
    border-radius: 6px;
    border: 1px solid #e2e8f0;
    resize: vertical;
    min-height: 52px;
    width: 100%;
    padding: 5px 8px;
    margin-top: 4px;
}

.checkout-textarea-sm:focus {
    border-color: rgba(34, 197, 94, 0.5);
    outline: none;
    box-shadow: 0 0 0 2px rgba(34, 197, 94, 0.08);
}

/* Mobile: stack */
@media (max-width: 640px) {
    .checkout-unified-card { flex-direction: column; }
    .checkout-unified-col + .checkout-unified-col {
        border-left: none;
        border-top: 1px solid #e2e8f0;
    }
}
</style>
@endpush

@php
    $showHeader = $showHeader ?? true;
    $wrapperClassName = $wrapperClassName ?? '';

    try {
        $entityType = null;
        $entityId   = null;
        $orgId      = null;

        if (!empty($cart->webinar_id)) {
            $entityType = 'course';
            $entityId   = $cart->webinar_id;
            $orgId      = optional($cart->webinar)->teacher_id;
        } elseif (!empty($cart->product_order_id) && !empty($cart->productOrder->product_id)) {
            $entityType = 'product';
            $entityId   = $cart->productOrder->product_id;
            $orgId      = optional(optional($cart->productOrder)->product)->creator_id;
        } elseif (!empty($cart->booking_id)) {
            $entityType = 'booking';
            $entityId   = $cart->booking_id;
            $orgId      = optional($cart->booking)->creator_id;
        } elseif (!empty($cart->reserve_meeting_id)) {
            $entityType = 'booking';
            $entityId   = $cart->reserve_meeting_id;
            $orgId      = optional(optional($cart->reserveMeeting)->meeting)->creator_id;
        }

        $checkoutModules = $checkoutModulesByCart[$cart->id] ?? [];

        if (empty($checkoutModules) && $entityType && $entityId && $orgId) {
            $checkoutModules = app(\App\Services\CheckoutModuleService::class)
                ->getModulesForEntity($entityType, $entityId, $orgId);
        }
    } catch (\Throwable $e) {
        $checkoutModules = [];
    }

    $itemKey = $cart->id ?? '0';
@endphp

@if(!empty($checkoutModules) && count($checkoutModules))

    {{-- Unified single green-border card — all modules side by side --}}
    <div class="checkout-unified-card {{ $wrapperClassName }}">

        @foreach($checkoutModules as $module)
            @php
                $prefix = "checkout_modules[{$itemKey}][{$module->name}]";
                $priceRule = $module->price_rule ?? [];
            @endphp

            {{-- ============ DAYS (Check-in / Check-out) ============ --}}
            @if($module->name === 'days')
                @php
                    $perDay = $priceRule['amount'] ?? 0;
                    $oldIn  = old('checkout_modules.' . $itemKey . '.days.check_in');
                    $oldOut = old('checkout_modules.' . $itemKey . '.days.check_out');
                @endphp
                <div class="checkout-unified-col"
                     id="checkout-module-days-{{ $itemKey }}"
                     data-module-name="days"
                     data-price-type="per_day"
                     data-price-amount="{{ $perDay }}">

                    <div class="checkout-unified-col__label">
                        <x-iconsax-lin-calendar-2 class="icons" width="13px" height="13px" style="color:#94a3b8"/>
                        {{ $module->translated_label ?? trans('checkout.check_in') }}
                        @if($module->is_required)<span class="text-danger">*</span>@endif
                        @if($perDay)
                            <span class="ms-auto" style="font-size:10px;color:#2563eb;font-weight:700;">{{ handlePrice($perDay) }}/day</span>
                        @endif
                    </div>

                    <div class="checkout-unified-col__value">
                        <x-iconsax-lin-calendar-2 class="icons icon-green" width="13px" height="13px"/>
                        <input type="date"
                               name="{{ $prefix }}[check_in]"
                               id="checkout_days_check_in_{{ $itemKey }}"
                               class="checkout-col-date-input"
                               value="{{ $oldIn }}"
                               min="{{ now()->format('Y-m-d') }}"
                               {{ $module->is_required ? 'required' : '' }}>
                        &nbsp;—&nbsp;
                        <input type="date"
                               name="{{ $prefix }}[check_out]"
                               id="checkout_days_check_out_{{ $itemKey }}"
                               class="checkout-col-date-input"
                               value="{{ $oldOut }}"
                               min="{{ now()->format('Y-m-d') }}"
                               {{ $module->is_required ? 'required' : '' }}>
                    </div>

                    <div class="checkout-nights-badge" id="checkout_days_nights_{{ $itemKey }}">
                        0 {{ trans('checkout.nights') ?? 'nights' }}
                    </div>

                    @error('checkout_modules.' . $itemKey . '.days.check_in')
                        <div class="text-danger" style="font-size:10px;margin-top:3px;">{{ $message }}</div>
                    @enderror
                </div>

            {{-- ============ HOURS (Time Slots) ============ --}}
            @elseif($module->name === 'hours')
                @php
                    $slots      = $module->config['slots'] ?? ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
                    $perHour    = $priceRule['amount'] ?? 0;
                @endphp
                <div class="checkout-unified-col"
                     id="checkout-module-hours-{{ $itemKey }}"
                     data-module-name="hours"
                     data-price-type="per_hour"
                     data-price-amount="{{ $perHour }}">

                    <div class="checkout-unified-col__label">
                        <x-iconsax-lin-clock class="icons" width="13px" height="13px" style="color:#94a3b8"/>
                        {{ $module->translated_label ?? trans('checkout.time_slot') }}
                        @if($module->is_required)<span class="text-danger">*</span>@endif
                        @if($perHour)
                            <span class="ms-auto" style="font-size:10px;color:#2563eb;font-weight:700;">{{ handlePrice($perHour) }}/hr</span>
                        @endif
                    </div>

                    <div class="checkout-time-pills">
                        @foreach($slots as $slot)
                            @php
                                try {
                                    $s = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                    $label = $s->format('h:i A') . ' - ' . $s->copy()->addHour()->format('h:i A');
                                } catch(\Throwable $e) { $label = $slot; }
                                $slotId  = 'cts_' . $itemKey . '_' . str_replace(':', '', $slot);
                                $checked = old('checkout_modules.' . $itemKey . '.hours') == $slot;
                            @endphp
                            <label class="checkout-time-pill" for="{{ $slotId }}">
                                <input type="radio" name="{{ $prefix }}" value="{{ $slot }}"
                                       id="{{ $slotId }}" {{ $checked ? 'checked' : '' }}
                                       {{ $module->is_required ? 'required' : '' }}>
                                {{ $label }}
                            </label>
                        @endforeach
                    </div>
                </div>

            {{-- ============ STAFF MEMBER ============ --}}
            @elseif($module->name === 'staff_member')
                @php $staffOptions = $module->config['staff'] ?? []; @endphp
                <div class="checkout-unified-col"
                     id="checkout-module-staff-{{ $itemKey }}"
                     data-module-name="staff_member"
                     data-price-type="none">

                    <div class="checkout-unified-col__label">
                        <x-iconsax-lin-profile-2user class="icons" width="13px" height="13px" style="color:#94a3b8"/>
                        {{ $module->translated_label ?? trans('checkout.staff_member') }}
                        @if($module->is_required)<span class="text-danger">*</span>@endif
                    </div>

                    <div class="checkout-unified-col__value">
                        <x-iconsax-lin-tick-circle class="icons icon-green" width="13px" height="13px"/>
                        <select name="{{ $prefix }}"
                                id="checkout_staff_{{ $itemKey }}"
                                class="checkout-col-staff-select"
                                {{ $module->is_required ? 'required' : '' }}>
                            <option value="">--- {{ trans('checkout.select_staff') ?? 'Select staff' }} ---</option>
                            @foreach($staffOptions as $staff)
                                <option value="{{ $staff['id'] ?? $staff['name'] }}"
                                    {{ old('checkout_modules.' . $itemKey . '.staff_member') == ($staff['id'] ?? $staff['name']) ? 'selected' : '' }}>
                                    {{ $staff['name'] }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

            {{-- ============ PERSONS / CHILDREN / ROOMS ============ --}}
            @elseif($module->name === 'persons_children')
                @php
                    $cfg      = $module->config ?? [];
                    $adCfg    = $cfg['adults']   ?? ['min'=>1,'max'=>20];
                    $chCfg    = $cfg['children']  ?? ['min'=>0,'max'=>10];
                    $rmCfg    = $cfg['rooms']     ?? ['min'=>1,'max'=>10];
                    $perPerson = $priceRule['amount'] ?? 0;
                @endphp
                <div class="checkout-unified-col"
                     id="checkout-module-persons-{{ $itemKey }}"
                     data-module-name="persons_children"
                     data-price-type="per_person"
                     data-price-amount="{{ $perPerson }}">

                    <div class="checkout-unified-col__label">
                        <x-iconsax-lin-people class="icons" width="13px" height="13px" style="color:#94a3b8"/>
                        {{ $module->translated_label ?? trans('checkout.guests') }}
                        @if($module->is_required)<span class="text-danger">*</span>@endif
                        @if($perPerson)
                            <span class="ms-auto" style="font-size:10px;color:#2563eb;font-weight:700;">{{ handlePrice($perPerson) }}/person</span>
                        @endif
                    </div>

                    @foreach([
                        ['field'=>'adults',   'label'=>trans('checkout.adults')??'Adults',   'cfg'=>$adCfg, 'default'=>$adCfg['min']??1],
                        ['field'=>'children', 'label'=>trans('checkout.children')??'Children','cfg'=>$chCfg, 'default'=>0],
                        ['field'=>'rooms',    'label'=>trans('checkout.rooms')??'Rooms',     'cfg'=>$rmCfg, 'default'=>$rmCfg['min']??1],
                    ] as $row)
                        <div class="d-flex align-items-center justify-content-between mb-6">
                            <span class="checkout-stepper-label">{{ $row['label'] }}</span>
                            <div class="checkout-stepper-sm">
                                <button type="button" class="checkout-stepper-sm__btn stepper-btn-minus">−</button>
                                <input type="number"
                                       name="{{ $prefix }}[{{ $row['field'] }}]"
                                       class="checkout-stepper-sm__input stepper-input"
                                       value="{{ old('checkout_modules.'.$itemKey.'.persons_children.'.$row['field'], $row['default']) }}"
                                       min="{{ $row['cfg']['min'] }}"
                                       max="{{ $row['cfg']['max'] }}">
                                <button type="button" class="checkout-stepper-sm__btn stepper-btn-plus">+</button>
                            </div>
                        </div>
                    @endforeach
                </div>

            {{-- ============ CANCELLATION POLICY ============ --}}
            @elseif($module->name === 'cancellation_policy')
                @php $policyText = $module->config['policy_text'] ?? trans('checkout.free_cancellation_hint') ?? 'Free cancellation up to 24 hours before check-in. After that, the first night is non-refundable.'; @endphp
                <div class="checkout-unified-col"
                     id="checkout-module-policy-{{ $itemKey }}"
                     data-module-name="cancellation_policy"
                     data-price-type="none">

                    <div class="checkout-unified-col__label">
                        <x-iconsax-lin-shield-tick class="icons" width="13px" height="13px" style="color:#94a3b8"/>
                        {{ $module->translated_label ?? trans('checkout.cancellation_policy') }}
                        @if($module->is_required)<span class="text-danger">*</span>@endif
                    </div>

                    <div class="checkout-policy-box">
                        <x-iconsax-lin-info-circle class="icons flex-shrink-0" width="13px" height="13px" style="color:#2563eb;margin-top:1px"/>
                        <p>{{ $policyText }}</p>
                    </div>

                    <div class="d-flex align-items-center gap-2">
                        <input type="checkbox"
                               id="cp_{{ $itemKey }}"
                               name="{{ $prefix }}"
                               value="1"
                               class="form-check-input"
                               style="width:13px;height:13px;accent-color:#22c55e;"
                               {{ old('checkout_modules.'.$itemKey.'.cancellation_policy') ? 'checked' : '' }}
                               {{ $module->is_required ? 'required' : '' }}>
                        <label for="cp_{{ $itemKey }}" style="font-size:11px;color:#475569;cursor:pointer;margin:0;">
                            {{ trans('checkout.i_agree_cancellation_policy') ?? 'I agree to cancellation policy' }}
                        </label>
                    </div>
                </div>

            {{-- ============ EXTRA SERVICES ============ --}}
            @elseif($module->name === 'extra_services')
                @php $options = $module->config['options'] ?? []; @endphp
                <div class="checkout-unified-col"
                     id="checkout-module-extras-{{ $itemKey }}"
                     data-module-name="extra_services"
                     data-price-type="additive">

                    <div class="checkout-unified-col__label">
                        <x-iconsax-lin-add-square class="icons" width="13px" height="13px" style="color:#94a3b8"/>
                        {{ $module->translated_label ?? 'Extra Services' }}
                        @if($module->is_required)<span class="text-danger">*</span>@endif
                    </div>

                    <div class="checkout-extras-list">
                        @foreach($options as $i => $opt)
                            <label class="checkout-extra-row">
                                <input type="checkbox"
                                       name="{{ $prefix }}[]"
                                       value="{{ $opt['label'] }}"
                                       class="checkout-extra-service"
                                       data-price="{{ $opt['price'] ?? 0 }}"
                                       {{ in_array($opt['label'], old('checkout_modules.'.$itemKey.'.extra_services', [])) ? 'checked' : '' }}>
                                <span style="flex:1;font-size:11px;">{{ $opt['label'] }}</span>
                                <span class="checkout-extra-price">+{{ handlePrice($opt['price'] ?? 0) }}</span>
                            </label>
                        @endforeach
                    </div>
                </div>

            {{-- ============ CHECKOUT MESSAGE ============ --}}
            @elseif($module->name === 'checkout_message')
                @php
                    $maxLen      = $module->config['max_length'] ?? 500;
                    $placeholder = $module->config['placeholder'] ?? (trans('checkout.special_instructions') ?? 'Special instructions...');
                @endphp
                <div class="checkout-unified-col"
                     id="checkout-module-msg-{{ $itemKey }}"
                     data-module-name="checkout_message"
                     data-price-type="none">

                    <div class="checkout-unified-col__label">
                        <x-iconsax-lin-message-text class="icons" width="13px" height="13px" style="color:#94a3b8"/>
                        {{ $module->translated_label ?? 'Message' }}
                        @if($module->is_required)<span class="text-danger">*</span>@endif
                    </div>

                    <textarea name="{{ $prefix }}"
                              id="checkout_msg_{{ $itemKey }}"
                              class="checkout-textarea-sm"
                              rows="3"
                              placeholder="{{ $placeholder }}"
                              maxlength="{{ $maxLen }}"
                              {{ $module->is_required ? 'required' : '' }}>{{ old('checkout_modules.'.$itemKey.'.checkout_message') }}</textarea>

                    <div style="font-size:10px;color:#94a3b8;margin-top:3px;">
                        <span id="msg_count_{{ $itemKey }}">0</span>/{{ $maxLen }}
                    </div>
                </div>

            {{-- ============ REVIEWER MESSAGE ============ --}}
            @elseif($module->name === 'reviewer_message')
                @php
                    $maxLen      = $module->config['max_length'] ?? 500;
                    $placeholder = $module->config['placeholder'] ?? (trans('checkout.message_to_reviewer') ?? 'Message to reviewer...');
                @endphp
                <div class="checkout-unified-col"
                     id="checkout-module-reviewer-{{ $itemKey }}"
                     data-module-name="reviewer_message"
                     data-price-type="none">

                    <div class="checkout-unified-col__label">
                        <x-iconsax-lin-message-text class="icons" width="13px" height="13px" style="color:#94a3b8"/>
                        {{ $module->translated_label ?? 'Reviewer Message' }}
                        @if($module->is_required)<span class="text-danger">*</span>@endif
                    </div>

                    <textarea name="{{ $prefix }}"
                              id="reviewer_msg_{{ $itemKey }}"
                              class="checkout-textarea-sm"
                              rows="3"
                              placeholder="{{ $placeholder }}"
                              maxlength="{{ $maxLen }}"
                              {{ $module->is_required ? 'required' : '' }}>{{ old('checkout_modules.'.$itemKey.'.reviewer_message') }}</textarea>

                    <div style="font-size:10px;color:#94a3b8;margin-top:3px;">
                        <span id="reviewer_count_{{ $itemKey }}">0</span>/{{ $maxLen }}
                    </div>
                </div>

            {{-- ============ UNKNOWN MODULE FALLBACK ============ --}}
            @else
                <div class="checkout-unified-col"
                     data-module-name="{{ $module->name }}"
                     data-price-type="none">
                    <div class="checkout-unified-col__label">{{ $module->translated_label }}</div>
                    @includeIf('partials.checkout_modules._' . $module->name, ['module' => $module, 'itemId' => $itemKey])
                </div>
            @endif

        @endforeach
    </div>
    {{-- End unified card --}}

@endif

@push('scripts_bottom')
<script>
(function($) {

    /* ---- Nights counter ---- */
    function updateNights_{{ $itemKey }}() {
        var inVal  = $('#checkout_days_check_in_{{ $itemKey }}').val();
        var outVal = $('#checkout_days_check_out_{{ $itemKey }}').val();
        var $badge = $('#checkout_days_nights_{{ $itemKey }}');
        if (!inVal || !outVal) { $badge.text('0 {{ trans("checkout.nights") ?? "nights" }}'); return; }
        var inD = new Date(inVal), outD = new Date(outVal);
        if (outD <= inD) {
            var next = new Date(inD); next.setDate(next.getDate() + 1);
            var y = next.getFullYear(), m = String(next.getMonth()+1).padStart(2,'0'), d = String(next.getDate()).padStart(2,'0');
            $('#checkout_days_check_out_{{ $itemKey }}').val(y+'-'+m+'-'+d);
            outD = next;
        }
        var nights = Math.max(0, Math.ceil((outD - inD) / 86400000));
        $badge.text(nights + ' {{ trans("checkout.nights") ?? "nights" }}');
        $(document).trigger('checkout:priceUpdate');
    }

    $(document).on('change', '#checkout_days_check_in_{{ $itemKey }}', function() {
        var v = $(this).val();
        if (v) $('#checkout_days_check_out_{{ $itemKey }}').attr('min', v);
        updateNights_{{ $itemKey }}();
    });

    $(document).on('change', '#checkout_days_check_out_{{ $itemKey }}', updateNights_{{ $itemKey }});
    $(document).ready(updateNights_{{ $itemKey }});

    /* ---- Stepper ---- */
    $(document).on('click', '.stepper-btn-minus, .stepper-btn-plus', function(e) {
        e.preventDefault();
        var $inp = $(this).closest('.checkout-stepper-sm').find('.stepper-input');
        var val = parseInt($inp.val(), 10) || 0;
        var mn  = parseInt($inp.attr('min'), 10) || 0;
        var mx  = parseInt($inp.attr('max'), 10) || 999;
        val = $(this).hasClass('stepper-btn-minus') ? Math.max(mn, val-1) : Math.min(mx, val+1);
        $inp.val(val).trigger('change');
        $(document).trigger('checkout:priceUpdate');
    });

    $(document).on('change', '.stepper-input, .checkout-extra-service, .checkout-col-staff-select', function() {
        $(document).trigger('checkout:priceUpdate');
    });

    /* ---- Char counters ---- */
    $(document).on('input', '#checkout_msg_{{ $itemKey }}', function() {
        $('#msg_count_{{ $itemKey }}').text($(this).val().length);
    });
    $(document).on('input', '#reviewer_msg_{{ $itemKey }}', function() {
        $('#reviewer_count_{{ $itemKey }}').text($(this).val().length);
    });

    /* ---- Time slots ---- */
    $(document).on('change', 'input[type="radio"][name^="checkout_modules"]', function() {
        $(document).trigger('checkout:priceUpdate');
    });

})(jQuery);
</script>
@endpush
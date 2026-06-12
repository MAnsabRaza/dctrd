{{--
    design_1/web/cart/overview/includes/checkout_modules.blade.php

    Green-border unified card showing all checkout modules side-by-side.
    Variables: $cart, $checkoutModules (Collection), $itemKey (string)
--}}

@once
@push('styles_top')
<style>
/* ──────────────────────────────────────────
   Checkout Modules — Unified Green Card
────────────────────────────────────────── */
.cmod-card {
    display: flex; align-items: stretch;
    border: 1.5px solid #22c55e;
    border-radius: 12px; overflow: hidden;
    background: #fff; margin-top: 12px;
}
.cmod-col {
    flex: 1 1 0; min-width: 0;
    padding: 9px 13px; position: relative;
}
.cmod-col + .cmod-col { border-left: 1px solid #e2e8f0; }

.cmod-label {
    font-size: 11px; font-weight: 700; color: #0f172a;
    margin-bottom: 5px;
    display: flex; align-items: center; gap: 4px;
}
.cmod-value {
    display: flex; align-items: center;
    gap: 5px; font-size: 12px; color: #0f172a; font-weight: 500;
}
.cmod-value .icon-green { color: #22c55e; flex-shrink: 0; }

.cmod-date-input {
    border: none !important; outline: none !important;
    background: transparent !important;
    font-size: 12px; color: #0f172a;
    padding: 0 !important; width: auto; max-width: 118px; cursor: pointer;
}
.cmod-date-input::-webkit-calendar-picker-indicator { opacity: .5; cursor: pointer; }

.cmod-staff-select {
    border: none !important; outline: none !important;
    background: transparent !important;
    font-size: 12px; color: #0f172a; padding: 0 !important;
    cursor: pointer; appearance: none; -webkit-appearance: none; max-width: 160px;
}

.cmod-nights {
    display: inline-flex; align-items: center; gap: 3px;
    margin-top: 5px; padding: 2px 7px; border-radius: 999px;
    background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3);
    font-size: 10px; color: #15803d; font-weight: 500;
}

.cmod-time-pills { display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px; }
.cmod-time-pill {
    padding: 2px 7px; border-radius: 6px; border: 1px solid #e2e8f0;
    font-size: 11px; color: #334155; cursor: pointer; transition: all .15s;
    display: inline-flex; align-items: center;
}
.cmod-time-pill input[type="radio"] { display: none; }
.cmod-time-pill:has(input:checked) {
    border-color: #22c55e; background: rgba(34,197,94,.08);
    color: #15803d; font-weight: 600;
}

.cmod-extras { display: flex; flex-direction: column; gap: 4px; margin-top: 4px; }
.cmod-extra-row {
    display: flex; align-items: center;
    justify-content: space-between; font-size: 11px; cursor: pointer;
}
.cmod-extra-row input[type="checkbox"] { margin-right: 4px; accent-color: #22c55e; }
.cmod-extra-price { color: #2563eb; font-weight: 600; font-size: 11px; }

.cmod-stepper {
    display: flex; align-items: center;
    border: 1px solid #e2e8f0; border-radius: 6px; overflow: hidden; height: 22px;
}
.cmod-stepper-btn {
    width: 20px; height: 22px; border: none; background: #f8fafc;
    font-size: 13px; font-weight: 700; cursor: pointer;
    display: flex; align-items: center; justify-content: center; color: #0f172a;
}
.cmod-stepper-btn:hover { background: #e2e8f0; }
.cmod-stepper-inp {
    width: 26px; text-align: center; border: none;
    border-left: 1px solid #e2e8f0; border-right: 1px solid #e2e8f0;
    font-size: 11px; font-weight: 600; color: #0f172a;
    background: #fff; padding: 0; height: 22px; -moz-appearance: textfield;
}
.cmod-stepper-inp:focus { outline: none; }
.cmod-stepper-inp::-webkit-outer-spin-button,
.cmod-stepper-inp::-webkit-inner-spin-button { -webkit-appearance: none; }
.cmod-stepper-lbl { font-size: 11px; color: #475569; flex: 1; }

.cmod-policy-box {
    display: flex; align-items: flex-start; gap: 5px;
    padding: 6px 8px; border-radius: 7px;
    background: rgba(30,84,255,.05); border: 1px solid rgba(30,84,255,.14);
    margin-bottom: 6px;
}
.cmod-policy-box p { font-size: 10px; color: #475569; margin: 0; line-height: 1.5; }

.cmod-textarea {
    font-size: 11px; border-radius: 6px; border: 1px solid #e2e8f0;
    resize: vertical; min-height: 46px; width: 100%; padding: 4px 7px; margin-top: 3px;
}
.cmod-textarea:focus {
    border-color: rgba(34,197,94,.5); outline: none;
    box-shadow: 0 0 0 2px rgba(34,197,94,.08);
}

@media (max-width: 640px) {
    .cmod-card { flex-direction: column; }
    .cmod-col + .cmod-col { border-left: none; border-top: 1px solid #e2e8f0; }
}
</style>
@endpush
@endonce

<div class="cmod-card">

    @foreach($checkoutModules as $module)
        @php
            $prefix    = "checkout_modules[{$itemKey}][{$module->name}]";
            $priceRule = $module->price_rule ?? [];
        @endphp

        {{-- ══ DAYS ══ --}}
        @if($module->name === 'days')
            @php
                $perDay = $priceRule['amount'] ?? 0;
                $oldIn  = old("checkout_modules.{$itemKey}.days.check_in");
                $oldOut = old("checkout_modules.{$itemKey}.days.check_out");
            @endphp
            <div class="cmod-col" id="cmod-days-{{ $itemKey }}"
                 data-module-name="days" data-price-type="per_day" data-price-amount="{{ $perDay }}">

                <div class="cmod-label">
                    <x-iconsax-lin-calendar-2 class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                    {{ $module->translated_label ?? trans('checkout.check_in') }}
                    @if($module->is_required)<span class="text-danger">*</span>@endif
                    @if($perDay)<span class="ms-auto ml-auto" style="font-size:10px;color:#2563eb;font-weight:700;">{{ handlePrice($perDay) }}/day</span>@endif
                </div>

                <div class="cmod-value">
                    <x-iconsax-lin-calendar-2 class="icons icon-green" width="12px" height="12px"/>
                    <input type="date" name="{{ $prefix }}[check_in]"
                           id="cmod_cin_{{ $itemKey }}" class="cmod-date-input"
                           value="{{ $oldIn }}" min="{{ now()->format('Y-m-d') }}"
                           {{ $module->is_required ? 'required' : '' }}>
                    <span class="text-gray-400">—</span>
                    <input type="date" name="{{ $prefix }}[check_out]"
                           id="cmod_cout_{{ $itemKey }}" class="cmod-date-input"
                           value="{{ $oldOut }}" min="{{ now()->format('Y-m-d') }}"
                           {{ $module->is_required ? 'required' : '' }}>
                </div>

                <div class="cmod-nights" id="cmod_nights_{{ $itemKey }}">
                    0 {{ trans('checkout.nights') ?? 'nights' }}
                </div>

                @error("checkout_modules.{$itemKey}.days.check_in")
                    <div class="text-danger" style="font-size:10px;margin-top:2px;">{{ $message }}</div>
                @enderror
            </div>

        {{-- ══ HOURS ══ --}}
        @elseif($module->name === 'hours')
            @php
                $slots   = $module->config['slots'] ?? ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
                $perHour = $priceRule['amount'] ?? 0;
            @endphp
            <div class="cmod-col" id="cmod-hours-{{ $itemKey }}"
                 data-module-name="hours" data-price-type="per_hour" data-price-amount="{{ $perHour }}">

                <div class="cmod-label">
                    <x-iconsax-lin-clock class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                    {{ $module->translated_label ?? trans('checkout.time_slot') }}
                    @if($module->is_required)<span class="text-danger">*</span>@endif
                    @if($perHour)<span class="ms-auto ml-auto" style="font-size:10px;color:#2563eb;font-weight:700;">{{ handlePrice($perHour) }}/hr</span>@endif
                </div>

                <div class="cmod-time-pills">
                    @foreach($slots as $slot)
                        @php
                            try {
                                $s = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                $lbl = $s->format('h:i A') . ' - ' . $s->copy()->addHour()->format('h:i A');
                            } catch(\Throwable $e) { $lbl = $slot; }
                            $sid = 'cmod_slot_' . $itemKey . '_' . str_replace(':', '', $slot);
                        @endphp
                        <label class="cmod-time-pill" for="{{ $sid }}">
                            <input type="radio" name="{{ $prefix }}" value="{{ $slot }}"
                                   id="{{ $sid }}"
                                   {{ old("checkout_modules.{$itemKey}.hours") == $slot ? 'checked' : '' }}
                                   {{ $module->is_required ? 'required' : '' }}>
                            {{ $lbl }}
                        </label>
                    @endforeach
                </div>
            </div>

        {{-- ══ STAFF MEMBER ══ --}}
        @elseif($module->name === 'staff_member')
            @php $staffOptions = $module->config['staff'] ?? []; @endphp
            <div class="cmod-col" id="cmod-staff-{{ $itemKey }}"
                 data-module-name="staff_member" data-price-type="none">

                <div class="cmod-label">
                    <x-iconsax-lin-profile-2user class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                    {{ $module->translated_label ?? trans('checkout.staff_member') }}
                    @if($module->is_required)<span class="text-danger">*</span>@endif
                </div>

                <div class="cmod-value">
                    <x-iconsax-lin-tick-circle class="icons icon-green" width="12px" height="12px"/>
                    <select name="{{ $prefix }}" id="cmod_staff_{{ $itemKey }}"
                            class="cmod-staff-select"
                            {{ $module->is_required ? 'required' : '' }}>
                        <option value="">--- {{ trans('checkout.select_staff') ?? 'Select staff' }} ---</option>
                        @foreach($staffOptions as $staff)
                            <option value="{{ $staff['id'] ?? $staff['name'] }}"
                                {{ old("checkout_modules.{$itemKey}.staff_member") == ($staff['id'] ?? $staff['name']) ? 'selected' : '' }}>
                                {{ $staff['name'] }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

        {{-- ══ PERSONS / CHILDREN / ROOMS ══ --}}
        @elseif($module->name === 'persons_children')
            @php
                $cfg       = $module->config ?? [];
                $adCfg     = $cfg['adults']   ?? ['min'=>1,'max'=>20];
                $chCfg     = $cfg['children'] ?? ['min'=>0,'max'=>10];
                $rmCfg     = $cfg['rooms']    ?? ['min'=>1,'max'=>10];
                $perPerson = $priceRule['amount'] ?? 0;
            @endphp
            <div class="cmod-col" id="cmod-persons-{{ $itemKey }}"
                 data-module-name="persons_children" data-price-type="per_person" data-price-amount="{{ $perPerson }}">

                <div class="cmod-label">
                    <x-iconsax-lin-people class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                    {{ $module->translated_label ?? trans('checkout.guests') }}
                    @if($module->is_required)<span class="text-danger">*</span>@endif
                    @if($perPerson)<span class="ms-auto ml-auto" style="font-size:10px;color:#2563eb;font-weight:700;">{{ handlePrice($perPerson) }}/person</span>@endif
                </div>

                @foreach([
                    ['f'=>'adults',   'l'=> trans('checkout.adults')   ?? 'Adults',   'c'=>$adCfg, 'd'=>$adCfg['min']??1],
                    ['f'=>'children', 'l'=> trans('checkout.children') ?? 'Children', 'c'=>$chCfg, 'd'=>0],
                    ['f'=>'rooms',    'l'=> trans('checkout.rooms')    ?? 'Rooms',    'c'=>$rmCfg, 'd'=>$rmCfg['min']??1],
                ] as $r)
                    <div class="d-flex align-items-center justify-content-between mb-4">
                        <span class="cmod-stepper-lbl">{{ $r['l'] }}</span>
                        <div class="cmod-stepper">
                            <button type="button" class="cmod-stepper-btn cmod-minus">−</button>
                            <input type="number" name="{{ $prefix }}[{{ $r['f'] }}]"
                                   class="cmod-stepper-inp"
                                   value="{{ old("checkout_modules.{$itemKey}.persons_children.{$r['f']}", $r['d']) }}"
                                   min="{{ $r['c']['min'] }}" max="{{ $r['c']['max'] }}">
                            <button type="button" class="cmod-stepper-btn cmod-plus">+</button>
                        </div>
                    </div>
                @endforeach
            </div>

        {{-- ══ CANCELLATION POLICY ══ --}}
        @elseif($module->name === 'cancellation_policy')
            @php
                $policyText = $module->config['policy_text']
                    ?? trans('checkout.free_cancellation_hint')
                    ?? 'Free cancellation up to 24 hours before check-in.';
            @endphp
            <div class="cmod-col" id="cmod-policy-{{ $itemKey }}"
                 data-module-name="cancellation_policy" data-price-type="none">

                <div class="cmod-label">
                    <x-iconsax-lin-shield-tick class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                    {{ $module->translated_label ?? trans('checkout.cancellation_policy') }}
                    @if($module->is_required)<span class="text-danger">*</span>@endif
                </div>

                <div class="cmod-policy-box">
                    <x-iconsax-lin-info-circle class="icons flex-shrink-0" width="12px" height="12px" style="color:#2563eb;margin-top:1px"/>
                    <p>{{ $policyText }}</p>
                </div>

                <div class="d-flex align-items-center gap-2">
                    <input type="checkbox" id="cmod_cp_{{ $itemKey }}" name="{{ $prefix }}" value="1"
                           style="width:13px;height:13px;accent-color:#22c55e;"
                           {{ old("checkout_modules.{$itemKey}.cancellation_policy") ? 'checked' : '' }}
                           {{ $module->is_required ? 'required' : '' }}>
                    <label for="cmod_cp_{{ $itemKey }}" style="font-size:10px;color:#475569;cursor:pointer;margin:0;">
                        {{ trans('checkout.i_agree_cancellation_policy') ?? 'I agree to cancellation policy' }}
                    </label>
                </div>
            </div>

        {{-- ══ EXTRA SERVICES ══ --}}
        @elseif($module->name === 'extra_services')
            @php $options = $module->config['options'] ?? []; @endphp
            <div class="cmod-col" id="cmod-extras-{{ $itemKey }}"
                 data-module-name="extra_services" data-price-type="additive">

                <div class="cmod-label">
                    <x-iconsax-lin-add-square class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                    {{ $module->translated_label ?? 'Extra Services' }}
                    @if($module->is_required)<span class="text-danger">*</span>@endif
                </div>

                <div class="cmod-extras">
                    @foreach($options as $opt)
                        <label class="cmod-extra-row">
                            <input type="checkbox" name="{{ $prefix }}[]"
                                   value="{{ $opt['label'] }}" class="cmod-extra-chk"
                                   data-price="{{ $opt['price'] ?? 0 }}"
                                   {{ in_array($opt['label'], old("checkout_modules.{$itemKey}.extra_services", [])) ? 'checked' : '' }}>
                            <span style="flex:1;">{{ $opt['label'] }}</span>
                            <span class="cmod-extra-price">+{{ handlePrice($opt['price'] ?? 0) }}</span>
                        </label>
                    @endforeach
                </div>
            </div>

        {{-- ══ CHECKOUT MESSAGE ══ --}}
        @elseif($module->name === 'checkout_message')
            @php
                $maxLen = $module->config['max_length'] ?? 500;
                $ph     = $module->config['placeholder'] ?? (trans('checkout.special_instructions') ?? 'Special instructions...');
            @endphp
            <div class="cmod-col" id="cmod-msg-{{ $itemKey }}"
                 data-module-name="checkout_message" data-price-type="none">

                <div class="cmod-label">
                    <x-iconsax-lin-message-text class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                    {{ $module->translated_label ?? 'Message' }}
                    @if($module->is_required)<span class="text-danger">*</span>@endif
                </div>

                <textarea name="{{ $prefix }}" id="cmod_msg_{{ $itemKey }}"
                          class="cmod-textarea" rows="2"
                          placeholder="{{ $ph }}" maxlength="{{ $maxLen }}"
                          {{ $module->is_required ? 'required' : '' }}>{{ old("checkout_modules.{$itemKey}.checkout_message") }}</textarea>
                <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                    <span id="cmod_msgc_{{ $itemKey }}">0</span>/{{ $maxLen }}
                </div>
            </div>

        {{-- ══ REVIEWER MESSAGE ══ --}}
        @elseif($module->name === 'reviewer_message')
            @php
                $maxLen = $module->config['max_length'] ?? 500;
                $ph     = $module->config['placeholder'] ?? (trans('checkout.message_to_reviewer') ?? 'Message to reviewer...');
            @endphp
            <div class="cmod-col" id="cmod-reviewer-{{ $itemKey }}"
                 data-module-name="reviewer_message" data-price-type="none">

                <div class="cmod-label">
                    <x-iconsax-lin-message-text class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                    {{ $module->translated_label ?? 'Reviewer Message' }}
                    @if($module->is_required)<span class="text-danger">*</span>@endif
                </div>

                <textarea name="{{ $prefix }}" id="cmod_reviewer_{{ $itemKey }}"
                          class="cmod-textarea" rows="2"
                          placeholder="{{ $ph }}" maxlength="{{ $maxLen }}"
                          {{ $module->is_required ? 'required' : '' }}>{{ old("checkout_modules.{$itemKey}.reviewer_message") }}</textarea>
                <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                    <span id="cmod_reviewerc_{{ $itemKey }}">0</span>/{{ $maxLen }}
                </div>
            </div>

        {{-- ══ FALLBACK ══ --}}
        @else
            <div class="cmod-col" data-module-name="{{ $module->name }}" data-price-type="none">
                <div class="cmod-label">{{ $module->translated_label }}</div>
                @includeIf('partials.checkout_modules._' . $module->name, ['module' => $module, 'itemId' => $itemKey])
            </div>
        @endif

    @endforeach
</div>

@push('scripts_bottom')
<script>
(function ($) {

    /* Nights counter */
    function cmodNights_{{ $itemKey }}() {
        var inV  = $('#cmod_cin_{{ $itemKey }}').val();
        var outV = $('#cmod_cout_{{ $itemKey }}').val();
        var $b   = $('#cmod_nights_{{ $itemKey }}');
        if (!inV || !outV) { $b.text('0 {{ trans("checkout.nights") ?? "nights" }}'); return; }
        var inD = new Date(inV), outD = new Date(outV);
        if (outD <= inD) {
            var n = new Date(inD); n.setDate(n.getDate()+1);
            var str = n.getFullYear()+'-'+String(n.getMonth()+1).padStart(2,'0')+'-'+String(n.getDate()).padStart(2,'0');
            $('#cmod_cout_{{ $itemKey }}').val(str);
            outD = n;
        }
        var nights = Math.max(0, Math.ceil((outD - inD) / 86400000));
        $b.text(nights + ' {{ trans("checkout.nights") ?? "nights" }}');
        $(document).trigger('checkout:priceUpdate');
    }
    $(document).on('change','#cmod_cin_{{ $itemKey }}', function(){
        var v=$(this).val(); if(v) $('#cmod_cout_{{ $itemKey }}').attr('min',v);
        cmodNights_{{ $itemKey }}();
    });
    $(document).on('change','#cmod_cout_{{ $itemKey }}', cmodNights_{{ $itemKey }});
    $(document).ready(cmodNights_{{ $itemKey }});

    /* Steppers */
    $(document).on('click', '.cmod-minus, .cmod-plus', function(e){
        e.preventDefault();
        var $i=$(this).closest('.cmod-stepper').find('.cmod-stepper-inp');
        var v=parseInt($i.val(),10)||0, mn=parseInt($i.attr('min'),10)||0, mx=parseInt($i.attr('max'),10)||999;
        $i.val($(this).hasClass('cmod-minus')?Math.max(mn,v-1):Math.min(mx,v+1)).trigger('change');
        $(document).trigger('checkout:priceUpdate');
    });

    /* Char counters */
    $(document).on('input','#cmod_msg_{{ $itemKey }}',function(){$('#cmod_msgc_{{ $itemKey }}').text($(this).val().length);});
    $(document).on('input','#cmod_reviewer_{{ $itemKey }}',function(){$('#cmod_reviewerc_{{ $itemKey }}').text($(this).val().length);});

    /* Price triggers */
    $(document).on('change','.cmod-extra-chk, .cmod-stepper-inp', function(){ $(document).trigger('checkout:priceUpdate'); });

})(jQuery);
</script>
@endpush
{{--
    partials/checkout_modules/_settings_form.blade.php

    Checkout Options Settings Panel — BOOKING ONLY
    - Sirf "Bookings" ke modules dikhaye jaate hain (days, hours, staff_member,
      persons_children, extra_services, cancellation_policy, checkout_message)
    - Har module ka EK toggle — ON/OFF karne se "enabled" AND "required"
      dono database mein update hote hain (org_checkout_modules table)
    - Data $moduleSettings (database se, CheckoutModuleService::getOrgModuleSettings) se aata hai
    - Field name "modules[name]" — UserController@checkoutOptionsUpdate ke
      validation se match karta hai
    - Hidden input + checkbox combo: toggle OFF karne par bhi "0" submit hota hai
--}}

@php
    $formId      = $formId      ?? 'checkoutOptionsForm';
    $formClass   = $formClass   ?? '';
    $saveUrl     = $saveUrl     ?? '#';
    $submitLabel = $submitLabel ?? trans('admin/main.save');
    $title       = $title       ?? trans('panel.checkout_options');
    $description = $description ?? trans('panel.checkout_options_hint');
    $wrapForm    = $wrapForm    ?? true;

    $rawSettings = $moduleSettings ?? [];

    // Sirf booking checkout pe use hone wale modules — order yahi rakha gaya hai
    $bookingModuleNames = [
        'days',
        'hours',
        'staff_member',
        'persons_children',
        'extra_services',
        'cancellation_policy',
        'checkout_message',
    ];

    $moduleLabels = [
        'days'                => trans('cart.module_days')                ?? 'Select Days',
        'hours'               => trans('cart.module_hours')               ?? 'Select Time Slot',
        'staff_member'        => trans('cart.module_staff_member')        ?? 'Select Staff Member',
        'persons_children'    => trans('cart.module_persons_children')    ?? 'Guests',
        'extra_services'      => trans('cart.module_extra_services')      ?? 'Extra Services',
        'cancellation_policy' => trans('cart.module_cancellation_policy') ?? 'Cancellation Policy',
        'checkout_message'    => trans('cart.module_checkout_message')    ?? 'Message for Check-out',
    ];
    $moduleDescs = [
        'days'                => trans('cart.module_days_desc')                ?? 'Check-in and check-out dates',
        'hours'               => trans('cart.module_hours_desc')               ?? 'Preferred time slot',
        'staff_member'        => trans('cart.module_staff_member_desc')        ?? 'Choose preferred staff member',
        'persons_children'    => trans('cart.module_persons_children_desc')    ?? 'Number of adults, children and rooms',
        'extra_services'      => trans('cart.module_extra_services_desc')      ?? 'Additional services you require',
        'cancellation_policy' => trans('cart.module_cancellation_policy_desc') ?? 'Please read and agree to our policy',
        'checkout_message'    => trans('cart.module_checkout_message_desc')    ?? 'Any special instructions for your booking',
    ];

    // $rawSettings, CheckoutModuleService::getOrgModuleSettings() se aata hai —
    // flat array of ['name' => ..., 'enabled' => ..., 'is_required' => ..., ...]
    $bookingModules = [];
    foreach ($bookingModuleNames as $modName) {
        $found = null;
        foreach ($rawSettings as $m) {
            if (($m['name'] ?? '') === $modName) {
                $found = $m;
                break;
            }
        }

        $bookingModules[] = [
            'name'        => $modName,
            'label'       => $found['label']       ?? ($moduleLabels[$modName] ?? $modName),
            'desc'        => $found['help_text']   ?? ($moduleDescs[$modName]  ?? ''),
            'enabled'     => $found['enabled']     ?? false,
            'is_required' => $found['is_required'] ?? false, // global, hamesha required
        ];
    }
@endphp

@push('styles_top')
<style>
.co-wrap {
    font-family: var(--font-sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
}

.co-section-header {
    padding: 6px 0 8px;
}

.co-section-name {
    font-size: 12.5px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: 0.01em;
}

.co-module-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 6px 7px 4px;
    border-radius: 7px;
    transition: background 0.12s;
}
.co-module-row:hover {
    background: #f1f5f9;
}

.co-module-row-left {
    display: flex;
    align-items: center;
    gap: 10px;
    min-width: 0;
}

.co-module-row-text {
    min-width: 0;
}

.co-module-row-name {
    font-size: 12.5px;
    font-weight: 500;
    color: #1e293b;
    line-height: 1.3;
}

.co-module-row-desc {
    font-size: 10.5px;
    color: #94a3b8;
    line-height: 1.3;
    margin-top: 1px;
}

.co-required-badge {
    font-size: 9.5px;
    font-weight: 600;
    color: #2563EB;
    background: #eff6ff;
    border-radius: 4px;
    padding: 1px 6px;
    margin-left: 6px;
    white-space: nowrap;
}

.co-save-row {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-top: 14px;
    padding-top: 12px;
    border-top: 0.5px solid #e2e8f0;
}

#co-save-btn {
    padding: 6px 18px;
    font-size: 12.5px;
    font-weight: 600;
    background: #2563EB;
    color: #fff;
    border: none;
    border-radius: 7px;
    cursor: pointer;
    transition: background 0.15s, opacity 0.15s;
}
#co-save-btn:hover   { background: #1d4ed8; }
#co-save-btn:disabled { opacity: 0.6; cursor: not-allowed; }

#co-save-msg {
    font-size: 12px;
    color: #16a34a;
    font-weight: 500;
    display: none;
}

/* ── MODULE TOGGLE (34×19) ── */
.co-mod-toggle {
    position: relative;
    width: 34px;
    height: 19px;
    flex-shrink: 0;
}
.co-mod-toggle input { opacity:0; width:0; height:0; position:absolute; }
.co-mod-slider {
    position: absolute;
    inset: 0;
    border-radius: 20px;
    background: #cbd5e1;
    cursor: pointer;
    transition: background 0.2s;
}
.co-mod-slider:before {
    content: '';
    position: absolute;
    width: 13px; height: 13px;
    border-radius: 50%;
    background: #fff;
    top: 3px; left: 3px;
    transition: transform 0.2s;
    box-shadow: 0 1px 2px rgba(0,0,0,0.15);
}
.co-mod-toggle input:checked + .co-mod-slider           { background: #2563EB; }
.co-mod-toggle input:checked + .co-mod-slider:before    { transform: translateX(15px); }
.co-mod-toggle input:disabled + .co-mod-slider          { opacity: 0.7; cursor: not-allowed; }
</style>
@endpush

{{-- ══════════════════════════════════════════
     HTML
══════════════════════════════════════════ --}}
<div class="co-wrap">

    @if($wrapForm)
        <form action="{{ $saveUrl }}" method="POST" id="{{ $formId }}" class="{{ trim($formClass) }}">
            @csrf
    @endif

    <div class="co-section-header">
        <span class="co-section-name">{{ trans('cart.section_bookings') ?? 'Bookings' }}</span>
    </div>

    <div id="co-mods-bookings">
        @foreach($bookingModules as $mod)
            <div class="co-module-row">
                <div class="co-module-row-left">

                    @if($mod['is_required'])
                        {{-- Globally required module — toggle hamesha ON, disable kiya hai --}}
                        <label class="co-mod-toggle" title="{{ $mod['label'] }}">
                            <input type="checkbox" checked disabled>
                            <span class="co-mod-slider"></span>
                        </label>
                        {{-- Hidden field zaroori nahi — service is_required modules ko
                             hamesha true rakhta hai chahe input aaye ya na aaye --}}
                    @else
                        {{-- Hidden input pehle — checkbox unchecked hone par "0" submit hoga --}}
                        <input type="hidden" name="modules[{{ $mod['name'] }}]" value="0">
                        <label class="co-mod-toggle" title="{{ $mod['label'] }}">
                            <input
                                type="checkbox"
                                class="co-module-toggle"
                                name="modules[{{ $mod['name'] }}]"
                                value="1"
                                data-module="{{ $mod['name'] }}"
                                {{ $mod['enabled'] ? 'checked' : '' }}
                            >
                            <span class="co-mod-slider"></span>
                        </label>
                    @endif

                    <div class="co-module-row-text">
                        <div class="co-module-row-name">
                            {{ $mod['label'] }}
                            @if($mod['is_required'])
                                <span class="co-required-badge">{{ trans('cart.always_required') ?? 'Always Required' }}</span>
                            @endif
                        </div>
                        @if($mod['desc'])
                            <div class="co-module-row-desc">{{ $mod['desc'] }}</div>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Save Row --}}
    <div class="co-save-row">
        @if($wrapForm)
            <button type="submit" id="co-save-btn">{{ $submitLabel }}</button>
        @else
            <button type="button" id="co-save-btn" onclick="coSaveSettings()">{{ $submitLabel }}</button>
        @endif
        <span id="co-save-msg">✓ {{ trans('cart.saved_successfully') ?? 'Saved successfully!' }}</span>
    </div>

    @if($wrapForm)
        </form>
    @endif

</div>

@push('scripts_bottom')
<script>
(function () {
    'use strict';

    /* AJAX save (jab wrapForm = false ho) */
    window.coSaveSettings = function () {
        var btn = document.getElementById('co-save-btn');
        var msg = document.getElementById('co-save-msg');
        var modules = {};

        document.querySelectorAll('#co-mods-bookings input[name^="modules["]').forEach(function (input) {
            var match = input.name.match(/modules\[(.+)\]/);
            if (!match) return;
            var modName = match[1];

            // checkbox priority: agar checkbox checked hai to true, warna hidden ki value
            if (input.type === 'checkbox') {
                modules[modName] = input.checked;
            } else if (!(modName in modules)) {
                modules[modName] = (input.value === '1');
            }
        });

        var csrf = document.querySelector('meta[name="csrf-token"]');
        btn.textContent = '{{ trans("cart.saving") ?? "Saving..." }}';
        btn.disabled = true;
        if (msg) msg.style.display = 'none';

        fetch('{{ $saveUrl }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf ? csrf.getAttribute('content') : '',
                'Accept': 'application/json',
            },
            body: JSON.stringify({ modules: modules })
        })
        .then(function (r) { return r.json(); })
        .then(function () {
            btn.textContent = '{{ $submitLabel }}';
            btn.disabled = false;
            if (msg) {
                msg.style.display = 'inline';
                setTimeout(function () { msg.style.display = 'none'; }, 2500);
            }
        })
        .catch(function () {
            btn.textContent = '{{ trans("cart.error_try_again") ?? "Error — try again" }}';
            btn.disabled = false;
        });
    };

    /* Standard form submit feedback */
    (function () {
        var form = document.getElementById('{{ $formId }}');
        if (!form) return;
        form.addEventListener('submit', function () {
            var btn = document.getElementById('co-save-btn');
            if (btn) { btn.textContent = '{{ trans("cart.saving") ?? "Saving..." }}'; btn.disabled = true; }
        });
    })();

})();
</script>
@endpush
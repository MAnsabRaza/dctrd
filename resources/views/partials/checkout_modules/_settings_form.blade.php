{{--
    partials/checkout_modules/_settings_form.blade.php

    Checkout Options Settings Panel — Single column layout:
    - Section name as header with master toggle
    - Module rows: mini toggle + name + description (full width, no right grid)
    - Blue (#2563EB) for ON toggles
    - Gray for OFF toggles
    - AJAX save
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
    $isFlat = isset($rawSettings[0]) || (count($rawSettings) && !is_array(reset($rawSettings)[0] ?? null));

    $sectionConfig = [
        'bookings'        => ['label' => trans('cart.section_bookings')        ?? 'checkout_section_bookings',  'modules' => ['days','hours','staff_member','persons_children','extra_services','cancellation_policy','checkout_message']],
        'booking_bundles' => ['label' => trans('cart.section_booking_bundles') ?? 'checkout_section_booking_bundles', 'modules' => ['days','hours','staff_member','checkout_message']],
        'products'        => ['label' => trans('cart.section_products')        ?? 'checkout_section_products',  'modules' => ['days','hours','persons_children','checkout_message']],
        'product_bundles' => ['label' => trans('cart.section_product_bundles') ?? 'checkout_section_product_bundles', 'modules' => ['days','hours','staff_member','checkout_message']],
        'courses'         => ['label' => trans('cart.section_courses')         ?? 'checkout_section_courses',   'modules' => ['days','hours','staff_member','checkout_message']],
        'course_bundles'  => ['label' => trans('cart.section_course_bundles')  ?? 'checkout_section_course_bundles',  'modules' => ['days','hours','staff_member','checkout_message']],
    ];

    $moduleLabels = [
        'days'                => trans('cart.module_days')                ?? 'Select Days',
        'hours'               => trans('cart.module_hours')               ?? 'Select Time Slot',
        'staff_member'        => trans('cart.module_staff_member')        ?? 'Select Staff Member',
        'persons_children'    => trans('cart.module_persons_children')    ?? 'Guests',
        'extra_services'      => trans('cart.module_extra_services')      ?? 'Extra Services',
        'cancellation_policy' => trans('cart.module_cancellation_policy') ?? 'Cancellation Policy',
        'checkout_message'    => trans('cart.module_checkout_message')    ?? 'Message for Check-out',
        'reviewer_message'    => trans('cart.module_reviewer_message')    ?? 'Message to Reviewer',
    ];
    $moduleDescs = [
        'days'                => trans('cart.module_days_desc')                ?? 'Check-in and check-out dates',
        'hours'               => trans('cart.module_hours_desc')               ?? 'Preferred time slot',
        'staff_member'        => trans('cart.module_staff_member_desc')        ?? 'Choose preferred staff member',
        'persons_children'    => trans('cart.module_persons_children_desc')    ?? 'Number of adults, children and rooms',
        'extra_services'      => trans('cart.module_extra_services_desc')      ?? 'Additional services you require',
        'cancellation_policy' => trans('cart.module_cancellation_policy_desc') ?? 'Please read and agree to our policy',
        'checkout_message'    => trans('cart.module_checkout_message_desc')    ?? 'Any special instructions for your booking',
        'reviewer_message'    => trans('cart.module_reviewer_message_desc')    ?? 'Private message to the organizer',
    ];

    $sections = [];
    foreach ($sectionConfig as $sectionKey => $sectionMeta) {
        $sectionModules = [];
        foreach ($sectionMeta['modules'] as $modName) {
            $found = null;
            if (isset($rawSettings[$sectionKey]) && is_array($rawSettings[$sectionKey])) {
                foreach ($rawSettings[$sectionKey] as $m) {
                    if (($m['name'] ?? '') === $modName) { $found = $m; break; }
                }
            } elseif ($isFlat) {
                foreach ($rawSettings as $m) {
                    if (($m['name'] ?? '') === $modName) { $found = $m; break; }
                }
            }
            $sectionModules[] = [
                'name'        => $modName,
                'label'       => $found['label']       ?? ($moduleLabels[$modName] ?? $modName),
                'desc'        => $found['help_text']   ?? ($moduleDescs[$modName]  ?? ''),
                'enabled'     => $found['enabled']     ?? false,
                'is_required' => $found['is_required'] ?? false,
                'input_type'  => $found['input_type']  ?? '',
                'id'          => $found['id']          ?? null,
            ];
        }
        $sectionEnabled = $orgModules[$sectionKey]['enabled'] ?? true;
        $sections[$sectionKey] = [
            'label'   => $sectionMeta['label'],
            'enabled' => $sectionEnabled,
            'modules' => $sectionModules,
        ];
    }
@endphp

@push('styles_top')
<style>
/* ══════════════════════════════════════
   CHECKOUT OPTIONS — SINGLE COLUMN
══════════════════════════════════════ */
.co-wrap {
    font-family: var(--font-sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
}

/* ── Section block ── */
.co-section-block {
    margin-bottom: 6px;
}

/* Section header row */
.co-section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 6px 0 4px;
}

.co-section-name {
    font-size: 11.5px;
    font-weight: 700;
    color: #0f172a;
    letter-spacing: 0.01em;
}

/* ── Module row ── */
.co-module-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 5px 6px 5px 4px;
    border-radius: 7px;
    transition: background 0.12s;
}
.co-module-row:hover {
    background: #f1f5f9;
}

.co-module-row-left {
    display: flex;
    align-items: center;
    gap: 8px;
    min-width: 0;
}

.co-module-row-text {
    min-width: 0;
}

.co-module-row-name {
    font-size: 11.5px;
    font-weight: 500;
    color: #1e293b;
    line-height: 1.3;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.co-module-row-desc {
    font-size: 10px;
    color: #94a3b8;
    line-height: 1.3;
    margin-top: 1px;
}

/* Section divider */
.co-section-divider {
    height: 0.5px;
    background: #e2e8f0;
    margin: 6px 0;
}

/* Save row */
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

/* ── MASTER TOGGLE (section header, 32×18) ── */
.co-master-toggle {
    position: relative;
    width: 32px;
    height: 18px;
    flex-shrink: 0;
}
.co-master-toggle input { opacity:0; width:0; height:0; position:absolute; }
.co-master-slider {
    position: absolute;
    inset: 0;
    border-radius: 20px;
    background: #cbd5e1;
    cursor: pointer;
    transition: background 0.2s;
}
.co-master-slider:before {
    content: '';
    position: absolute;
    width: 12px; height: 12px;
    border-radius: 50%;
    background: #fff;
    top: 3px; left: 3px;
    transition: transform 0.2s;
}
.co-master-toggle input:checked + .co-master-slider            { background: #2563EB; }
.co-master-toggle input:checked + .co-master-slider:before     { transform: translateX(14px); }

/* ── MODULE TOGGLE (each row, 34×19) ── */
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

/* Faded when master OFF */
.co-section-faded {
    opacity: 0.35;
    pointer-events: none;
    user-select: none;
}
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

    @foreach($sections as $sectionKey => $section)

        <div class="co-section-block">

            {{-- Section header: name + master toggle --}}
            <div class="co-section-header">
                <label class="co-master-toggle" title="{{ $section['label'] }}">
                    <input
                        type="checkbox"
                        class="co-master-check"
                        data-section="{{ $sectionKey }}"
                        {{ $section['enabled'] ? 'checked' : '' }}
                        onchange="coMasterToggle('{{ $sectionKey }}', this)"
                    >
                    <span class="co-master-slider"></span>
                </label>
                <span class="co-section-name">{{ $section['label'] }}</span>
            </div>

            {{-- Module rows --}}
            <div id="co-mods-{{ $sectionKey }}" class="{{ !$section['enabled'] ? 'co-section-faded' : '' }}">
                @foreach($section['modules'] as $mod)
                    <div class="co-module-row">
                        <div class="co-module-row-left">
                            <label class="co-mod-toggle" title="{{ $mod['label'] }}">
                                <input
                                    type="checkbox"
                                    class="co-module-toggle"
                                    name="sections[{{ $sectionKey }}][{{ $mod['name'] }}]"
                                    value="1"
                                    data-section="{{ $sectionKey }}"
                                    data-module="{{ $mod['name'] }}"
                                    {{ $mod['enabled'] ? 'checked' : '' }}
                                >
                                <span class="co-mod-slider"></span>
                            </label>
                            <div class="co-module-row-text">
                                <div class="co-module-row-name">{{ $mod['label'] }}</div>
                                @if($mod['desc'])
                                    <div class="co-module-row-desc">{{ $mod['desc'] }}</div>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

        </div>

        @if(!$loop->last)
            <div class="co-section-divider"></div>
        @endif

    @endforeach

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

    /* Master toggle — fades/unfades module rows */
    window.coMasterToggle = function (sectionId, checkbox) {
        var modsBlock = document.getElementById('co-mods-' + sectionId);
        if (!modsBlock) return;
        if (checkbox.checked) {
            modsBlock.classList.remove('co-section-faded');
        } else {
            modsBlock.classList.add('co-section-faded');
        }
    };

    /* AJAX save (wrapForm = false) */
    window.coSaveSettings = function () {
        var btn = document.getElementById('co-save-btn');
        var msg = document.getElementById('co-save-msg');
        var settings = {};

        document.querySelectorAll('.co-module-toggle').forEach(function (t) {
            var sec = t.dataset.section;
            var mod = t.dataset.module;
            if (!settings[sec]) settings[sec] = {};
            settings[sec][mod] = t.checked;
        });

        document.querySelectorAll('.co-master-check').forEach(function (cb) {
            var sec = cb.dataset.section;
            if (!settings[sec]) settings[sec] = {};
            settings[sec]['_enabled'] = cb.checked;
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
            body: JSON.stringify({ settings: settings })
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
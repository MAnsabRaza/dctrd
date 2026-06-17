{{--
    partials/checkout_modules/_settings_form.blade.php

    Checkout Options Settings Panel — matches the image exactly:
    - Outer card with thin border
    - Note box at top
    - TWO-COLUMN layout: Left (200px fixed) = sections + mini toggles | Right = module cards grid (2 cols)
    - Blue (#2563EB) for ON toggles only
    - Gray for OFF toggles
    - Master toggle per section
    - Save via AJAX (no page reload)

    Required variables passed via @include:
        $moduleSettings  → array of modules (from controller)
        $saveUrl         → POST url
        $formId          → form element id
        $title           → page title string
        $description     → subtitle string
        $submitLabel     → save button label
        $wrapForm        → bool

    Controller must pass $moduleSettings structured as:
    [
      'bookings'        => [ ['id'=>1,'name'=>'days','label'=>'Days','enabled'=>true,'is_required'=>false,'input_type'=>'date_range','help_text'=>'...'], ... ],
      'booking_bundles' => [...],
      'products'        => [...],
      'product_bundles' => [...],
      'courses'         => [...],
      'course_bundles'  => [...],
    ]
    and $orgModules = [ 'bookings'=>['enabled'=>true], ... ]
--}}

@php
    $formId      = $formId      ?? 'checkoutOptionsForm';
    $formClass   = $formClass   ?? '';
    $saveUrl     = $saveUrl     ?? '#';
    $submitLabel = $submitLabel ?? trans('admin/main.save');
    $title       = $title       ?? trans('panel.checkout_options');
    $description = $description ?? trans('panel.checkout_options_hint');
    $wrapForm    = $wrapForm    ?? true;

    // Support both flat array (old) and sectioned array (new)
    $rawSettings = $moduleSettings ?? [];

    // Detect if it's a flat array (old format) or sectioned (new format)
    $isFlat = isset($rawSettings[0]) || (count($rawSettings) && !is_array(reset($rawSettings)[0] ?? null));

    // Define the 6 sections with their module lists
    $sectionConfig = [
        'bookings'        => ['label' => trans('checkout.section_bookings'),        'modules' => ['days','hours','staff_member','persons_children','extra_services','cancellation_policy','checkout_message']],
        'booking_bundles' => ['label' => trans('checkout.section_booking_bundles'), 'modules' => ['days','hours','staff_member','checkout_message']],
        'products'        => ['label' => trans('checkout.section_products'),        'modules' => ['days','hours','persons_children','checkout_message']],
        'product_bundles' => ['label' => trans('checkout.section_product_bundles'), 'modules' => ['days','hours','staff_member','checkout_message']],
        'courses'         => ['label' => trans('checkout.section_courses'),         'modules' => ['days','hours','staff_member','checkout_message']],
        'course_bundles'  => ['label' => trans('checkout.section_course_bundles'),  'modules' => ['days','hours','staff_member','checkout_message']],
    ];

    // Module display names + descriptions
    $moduleLabels = [
        'days'                => trans('checkout.module_days'),
        'hours'               => trans('checkout.module_hours'),
        'staff_member'        => trans('checkout.module_staff_member'),
        'persons_children'    => trans('checkout.module_persons_children'),
        'extra_services'      => trans('checkout.module_extra_services'),
        'cancellation_policy' => trans('checkout.module_cancellation_policy'),
        'checkout_message'    => trans('checkout.module_checkout_message'),
        'reviewer_message'    => trans('checkout.module_reviewer_message'),
    ];
    $moduleDescs = [
        'days'                => trans('checkout.module_days_desc'),
        'hours'               => trans('checkout.module_hours_desc'),
        'staff_member'        => trans('checkout.module_staff_member_desc'),
        'persons_children'    => trans('checkout.module_persons_children_desc'),
        'extra_services'      => trans('checkout.module_extra_services_desc'),
        'cancellation_policy' => trans('checkout.module_cancellation_policy_desc'),
        'checkout_message'    => trans('checkout.module_checkout_message_desc'),
        'reviewer_message'    => trans('checkout.module_reviewer_message_desc'),
    ];

    // Build sectioned modules from whatever format we got
    $sections = [];
    foreach ($sectionConfig as $sectionKey => $sectionMeta) {
        $sectionModules = [];
        foreach ($sectionMeta['modules'] as $modName) {
            // Try to find in the passed $moduleSettings
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
                'name'       => $modName,
                'label'      => $found['label']      ?? ($moduleLabels[$modName] ?? $modName),
                'desc'       => $found['help_text']  ?? ($moduleDescs[$modName]  ?? ''),
                'enabled'    => $found['enabled']    ?? false,
                'is_required'=> $found['is_required'] ?? false,
                'input_type' => $found['input_type'] ?? '',
                'id'         => $found['id']         ?? null,
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
/* ══════════════════════════════════════════
   CHECKOUT OPTIONS SETTINGS PANEL
══════════════════════════════════════════ */
.co-wrap {
    padding: 0;
    font-family: var(--font-sans, -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif);
}
.co-wrap h2.co-page-title {
    font-size: 15px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 12px;
}

/* Outer card */
.co-outer-card {
    background: #ffffff;
    border: 0.5px solid #d1d5db;
    border-radius: 14px;
    padding: 16px;
}

/* Note box */
.co-note-box {
    background: #f8fafc;
    border-radius: 8px;
    padding: 8px 12px;
    margin-bottom: 14px;
    font-size: 11px;
    color: #64748b;
    line-height: 1.5;
    border: 0.5px solid #e2e8f0;
}

/* Two column layout */
.co-two-col {
    display: grid;
    grid-template-columns: 200px 1fr;
    gap: 14px;
    align-items: start;
}

/* ── LEFT COLUMN ── */
.co-left-col {}

.co-section-row {
    margin-bottom: 0;
}

.co-section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 8px 0 6px;
}

.co-section-name {
    font-size: 12px;
    font-weight: 600;
    color: #0f172a;
}

.co-module-left-list {
    padding-left: 2px;
    padding-bottom: 6px;
}

.co-module-left-item {
    font-size: 11px;
    color: #64748b;
    padding: 3px 0;
    display: flex;
    align-items: center;
    gap: 7px;
    line-height: 1.3;
}

.co-section-divider {
    height: 0.5px;
    background: #e2e8f0;
    margin: 4px 0;
}

/* ── RIGHT COLUMN ── */
.co-right-col {
    background: #f8fafc;
    border-radius: 10px;
    padding: 12px;
}

.co-right-section {
    margin-bottom: 12px;
}
.co-right-section:last-child {
    margin-bottom: 0;
}

.co-modules-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 7px;
}

/* Module card */
.co-module-card {
    background: #ffffff;
    border: 0.5px solid #e2e8f0;
    border-radius: 9px;
    padding: 9px 11px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    transition: border-color 0.15s;
}
.co-module-card:hover {
    border-color: #cbd5e1;
}

.co-module-card-name {
    font-size: 11.5px;
    font-weight: 600;
    color: #0f172a;
    margin-bottom: 1px;
    line-height: 1.3;
}

.co-module-card-desc {
    font-size: 10px;
    color: #94a3b8;
    line-height: 1.3;
}

/* Save row */
.co-save-row {
    margin-top: 14px;
    padding-top: 12px;
    border-top: 0.5px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 10px;
}

#co-save-btn {
    padding: 7px 20px;
    font-size: 13px;
    font-weight: 600;
    background: #2563EB;
    color: #fff;
    border: none;
    border-radius: 8px;
    cursor: pointer;
    transition: background 0.15s, opacity 0.15s;
}
#co-save-btn:hover { background: #1d4ed8; }
#co-save-btn:disabled { opacity: 0.65; cursor: not-allowed; }

#co-save-msg {
    font-size: 12px;
    color: #16a34a;
    font-weight: 500;
    display: none;
}

/* ── MASTER TOGGLE (left, 32×18) ── */
.co-master-toggle {
    position: relative;
    width: 32px;
    height: 18px;
    flex-shrink: 0;
}
.co-master-toggle input {
    opacity: 0; width: 0; height: 0; position: absolute;
}
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
.co-master-toggle input:checked + .co-master-slider { background: #2563EB; }
.co-master-toggle input:checked + .co-master-slider:before { transform: translateX(14px); }

/* ── MINI TOGGLE (left module list, 26×15) ── */
.co-mini-toggle {
    position: relative;
    width: 26px;
    height: 15px;
    flex-shrink: 0;
}
.co-mini-toggle input {
    opacity: 0; width: 0; height: 0; position: absolute;
}
.co-mini-slider {
    position: absolute;
    inset: 0;
    border-radius: 20px;
    background: #cbd5e1;
    cursor: pointer;
    transition: background 0.2s;
}
.co-mini-slider:before {
    content: '';
    position: absolute;
    width: 9px; height: 9px;
    border-radius: 50%;
    background: #fff;
    top: 3px; left: 3px;
    transition: transform 0.2s;
}
.co-mini-toggle input:checked + .co-mini-slider { background: #94a3b8; }
.co-mini-toggle input:checked + .co-mini-slider:before { transform: translateX(11px); }

/* ── BIG TOGGLE (right module cards, 36×20) ── */
.co-toggle {
    position: relative;
    width: 36px;
    height: 20px;
    flex-shrink: 0;
}
.co-toggle input {
    opacity: 0; width: 0; height: 0; position: absolute;
}
.co-slider {
    position: absolute;
    inset: 0;
    border-radius: 20px;
    background: #cbd5e1;
    cursor: pointer;
    transition: background 0.2s;
}
.co-slider:before {
    content: '';
    position: absolute;
    width: 14px; height: 14px;
    border-radius: 50%;
    background: #fff;
    top: 3px; left: 3px;
    transition: transform 0.2s;
    box-shadow: 0 1px 3px rgba(0,0,0,0.18);
}
.co-toggle input:checked + .co-slider { background: #2563EB; }
.co-toggle input:checked + .co-slider:before { transform: translateX(16px); }

/* Disabled / faded section */
.co-section-faded {
    opacity: 0.35;
    pointer-events: none;
    user-select: none;
}

@media (max-width: 768px) {
    .co-two-col {
        grid-template-columns: 1fr;
    }
    .co-left-col {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0 10px;
    }
    .co-section-divider { display: none; }
    .co-modules-grid { grid-template-columns: 1fr; }
}
</style>
@endpush

{{-- ════════════════════════════════════════════════════════
     TEMPLATE
════════════════════════════════════════════════════════ --}}
<div class="co-wrap">

    {{-- Outer Card --}}
    <div class="co-outer-card">

        {{-- Note Box --}}
        <div class="co-note-box">
            {{ trans('checkout.note_a') ?? 'The fields shown here are examples — configure them for your platform.' }}<br>
            {{ trans('checkout.note_b') ?? 'All options are Disabled by default.' }}
        </div>

        @if($wrapForm)
            <form action="{{ $saveUrl }}" method="POST" id="{{ $formId }}" class="{{ trim($formClass) }}">
                @csrf
        @endif

        {{-- Two Column Layout --}}
        <div class="co-two-col">

            {{-- ══ LEFT COLUMN ══ --}}
            <div class="co-left-col">

                @foreach($sections as $sectionKey => $section)

                    <div class="co-section-row">

                        {{-- Section header: name + master toggle --}}
                        <div class="co-section-header">
                            <span class="co-section-name">{{ $section['label'] }}</span>
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
                        </div>

                        {{-- Mini module list --}}
                        <div class="co-module-left-list" id="co-left-{{ $sectionKey }}">
                            @foreach($section['modules'] as $mod)
                                <div class="co-module-left-item" data-section="{{ $sectionKey }}" data-module="{{ $mod['name'] }}">
                                    <label class="co-mini-toggle">
                                        <input
                                            type="checkbox"
                                            class="co-mini-input"
                                            data-section="{{ $sectionKey }}"
                                            data-module="{{ $mod['name'] }}"
                                            {{ $mod['enabled'] ? 'checked' : '' }}
                                            onchange="coSyncRightToggle('{{ $sectionKey }}', '{{ $mod['name'] }}', this)"
                                        >
                                        <span class="co-mini-slider"></span>
                                    </label>
                                    <span>{{ $mod['label'] }}</span>
                                </div>
                            @endforeach
                        </div>

                    </div>

                    @if(!$loop->last)
                        <div class="co-section-divider"></div>
                    @endif

                @endforeach

            </div>
            {{-- /.co-left-col --}}

            {{-- ══ RIGHT COLUMN ══ --}}
            <div class="co-right-col">

                @foreach($sections as $sectionKey => $section)

                    <div
                        class="co-right-section {{ !$section['enabled'] ? 'co-section-faded' : '' }}"
                        id="co-right-{{ $sectionKey }}"
                    >
                        <div class="co-modules-grid">
                            @foreach($section['modules'] as $mod)
                                <div class="co-module-card">
                                    <div style="min-width:0;">
                                        <div class="co-module-card-name">{{ $mod['label'] }}</div>
                                        <div class="co-module-card-desc">{{ $mod['desc'] }}</div>
                                    </div>
                                    <label class="co-toggle" title="{{ $mod['label'] }}">
                                        <input
                                            type="checkbox"
                                            class="co-module-toggle"
                                            name="sections[{{ $sectionKey }}][{{ $mod['name'] }}]"
                                            value="1"
                                            data-section="{{ $sectionKey }}"
                                            data-module="{{ $mod['name'] }}"
                                            {{ $mod['enabled'] ? 'checked' : '' }}
                                            onchange="coSyncMiniToggle('{{ $sectionKey }}', '{{ $mod['name'] }}', this)"
                                        >
                                        <span class="co-slider"></span>
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    @if(!$loop->last)
                        <hr style="border:none;border-top:0.5px solid #e2e8f0;margin:10px 0;">
                    @endif

                @endforeach

            </div>
            {{-- /.co-right-col --}}

        </div>
        {{-- /.co-two-col --}}

        {{-- Save Row --}}
        <div class="co-save-row">
            @if($wrapForm)
                <button type="submit" id="co-save-btn">
                    {{ $submitLabel }}
                </button>
            @else
                <button type="button" id="co-save-btn" onclick="coSaveSettings()">
                    {{ $submitLabel }}
                </button>
            @endif
            <span id="co-save-msg">✓ {{ trans('checkout.saved_successfully') ?? 'Saved successfully!' }}</span>
        </div>

        @if($wrapForm)
            </form>
        @endif

    </div>
    {{-- /.co-outer-card --}}

</div>
{{-- /.co-wrap --}}

@push('scripts_bottom')
<script>
(function () {
    'use strict';

    /* ─────────────────────────────────────────
       Master toggle → fades/unfades right section
    ───────────────────────────────────────── */
    window.coMasterToggle = function (sectionId, checkbox) {
        var rightSection = document.getElementById('co-right-' + sectionId);
        var leftList     = document.getElementById('co-left-' + sectionId);

        if (rightSection) {
            if (checkbox.checked) {
                rightSection.classList.remove('co-section-faded');
            } else {
                rightSection.classList.add('co-section-faded');
            }
        }
    };

    /* ─────────────────────────────────────────
       Right big toggle → sync left mini toggle
    ───────────────────────────────────────── */
    window.coSyncMiniToggle = function (sectionId, moduleName, rightToggle) {
        var miniInput = document.querySelector(
            '.co-mini-input[data-section="' + sectionId + '"][data-module="' + moduleName + '"]'
        );
        if (miniInput) {
            miniInput.checked = rightToggle.checked;
        }
    };

    /* ─────────────────────────────────────────
       Left mini toggle → sync right big toggle
    ───────────────────────────────────────── */
    window.coSyncRightToggle = function (sectionId, moduleName, miniToggle) {
        var rightInput = document.querySelector(
            '.co-module-toggle[data-section="' + sectionId + '"][data-module="' + moduleName + '"]'
        );
        if (rightInput) {
            rightInput.checked = miniToggle.checked;
        }
    };

    /* ─────────────────────────────────────────
       AJAX save (used when wrapForm = false)
    ───────────────────────────────────────── */
    window.coSaveSettings = function () {
        var btn = document.getElementById('co-save-btn');
        var msg = document.getElementById('co-save-msg');
        var settings = {};

        // Collect module toggles
        document.querySelectorAll('.co-module-toggle').forEach(function (t) {
            var sec = t.dataset.section;
            var mod = t.dataset.module;
            if (!settings[sec]) settings[sec] = {};
            settings[sec][mod] = t.checked;
        });

        // Collect master toggles
        document.querySelectorAll('.co-master-check').forEach(function (cb) {
            var sec = cb.dataset.section;
            if (!settings[sec]) settings[sec] = {};
            settings[sec]['_enabled'] = cb.checked;
        });

        var csrfMeta = document.querySelector('meta[name="csrf-token"]');
        var csrfToken = csrfMeta ? csrfMeta.getAttribute('content') : '';

        btn.textContent = '{{ trans("checkout.saving") ?? "Saving..." }}';
        btn.disabled = true;
        if (msg) msg.style.display = 'none';

        fetch('{{ $saveUrl }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'Accept': 'application/json',
            },
            body: JSON.stringify({ settings: settings })
        })
        .then(function (res) { return res.json(); })
        .then(function (data) {
            btn.textContent = '{{ $submitLabel }}';
            btn.disabled = false;
            if (msg) {
                msg.style.display = 'inline';
                setTimeout(function () { msg.style.display = 'none'; }, 2500);
            }
        })
        .catch(function (err) {
            btn.textContent = '{{ trans("checkout.error_try_again") ?? "Error — try again" }}';
            btn.disabled = false;
        });
    };

    /* ─────────────────────────────────────────
       Standard form submit feedback
    ───────────────────────────────────────── */
    (function () {
        var form = document.getElementById('{{ $formId }}');
        if (!form) return;
        form.addEventListener('submit', function () {
            var btn = document.getElementById('co-save-btn');
            if (btn) {
                btn.textContent = '{{ trans("checkout.saving") ?? "Saving..." }}';
                btn.disabled = true;
            }
        });
    })();

})();
</script>
@endpush
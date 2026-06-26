{{--
    design_1/web/cart/overview/includes/checkout_item_modules.blade.php

    ══════════════════════════════════════════════════════════════════════
    MERGED FILE — Styles + Scripts + Template (single source of truth)
    ══════════════════════════════════════════════════════════════════════

    Previously split across three files:
      • checkout_item_modules.blade.php    (template / HTML)
      • _checkout_modules_styles.blade.php (CSS)
      • _checkout_modules_scripts.blade.php (JS)

    Used by:
      - cart_items.blade.php          (cart page, booking rows)
      - booking.blade.php             (cart page, alternate booking card)
      - checkout page (same partial, no duplication)

    Visual style: 3-column grid (booking-info-card) — matches the
    cart_items.blade.php look.

    Required variables:
        $cart              -> the cart row (booking cart item)
        $checkoutModules   -> Collection of active modules for this item
        $itemKey           -> string/int, used to namespace field names + ids

    Optional variables:
        $showHeader        -> bool, show "Booking Details" style header (default false)
        $wrapperClassName  -> extra class for outer wrapper
--}}

{{-- ════════════════════════════════════════
     STYLES  (injected once via @once)
════════════════════════════════════════ --}}
@once
    @push('styles_top')
    <style>
        .booking-info-shell {
            margin-top: 16px; background: #fff; border: 1px solid #e2e8f0;
            border-radius: 18px; padding: 18px;
            box-shadow: 0 10px 24px rgba(15, 23, 42, .08);
        }
        .booking-info-grid {
            margin-top: 4px;
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 12px;
        }
        .booking-info-card {
            background: #fff; border: 1px solid #e2e8f0; border-radius: 16px;
            padding: 16px; min-height: 110px;
            display: flex; flex-direction: column; justify-content: flex-start; gap: 8px;
        }
        .booking-info-title {
            font-size: 11px; font-weight: 700; color: #64748b;
            text-transform: uppercase; letter-spacing: .04em;
        }
        .booking-info-content {
            display: flex; align-items: center; gap: 12px;
        }
        .booking-info-icon {
            width: 30px; height: 30px; min-width: 30px; min-height: 30px;
            background: rgba(34, 197, 94, .1); border-radius: 12px;
            display: inline-flex; align-items: center; justify-content: center;
            color: #22c55e;
        }
        .booking-info-value {
            font-size: 14px; font-weight: 700; color: #0f172a; line-height: 1.3;
        }
        .booking-info-label { font-size: 13px; color: #475569; }
        .booking-cancellation-card {
            margin-top: 14px; border: 1px solid #e2e8f0;
            border-radius: 16px; background: #fff; padding: 16px;
        }
        .booking-cancellation-card label {
            display: flex; align-items: center; gap: 10px;
            font-size: 13px; font-weight: 600; color: #0f172a; cursor: pointer;
        }
        .booking-cancellation-card .booking-cancellation-text {
            margin-top: 12px; font-size: 12px; color: #6b7280; line-height: 1.6;
        }
        .cmod-nights {
            display: inline-flex; align-items: center; gap: 3px;
            margin-top: 2px; padding: 2px 7px; border-radius: 999px;
            background: rgba(34,197,94,.1); border: 1px solid rgba(34,197,94,.3);
            font-size: 10px; color: #15803d; font-weight: 500;
        }
        /* Stepper */
        .bmod-stepper-btn {
            width: 28px; height: 28px; border-radius: 8px;
            border: 1px solid #e2e8f0; background: #f8fafc;
            font-size: 16px; line-height: 1; cursor: pointer;
            display: inline-flex; align-items: center; justify-content: center;
            transition: background .15s;
        }
        .bmod-stepper-btn:hover { background: #e2e8f0; }
        .bmod-stepper-input {
            width: 40px; text-align: center; border: 1px solid #e2e8f0;
            border-radius: 8px; padding: 4px 0; font-size: 14px; font-weight: 600;
        }
        .bmod-date-input { border-radius: 12px; }
        @media (max-width: 991px) {
            .booking-info-grid { grid-template-columns: 1fr; }
        }
    </style>
    @endpush
@endonce

{{-- ════════════════════════════════════════
     PHP SETUP
════════════════════════════════════════ --}}
@php
    $itemKey            = $itemKey ?? ($cart->id ?? '0');
    $checkoutModules    = $checkoutModules ?? collect();
    $activeModules      = $checkoutModules->filter(fn($m) => $m->is_active ?? true);

    // Grid modules go in the 3-column top row, everything else stacks below
    $gridModules   = $activeModules->filter(fn($m) => in_array($m->name, ['days', 'hours', 'staff_member']));
    $bottomModules = $activeModules->filter(fn($m) => !in_array($m->name, ['days', 'hours', 'staff_member']));

    $daysModule  = $activeModules->firstWhere('name', 'days');
    $hoursModule = $activeModules->firstWhere('name', 'hours');
    $staffModule = $activeModules->firstWhere('name', 'staff_member');

    // Prefix helpers (namespaced per cart item so multiple bookings don't collide)
    $datePrefix   = "checkout_modules[{$itemKey}][days]";
    $timePrefix   = "checkout_modules[{$itemKey}][hours]";
    $staffPrefix  = "checkout_modules[{$itemKey}][staff_member]";
    $paxPrefix    = "checkout_modules[{$itemKey}][persons_children]";
    $policyPrefix = "checkout_modules[{$itemKey}][cancellation_policy]";
    $extrasPrefix = "checkout_modules[{$itemKey}][extra_services]";
    $msgPrefix    = "checkout_modules[{$itemKey}][checkout_message]";
    $revPrefix    = "checkout_modules[{$itemKey}][reviewer_message]";

    $authUserName = auth()->check() ? auth()->user()->full_name : '';

    $oldCheckIn  = old("checkout_modules.{$itemKey}.days.check_in");
    $oldCheckOut = old("checkout_modules.{$itemKey}.days.check_out");

    // Fallbacks from cart->meta (slot info captured at "Add to cart" time)
    $slotDate  = $cart->meta['slot_date']  ?? null;
    $slotStart = $cart->meta['slot_start'] ?? null;
    $slotEnd   = $cart->meta['slot_end']   ?? null;

    $checkInLabel = $oldCheckIn
        ? \Carbon\Carbon::parse($oldCheckIn)->format('d M Y')
        : ($slotDate ? \Carbon\Carbon::parse($slotDate)->format('d M Y') : (trans('cart.not_selected') ?? 'Not selected'));

    $selectedTime = old("checkout_modules.{$itemKey}.hours", $slotStart ?? '');
    $timeLabel    = $selectedTime
        ?: ($slotStart ? $slotStart . ($slotEnd ? ' - ' . $slotEnd : '') : (trans('cart.not_selected') ?? 'Not selected'));

    $selectedStaff = old("checkout_modules.{$itemKey}.staff_member", $authUserName);
    $staffLabel    = $selectedStaff ?: ($authUserName ?: (trans('cart.guest') ?? 'Guest'));

    $showHeader       = $showHeader ?? false;
    $wrapperClassName = $wrapperClassName ?? '';
@endphp

{{-- ════════════════════════════════════════
     TEMPLATE / HTML
════════════════════════════════════════ --}}
@if($activeModules->isNotEmpty())
<div class="booking-info-shell {{ $wrapperClassName }}" data-item-key="{{ $itemKey }}">

    @if($showHeader)
        <div class="cart-section-label">{{ trans('cart.booking_details') ?? 'Booking Details' }}</div>
    @endif

    {{-- TOP ROW: 3-column grid (days, hours, staff) --}}
    @if($gridModules->isNotEmpty())
        <div class="booking-info-grid">

            {{-- DAYS MODULE --}}
            @if($daysModule)
                @php $perDay = $daysModule->price_rule['amount'] ?? 0; @endphp
                <div class="booking-info-card" data-module-name="days" data-price-type="per_day" data-price-amount="{{ $perDay }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="booking-info-title">
                            {{ $daysModule->translated_label ?? trans('update.check_in_date') ?? 'Check-in Date' }}
                            @if($daysModule->is_required)<span class="text-danger">*</span>@endif
                        </div>
                        @if($perDay)
                            <span class="font-11 font-weight-bold text-primary">{{ handlePrice($perDay) }}/{{ trans('cart.day') ?? 'day' }}</span>
                        @endif
                    </div>
                    <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:8px;">
                        <div class="booking-info-icon">
                            <x-iconsax-lin-calendar-2 class="icons" width="16px" height="16px"/>
                        </div>
                        <input type="date"
                               name="{{ $datePrefix }}[check_in]"
                               id="bmod_cin_{{ $itemKey }}"
                               class="form-control form-control-sm bmod-date-input bmod-cin"
                               value="{{ $oldCheckIn }}"
                               min="{{ now()->format('Y-m-d') }}"
                               {{ $daysModule->is_required ? 'required' : '' }}
                               style="border-radius:12px;">
                        <input type="date"
                               name="{{ $datePrefix }}[check_out]"
                               id="bmod_cout_{{ $itemKey }}"
                               class="form-control form-control-sm bmod-date-input bmod-cout"
                               value="{{ $oldCheckOut }}"
                               min="{{ now()->format('Y-m-d') }}"
                               {{ $daysModule->is_required ? 'required' : '' }}
                               style="border-radius:12px;">
                        <div class="booking-info-label bmod-cin-label">{{ $checkInLabel }}</div>
                        <div class="cmod-nights" id="bmod_nights_{{ $itemKey }}">0 {{ trans('cart.nights') ?? 'nights' }}</div>
                    </div>
                    @error("checkout_modules.{$itemKey}.days.check_in")
                        <div class="text-danger font-11 mt-4">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            {{-- HOURS MODULE --}}
            @if($hoursModule)
                @php
                    $slots   = $hoursModule->config['slots'] ?? ['09:00','10:00','11:00','12:00','13:00','14:00','15:00','16:00','17:00'];
                    $perHour = $hoursModule->price_rule['amount'] ?? 0;
                @endphp
                <div class="booking-info-card" data-module-name="hours" data-price-type="per_hour" data-price-amount="{{ $perHour }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="booking-info-title">
                            {{ $hoursModule->translated_label ?? trans('update.check_in_time') ?? 'Check-in Time' }}
                            @if($hoursModule->is_required)<span class="text-danger">*</span>@endif
                        </div>
                        @if($perHour)
                            <span class="font-11 font-weight-bold text-primary">{{ handlePrice($perHour) }}/{{ trans('cart.hour') ?? 'hr' }}</span>
                        @endif
                    </div>
                    <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:8px;">
                        <div class="booking-info-icon">
                            <x-iconsax-lin-clock class="icons" width="16px" height="16px"/>
                        </div>
                        <select name="{{ $timePrefix }}"
                                id="bmod_time_{{ $itemKey }}"
                                class="form-control form-control-sm bmod-time-select"
                                {{ $hoursModule->is_required ? 'required' : '' }}
                                style="border-radius:12px;">
                            <option value="">— {{ trans('cart.select') ?? 'Select' }} —</option>
                            @foreach($slots as $slot)
                                @php
                                    try {
                                        $sc  = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                        $ec  = $sc->copy()->addHour();
                                        $lbl = $sc->format('h:i A') . ' - ' . $ec->format('h:i A');
                                    } catch (\Throwable $e) {
                                        $lbl = $slot;
                                    }
                                @endphp
                                <option value="{{ $slot }}" {{ $selectedTime == $slot ? 'selected' : '' }}>{{ $lbl }}</option>
                            @endforeach
                        </select>
                        <div class="booking-info-label bmod-time-label">{{ $timeLabel }}</div>
                    </div>
                    @error("checkout_modules.{$itemKey}.hours")
                        <div class="text-danger font-11 mt-4">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            {{-- STAFF MODULE --}}
            @if($staffModule)
                @php $staffOptions = $staffModule->config['staff'] ?? []; @endphp
                <div class="booking-info-card" data-module-name="staff_member" data-price-type="none">
                    <div class="booking-info-title">
                        {{ $staffModule->translated_label ?? trans('cart.staff_member') ?? 'Assigned Staff' }}
                        @if($staffModule->is_required)<span class="text-danger">*</span>@endif
                    </div>
                    <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:8px;">
                        <div class="booking-info-icon">
                            <x-iconsax-lin-profile class="icons" width="16px" height="16px"/>
                        </div>
                        @if(!empty($staffOptions))
                            <select name="{{ $staffPrefix }}"
                                    id="bmod_staff_{{ $itemKey }}"
                                    class="form-control form-control-sm"
                                    {{ $staffModule->is_required ? 'required' : '' }}
                                    style="border-radius:12px;">
                                <option value="">— {{ trans('cart.select_staff') ?? 'Select staff' }} —</option>
                                @foreach($staffOptions as $staff)
                                    @php $value = $staff['id'] ?? $staff['name']; @endphp
                                    <option value="{{ $value }}" {{ $selectedStaff == $value ? 'selected' : '' }}>{{ $staff['name'] }}</option>
                                @endforeach
                            </select>
                        @else
                            <div class="booking-info-value">{{ $staffLabel }}</div>
                            <input type="hidden" name="{{ $staffPrefix }}" value="{{ $selectedStaff }}">
                        @endif
                        <div class="booking-info-label">{{ $staffLabel }}</div>
                    </div>
                    @error("checkout_modules.{$itemKey}.staff_member")
                        <div class="text-danger font-11 mt-4">{{ $message }}</div>
                    @enderror
                </div>
            @endif

        </div>{{-- .booking-info-grid --}}
    @endif

    {{-- PERSONS + CHILDREN + ROOMS --}}
    @if($paxModule = $bottomModules->firstWhere('name', 'persons_children'))
        @php
            $paxConfig   = $paxModule->config ?? [];
            $adultMin    = $paxConfig['adults']['min']   ?? 1;
            $adultMax    = $paxConfig['adults']['max']   ?? 20;
            $childMin    = $paxConfig['children']['min'] ?? 0;
            $childMax    = $paxConfig['children']['max'] ?? 10;
            $roomMin     = $paxConfig['rooms']['min']    ?? 1;
            $roomMax     = $paxConfig['rooms']['max']    ?? 10;
            $oldAdults   = old("checkout_modules.{$itemKey}.persons_children.adults",   $adultMin);
            $oldChildren = old("checkout_modules.{$itemKey}.persons_children.children", 0);
            $oldRooms    = old("checkout_modules.{$itemKey}.persons_children.rooms",    $roomMin);
            $perPerson   = $paxModule->price_rule['amount'] ?? 0;
        @endphp
        <div class="booking-cancellation-card" data-module-name="persons_children" data-price-type="per_person" data-price-amount="{{ $perPerson }}">
            <div class="d-flex align-items-center justify-content-between">
                <div class="booking-info-title">
                    {{ $paxModule->translated_label ?? trans('cart.guests') ?? 'Guests' }}
                    @if($paxModule->is_required)<span class="text-danger">*</span>@endif
                </div>
                @if($perPerson)
                    <span class="font-11 font-weight-bold text-primary">{{ handlePrice($perPerson) }}/{{ trans('cart.person') ?? 'person' }}</span>
                @endif
            </div>

            <div class="d-flex flex-wrap gap-20 mt-12">
                {{-- Adults --}}
                <div class="d-flex align-items-center gap-8">
                    <span class="font-13 text-gray-600">{{ trans('cart.adults') ?? 'Adults' }}</span>
                    <div class="d-flex align-items-center gap-4">
                        <button type="button" class="bmod-stepper-btn" data-target="pax_adults_{{ $itemKey }}" data-action="dec" data-min="{{ $adultMin }}">−</button>
                        <input type="number" id="pax_adults_{{ $itemKey }}" name="{{ $paxPrefix }}[adults]"
                               value="{{ $oldAdults }}" min="{{ $adultMin }}" max="{{ $adultMax }}"
                               class="bmod-stepper-input" readonly>
                        <button type="button" class="bmod-stepper-btn" data-target="pax_adults_{{ $itemKey }}" data-action="inc" data-max="{{ $adultMax }}">+</button>
                    </div>
                </div>

                {{-- Children --}}
                <div class="d-flex align-items-center gap-8">
                    <span class="font-13 text-gray-600">{{ trans('cart.children') ?? 'Children' }}</span>
                    <div class="d-flex align-items-center gap-4">
                        <button type="button" class="bmod-stepper-btn" data-target="pax_children_{{ $itemKey }}" data-action="dec" data-min="{{ $childMin }}">−</button>
                        <input type="number" id="pax_children_{{ $itemKey }}" name="{{ $paxPrefix }}[children]"
                               value="{{ $oldChildren }}" min="{{ $childMin }}" max="{{ $childMax }}"
                               class="bmod-stepper-input" readonly>
                        <button type="button" class="bmod-stepper-btn" data-target="pax_children_{{ $itemKey }}" data-action="inc" data-max="{{ $childMax }}">+</button>
                    </div>
                </div>

                {{-- Rooms --}}
                <div class="d-flex align-items-center gap-8">
                    <span class="font-13 text-gray-600">{{ trans('cart.rooms') ?? 'Rooms' }}</span>
                    <div class="d-flex align-items-center gap-4">
                        <button type="button" class="bmod-stepper-btn" data-target="pax_rooms_{{ $itemKey }}" data-action="dec" data-min="{{ $roomMin }}">−</button>
                        <input type="number" id="pax_rooms_{{ $itemKey }}" name="{{ $paxPrefix }}[rooms]"
                               value="{{ $oldRooms }}" min="{{ $roomMin }}" max="{{ $roomMax }}"
                               class="bmod-stepper-input" readonly>
                        <button type="button" class="bmod-stepper-btn" data-target="pax_rooms_{{ $itemKey }}" data-action="inc" data-max="{{ $roomMax }}">+</button>
                    </div>
                </div>
            </div>
            @error("checkout_modules.{$itemKey}.persons_children")
                <div class="text-danger font-11 mt-4">{{ $message }}</div>
            @enderror
        </div>
    @endif

    {{-- EXTRA SERVICES --}}
    @if($extrasModule = $bottomModules->firstWhere('name', 'extra_services'))
        @php $extraOptions = $extrasModule->config['options'] ?? []; @endphp
        <div class="booking-cancellation-card" data-module-name="extra_services" data-price-type="additive">
            <div class="booking-info-title">
                {{ $extrasModule->translated_label ?? trans('cart.extra_services') ?? 'Extra Services' }}
                @if($extrasModule->is_required)<span class="text-danger">*</span>@endif
            </div>
            <div class="d-flex flex-wrap gap-8 mt-12">
                @foreach($extraOptions as $opt)
                    <label class="d-flex align-items-center gap-6 px-10 py-8 rounded-12"
                           style="border:1px solid #e2e8f0;cursor:pointer;font-size:13px;">
                        <input type="checkbox"
                               name="{{ $extrasPrefix }}[]"
                               value="{{ $opt['label'] }}"
                               data-price="{{ (float)($opt['price'] ?? 0) }}"
                               class="bmod-extra-chk"
                               style="accent-color:#22c55e;"
                               {{ in_array($opt['label'], old("checkout_modules.{$itemKey}.extra_services", [])) ? 'checked' : '' }}>
                        <span>{{ $opt['label'] }}</span>
                        <span class="font-weight-bold text-primary">+{{ handlePrice($opt['price'] ?? 0) }}</span>
                    </label>
                @endforeach
            </div>
            @error("checkout_modules.{$itemKey}.extra_services")
                <div class="text-danger font-11 mt-4">{{ $message }}</div>
            @enderror
        </div>
    @endif

    {{-- CANCELLATION POLICY --}}
    @if($policyModule = $bottomModules->firstWhere('name', 'cancellation_policy'))
        @php
            $policyText = $policyModule->config['policy_text']
                ?? trans('cart.free_cancellation_hint')
                ?? 'Free cancellation up to 24 hours before check-in.';
        @endphp
        <div class="booking-cancellation-card" data-module-name="cancellation_policy" data-price-type="none">
            <label for="cp_agree_{{ $itemKey }}" class="d-flex align-items-center gap-8" style="cursor:pointer;">
                <input type="checkbox"
                       id="cp_agree_{{ $itemKey }}"
                       name="{{ $policyPrefix }}"
                       value="1"
                       {{ old("checkout_modules.{$itemKey}.cancellation_policy") ? 'checked' : '' }}
                       {{ $policyModule->is_required ? 'required' : '' }}>
                <span class="font-13 font-weight-bold">
                    {{ $policyModule->translated_label ?? trans('cart.cancellation_policy') ?? 'Cancellation Policy' }}
                    @if($policyModule->is_required)<span class="text-danger">*</span>@endif
                </span>
            </label>
            <div class="booking-cancellation-text mt-6">{{ $policyText }}</div>
            @error("checkout_modules.{$itemKey}.cancellation_policy")
                <div class="text-danger font-11 mt-4">{{ $message }}</div>
            @enderror
        </div>
    @endif

    {{-- CHECKOUT MESSAGE --}}
    @if($msgModule = $bottomModules->firstWhere('name', 'checkout_message'))
        @php
            $maxLen = $msgModule->config['max_length'] ?? 500;
            $ph     = $msgModule->config['placeholder'] ?? (trans('cart.special_instructions') ?? 'Special instructions...');
            $oldMsg = old("checkout_modules.{$itemKey}.checkout_message", '');
        @endphp
        <div class="booking-cancellation-card" data-module-name="checkout_message" data-price-type="none">
            <div class="booking-info-title">
                {{ $msgModule->translated_label ?? trans('cart.message_for_checkout') ?? 'Message for Check-out' }}
                @if($msgModule->is_required)<span class="text-danger">*</span>@endif
            </div>
            <textarea name="{{ $msgPrefix }}"
                      class="form-control mt-3"
                      rows="3"
                      style="font-size:13px;border-radius:12px;min-height:88px;"
                      placeholder="{{ $ph }}"
                      maxlength="{{ $maxLen }}"
                      id="bmod_msg_{{ $itemKey }}"
                      {{ $msgModule->is_required ? 'required' : '' }}>{{ $oldMsg }}</textarea>
            <div style="font-size:11px;color:#94a3b8;margin-top:8px;">
                <span id="bmod_msgc_{{ $itemKey }}">{{ strlen($oldMsg) }}</span>/{{ $maxLen }} {{ trans('cart.characters') ?? 'characters' }}
            </div>
            @error("checkout_modules.{$itemKey}.checkout_message")
                <div class="text-danger font-11 mt-4">{{ $message }}</div>
            @enderror
        </div>
    @endif

    {{-- REVIEWER MESSAGE --}}
    @if($revModule = $bottomModules->firstWhere('name', 'reviewer_message'))
        @php
            $revMaxLen = $revModule->config['max_length'] ?? 500;
            $revPh     = $revModule->config['placeholder'] ?? (trans('cart.message_to_reviewer') ?? 'Message to instructor or organizer');
            $oldRev    = old("checkout_modules.{$itemKey}.reviewer_message", '');
        @endphp
        <div class="booking-cancellation-card" data-module-name="reviewer_message" data-price-type="none">
            <div class="booking-info-title">
                {{ $revModule->translated_label ?? trans('cart.message_to_reviewer') ?? 'Message to Reviewer' }}
                @if($revModule->is_required)<span class="text-danger">*</span>@endif
            </div>
            <textarea name="{{ $revPrefix }}"
                      class="form-control mt-3"
                      rows="3"
                      style="font-size:13px;border-radius:12px;min-height:88px;"
                      placeholder="{{ $revPh }}"
                      maxlength="{{ $revMaxLen }}"
                      id="bmod_rev_{{ $itemKey }}"
                      {{ $revModule->is_required ? 'required' : '' }}>{{ $oldRev }}</textarea>
            <div style="font-size:11px;color:#94a3b8;margin-top:8px;">
                <span id="bmod_revc_{{ $itemKey }}">{{ strlen($oldRev) }}</span>/{{ $revMaxLen }} {{ trans('cart.characters') ?? 'characters' }}
            </div>
            @error("checkout_modules.{$itemKey}.reviewer_message")
                <div class="text-danger font-11 mt-4">{{ $message }}</div>
            @enderror
        </div>
    @endif

    {{-- FALLBACK for any custom module added via DB (extensible system) --}}
    @foreach($bottomModules->whereNotIn('name', [
        'persons_children', 'extra_services', 'cancellation_policy',
        'checkout_message', 'reviewer_message'
    ]) as $customModule)
        <div class="booking-cancellation-card" data-module-name="{{ $customModule->name }}">
            <div class="booking-info-title">{{ $customModule->translated_label ?? $customModule->name }}</div>
            @includeIf('partials.checkout_modules._' . $customModule->name, [
                'module' => $customModule,
                'itemId' => $itemKey,
            ])
        </div>
    @endforeach

</div>{{-- .booking-info-shell --}}
@endif

{{-- ════════════════════════════════════════
     SCRIPTS  (injected once via @once)
════════════════════════════════════════ --}}
@once
    @push('scripts_bottom')
    <script>
    (function ($) {
        'use strict';

        /* ════════════════════════════════════════
           NIGHTS COUNTER (days module)
        ════════════════════════════════════════ */
        function bmodUpdateNights($shell) {
            var itemKey = $shell.data('item-key');
            var $cin    = $('#bmod_cin_'  + itemKey);
            var $cout   = $('#bmod_cout_' + itemKey);
            var $badge  = $('#bmod_nights_' + itemKey);

            if (!$cin.length || !$cout.length || !$badge.length) return;

            var inVal  = $cin.val();
            var outVal = $cout.val();

            if (!inVal || !outVal) {
                $badge.text('0 {{ trans("cart.nights") ?? "nights" }}');
                return;
            }

            var inDate  = new Date(inVal);
            var outDate = new Date(outVal);

            if (outDate <= inDate) {
                var nextDay = new Date(inDate);
                nextDay.setDate(nextDay.getDate() + 1);
                var yyyy = nextDay.getFullYear();
                var mm   = String(nextDay.getMonth() + 1).padStart(2, '0');
                var dd   = String(nextDay.getDate()).padStart(2, '0');
                $cout.val(yyyy + '-' + mm + '-' + dd);
                outDate = nextDay;
            }

            var nights = Math.max(0, Math.ceil((outDate - inDate) / 86400000));
            $badge.text(nights + ' {{ trans("cart.nights") ?? "nights" }}');
        }

        $(document).on('change', '.bmod-cin', function () {
            var $shell = $(this).closest('[data-item-key]');
            var val = $(this).val();
            if (val) {
                $(this).closest('.booking-info-card').find('.bmod-cout').attr('min', val);
            }
            bmodUpdateNights($shell);

            // Update display label
            if (val) {
                var d = new Date(val);
                var lbl = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                $shell.find('.bmod-cin-label').text(lbl);
            } else {
                $shell.find('.bmod-cin-label').text('{{ trans("cart.not_selected") ?? "Not selected" }}');
            }

            $(document).trigger('checkout:priceUpdate');
        });

        $(document).on('change', '.bmod-cout', function () {
            var $shell = $(this).closest('[data-item-key]');
            bmodUpdateNights($shell);
            $(document).trigger('checkout:priceUpdate');
        });

        // Initial run on page load (for old()/prefilled values)
        $(function () {
            $('[data-item-key]').each(function () {
                bmodUpdateNights($(this));
            });
        });

        /* ════════════════════════════════════════
           TIME SLOT SELECT (hours module)
        ════════════════════════════════════════ */
        $(document).on('change', '.bmod-time-select', function () {
            var $shell = $(this).closest('[data-item-key]');
            var val = $(this).val();
            $shell.find('.bmod-time-label').text(val ? $(this).find('option:selected').text() : '{{ trans("cart.not_selected") ?? "Not selected" }}');
            $(document).trigger('checkout:priceUpdate');
        });

        /* ════════════════════════════════════════
           STAFF SELECT
        ════════════════════════════════════════ */
        $(document).on('change', '[id^="bmod_staff_"]', function () {
            $(document).trigger('checkout:priceUpdate');
        });

        /* ════════════════════════════════════════
           STEPPER BUTTONS (persons_children)
        ════════════════════════════════════════ */
        $(document).on('click', '.bmod-stepper-btn', function () {
            var targetId = $(this).data('target');
            var action   = $(this).data('action');
            var min      = parseInt($(this).data('min') ?? 0, 10);
            var max      = parseInt($(this).data('max') ?? 99, 10);
            var $input   = $('#' + targetId);
            var current  = parseInt($input.val() ?? 0, 10);

            if (action === 'inc' && current < max) {
                $input.val(current + 1);
            } else if (action === 'dec' && current > min) {
                $input.val(current - 1);
            }

            $input.trigger('change');
            $(document).trigger('checkout:priceUpdate');
        });

        /* ════════════════════════════════════════
           EXTRA SERVICES CHECKBOXES
        ════════════════════════════════════════ */
        $(document).on('change', '.bmod-extra-chk', function () {
            $(document).trigger('checkout:priceUpdate');
        });

        /* ════════════════════════════════════════
           CHARACTER COUNTERS (checkout_message / reviewer_message)
        ════════════════════════════════════════ */
        $(document).on('input', '[id^="bmod_msg_"]', function () {
            var id  = $(this).attr('id');
            var key = id.replace('bmod_msg_', '');
            $('#bmod_msgc_' + key).text($(this).val().length);
        });

        $(document).on('input', '[id^="bmod_rev_"]', function () {
            var id  = $(this).attr('id');
            var key = id.replace('bmod_rev_', '');
            $('#bmod_revc_' + key).text($(this).val().length);
        });

        /* ════════════════════════════════════════
           CANCELLATION POLICY CHECKBOX
        ════════════════════════════════════════ */
        $(document).on('change', '[id^="cp_agree_"]', function () {
            $(document).trigger('checkout:priceUpdate');
        });
        /* ════════════════════════════════════════
   PRICE UPDATER — Extra Services + Persons → Summary
════════════════════════════════════════ */
$(document).on('checkout:priceUpdate', function () {

    var extrasTotal = 0;
    var personsTotal = 0;

    // Loop har active booking shell ke liye
    $('[data-item-key]').each(function () {
        var $shell = $(this);

        // --- Extra Services ---
        $shell.find('.bmod-extra-chk:checked').each(function () {
            extrasTotal += parseFloat($(this).data('price') || 0);
        });

        // --- Persons / Children (per_person module) ---
      // --- Persons / Children (per_person module) ---
var $paxCard = $shell.find('[data-module-name="persons_children"]');
if ($paxCard.length) {
    var perPerson = parseFloat($paxCard.attr('data-price-amount') || 0);
    
    // DEBUG: Console mein dekho kya aa raha hai
    console.log('Per person price:', perPerson);
    console.log('PAX card found:', $paxCard.length);
    
    if (perPerson > 0) {
        var itemKey  = $shell.data('item-key');
        var adults   = parseInt($('#pax_adults_'   + itemKey).val() || 0, 10);
        var children = parseInt($('#pax_children_' + itemKey).val() || 0, 10);
        var paxAmt   = (adults + children) * perPerson;
        
        console.log('Adults:', adults, 'Children:', children, 'Amount:', paxAmt);
        
        personsTotal += paxAmt;
    }
}

        // --- Days module (per_day) ---
        var $daysCard = $shell.find('[data-module-name="days"]');
        if ($daysCard.length) {
            var perDay  = parseFloat($daysCard.data('price-amount') || 0);
            var itemKey = $shell.data('item-key');
            var nights  = parseInt($('#bmod_nights_' + itemKey).text() || '0', 10);
            extrasTotal += nights * perDay;
        }

        // --- Hours module (per_hour) ---
        var $hoursCard = $shell.find('[data-module-name="hours"]');
        if ($hoursCard.length) {
            var perHour = parseFloat($hoursCard.data('price-amount') || 0);
            var itemKeyH = $shell.data('item-key');
            var selectedHour = $('#bmod_time_' + itemKeyH).val();
            if (selectedHour) {
                extrasTotal += perHour; // 1 slot = 1 hour
            }
        }
    });

    var moduleAdditions = extrasTotal + personsTotal;

    // Summary mein update karo
    var $extrasEl = $('.js-cart-extras');
    $extrasEl.text(handlePriceJS(moduleAdditions));
    $extrasEl.data('amount', moduleAdditions);

    // Total recalculate karo
    recalcCartTotal();
});

function recalcCartTotal() {
    var subtotal  = parseFloat($('.js-cart-subtotal').data('amount') || 0);
    var discount  = parseFloat($('.js-cart-discount').data('amount') || 0);
    var extras    = parseFloat($('.js-cart-extras').data('amount')   || 0);
    var tax       = parseFloat($('.js-cart-tax').data('amount')      || 0);
    var delivery  = parseFloat($('.js-cart-delivery_fee').data('amount') || 0);

    var total = subtotal - discount + extras + tax + delivery;

    $('.js-cart-total').text(handlePriceJS(total));
    $('.js-cart-total').data('amount', total);
}

// Currency format helper (Laravel handlePrice() ka JS version)
// Agar tumhara currency symbol alag ha to yahan change karo
function handlePriceJS(amount) {
    // Try karo pehle se rendered price se symbol detect karna
    var existingPrice = $('.js-cart-subtotal').text().trim();
    var symbol = existingPrice.replace(/[\d,. ]+/g, '').trim() || '$';

    return symbol + parseFloat(amount).toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

    })(jQuery);
    </script>
    @endpush
@endonce
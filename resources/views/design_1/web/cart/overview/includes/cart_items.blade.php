@foreach($carts as $cart)
    @php
        $cartItemInfo = app(\App\Mixins\Cart\CartItemInfo::class);
        $itemInfo     = $cartItemInfo->getItemInfo($cart);
        $itemKey      = $cart->id;
        $cartModules  = $checkoutModulesByCart[$cart->id] ?? collect();

        // Sirf is_active = true wale modules
        $activeModules = $cartModules->filter(fn($m) => $m->is_active);
    @endphp

    {{-- ════ BOOKING ITEM ════ --}}
    @if(!empty($cart->booking_id))
        @php
            $booking     = $cart->booking;
            $title       = $booking->title ?? ($itemInfo['title'] ?? '');
            $city        = $booking->city ?? ($itemInfo['city'] ?? '');
            $country     = $booking->country ?? ($itemInfo['country'] ?? '');
            $locationStr = collect(array_filter([$city, $country]))->implode(', ');
            $thumbUrl    = $itemInfo['imgPath'] ?? $booking->thumbnail_url ?? '';

            // Price
            $rawPrice      = (float) ($booking->price ?? 0);
            $discountPrice = !empty($booking->discount_price) ? (float) $booking->discount_price : $rawPrice;
            $hasDiscount   = $discountPrice < $rawPrice;
            $displayPrice  = $discountPrice;

            // Slot data from cart meta
            $slotDate  = $cart->meta['slot_date']  ?? null;
            $slotStart = $cart->meta['slot_start'] ?? null;
            $slotEnd   = $cart->meta['slot_end']   ?? null;
            $slotLabel = null;
            if ($slotDate) {
                $slotLabel = \Carbon\Carbon::parse($slotDate)->format('d M Y');
                if ($slotStart && $slotEnd) {
                    $slotLabel .= ' · ' . $slotStart . ' - ' . $slotEnd;
                } elseif ($slotStart) {
                    $slotLabel .= ' · ' . $slotStart;
                }
            }

            // Old values
            $oldCheckIn  = old("checkout_modules.{$itemKey}.days.check_in",  $slotDate ?? '');
            $oldCheckOut = old("checkout_modules.{$itemKey}.days.check_out", '');
            $authUserName = auth()->check() ? auth()->user()->full_name : '';

            // Active module references
            $daysModule   = $activeModules->firstWhere('name', 'days');
            $hoursModule  = $activeModules->firstWhere('name', 'hours');
            $staffModule  = $activeModules->firstWhere('name', 'staff_member');

            // Top 3 grid modules
            $gridModules = $activeModules->filter(fn($m) => in_array($m->name, ['days', 'hours', 'staff_member']));

            // Bottom section modules
            $bottomModules = $activeModules->filter(fn($m) => in_array($m->name, [
                'persons_children', 'extra_services', 'cancellation_policy',
                'checkout_message', 'reviewer_message'
            ]));

            // Prefix helpers
            $datePrefix   = "checkout_modules[{$itemKey}][days]";
            $timePrefix   = "checkout_modules[{$itemKey}][hours]";
            $staffPrefix  = "checkout_modules[{$itemKey}][staff_member]";
            $paxPrefix    = "checkout_modules[{$itemKey}][persons_children]";
            $policyPrefix = "checkout_modules[{$itemKey}][cancellation_policy]";
            $extrasPrefix = "checkout_modules[{$itemKey}][extra_services]";
            $msgPrefix    = "checkout_modules[{$itemKey}][checkout_message]";
            $revPrefix    = "checkout_modules[{$itemKey}][reviewer_message]";

            // Display labels
            $selectedTime  = old("checkout_modules.{$itemKey}.hours", $slotStart ?? '');
            $selectedStaff = old("checkout_modules.{$itemKey}.staff_member", $authUserName);
            $checkInLabel  = $oldCheckIn
                ? \Carbon\Carbon::parse($oldCheckIn)->format('d M Y')
                : ($slotDate ? \Carbon\Carbon::parse($slotDate)->format('d M Y') : 'Not selected');
            $timeLabel     = $selectedTime
                ?: ($slotStart ? $slotStart . ($slotEnd ? ' - ' . $slotEnd : '') : 'Not selected');
            $staffLabel    = $selectedStaff ?: ($authUserName ?: 'Guest');
        @endphp

        <div class="cart-booking-row" id="cart-item-{{ $itemKey }}">

            {{-- Thumb --}}
            <div class="cart-booking-thumb">
                @if($thumbUrl)
                    <img src="{{ $thumbUrl }}" alt="{{ $title }}">
                @else
                    <x-iconsax-lin-calendar-2 class="icons text-gray-400" width="28px" height="28px"/>
                @endif
            </div>

            {{-- Content --}}
            <div class="flex-1 min-w-0">

                {{-- Title row --}}
                <div class="d-flex align-items-start justify-content-between gap-8">
                    <div class="min-w-0">
                        <div class="font-14 font-weight-bold text-dark">
                            {{ $title }}
                            @if($locationStr)
                                <span class="font-12 font-weight-400 text-gray-400"> at {{ $locationStr }}</span>
                            @endif
                        </div>

                        {{-- Creator + category --}}
                        <div class="d-flex align-items-center flex-wrap gap-8 mt-6">
                            @if(!empty($booking->creator))
                                <a href="{{ $booking->creator->getProfileUrl() }}"
                                   class="d-flex align-items-center text-gray-500 text-decoration-none"
                                   onclick="event.stopPropagation()">
                                    <img src="{{ $booking->creator->getAvatar(20) }}"
                                         class="rounded-circle img-cover mr-4"
                                         style="width:18px;height:18px;"
                                         alt="{{ $booking->creator->full_name }}">
                                    <span class="font-12">{{ $booking->creator->full_name }}</span>
                                </a>
                            @endif
                            @if(!empty($booking->category))
                                <span class="font-12 text-gray-400">{{ $booking->category->title }}</span>
                            @endif
                            <span class="badge badge-light font-11 text-gray-500">
                                <x-iconsax-lin-calendar-2 class="icons" width="11px" height="11px"/> Booking
                            </span>
                        </div>
                    </div>

                    {{-- Price + Remove --}}
                    <div class="d-flex flex-column align-items-end flex-shrink-0 gap-4">
                        <button type="button" class="cart-remove-btn"
                                data-cart-id="{{ $itemKey }}"
                                title="{{ trans('public.remove') ?? 'Remove' }}"
                                style="background:transparent;border:1px solid #e2e8f0;padding:0;cursor:pointer;">
                            <x-iconsax-lin-close-circle class="icons text-gray-400" width="18px" height="18px"/>
                        </button>
                        <div class="text-right mt-4">
                            <div class="font-14 font-weight-bold text-primary">{{ handlePrice($displayPrice) }}</div>
                            @if($hasDiscount)
                                <div class="font-11 text-gray-400 text-decoration-line-through">{{ handlePrice($rawPrice) }}</div>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- BOOKING MODULES --}}
                @if($activeModules->isNotEmpty())
                    <div class="booking-info-shell" data-item-key="{{ $itemKey }}">

                        {{-- TOP ROW: 3-column grid (days, hours, staff) --}}
                        @if($gridModules->isNotEmpty())
                            <div class="booking-info-grid">

                                {{-- DAYS MODULE --}}
                                @if($daysModule)
                                    <div class="booking-info-card">
                                        <div class="booking-info-title">
                                            {{ $daysModule->translated_label ?? trans('update.check_in_date') ?? 'Check-in Date' }}
                                        </div>
                                        <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                            <div class="booking-info-icon">
                                                <x-iconsax-lin-calendar-2 class="icons" width="16px" height="16px"/>
                                            </div>
                                            <input type="date"
                                                   name="{{ $datePrefix }}[check_in]"
                                                   class="form-control form-control-sm bmod-date-input bmod-cin"
                                                   value="{{ $oldCheckIn }}"
                                                   min="{{ now()->format('Y-m-d') }}"
                                                   style="border-radius:12px;" />
                                            <div class="booking-info-label bmod-cin-label">{{ $checkInLabel }}</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- HOURS MODULE --}}
                                @if($hoursModule)
                                    <div class="booking-info-card">
                                        <div class="booking-info-title">
                                            {{ $hoursModule->translated_label ?? trans('update.check_in_time') ?? 'Check-in Time' }}
                                        </div>
                                        <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                            <div class="booking-info-icon">
                                                <x-iconsax-lin-clock class="icons" width="16px" height="16px"/>
                                            </div>
                                            <select name="{{ $timePrefix }}"
                                                    class="form-control form-control-sm bmod-time-select"
                                                    style="border-radius:12px;">
                                                <option value="">— {{ trans('checkout.select') ?? 'Select' }} —</option>
                                                @foreach($hoursModule->config['slots'] ?? [] as $slot)
                                                    @php
                                                        try {
                                                            $sc  = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                                            $ec  = $sc->copy()->addHour();
                                                            $lbl = $sc->format('h:i A') . ' - ' . $ec->format('h:i A');
                                                        } catch (\Throwable $e) {
                                                            $lbl = $slot;
                                                        }
                                                    @endphp
                                                    <option value="{{ $slot }}"
                                                        {{ $selectedTime == $slot ? 'selected' : '' }}>
                                                        {{ $lbl }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <div class="booking-info-label bmod-time-label">{{ $timeLabel }}</div>
                                        </div>
                                    </div>
                                @endif

                                {{-- STAFF MODULE --}}
                                @if($staffModule)
                                    <div class="booking-info-card">
                                        <div class="booking-info-title">
                                            {{ $staffModule->translated_label ?? 'Assigned Staff' }}
                                        </div>
                                        <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:8px;">
                                            <div class="booking-info-icon">
                                                <x-iconsax-lin-profile class="icons" width="16px" height="16px"/>
                                            </div>
                                            @if(!empty($staffModule->config['staff']))
                                                <select name="{{ $staffPrefix }}"
                                                        class="form-control form-control-sm"
                                                        style="border-radius:12px;">
                                                    <option value="">— {{ trans('checkout.select_staff') ?? 'Select staff' }} —</option>
                                                    @foreach($staffModule->config['staff'] as $staff)
                                                        @php $value = $staff['id'] ?? $staff['name']; @endphp
                                                        <option value="{{ $value }}"
                                                            {{ $selectedStaff == $value ? 'selected' : '' }}>
                                                            {{ $staff['name'] }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            @else
                                                <div class="booking-info-value">{{ $authUserName ?: 'Guest' }}</div>
                                                <input type="hidden" name="{{ $staffPrefix }}" value="{{ $authUserName }}">
                                            @endif
                                            <div class="booking-info-label">{{ $staffLabel }}</div>
                                        </div>
                                    </div>
                                @endif

                            </div>{{-- .booking-info-grid --}}
                        @endif

                        {{-- PERSONS + CHILDREN --}}
                        @if($paxModule = $activeModules->firstWhere('name', 'persons_children'))
                            @php
                                $oldAdults   = old("checkout_modules.{$itemKey}.persons_children.adults",   1);
                                $oldChildren = old("checkout_modules.{$itemKey}.persons_children.children", 0);
                                $oldRooms    = old("checkout_modules.{$itemKey}.persons_children.rooms",    1);
                                $paxConfig   = $paxModule->config ?? [];
                                $adultMin    = $paxConfig['adults']['min']   ?? 1;
                                $adultMax    = $paxConfig['adults']['max']   ?? 20;
                                $childMin    = $paxConfig['children']['min'] ?? 0;
                                $childMax    = $paxConfig['children']['max'] ?? 10;
                                $roomMin     = $paxConfig['rooms']['min']    ?? 1;
                                $roomMax     = $paxConfig['rooms']['max']    ?? 10;
                            @endphp
                            <div class="booking-cancellation-card">
                                <div class="booking-info-title mb-12">
                                    {{ $paxModule->translated_label ?? 'Guests' }}
                                </div>
                                <div class="d-flex flex-wrap gap-16">

                                    {{-- Adults --}}
                                    <div class="d-flex align-items-center gap-8">
                                        <span class="font-13 text-gray-600">Adults</span>
                                        <div class="d-flex align-items-center gap-4">
                                            <button type="button" class="bmod-stepper-btn"
                                                    data-target="pax_adults_{{ $itemKey }}"
                                                    data-action="dec"
                                                    data-min="{{ $adultMin }}">−</button>
                                            <input type="number"
                                                   id="pax_adults_{{ $itemKey }}"
                                                   name="{{ $paxPrefix }}[adults]"
                                                   value="{{ $oldAdults }}"
                                                   min="{{ $adultMin }}"
                                                   max="{{ $adultMax }}"
                                                   class="bmod-stepper-input"
                                                   readonly>
                                            <button type="button" class="bmod-stepper-btn"
                                                    data-target="pax_adults_{{ $itemKey }}"
                                                    data-action="inc"
                                                    data-max="{{ $adultMax }}">+</button>
                                        </div>
                                    </div>

                                    {{-- Children --}}
                                    <div class="d-flex align-items-center gap-8">
                                        <span class="font-13 text-gray-600">Children</span>
                                        <div class="d-flex align-items-center gap-4">
                                            <button type="button" class="bmod-stepper-btn"
                                                    data-target="pax_children_{{ $itemKey }}"
                                                    data-action="dec"
                                                    data-min="{{ $childMin }}">−</button>
                                            <input type="number"
                                                   id="pax_children_{{ $itemKey }}"
                                                   name="{{ $paxPrefix }}[children]"
                                                   value="{{ $oldChildren }}"
                                                   min="{{ $childMin }}"
                                                   max="{{ $childMax }}"
                                                   class="bmod-stepper-input"
                                                   readonly>
                                            <button type="button" class="bmod-stepper-btn"
                                                    data-target="pax_children_{{ $itemKey }}"
                                                    data-action="inc"
                                                    data-max="{{ $childMax }}">+</button>
                                        </div>
                                    </div>

                                    {{-- Rooms --}}
                                    <div class="d-flex align-items-center gap-8">
                                        <span class="font-13 text-gray-600">Rooms</span>
                                        <div class="d-flex align-items-center gap-4">
                                            <button type="button" class="bmod-stepper-btn"
                                                    data-target="pax_rooms_{{ $itemKey }}"
                                                    data-action="dec"
                                                    data-min="{{ $roomMin }}">−</button>
                                            <input type="number"
                                                   id="pax_rooms_{{ $itemKey }}"
                                                   name="{{ $paxPrefix }}[rooms]"
                                                   value="{{ $oldRooms }}"
                                                   min="{{ $roomMin }}"
                                                   max="{{ $roomMax }}"
                                                   class="bmod-stepper-input"
                                                   readonly>
                                            <button type="button" class="bmod-stepper-btn"
                                                    data-target="pax_rooms_{{ $itemKey }}"
                                                    data-action="inc"
                                                    data-max="{{ $roomMax }}">+</button>
                                        </div>
                                    </div>

                                </div>
                            </div>
                        @endif

                        {{-- EXTRA SERVICES --}}
                        @if($extrasModule = $activeModules->firstWhere('name', 'extra_services'))
                            @php $extraOptions = $extrasModule->config['options'] ?? []; @endphp
                            <div class="booking-cancellation-card">
                                <div class="booking-info-title">
                                    {{ $extrasModule->translated_label ?? 'Extra Services' }}
                                </div>
                                <div class="d-flex flex-wrap gap-8 mt-3">
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
                            </div>
                        @endif

                        {{-- CANCELLATION POLICY --}}
                        @if($policyModule = $activeModules->firstWhere('name', 'cancellation_policy'))
                            @php
                                $policyText = $policyModule->config['policy_text']
                                    ?? trans('checkout.free_cancellation_hint')
                                    ?? 'Free cancellation up to 24 hours before check-in.';
                            @endphp
                            <div class="booking-cancellation-card">
                                <label for="cp_agree_{{ $itemKey }}"
                                       class="d-flex align-items-center gap-8"
                                       style="cursor:pointer;">
                                    <input type="checkbox"
                                           id="cp_agree_{{ $itemKey }}"
                                           name="{{ $policyPrefix }}"
                                           value="1"
                                           {{ old("checkout_modules.{$itemKey}.cancellation_policy") ? 'checked' : '' }}>
                                    <span class="font-13 font-weight-bold">
                                        {{ $policyModule->translated_label ?? (trans('checkout.cancellation_policy') ?? 'Cancellation Policy') }}
                                    </span>
                                </label>
                                <div class="booking-cancellation-text mt-6">{{ $policyText }}</div>
                            </div>
                        @endif

                        {{-- CHECKOUT MESSAGE --}}
                        @if($msgModule = $activeModules->firstWhere('name', 'checkout_message'))
                            @php
                                $maxLen = $msgModule->config['max_length'] ?? 500;
                                $ph     = $msgModule->config['placeholder'] ?? (trans('checkout.special_instructions') ?? 'Special instructions...');
                            @endphp
                            <div class="booking-cancellation-card">
                                <div class="booking-info-title">
                                    {{ $msgModule->translated_label ?? 'Message for Check-out' }}
                                </div>
                                <textarea name="{{ $msgPrefix }}"
                                          class="form-control mt-3"
                                          rows="3"
                                          style="font-size:13px;border-radius:12px;min-height:88px;"
                                          placeholder="{{ $ph }}"
                                          maxlength="{{ $maxLen }}"
                                          id="bmod_msg_{{ $itemKey }}"
                                          {{ $msgModule->is_active ? '' : 'disabled' }}>{{ old("checkout_modules.{$itemKey}.checkout_message") }}</textarea>
                                <div style="font-size:11px;color:#94a3b8;margin-top:8px;">
                                    <span id="bmod_msgc_{{ $itemKey }}">{{ strlen(old("checkout_modules.{$itemKey}.checkout_message", '')) }}</span>/{{ $maxLen }}
                                </div>
                            </div>
                        @endif

                        {{-- REVIEWER MESSAGE --}}
                        @if($revModule = $activeModules->firstWhere('name', 'reviewer_message'))
                            @php
                                $revMaxLen = $revModule->config['max_length'] ?? 500;
                                $revPh     = $revModule->config['placeholder'] ?? 'Message to instructor or organizer';
                            @endphp
                            <div class="booking-cancellation-card">
                                <div class="booking-info-title">
                                    {{ $revModule->translated_label ?? 'Message to Reviewer' }}
                                </div>
                                <textarea name="{{ $revPrefix }}"
                                          class="form-control mt-3"
                                          rows="3"
                                          style="font-size:13px;border-radius:12px;min-height:88px;"
                                          placeholder="{{ $revPh }}"
                                          maxlength="{{ $revMaxLen }}"
                                          id="bmod_rev_{{ $itemKey }}">{{ old("checkout_modules.{$itemKey}.reviewer_message") }}</textarea>
                                <div style="font-size:11px;color:#94a3b8;margin-top:8px;">
                                    <span id="bmod_revc_{{ $itemKey }}">{{ strlen(old("checkout_modules.{$itemKey}.reviewer_message", '')) }}</span>/{{ $revMaxLen }}
                                </div>
                            </div>
                        @endif

                    </div>{{-- .booking-info-shell --}}
                @endif

            </div>{{-- .flex-1 --}}
        </div>{{-- .cart-booking-row --}}

    {{-- ════ ALL OTHER ITEMS (non-booking) ════ --}}
    @else
        <div class="cart-item-card" id="cart-item-{{ $itemKey }}">
            <div class="flex-shrink-0">
                <img src="{{ $itemInfo['imgPath'] ?? '' }}"
                     alt="{{ $itemInfo['title'] ?? '' }}"
                     class="rounded-12 img-cover"
                     style="width:68px;height:68px;object-fit:cover;">
            </div>

            <div class="flex-1 min-w-0">
                @if(!empty($itemInfo['itemUrl']))
                    <a href="{{ $itemInfo['itemUrl'] }}" class="font-14 font-weight-bold text-dark d-block">
                        {{ $itemInfo['title'] ?? '' }}
                    </a>
                @else
                    <div class="font-14 font-weight-bold text-dark">{{ $itemInfo['title'] ?? '' }}</div>
                @endif

                @if(!empty($itemInfo['teacherName']))
                    <div class="font-12 text-gray-500 mt-4">{{ $itemInfo['teacherName'] }}</div>
                @endif

                @if(!empty($itemInfo['extraHint']))
                    <span class="badge badge-light font-11 mt-4">{{ $itemInfo['extraHint'] }}</span>
                @endif
            </div>

            <div class="flex-shrink-0 text-right">
                @if(!empty($itemInfo['discountPrice']))
                    <div class="font-14 font-weight-bold text-primary">{{ handlePrice($itemInfo['discountPrice']) }}</div>
                    <div class="font-12 text-gray-400 text-line-through mt-2">{{ handlePrice($itemInfo['price']) }}</div>
                @else
                    <div class="font-14 font-weight-bold text-primary">{{ handlePrice($itemInfo['price'] ?? 0) }}</div>
                @endif
            </div>

            <button type="button" class="cart-remove-btn"
                    data-cart-id="{{ $itemKey }}"
                    title="{{ trans('public.remove') ?? 'Remove' }}">
                <x-iconsax-lin-close-circle class="icons text-gray-400" width="16px" height="16px"/>
            </button>
        </div>
    @endif

@endforeach

@push('scripts_bottom')
<script>
(function($){

    /* ── Char counter for message fields ── */
    $(document).on('input', '[id^="bmod_msg_"], [id^="bmod_rev_"]', function(){
        var id     = $(this).attr('id');
        var key    = id.replace('bmod_msg_','').replace('bmod_rev_','');
        var prefix = id.startsWith('bmod_rev_') ? 'bmod_revc_' : 'bmod_msgc_';
        $('#' + prefix + key).text($(this).val().length);
    });

    /* ── Stepper buttons (persons/children/rooms) ── */
    $(document).on('click', '.bmod-stepper-btn', function(){
        var targetId = $(this).data('target');
        var action   = $(this).data('action');
        var min      = parseInt($(this).data('min') ?? 0);
        var max      = parseInt($(this).data('max') ?? 99);
        var $input   = $('#' + targetId);
        var current  = parseInt($input.val() ?? 0);
        if (action === 'inc' && current < max) { $input.val(current + 1); }
        else if (action === 'dec' && current > min) { $input.val(current - 1); }
    });

    /* ── Date input change → update display label ── */
    $(document).on('change', '.bmod-cin', function(){
        var $shell = $(this).closest('[data-item-key]');
        var val    = $(this).val();
        if (val) {
            var d   = new Date(val);
            var lbl = d.toLocaleDateString('en-GB', { day:'2-digit', month:'short', year:'numeric' });
            $shell.find('.bmod-cin-label').text(lbl);
        } else {
            $shell.find('.bmod-cin-label').text('Not selected');
        }
    });

    /* ── Time select change → update display label ── */
    $(document).on('change', '.bmod-time-select', function(){
        var $shell = $(this).closest('[data-item-key]');
        var val    = $(this).val();
        $shell.find('.bmod-time-label').text(val ? $(this).find('option:selected').text() : 'Not selected');
    });

})(jQuery);
</script>
@endpush
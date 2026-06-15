

    @foreach($carts as $cart)
        @php
            $cartItemInfo = app(\App\Mixins\Cart\CartItemInfo::class);
            $itemInfo     = $cartItemInfo->getItemInfo($cart);
            $itemKey      = $cart->id;
            $cartModules  = $checkoutModulesByCart[$cart->id] ?? collect();
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

                // ── Price: same formula as list page & CartController::handleOrderPrices ──
                $rawPrice      = (float) ($booking->price ?? 0);
                $discountPrice = !empty($booking->discount_price) ? (float) $booking->discount_price : $rawPrice;
                $hasDiscount   = $discountPrice < $rawPrice;
                $displayPrice  = $discountPrice;   // what user actually pays

                // ── Slot data saved in cart meta (set on booking page before add-to-cart) ──
                // Stored as: cart->meta['slot_date'], cart->meta['slot_start'], cart->meta['slot_end']
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

                // ── Check-in / check-out from old() or module config ──
                $oldCheckIn  = old("checkout_modules.{$itemKey}.days.check_in",  $slotDate ?? '');
                $oldCheckOut = old("checkout_modules.{$itemKey}.days.check_out", '');

                // ── Staff: show logged-in user name ──
                $authUserName = auth()->check() ? auth()->user()->full_name : '';

                // ── Find days module & hours module if enabled ──
                $daysModule  = $cartModules->firstWhere('name', 'days');
                $hoursModule = $cartModules->firstWhere('name', 'hours');
                $staffModule = $cartModules->firstWhere('name', 'staff_member');
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

                    {{-- ── Booking Modules — plain card, no green border ── --}}
                    @if($cartModules->isNotEmpty())
                    <div class="bmod-wrap" data-item-key="{{ $itemKey }}">
                        <div class="bmod-row">

                            {{-- Date & Time (Days module) --}}
                            @if($daysModule)
                                @php
                                    $prefix     = "checkout_modules[{$itemKey}][days]";
                                    $perDay     = $daysModule->price_rule['amount'] ?? 0;
                                    $isRequired = $daysModule->is_required;
                                @endphp
                                <div class="bmod-col">
                                    <div class="bmod-label">
                                        <x-iconsax-lin-calendar-2 class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                                        {{ $daysModule->translated_label ?? 'Date & Time' }}
                                        @if($isRequired)<span class="text-danger">*</span>@endif
                                        @if($perDay)<span class="ml-auto" style="font-size:10px;color:#2563eb;font-weight:700;">{{ handlePrice($perDay) }}/day</span>@endif
                                    </div>
                                    <div class="d-flex align-items-center gap-6 flex-wrap">
                                        <input type="date"
                                            name="{{ $prefix }}[check_in]"
                                            class="bmod-date-input bmod-cin"
                                            value="{{ $oldCheckIn }}"
                                            min="{{ now()->format('Y-m-d') }}"
                                            {{ $isRequired ? 'required' : '' }}>
                                        <span class="text-gray-400 font-12">—</span>
                                        <input type="date"
                                            name="{{ $prefix }}[check_out]"
                                            class="bmod-date-input bmod-cout"
                                            value="{{ $oldCheckOut }}"
                                            min="{{ now()->format('Y-m-d') }}"
                                            {{ $isRequired ? 'required' : '' }}>
                                    </div>
                                    <div class="bmod-nights-badge mt-5">0 nights</div>
                                    @error("checkout_modules.{$itemKey}.days.check_in")
                                        <div class="text-danger" style="font-size:10px;margin-top:2px;">{{ $message }}</div>
                                    @enderror
                                </div>
                            @elseif($slotLabel)
                                {{-- No days module but slot data exists — show read-only --}}
                                <div class="bmod-col">
                                    <div class="bmod-label">
                                        <x-iconsax-lin-calendar-2 class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                                        Date & Time
                                    </div>
                                    <div class="bmod-value">{{ $slotLabel }}</div>
                                </div>
                            @endif

                            {{-- Hours module --}}
                            @if($hoursModule)
                                @php
                                    $slots     = $hoursModule->config['slots'] ?? [];
                                    $prefix    = "checkout_modules[{$itemKey}][hours]";
                                    $perHour   = $hoursModule->price_rule['amount'] ?? 0;
                                    $isReq     = $hoursModule->is_required;
                                    $oldSlot   = old("checkout_modules.{$itemKey}.hours", $slotStart ?? '');

                                    // Pre-select slot from slot data if matches
                                    function fmtSlot12Hr($t) {
                                        try { return \Carbon\Carbon::createFromFormat('H:i', $t)->format('h:i A'); }
                                        catch (\Throwable $e) { return $t; }
                                    }
                                @endphp
                                <div class="bmod-col">
                                    <div class="bmod-label">
                                        <x-iconsax-lin-clock class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                                        {{ $hoursModule->translated_label ?? 'Time Slot' }}
                                        @if($isReq)<span class="text-danger">*</span>@endif
                                        @if($perHour)<span class="ml-auto" style="font-size:10px;color:#2563eb;font-weight:700;">{{ handlePrice($perHour) }}/hr</span>@endif
                                    </div>
                                    <select name="{{ $prefix }}"
                                            class="form-control form-control-sm mt-4"
                                            style="font-size:12px;padding:3px 6px;border-radius:6px;"
                                            {{ $isReq ? 'required' : '' }}>
                                        <option value="">— {{ trans('checkout.select') ?? 'Select' }} —</option>
                                        @foreach($slots as $slot)
                                            @php
                                                try {
                                                    $sc = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                                    $ec = $sc->copy()->addHour();
                                                    $lbl = $sc->format('h:i A') . ' - ' . $ec->format('h:i A');
                                                } catch (\Throwable $e) { $lbl = $slot; }
                                            @endphp
                                            <option value="{{ $slot }}" {{ $oldSlot == $slot ? 'selected' : '' }}>{{ $lbl }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            @endif

                            {{-- Staff Member column: show logged-in user name --}}
                            @if($staffModule)
                                @php
                                    $staffPrefix  = "checkout_modules[{$itemKey}][staff_member]";
                                    $staffOptions = $staffModule->config['staff'] ?? [];
                                    $isReqStaff   = $staffModule->is_required;
                                @endphp
                                <div class="bmod-col">
                                    <div class="bmod-label">
                                        <x-iconsax-lin-profile-2user class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                                        {{ $staffModule->translated_label ?? 'Staff Member' }}
                                    </div>
                                    @if(!empty($staffOptions))
                                        <select name="{{ $staffPrefix }}"
                                                class="form-control form-control-sm mt-4"
                                                style="font-size:12px;padding:3px 6px;border-radius:6px;"
                                                {{ $isReqStaff ? 'required' : '' }}>
                                            <option value="">— {{ trans('checkout.select_staff') ?? 'Select staff' }} —</option>
                                            @foreach($staffOptions as $staff)
                                                <option value="{{ $staff['id'] ?? $staff['name'] }}"
                                                    {{ old("checkout_modules.{$itemKey}.staff_member") == ($staff['id'] ?? $staff['name']) ? 'selected' : '' }}>
                                                    {{ $staff['name'] }}
                                                </option>
                                            @endforeach
                                        </select>
                                    @else
                                        {{-- No staff options: show logged-in user name as readonly --}}
                                        <div class="bmod-value mt-4">
                                            <x-iconsax-lin-profile class="icons text-primary" width="13px" height="13px"/>
                                            <span>{{ $authUserName }}</span>
                                        </div>
                                        <input type="hidden" name="{{ $staffPrefix }}" value="{{ $authUserName }}">
                                    @endif
                                </div>
                            @elseif($authUserName)
                                {{-- Always show a staff/guest column with user name even if no staff module --}}
                                <div class="bmod-col">
                                    <div class="bmod-label">
                                        <x-iconsax-lin-profile-2user class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                                        Guest
                                    </div>
                                    <div class="bmod-value mt-4">
                                        <x-iconsax-lin-profile class="icons text-primary" width="13px" height="13px"/>
                                        <span class="font-13 font-weight-600">{{ $authUserName }}</span>
                                    </div>
                                </div>
                            @endif

                            {{-- Cancellation Policy --}}
                            @php $policyModule = $cartModules->firstWhere('name', 'cancellation_policy'); @endphp
                            @if($policyModule)
                                @php
                                    $policyText   = $policyModule->config['policy_text'] ?? trans('checkout.free_cancellation_hint') ?? 'Free cancellation up to 24 hours before check-in.';
                                    $policyPrefix = "checkout_modules[{$itemKey}][cancellation_policy]";
                                @endphp
                                <div class="bmod-col" style="flex-basis:100%;">
                                    <div class="bmod-label">
                                        <x-iconsax-lin-shield-tick class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                                        {{ $policyModule->translated_label ?? 'Cancellation Policy' }}
                                        @if($policyModule->is_required)<span class="text-danger">*</span>@endif
                                    </div>
                                    <div class="d-flex align-items-start gap-6 p-8 rounded-8 mt-4"
                                        style="background:rgba(30,84,255,.05);border:1px solid rgba(30,84,255,.14);">
                                        <x-iconsax-lin-info-circle class="icons flex-shrink-0" width="12px" height="12px" style="color:#2563eb;margin-top:1px"/>
                                        <p class="font-11 text-gray-500 mb-0">{{ $policyText }}</p>
                                    </div>
                                    <div class="d-flex align-items-center gap-6 mt-8">
                                        <input type="checkbox" id="cp_agree_{{ $itemKey }}"
                                            name="{{ $policyPrefix }}" value="1"
                                            style="width:13px;height:13px;accent-color:#22c55e;"
                                            {{ old("checkout_modules.{$itemKey}.cancellation_policy") ? 'checked' : '' }}
                                            {{ $policyModule->is_required ? 'required' : '' }}>
                                        <label for="cp_agree_{{ $itemKey }}" class="font-11 text-gray-600 mb-0" style="cursor:pointer;">
                                            {{ trans('cart.i_agree_cancellation_policy') ?? 'I agree to the cancellation policy' }}
                                        </label>
                                    </div>
                                </div>
                            @endif

                            {{-- Extra Services --}}
                            @php $extrasModule = $cartModules->firstWhere('name', 'extra_services'); @endphp
                            @if($extrasModule)
                                @php
                                    $extraOptions = $extrasModule->config['options'] ?? [];
                                    $extrasPrefix = "checkout_modules[{$itemKey}][extra_services]";
                                @endphp
                                <div class="bmod-col" style="flex-basis:100%;">
                                    <div class="bmod-label">
                                        <x-iconsax-lin-add-square class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                                        {{ $extrasModule->translated_label ?? 'Extra Services' }}
                                        @if($extrasModule->is_required)<span class="text-danger">*</span>@endif
                                    </div>
                                    <div class="d-flex flex-wrap gap-8 mt-6">
                                        @foreach($extraOptions as $idx => $opt)
                                            <label class="d-flex align-items-center gap-6 px-10 py-6 rounded-8"
                                                style="border:1px solid #e2e8f0;cursor:pointer;font-size:12px;">
                                                <input type="checkbox" name="{{ $extrasPrefix }}[]"
                                                    value="{{ $opt['label'] }}"
                                                    style="accent-color:#22c55e;"
                                                    {{ in_array($opt['label'], old("checkout_modules.{$itemKey}.extra_services", [])) ? 'checked' : '' }}>
                                                <span>{{ $opt['label'] }}</span>
                                                <span class="font-weight-bold text-primary">+{{ handlePrice($opt['price'] ?? 0) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            {{-- Checkout Message --}}
                            @php $msgModule = $cartModules->firstWhere('name', 'checkout_message'); @endphp
                            @if($msgModule)
                                @php
                                    $maxLen    = $msgModule->config['max_length'] ?? 500;
                                    $ph        = $msgModule->config['placeholder'] ?? (trans('checkout.special_instructions') ?? 'Special instructions...');
                                    $msgPrefix = "checkout_modules[{$itemKey}][checkout_message]";
                                @endphp
                                <div class="bmod-col" style="flex-basis:100%;">
                                    <div class="bmod-label">
                                        <x-iconsax-lin-message-text class="icons" width="12px" height="12px" style="color:#94a3b8"/>
                                        {{ $msgModule->translated_label ?? 'Message' }}
                                        @if($msgModule->is_required)<span class="text-danger">*</span>@endif
                                    </div>
                                    <textarea name="{{ $msgPrefix }}" class="form-control mt-4"
                                            rows="2" style="font-size:12px;border-radius:6px;"
                                            placeholder="{{ $ph }}" maxlength="{{ $maxLen }}"
                                            id="bmod_msg_{{ $itemKey }}"
                                            {{ $msgModule->is_required ? 'required' : '' }}>{{ old("checkout_modules.{$itemKey}.checkout_message") }}</textarea>
                                    <div style="font-size:10px;color:#94a3b8;margin-top:2px;">
                                        <span id="bmod_msgc_{{ $itemKey }}">0</span>/{{ $maxLen }}
                                    </div>
                                </div>
                            @endif

                        </div>{{-- .bmod-row --}}
                    </div>{{-- .bmod-wrap --}}
                    @endif

                </div>{{-- flex-1 --}}
            </div>{{-- .cart-booking-row --}}

        {{-- ════ ALL OTHER ITEMS ════ --}}
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
                        <a href="{{ $itemInfo['itemUrl'] }}" class="font-14 font-weight-bold text-dark d-block">{{ $itemInfo['title'] ?? '' }}</a>
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

                <button type="button" class="cart-remove-btn" data-cart-id="{{ $itemKey }}"
                        title="{{ trans('public.remove') ?? 'Remove' }}">
                    <x-iconsax-lin-close-circle class="icons text-gray-400" width="16px" height="16px"/>
                </button>
            </div>
        @endif

    @endforeach

    @push('scripts_bottom')
    <script>
    (function($){
        /* Char counter for booking message fields */
        $(document).on('input', '[id^="bmod_msg_"]', function(){
            var key = $(this).attr('id').replace('bmod_msg_','');
            $('#bmod_msgc_' + key).text($(this).val().length);
        });

        /* Nights badge on page load for any pre-filled dates */
        $('.bmod-wrap').each(function(){
            var $w = $(this);
            var inV  = $w.find('.bmod-cin').val();
            var outV = $w.find('.bmod-cout').val();
            if (inV && outV) {
                var inD = new Date(inV), outD = new Date(outV);
                var nights = Math.max(0, Math.ceil((outD - inD) / 86400000));
                $w.find('.bmod-nights-badge').text(nights + ' nights');
            }
        });
    })(jQuery);
    </script>
    @endpush


    @foreach($carts as $cart)
            @php
            $cartItemInfo = app(\App\Mixins\Cart\CartItemInfo::class);
            $itemInfo     = $cartItemInfo->getItemInfo($cart);
            $itemKey      = $cart->id;
            // Load modules for this cart item then filter out inactive modules
            $cartModulesRaw = $checkoutModulesByCart[$cart->id] ?? collect();
            $cartModules = collect($cartModulesRaw)->filter(function($m) {
                // default to true (keep) if flag not present
                return data_get($m, 'is_active', true);
            });
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
                $oldCheckOut = old("checkout_modules.{$itemKey}.days.check_out", $slotEnd ?? '');

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

                    {{-- ── Booking Modules — modern 3-card layout, cancellation below ── --}}
                    @if($cartModules->isNotEmpty())
                        @php
                            $datePrefix   = "checkout_modules[{$itemKey}][days]";
                            $timePrefix   = "checkout_modules[{$itemKey}][hours]";
                            $staffPrefix  = "checkout_modules[{$itemKey}][staff_member]";
                            $policyPrefix = "checkout_modules[{$itemKey}][cancellation_policy]";
                            $extrasPrefix = "checkout_modules[{$itemKey}][extra_services]";
                            $msgPrefix    = "checkout_modules[{$itemKey}][checkout_message]";
                            $selectedTime = old("checkout_modules.{$itemKey}.hours", $slotStart ?? '');
                            $selectedStaff = old("checkout_modules.{$itemKey}.staff_member", $authUserName);
                            $checkInLabel = $oldCheckIn ? \Carbon\Carbon::parse($oldCheckIn)->format('d M Y') : ($slotDate ? \Carbon\Carbon::parse($slotDate)->format('d M Y') : 'Not selected');
                            $timeLabel = $selectedTime ?: ($slotStart ? $slotStart . ($slotEnd ? ' - ' . $slotEnd : '') : 'Not selected');
                            $staffLabel = $selectedStaff ?: ($authUserName ?: 'Guest');
                        @endphp

                        <div class="booking-info-shell" data-item-key="{{ $itemKey }}">
                            @if($daysModule || $hoursModule || $staffModule)
                                <div class="booking-info-grid">
                                    @if($daysModule)
                                        <div class="booking-info-card">
                                            <div class="booking-info-title">
                                                {{ trans('update.check_in_date') ?? 'Check-in Date' }}
                                                @if($daysModule->is_required)<span class="text-danger">*</span>@endif
                                            </div>
                                            <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:12px;">
                                                <div class="booking-info-icon">
                                                    <x-iconsax-lin-calendar-2 class="icons" width="16px" height="16px"/>
                                                </div>
                                                <input type="date" name="{{ $datePrefix }}[check_in]" class="form-control form-control-sm bmod-date-input bmod-cin" value="{{ $oldCheckIn }}" min="{{ now()->format('Y-m-d') }}" {{ $daysModule->is_required ? 'required' : '' }} style="border-radius:12px;width:100%;" />
                                                <div class="booking-info-label">{{ $checkInLabel }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($hoursModule)
                                        <div class="booking-info-card">
                                            <div class="booking-info-title">
                                                {{ $hoursModule->translated_label ?? trans('update.check_in_time') ?? 'Check-in Time' }}
                                                @if($hoursModule->is_required)<span class="text-danger">*</span>@endif
                                            </div>
                                            <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:12px;">
                                                <div class="booking-info-icon">
                                                    <x-iconsax-lin-clock class="icons" width="16px" height="16px"/>
                                                </div>
                                                <select name="{{ $timePrefix }}" class="form-control form-control-sm" style="border-radius:12px;">
                                                    <option value="">— {{ trans('checkout.select') ?? 'Select' }} —</option>
                                                    @foreach($hoursModule->config['slots'] ?? [] as $slot)
                                                        @php
                                                            try {
                                                                $sc = \Carbon\Carbon::createFromFormat('H:i', $slot);
                                                                $ec = $sc->copy()->addHour();
                                                                $label = $sc->format('h:i A') . ' - ' . $ec->format('h:i A');
                                                            } catch (\Throwable $e) {
                                                                $label = $slot;
                                                            }
                                                        @endphp
                                                        <option value="{{ $slot }}" {{ $selectedTime == $slot ? 'selected' : '' }}>{{ $label }}</option>
                                                    @endforeach
                                                </select>
                                                <div class="mt-3" style="width:100%;">
                                                    <div class="font-12 text-gray-500 mb-2">{{ trans('update.check_out_date') ?? 'Check-out Date' }}</div>
                                                    <input type="date" name="{{ $datePrefix }}[check_out]" class="form-control form-control-sm bmod-date-input bmod-cout" value="{{ $oldCheckOut }}" min="{{ now()->format('Y-m-d') }}" {{ $daysModule && $daysModule->is_required ? 'required' : '' }} style="border-radius:12px;width:100%;" />
                                                </div>
                                                <div class="booking-info-label">{{ $timeLabel }}</div>
                                            </div>
                                        </div>
                                    @endif

                                    @if($staffModule)
                                        <div class="booking-info-card">
                                            <div class="booking-info-title">
                                                {{ $staffModule->translated_label ?? 'Assigned Staff' }}
                                                @if($staffModule->is_required)<span class="text-danger">*</span>@endif
                                            </div>
                                            <div class="booking-info-content" style="flex-direction:column;align-items:flex-start;gap:12px;">
                                                <div class="booking-info-icon">
                                                    <x-iconsax-lin-profile class="icons" width="16px" height="16px"/>
                                                </div>
                                                @if(!empty($staffModule->config['staff']))
                                                    <select name="{{ $staffPrefix }}" class="form-control form-control-sm" style="border-radius:12px;">
                                                        <option value="">— {{ trans('checkout.select_staff') ?? 'Select staff' }} —</option>
                                                        @foreach($staffModule->config['staff'] as $staff)
                                                            @php $value = $staff['id'] ?? $staff['name']; @endphp
                                                            <option value="{{ $value }}" {{ $selectedStaff == $value ? 'selected' : '' }}>{{ $staff['name'] }}</option>
                                                        @endforeach
                                                    </select>
                                                @else
                                                    <div class="booking-info-value">{{ $authUserName ?: 'Guest' }}</div>
                                                    <input type="hidden" name="{{ $staffPrefix }}" value="{{ $authUserName }}">
                                                @endif
                                                <div class="booking-info-label">{{ $staffModule->is_required ? 'Required' : 'Optional' }}</div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            @endif

                            @if($policyModule = $cartModules->firstWhere('name', 'cancellation_policy'))
                                @php
                                    $policyText = $policyModule->config['policy_text'] ?? trans('checkout.free_cancellation_hint') ?? 'Free cancellation up to 24 hours before check-in.';
                                @endphp
                                <div class="booking-cancellation-card">
                                    <label for="cp_agree_{{ $itemKey }}">
                                        <input type="checkbox" id="cp_agree_{{ $itemKey }}" name="{{ $policyPrefix }}" value="1"
                                               {{ old("checkout_modules.{$itemKey}.cancellation_policy") ? 'checked' : '' }}
                                               {{ $policyModule->is_required ? 'required' : '' }}>
                                        {{ $policyModule->translated_label ?? (trans('checkout.cancellation_policy') ?? 'Cancellation Policy') }}
                                    </label>
                                    <div class="booking-cancellation-text">{{ $policyText }}</div>
                                </div>
                            @endif

                            @if($extrasModule = $cartModules->firstWhere('name', 'extra_services'))
                                @php
                                    $extraOptions = $extrasModule->config['options'] ?? [];
                                @endphp
                                <div class="booking-cancellation-card">
                                    <div class="booking-info-title">{{ $extrasModule->translated_label ?? 'Extra Services' }}</div>
                                    <div class="d-flex flex-wrap gap-8 mt-3">
                                        @foreach($extraOptions as $opt)
                                            <label class="d-flex align-items-center gap-6 px-10 py-8 rounded-12" style="border:1px solid #e2e8f0;cursor:pointer;font-size:13px;">
                                                <input type="checkbox" name="{{ $extrasPrefix }}[]" value="{{ $opt['label'] }}" style="accent-color:#22c55e;" {{ in_array($opt['label'], old("checkout_modules.{$itemKey}.extra_services", [])) ? 'checked' : '' }}>
                                                <span>{{ $opt['label'] }}</span>
                                                <span class="font-weight-bold text-primary">+{{ handlePrice($opt['price'] ?? 0) }}</span>
                                            </label>
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if($msgModule = $cartModules->firstWhere('name', 'checkout_message'))
                                @php
                                    $maxLen = $msgModule->config['max_length'] ?? 500;
                                    $ph = $msgModule->config['placeholder'] ?? (trans('checkout.special_instructions') ?? 'Special instructions...');
                                @endphp
                                <div class="booking-cancellation-card">
                                    <div class="booking-info-title">{{ $msgModule->translated_label ?? 'Message' }}</div>
                                    <textarea name="{{ $msgPrefix }}" class="form-control mt-3" rows="3" style="font-size:13px;border-radius:12px;min-height:88px;" placeholder="{{ $ph }}" maxlength="{{ $maxLen }}" id="bmod_msg_{{ $itemKey }}" {{ $msgModule->is_required ? 'required' : '' }}>{{ old("checkout_modules.{$itemKey}.checkout_message") }}</textarea>
                                    <div style="font-size:11px;color:#94a3b8;margin-top:8px;">
                                        <span id="bmod_msgc_{{ $itemKey }}">{{ strlen(old("checkout_modules.{$itemKey}.checkout_message")) }}</span>/{{ $maxLen }}
                                    </div>
                                </div>
                            @endif
                        </div>
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
        $('.booking-info-shell').each(function(){
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
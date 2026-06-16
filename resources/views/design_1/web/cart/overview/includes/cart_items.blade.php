

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

                    {{-- ── Booking Modules — full DB-driven checkout module display ── --}}
                    @if($cartModules->isNotEmpty())
                        @include('design_1.web.cart.overview.includes.checkout_item_modules', [
                            'checkoutModules' => $cartModules,
                            'itemKey'         => $itemKey,
                        ])
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
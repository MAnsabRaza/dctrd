@foreach($carts as $cart)
    @php
        $cartItemInfo = app(\App\Mixins\Cart\CartItemInfo::class);
        $itemInfo     = $cartItemInfo->getItemInfo($cart);
        $itemKey      = $cart->id;
        $cartModules  = $checkoutModulesByCart[$cart->id] ?? collect();
    @endphp

    {{-- ════ BOOKING ITEM ════ --}}
    @if(!empty($cart->booking_order_id) || !empty($cart->booking_id))
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

                {{--
                    BOOKING MODULES — single shared partial.
                    No more duplicate hardcoded HTML here; everything
                    (days/hours/staff/persons/extras/policy/messages)
                    is rendered by checkout_item_modules.blade.php,
                    the same partial used in booking.blade.php / checkout.
                --}}
                @include('design_1.web.cart.overview.includes.checkout_item_modules', [
                    'cart'             => $cart,
                    'checkoutModules'  => $cartModules,
                    'itemKey'          => $itemKey,
                ])

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

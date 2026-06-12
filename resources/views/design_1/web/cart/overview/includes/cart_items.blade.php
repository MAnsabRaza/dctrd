{{--
    design_1/web/cart/overview/includes/cart_items.blade.php
    Loops all cart items. Booking items get Image-3 style card (green border modules).
    All others get the standard card.
--}}

@foreach($carts as $cart)
    @php
        $cartItemInfo = app(\App\Mixins\Cart\CartItemInfo::class);
        $itemInfo     = $cartItemInfo->getItemInfo($cart);
        $itemKey      = $cart->id;
        $cartModules  = $checkoutModulesByCart[$cart->id] ?? collect();
    @endphp

    {{-- ════════════════════════════════════════
         BOOKING ITEM  (Image-3 style)
    ════════════════════════════════════════ --}}
    @if(!empty($cart->booking_id))
        @php
            $booking     = $cart->booking;
            $title       = $booking->title ?? ($itemInfo['title'] ?? '');
            $city        = $booking->city ?? ($itemInfo['city'] ?? '');
            $country     = $booking->country ?? ($itemInfo['country'] ?? '');
            $locationStr = collect(array_filter([$city, $country]))->implode(', ');
            $thumbUrl    = $itemInfo['imgPath'] ?? $booking->thumbnail_url ?? '';

            // Price: use cart-level price (which reflects any discount/rate at add-to-cart time)
            $displayPrice    = $itemInfo['discountPrice'] ?? $itemInfo['price'] ?? $cart->price ?? 0;
            $originalPrice   = $itemInfo['price'] ?? null;
            $hasDiscount     = !empty($itemInfo['discountPrice']) && $itemInfo['discountPrice'] != $originalPrice;

            // Subtitle: e.g. "1 Room Hotel at Athens"
            $roomsVal   = null;
            if ($cartModules->isNotEmpty()) {
                $personsModule = $cartModules->firstWhere('name', 'persons_children');
                if ($personsModule) {
                    $oldRooms = old("checkout_modules.{$itemKey}.persons_children.rooms");
                    $roomsVal = $oldRooms ?? ($personsModule->config['rooms']['min'] ?? 1);
                }
            }
            $subtitleRooms = $roomsVal ? $roomsVal . ' ' . trans('checkout.rooms', [], null, 'room') : null;
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

                        {{-- Subtitle line (e.g. "1 Room Hotel at Athens") --}}
                        @if($subtitleRooms || $locationStr)
                            <div class="font-12 text-gray-400 mb-2">
                                @if($subtitleRooms){{ $subtitleRooms }} @endif
                                @if($title){{ $title }}@endif
                                @if($locationStr) <span class="text-gray-300">·</span> {{ $locationStr }}@endif
                            </div>
                        @endif

                        {{-- Main title --}}
                        <div class="font-14 font-weight-bold text-dark text-ellipsis">
                            {{ $title }}
                            @if($locationStr)
                                <span class="font-12 font-weight-400 text-gray-400"> at {{ $locationStr }}</span>
                            @endif
                        </div>

                        {{-- Creator + category badges --}}
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

                    {{-- Right: Price + Remove --}}
                    <div class="d-flex flex-column align-items-end flex-shrink-0 gap-6">

                        {{-- × Remove button --}}
                        <button type="button"
                                class="cart-remove-btn"
                                data-cart-id="{{ $itemKey }}"
                                title="{{ trans('public.remove') ?? 'Remove' }}"
                                style="background:transparent;border:none;padding:0;cursor:pointer;line-height:1;">
                            <x-iconsax-lin-close-circle class="icons text-gray-400" width="20px" height="20px"/>
                        </button>

                        {{-- Price --}}
                        <div class="text-right mt-4">
                            @if($hasDiscount)
                                <div class="font-14 font-weight-bold text-primary">{{ handlePrice($displayPrice) }}</div>
                                <div class="font-11 text-gray-400 text-decoration-line-through">{{ handlePrice($originalPrice) }}</div>
                            @else
                                <div class="font-14 font-weight-bold text-primary">{{ handlePrice($displayPrice) }}</div>
                            @endif
                        </div>

                    </div>
                </div>

                {{-- ── Checkout Modules (green border unified card) ── --}}
                @if($cartModules->isNotEmpty())
                    @include('design_1.web.cart.overview.includes.checkout_item_modules', [
                        'cart'            => $cart,
                        'checkoutModules' => $cartModules,
                        'itemKey'         => $itemKey,
                    ])
                @endif

            </div>
        </div>

    {{-- ════════════════════════════════════════
         ALL OTHER ITEMS (courses, products…)
    ════════════════════════════════════════ --}}
    @else
        <div class="cart-item-card" id="cart-item-{{ $itemKey }}">

            {{-- Thumb --}}
            <div class="flex-shrink-0">
                <img src="{{ $itemInfo['imgPath'] ?? '' }}"
                     alt="{{ $itemInfo['title'] ?? '' }}"
                     class="rounded-12 img-cover"
                     style="width:68px;height:68px;object-fit:cover;">
            </div>

            {{-- Info --}}
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

                {{-- Checkout modules if any --}}
                @if($cartModules->isNotEmpty())
                    @include('design_1.web.cart.overview.includes.checkout_item_modules', [
                        'cart'            => $cart,
                        'checkoutModules' => $cartModules,
                        'itemKey'         => $itemKey,
                    ])
                @endif
            </div>

            {{-- Price --}}
            <div class="flex-shrink-0 text-right">
                @if(!empty($itemInfo['discountPrice']))
                    <div class="font-14 font-weight-bold text-primary">{{ handlePrice($itemInfo['discountPrice']) }}</div>
                    <div class="font-12 text-gray-400 text-line-through mt-2">{{ handlePrice($itemInfo['price']) }}</div>
                @else
                    <div class="font-14 font-weight-bold text-primary">{{ handlePrice($itemInfo['price'] ?? 0) }}</div>
                @endif
            </div>

            {{-- × Remove --}}
            <button type="button"
                    class="cart-remove-btn"
                    data-cart-id="{{ $itemKey }}"
                    title="{{ trans('public.remove') ?? 'Remove' }}">
                <x-iconsax-lin-close-circle class="icons text-gray-400" width="16px" height="16px"/>
            </button>

        </div>
    @endif

@endforeach
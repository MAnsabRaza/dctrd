@php
    $cartTaxType = 'general';
@endphp

<div class="card-with-mask mb-20 {{ !empty($className) ? $className : '' }}">
    <div class="mask-8-white z-index-1 border-dashed border-gray-300"></div>

    <div class="cart-item-card position-relative z-index-2 d-flex flex-column flex-xl-row align-items-xl-start justify-content-between bg-white p-12 p-lg-16 rounded-16 border-gray-200 w-100 h-100">
        <div class="d-flex">
            <div class="cart-item__image position-relative rounded-8 bg-gray-200">
                <img src="{{ $cartItemInfo['imgPath'] }}" class="img-cover rounded-8" alt="{{ $cartItemInfo['title'] }}">
            </div>

            <div class="ml-8 cart-item-card__meta">
                <a href="{{ $cartItemInfo['itemUrl'] ?? '#!' }}" target="_blank">
                    <h6 class="font-12 text-dark">{{ $cartItemInfo['title'] }}</h6>
                </a>

                @if(!is_null($cartItemInfo['rate']))
                    @include('design_1.web.components.rate', [
                         'rate' => $cartItemInfo['rate'],
                         'rateCount' => $cartItemInfo['rateCount'],
                         'rateClassName' => 'mt-8'
                     ])
                @endif

                <div class="d-flex align-items-center mt-16 gap-12 gap-lg-20">
                    @if(!empty($cartItemInfo['profileUrl']))
                        <a href="{{ $cartItemInfo['profileUrl'] }}" target="_blank" class="d-flex align-items-center">
                            <x-iconsax-lin-profile class="icons text-gray-500" width="16px" height="16px"/>
                            <span class="ml-4 font-12 text-gray-500">{{ $cartItemInfo['teacherName'] }}</span>
                        </a>
                    @endif

                    <div class="d-flex align-items-center">
                        <x-iconsax-lin-calendar-2 class="icons text-gray-500" width="16px" height="16px"/>
                        <span class="font-12 text-gray-500 ml-4">{{ trans('update.booking') }}</span>
                    </div>
                </div>
            </div>
        </div>

        @if(!empty($cart))
            <div class="w-100 mt-16">
                @include('design_1.web.cart.overview.includes.checkout_item_modules', ['cart' => $cart])
            </div>
        @endif

        <div class="d-flex align-items-center justify-content-between mt-16 mt-xl-0 cart-item-card__quantity">
            <div class="d-flex align-items-center mr-56 mr-lg-72">
                @if(!empty($cartItemInfo['discountPrice']))
                    <div class="d-flex flex-column">
                        <span class="font-16 font-weight-bold text-primary">{{ handlePrice($cartItemInfo['discountPrice'], true, true, false, null, true, $cartTaxType) }}</span>
                        <span class="text-decoration-line-through font-12 text-gray-500 mt-4">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true, $cartTaxType) }}</span>
                    </div>
                @else
                    <span class="font-16 font-weight-bold text-primary">{{ handlePrice($cartItemInfo['price'], true, true, false, null, true, $cartTaxType) }}</span>
                @endif
            </div>

            @if(!empty($cart))
                <a href="{{ $cartItemInfo['itemUrl'] ?? '#!' }}" target="_blank" class="btn btn-sm btn-outline-primary">
                    <x-iconsax-lin-arrow-right-1 class="icons" width="16px" height="16px"/>
                </a>
            @endif
        </div>
    </div>
</div>

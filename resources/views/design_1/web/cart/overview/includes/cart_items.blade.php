<div class="cart-items-stack">
    @if($carts->whereNotNull('webinar_id')->count())
        <div class="cart-items-group">
            <div class="d-flex align-items-center justify-content-between mb-16">
                <h5 class="font-14 font-weight-bold mb-0">{{ trans('update.courses') }}</h5>
                <span class="text-gray-500 font-12">{{ $carts->whereNotNull('webinar_id')->count() }}</span>
            </div>

            @foreach($carts->whereNotNull('webinar_id') as $cartItem)
                @include('design_1.web.cart.overview.includes.item_cards.course', [
                    'cartItemInfo' => $cartItem->getItemInfo(),
                    'cart' => $cartItem,
                ])
            @endforeach
        </div>
    @endif

    @if($carts->whereNotNull('bundle_id')->count())
        <div class="cart-items-group mt-16">
            <div class="d-flex align-items-center justify-content-between mb-16">
                <h5 class="font-14 font-weight-bold mb-0">{{ trans('update.bundles') }}</h5>
                <span class="text-gray-500 font-12">{{ $carts->whereNotNull('bundle_id')->count() }}</span>
            </div>

            @foreach($carts->whereNotNull('bundle_id') as $cartItem)
                @include('design_1.web.cart.overview.includes.item_cards.course', [
                    'cartItemInfo' => $cartItem->getItemInfo(),
                    'cart' => $cartItem,
                ])
            @endforeach
        </div>
    @endif

    @if($carts->whereNotNull('reserve_meeting_id')->count() or $carts->whereNotNull('meeting_package_id')->count())
        <div class="cart-items-group mt-16">
            <div class="d-flex align-items-center justify-content-between mb-16">
                <h5 class="font-14 font-weight-bold mb-0">{{ trans('panel.meetings') }}</h5>
                <span class="text-gray-500 font-12">{{ $carts->whereNotNull('reserve_meeting_id')->count() + $carts->whereNotNull('meeting_package_id')->count() }}</span>
            </div>

            @foreach($carts->whereNotNull('reserve_meeting_id') as $cartItem)
                @include('design_1.web.cart.overview.includes.item_cards.meeting', [
                    'cartItemInfo' => $cartItem->getItemInfo(),
                    'cart' => $cartItem,
                ])
            @endforeach

            @foreach($carts->whereNotNull('meeting_package_id') as $cartItem)
                @include('design_1.web.cart.overview.includes.item_cards.meeting_package', [
                    'cartItemInfo' => $cartItem->getItemInfo(),
                    'cart' => $cartItem,
                ])
            @endforeach
        </div>
    @endif

    @if($carts->whereNotNull('product_order_id')->count())
        <div class="cart-items-group mt-16">
            <div class="d-flex align-items-center justify-content-between mb-16">
                <h5 class="font-14 font-weight-bold mb-0">{{ trans('update.products') }}</h5>
                <span class="text-gray-500 font-12">{{ $carts->whereNotNull('product_order_id')->count() }}</span>
            </div>

            @foreach($carts->whereNotNull('product_order_id') as $cartItem)
                @include('design_1.web.cart.overview.includes.item_cards.product', [
                    'cartItemInfo' => $cartItem->getItemInfo(),
                    'cart' => $cartItem,
                ])
            @endforeach
        </div>
    @endif

    @if($carts->whereNotNull('event_ticket_id')->count())
        <div class="cart-items-group mt-16">
            <div class="d-flex align-items-center justify-content-between mb-16">
                <h5 class="font-14 font-weight-bold mb-0">{{ trans('update.event_tickets') }}</h5>
                <span class="text-gray-500 font-12">{{ $carts->whereNotNull('event_ticket_id')->count() }}</span>
            </div>

            @foreach($carts->whereNotNull('event_ticket_id') as $cartItem)
                @include('design_1.web.cart.overview.includes.item_cards.event_ticket', [
                    'cartItemInfo' => $cartItem->getItemInfo(),
                    'cart' => $cartItem,
                ])
            @endforeach
        </div>
    @endif

    @if($carts->whereNotNull('installment_payment_id')->count())
        <div class="cart-items-group mt-16">
            <div class="d-flex align-items-center justify-content-between mb-16">
                <h5 class="font-14 font-weight-bold mb-0">{{ trans('update.installment_upfront') }}</h5>
                <span class="text-gray-500 font-12">{{ $carts->whereNotNull('installment_payment_id')->count() }}</span>
            </div>

            @foreach($carts->whereNotNull('installment_payment_id') as $cartItem)
                @include('design_1.web.cart.overview.includes.item_cards.installment_payment', [
                    'cartItemInfo' => $cartItem->getItemInfo(),
                    'cart' => $cartItem,
                ])
            @endforeach
        </div>
    @endif
</div>

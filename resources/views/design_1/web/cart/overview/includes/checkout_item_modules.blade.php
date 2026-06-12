@php
    $showHeader = $showHeader ?? true;
    $wrapperClassName = $wrapperClassName ?? '';

    try {
        $entityType = null;
        $entityId = null;
        $orgId = null;

        if (!empty($cart->webinar_id)) {
            $entityType = 'course';
            $entityId = $cart->webinar_id;
            $orgId = optional($cart->webinar)->teacher_id;
        } elseif (!empty($cart->product_order_id) && !empty($cart->productOrder->product_id)) {
            $entityType = 'product';
            $entityId = $cart->productOrder->product_id;
            $orgId = optional(optional($cart->productOrder)->product)->creator_id;
        } elseif (!empty($cart->booking_id)) {
            $entityType = 'booking';
            $entityId = $cart->booking_id;
            $orgId = optional($cart->booking)->creator_id;
        } elseif (!empty($cart->reserve_meeting_id)) {
            $entityType = 'booking';
            $entityId = $cart->reserve_meeting_id;
            $orgId = optional(optional($cart->reserveMeeting)->meeting)->creator_id;
        }

        $checkoutModules = $checkoutModulesByCart[$cart->id] ?? [];

        if (empty($checkoutModules) && $entityType && $entityId && $orgId) {
            $checkoutModules = app(\App\Services\CheckoutModuleService::class)->getModulesForEntity($entityType, $entityId, $orgId);
        }
    } catch (\Throwable $e) {
        $checkoutModules = [];
    }
@endphp

@if(!empty($checkoutModules) && count($checkoutModules))
    <div class="cart-module-shell mt-16 p-12 p-lg-16 rounded-16 {{ $wrapperClassName }}">
        @if($showHeader)
            <div class="d-flex align-items-center justify-content-between mb-12">
                <h6 class="font-13 font-weight-bold text-secondary mb-0">{{ trans('update.checkout_options') }}</h6>
                <span class="font-12 text-gray-500">{{ count($checkoutModules) }}</span>
            </div>
        @endif

        <div class="row gx-3 gy-3">
            @foreach($checkoutModules as $module)
                <div class="col-12 col-md-6 col-xl-4">
                    @includeIf('partials.checkout_modules._' . $module->name, ['module' => $module, 'itemId' => $cart->id])
                </div>
            @endforeach
        </div>
    </div>
@endif

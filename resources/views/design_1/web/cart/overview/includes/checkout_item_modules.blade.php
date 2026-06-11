@php
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
        } elseif (!empty($cart->reserve_meeting_id)) {
            $entityType = 'booking';
            $entityId = $cart->reserve_meeting_id;
            $orgId = optional(optional($cart->reserveMeeting)->meeting)->creator_id;
        }

        $checkoutModules = [];
        if ($entityType && $entityId && $orgId) {
            $checkoutModules = app(\App\Services\CheckoutModuleService::class)->getModulesForEntity($entityType, $entityId, $orgId);
        }
    } catch (\Throwable $e) {
        $checkoutModules = [];
    }
@endphp

@if(!empty($checkoutModules) && count($checkoutModules))
    <div class="mt-16 p-16 rounded-16 bg-gray-100 border border-gray-200">
        <div class="d-flex align-items-center justify-content-between mb-12">
            <h6 class="font-14 text-secondary mb-0">{{ trans('update.checkout_options') }}</h6>
        </div>

        <div class="row gx-3 gy-3">
            @foreach($checkoutModules as $module)
                <div class="col-12">
                    @includeIf('partials.checkout_modules._' . $module->name, ['module' => $module, 'itemId' => $cart->id])
                </div>
            @endforeach
        </div>
    </div>
@endif

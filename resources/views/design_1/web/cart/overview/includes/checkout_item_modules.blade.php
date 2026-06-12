@push('styles_top')
<style>
/* ============================================================
   Checkout Modules — matches Image 2 green-border card design
   ============================================================ */

.booking-checkout-shell {
    padding: 10px;
    border: 1.5px solid rgba(30, 84, 255, 0.18);
    border-radius: 16px;
    background: linear-gradient(180deg, rgba(30, 84, 255, 0.03) 0%, #ffffff 40%);
    box-shadow: 0 8px 24px rgba(15, 23, 42, 0.04);
}

.booking-checkout-shell .cart-module-shell {
    margin-top: 0 !important;
    padding: 0 !important;
    background: transparent;
    border: 0;
    box-shadow: none;
}

/* All module cards in ONE horizontal row */
.booking-checkout-shell .row {
    --bs-gutter-x: 0px;
    --bs-gutter-y: 0px;
    display: flex !important;
    flex-wrap: nowrap !important;
    align-items: stretch;
}

.booking-checkout-shell .row > [class*="col-"] {
    flex: 1 1 0 !important;
    min-width: 0 !important;
    max-width: none !important;
    padding: 0 !important;
}

/* ---- Individual module card ---- */
.checkout-module-card {
    height: 100%;
    margin-bottom: 0 !important;
    padding: 10px 14px;
    border: 1.5px solid rgba(34, 197, 94, 0.45);
    border-radius: 0;
    background: #ffffff;
    box-shadow: none;
}

/* First card rounded left */
.booking-checkout-shell .row > [class*="col-"]:first-child .checkout-module-card {
    border-radius: 12px 0 0 12px;
}

/* Last card rounded right */
.booking-checkout-shell .row > [class*="col-"]:last-child .checkout-module-card {
    border-radius: 0 12px 12px 0;
}

/* Only child full radius */
.booking-checkout-shell .row > [class*="col-"]:only-child .checkout-module-card {
    border-radius: 12px;
}

/* Divider between cards — remove duplicate left border */
.booking-checkout-shell .row > [class*="col-"] + [class*="col-"] .checkout-module-card {
    border-left: none;
}

/* Vertical separator line between cards */
.booking-checkout-shell .row > [class*="col-"] + [class*="col-"] {
    border-left: 1px solid rgba(34, 197, 94, 0.3);
}

/* ---- Price badge ---- */
.checkout-module-price {
    color: var(--primary, #1e54ff);
    font-weight: 700;
    font-size: 12px;
    white-space: nowrap;
}

/* ---- Helper text ---- */
.checkout-module-helper {
    color: #94a3b8;
    font-size: 11px;
}

/* ---- Nights / chars badge ---- */
.checkout-module-badge {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 999px;
    background: rgba(30, 84, 255, 0.07);
    border: 1px solid rgba(30, 84, 255, 0.14);
    font-size: 11px;
    color: #334155;
}

/* ---- Inline fields card (inner green box) ---- */
.checkout-inline-fields-card {
    border: 1.5px solid rgba(34, 197, 94, 0.4) !important;
    border-radius: 10px !important;
    background: rgba(34, 197, 94, 0.04) !important;
    padding: 8px 10px !important;
}

.checkout-inline-fields-card .row {
    flex-wrap: nowrap !important;
}

.checkout-inline-fields-card .col-6 {
    padding: 0 8px;
}

.checkout-inline-fields-card .col-6:first-child {
    padding-left: 0;
}

.checkout-inline-fields-card .col-6:last-child {
    padding-right: 0;
}

/* ---- Date inputs ---- */
.checkout-inline-date-input,
.checkout-date-input {
    border: none !important;
    background: transparent !important;
    font-size: 12px;
    color: #0f172a;
    padding: 0 !important;
    width: 100%;
    outline: none;
    cursor: pointer;
}

.checkout-inline-date-input::-webkit-calendar-picker-indicator,
.checkout-date-input::-webkit-calendar-picker-indicator {
    opacity: 0.5;
    cursor: pointer;
}

/* ---- Staff select ---- */
.checkout-staff-select {
    border: none !important;
    background: transparent !important;
    font-size: 12px;
    color: #0f172a;
    flex: 1;
    padding: 0;
    outline: none;
    cursor: pointer;
    appearance: none;
    -webkit-appearance: none;
    width: 100%;
}

/* ---- Time slot grid ---- */
.checkout-time-slot-grid {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-top: 8px;
}

.checkout-time-slot-option {
    display: flex;
    align-items: center;
    padding: 5px 10px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    font-size: 11px;
    color: #334155;
    transition: all 0.15s ease;
    margin: 0;
}

.checkout-time-slot-option input[type="radio"] {
    display: none;
}

.checkout-time-slot-option:has(input:checked) {
    border-color: var(--primary, #1e54ff);
    background: rgba(30, 84, 255, 0.07);
    color: var(--primary, #1e54ff);
    font-weight: 600;
}

.checkout-time-slot-option:hover {
    border-color: var(--primary, #1e54ff);
    background: rgba(30, 84, 255, 0.04);
}

/* ---- Extra services ---- */
.checkout-extra-grid {
    display: flex;
    flex-direction: column;
    gap: 6px;
    margin-top: 8px;
}

.checkout-extra-option {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 7px 10px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    cursor: pointer;
    font-size: 12px;
    transition: all 0.15s ease;
    margin: 0;
}

.checkout-extra-option input[type="checkbox"] {
    margin-right: 8px;
    accent-color: var(--primary, #1e54ff);
}

.checkout-extra-option__label { flex: 1; color: #0f172a; }

.checkout-extra-option__price {
    color: var(--primary, #1e54ff);
    font-weight: 600;
    font-size: 11px;
}

.checkout-extra-option:has(input:checked) {
    border-color: var(--primary, #1e54ff);
    background: rgba(30, 84, 255, 0.04);
}

/* ---- Textarea ---- */
.checkout-textarea {
    font-size: 12px;
    border-radius: 8px;
    border: 1px solid #e2e8f0;
    resize: vertical;
    min-height: 72px;
}

.checkout-textarea:focus {
    border-color: rgba(30, 84, 255, 0.4);
    box-shadow: 0 0 0 3px rgba(30, 84, 255, 0.06);
}

/* ---- Stepper (persons/rooms) ---- */
.checkout-stepper {
    display: flex;
    align-items: center;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
}

.checkout-stepper-btn {
    width: 28px;
    height: 28px;
    border: none;
    background: #f8fafc;
    color: #0f172a;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: background 0.1s;
}

.checkout-stepper-btn:hover { background: #e2e8f0; }
.checkout-stepper-btn:active { background: #cbd5e1; }

.checkout-stepper-input {
    width: 36px;
    text-align: center;
    border: none;
    border-left: 1px solid #e2e8f0;
    border-right: 1px solid #e2e8f0;
    border-radius: 0;
    font-size: 12px;
    font-weight: 600;
    color: #0f172a;
    background: #fff;
    padding: 0;
    height: 28px;
    -moz-appearance: textfield;
}

.checkout-stepper-input:focus { outline: none; box-shadow: none; }
.checkout-stepper-input::-webkit-outer-spin-button,
.checkout-stepper-input::-webkit-inner-spin-button { -webkit-appearance: none; }

/* ---- Cancellation policy info box ---- */
.checkout-module-card--policy .policy-info-box {
    display: flex;
    align-items: flex-start;
    gap: 8px;
    padding: 10px 12px;
    border-radius: 10px;
    background: rgba(30, 84, 255, 0.05);
    border: 1px solid rgba(30, 84, 255, 0.14);
    margin-top: 10px;
}

.checkout-module-card--policy .policy-info-box p {
    font-size: 12px;
    color: #475569;
    margin: 0;
    line-height: 1.6;
}

/* ---- Stepper row spacing ---- */
.checkout-stepper-row > div { margin-bottom: 8px; }
.checkout-stepper-row > div:last-child { margin-bottom: 0; }

/* ---- Mobile: stack vertically ---- */
@media (max-width: 767px) {
    .booking-checkout-shell .row {
        flex-wrap: wrap !important;
    }

    .booking-checkout-shell .row > [class*="col-"] {
        flex: 1 1 100% !important;
    }

    .booking-checkout-shell .row > [class*="col-"] + [class*="col-"] {
        border-left: none;
        border-top: 1px solid rgba(34, 197, 94, 0.3);
    }

    .booking-checkout-shell .row > [class*="col-"]:first-child .checkout-module-card {
        border-radius: 12px 12px 0 0;
    }

    .booking-checkout-shell .row > [class*="col-"]:last-child .checkout-module-card {
        border-radius: 0 0 12px 12px;
    }
}
</style>
@endpush

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

        <div class="row">
            @foreach($checkoutModules as $module)
                <div class="col">
                    @includeIf('partials.checkout_modules._' . $module->name, ['module' => $module, 'itemId' => $cart->id])
                </div>
            @endforeach
        </div>
    </div>
@endif
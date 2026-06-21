<div class="d-grid grid-columns-2 grid-lg-columns-3 gap-24 mt-16">
    {{-- Customers --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-profile class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ $booking->salesCount() }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.customers') }}</span>
        </div>
    </div>
    {{-- Sales --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-money-3 class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ handlePrice($booking->sales()->sum('total_amount')) }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('panel.sales') }}</span>
        </div>
    </div>
    {{-- Availability --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-box-1 class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">
                @if(empty($booking->inventory))
                    {{ trans('update.unlimited') }}
                @else
                    {{ $booking->getAvailability() }}
                @endif
            </span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.availability') }}</span>
        </div>
    </div>
    {{-- Views --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-eye class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ $booking->views_count ?? $booking->views }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.views') }}</span>
        </div>
    </div>
    {{-- Waiting Orders --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-bag class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ $booking->bookingOrders->whereIn('status',[\App\Models\BookingOrder::$waitingDelivery, \App\Models\BookingOrder::$shipped])->count() }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.waiting_orders') }}</span>
        </div>
    </div>
    {{-- Last Purchase --}}
    <div class="d-flex align-items-center">
        <div class="d-flex-center size-36 bg-white rounded-circle">
            <x-iconsax-lin-bag-timer class="icons text-gray-400" width="20px" height="20px"/>
        </div>
        <div class="ml-8 font-12">
            <span class="d-block font-weight-bold text-dark">{{ !empty($booking->last_purchase_date) ? dateTimeFormat($booking->last_purchase_date, 'j M Y') : '-' }}</span>
            <span class="d-block mt-2 text-gray-500">{{ trans('update.last_purchase') }}</span>
        </div>
    </div>
</div>

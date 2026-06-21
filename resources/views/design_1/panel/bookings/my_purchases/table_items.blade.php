<tr>
    <td class="text-left">
        <div class="user-inline-avatar d-flex align-items-center">
            <div class="size-48 bg-gray-200 rounded-circle">
                <img src="{{ !empty($order->seller) ? $order->seller->getAvatar() : '' }}" class="img-cover rounded-circle" alt="">
            </div>
            <div class="ml-4">
                <span class="d-block">{{ !empty($order->seller) ? $order->seller->full_name : '' }}</span>
                <span class="mt-4 font-12 text-gray-500 d-block">{{ !empty($order->seller) ? $order->seller->email : '' }}</span>
            </div>
        </div>
    </td>

    <td class="text-center">
        <span class="d-block font-weight-500">{{ $order->id }}</span>
        <span class="d-block font-12 text-gray-500">
            {{ !empty($order->item) ? $order->item->title : '#'.$order->id }}
            ({{ $order->quantity }} {{ trans('update.unit') }})
        </span>
    </td>

    <td class="text-center">
        <span>{{ handlePrice($order->sale->amount) }}</span>
    </td>

    <td class="text-center">
        @if(!empty($order->sale->discount) and (int)$order->sale->discount > 0)
            {{ handlePrice($order->sale->discount ?? 0) }}
        @else
            -
        @endif
    </td>

    <td class="text-center">
        @if(!empty($order->sale->tax))
            {{ handlePrice($order->sale->tax) }}
        @else
            -
        @endif
    </td>

    <td class="text-center">
        <span>{{ handlePrice($order->sale->total_amount) }}</span>
    </td>

    <td class="text-center">
        @if($order->status == \App\Models\BookingOrder::$waitingConfirmation)
            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-warning-30 font-12 text-warning">{{ trans('update.booking_order_status_waiting_confirmation') }}</span>
        @elseif($order->status == \App\Models\BookingOrder::$confirmed)
            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-primary-30 font-12 text-primary">{{ trans('update.booking_order_status_confirmed') }}</span>
        @elseif($order->status == \App\Models\BookingOrder::$completed)
            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-success-30 font-12 text-success">{{ trans('update.booking_order_status_completed') }}</span>
        @elseif($order->status == \App\Models\BookingOrder::$canceled)
            <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-danger-30 font-12 text-danger">{{ trans('update.booking_order_status_canceled') }}</span>
        @endif
    </td>

    <td class="text-center">
        <span>{{ dateTimeFormat($order->created_at, 'j M Y H:i') }}</span>
    </td>

    <td class="text-right">
        <div class="actions-dropdown position-relative d-flex justify-content-end align-items-center">
            <button type="button" class="d-flex-center size-36 bg-gray border-gray-200 rounded-10">
                <x-iconsax-lin-more class="icons text-gray-500" width="18"/>
            </button>

            <div class="actions-dropdown__dropdown-menu dropdown-menu-width-220 dropdown-menu-top-32">
                <ul class="my-8">

                    <li class="actions-dropdown__dropdown-menu-item">
                        <a href="/panel/bookings/purchases/{{ $order->sale_id }}/orderItem/{{ $order->id }}/invoice" target="_blank">{{ trans('public.invoice') }}</a>
                    </li>

                    @if(!empty($order->item) and $order->status == \App\Models\BookingOrder::$completed)
                        <li class="actions-dropdown__dropdown-menu-item">
                            <a href="{{ method_exists($order->item, 'getUrl') ? $order->item->getUrl() : '#' }}" target="_blank">{{ trans('public.feedback') }}</a>
                        </li>
                    @endif

                    @if(in_array($order->status, [\App\Models\BookingOrder::$waitingConfirmation, \App\Models\BookingOrder::$confirmed]))
                        <li class="actions-dropdown__dropdown-menu-item">
                            <button type="button" data-sale-id="{{ $order->sale_id }}" data-order-id="{{ $order->id }}" class="js-view-booking-details">{{ trans('update.view_booking_details') }}</button>
                        </li>
                    @endif

                    @if($order->status == \App\Models\BookingOrder::$confirmed)
                        <li class="actions-dropdown__dropdown-menu-item">
                            <button type="button" data-sale-id="{{ $order->sale_id }}" data-order-id="{{ $order->id }}" class="js-set-completed">{{ trans('update.confirm_booking_completed') }}</button>
                        </li>
                    @endif

                </ul>
            </div>
        </div>
    </td>
</tr>
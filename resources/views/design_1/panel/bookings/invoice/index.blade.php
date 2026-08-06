@extends('design_1.panel.layouts.panel')

@section('content')

    <div class="bg-white p-24 rounded-24 mt-20" id="invoiceBox">

        <div class="d-flex align-items-center justify-content-between pb-16 border-bottom-gray-100">
            <div>
                <h3 class="font-20 font-weight-600">{{ trans('public.invoice') }}</h3>
                <span class="text-gray-500 font-14">#{{ $order->id }} — {{ dateTimeFormat($order->created_at, 'j M Y H:i') }}</span>
            </div>
            <button type="button" class="btn btn-primary" onclick="window.print()">
                {{ trans('public.print') }}
            </button>
        </div>

        {{-- Seller / Buyer --}}
        <div class="row mt-24">
            <div class="col-md-6">
                <h5 class="font-14 text-gray-500">{{ trans('update.seller') }}</h5>
                <p class="mb-4 font-weight-500">{{ !empty($seller) ? $seller->full_name : '-' }}</p>
                <p class="mb-0 text-gray-500">{{ !empty($seller) ? $seller->email : '-' }}</p>
            </div>
            <div class="col-md-6 text-md-right">
                <h5 class="font-14 text-gray-500">{{ trans('public.buyer') }}</h5>
                <p class="mb-4 font-weight-500">{{ !empty($buyer) ? $buyer->full_name : '-' }}</p>
                <p class="mb-0 text-gray-500">{{ !empty($buyer) ? $buyer->email : '-' }}</p>
            </div>
        </div>

        {{-- Item / Booking details --}}
        <div class="table-responsive mt-24">
            <table class="table panel-table">
                <thead>
                <tr>
                    <th class="text-left">{{ trans('public.item') }}</th>
                    <th class="text-center">{{ trans('update.order_id') }}</th>
                    <th class="text-center">{{ trans('public.price') }}</th>
                    <th class="text-center">{{ trans('public.discount') }}</th>
                    <th class="text-center">{{ trans('cart.tax') }}</th>
                    <th class="text-right">{{ trans('financial.total_amount') }}</th>
                </tr>
                </thead>
                <tbody>
                <tr>
                    <td class="text-left">
                        {{ !empty($item) ? $item->title : ('#' . $order->id) }}
                        <span class="d-block font-12 text-gray-500">
                            {{ $order->quantity }} {{ trans('update.unit') }}
                        </span>
                    </td>
                    <td class="text-center">{{ $order->id }}</td>
                    <td class="text-center">{{ !empty($sale) ? handlePrice($sale->amount) : '-' }}</td>
                    <td class="text-center">
                        @if(!empty($sale) and !empty($sale->discount) and (int)$sale->discount > 0)
                            {{ handlePrice($sale->discount) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-center">
                        @if(!empty($sale) and !empty($sale->tax))
                            {{ handlePrice($sale->tax) }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="text-right font-weight-600">
                        {{ !empty($sale) ? handlePrice($sale->total_amount) : '-' }}
                    </td>
                </tr>
                </tbody>
            </table>
        </div>

        {{-- Status --}}
        <div class="mt-16">
            <span class="text-gray-500 font-14">{{ trans('public.status') }}:</span>
            @if($order->status == \App\Models\BookingOrder::$waitingDelivery)
                <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-warning-30 font-12 text-warning">{{ trans('update.booking_order_status_waiting_delivery') }}</span>
            @elseif($order->status == \App\Models\BookingOrder::$shipped)
                <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-primary-30 font-12 text-primary">{{ trans('update.booking_order_status_shipped') }}</span>
            @elseif($order->status == \App\Models\BookingOrder::$success)
                <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-success-30 font-12 text-success">{{ trans('update.booking_order_status_success') }}</span>
            @elseif($order->status == \App\Models\BookingOrder::$canceled)
                <span class="d-inline-flex-center px-8 py-6 rounded-8 bg-danger-30 font-12 text-danger">{{ trans('update.booking_order_status_canceled') }}</span>
            @endif
        </div>

    </div>

@endsection
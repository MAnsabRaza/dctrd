@extends('design_1.panel.layouts.panel')

@section('content')

{{-- TOP STATS --}}
@include('design_1.panel.bookings.order.top_stats')

@if(!empty($orders) and !$orders->isEmpty())

    <div class="bg-white pt-16 rounded-24 mt-20">

        <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">

            <h3 class="font-16">
                Orders History
            </h3>

        </div>

        {{-- FILTERS --}}
        @include('design_1.panel.bookings.order.filters')

        <div class="table-responsive-lg">

            <table class="table panel-table">

                <thead>

                    <tr>
                        <th class="text-left">
                            Order #
                        </th>

                        <th class="text-left">
                            Booking
                        </th>

                        <th class="text-center">
                            Total
                        </th>

                        <th class="text-center">
                            Status
                        </th>

                        <th class="text-center">
                            Payment
                        </th>

                        <th class="text-center">
                            Date
                        </th>
                    </tr>

                </thead>

                <tbody>

                    @foreach($orders as $order)

                        @include('design_1.panel.bookings.order.table_item', [
                            'order' => $order
                        ])

                    @endforeach

                </tbody>

            </table>

            <div class="px-16 pb-16">

                {{ $orders->appends(request()->input())->links() }}

            </div>

        </div>

    </div>

@else

    @include('design_1.panel.includes.no-result',[
        'file_name' => 'store_sales.svg',
        'title' => 'No Booking Orders',
        'hint' => 'You have no booking orders yet.',
    ])

@endif

@endsection
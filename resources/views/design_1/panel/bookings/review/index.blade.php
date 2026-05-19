@extends('design_1.panel.layouts.panel')

@section('content')

@include('design_1.panel.bookings.review.top_stats')

@if(!empty($reviews) and !$reviews->isEmpty())

    <div class="bg-white pt-16 rounded-24 mt-20">

        <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">

            <h3 class="font-16">
                Booking Reviews
            </h3>

        </div>

        @include('design_1.panel.bookings.review.filters')

        <div id="tableListContainer"
             class="table-responsive-lg"
             data-view-data-path="/panel/bookings/reviews">

            <table class="table panel-table">

                <thead>

                    <tr>

                        <th class="text-left">
                            Booking
                        </th>

                        <th class="text-center">
                            Rating
                        </th>

                        <th class="text-center">
                            Review
                        </th>

                        <th class="text-center">
                            Status
                        </th>

                        <th class="text-center">
                            Date
                        </th>

                    </tr>

                </thead>

                <tbody class="js-table-body-lists">

                    @foreach($reviews as $reviewRow)

                        @include(
                            'design_1.panel.bookings.review.table_items',
                            ['review' => $reviewRow]
                        )

                    @endforeach

                </tbody>

            </table>

            <div id="pagination"
                 class="js-ajax-pagination"
                 data-container-id="tableListContainer"
                 data-container-items=".js-table-body-lists">

                {!! $pagination !!}

            </div>

        </div>

    </div>

@endif

@endsection
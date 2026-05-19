@extends('design_1.panel.layouts.panel')

@section('content')

@include('design_1.panel.bookings.comment.top_stats')

@if(!empty($comments) and !$comments->isEmpty())

    <div class="bg-white pt-16 rounded-24 mt-20">

        <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">

            <h3 class="font-16">
                Booking Comments
            </h3>

        </div>

        @include('design_1.panel.bookings.comment.filters')

        <div id="tableListContainer"
             class="table-responsive-lg"
             data-view-data-path="/panel/bookings/comments">

            <table class="table panel-table">

                <thead>

                    <tr>

                        <th class="text-left">
                            Booking
                        </th>

                        <th class="text-center">
                            Comment
                        </th>

                        <th class="text-center">
                            Status
                        </th>

                        <th class="text-center">
                            Date
                        </th>

                        <th class="text-right">
                            Controls
                        </th>

                    </tr>

                </thead>

                <tbody class="js-table-body-lists">

                    @foreach($comments as $commentRow)

                        @include(
                            'design_1.panel.bookings.comment.table_items',
                            ['comment' => $commentRow]
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
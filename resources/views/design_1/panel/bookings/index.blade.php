@extends('design_1.panel.layouts.panel')

@section('content')

@include('design_1.panel.bookings.top_stats')

<div class="bg-white rounded-24 p-16 mt-20">

    <div class="d-flex align-items-center justify-content-between mb-20">

        <div>

            <h3 class="font-16 font-weight-bold">
                Bookings
            </h3>

        </div>

        <a href="{{ route('panel.bookings.create') }}"
           class="btn btn-primary">

            New Booking

        </a>

    </div>

    {{-- FILTERS --}}

    @include('design_1.panel.bookings.filters')

    {{-- TABLE --}}

    <div class="table-responsive-lg mt-20">

        <table class="table panel-table">

            <thead>

                <tr>

                    <th class="text-left">
                        Title
                    </th>

                    <th class="text-left">
                        Category
                    </th>

                    <th class="text-center">
                        Price
                    </th>

                    <th class="text-center">
                        Capacity
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

            <tbody>

                @forelse($bookings as $booking)

                    @include(
                        'design_1.panel.bookings.table_item',
                        ['booking' => $booking]
                    )

                @empty

                    <tr>

                        <td colspan="7" class="text-center">

                            No bookings found

                        </td>

                    </tr>

                @endforelse

            </tbody>

        </table>

    </div>

    <div class="mt-20">

        {!! $pagination !!}

    </div>

</div>

@endsection
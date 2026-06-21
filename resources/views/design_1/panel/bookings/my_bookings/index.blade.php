@extends('design_1.panel.layouts.panel')

@push("styles_top")

@endpush

@section('content')

    {{-- Top Stats --}}
    @include('design_1.panel.bookings.my_bookings.top_stats')

    {{-- List Grid --}}
    @if(!empty($bookings) and $bookings->isNotEmpty())
        <div id="tableListContainer" class="" data-view-data-path="/panel/bookings">
            <div class="js-page-bookings-lists row mt-20">
                @foreach($bookings as $booking)
                    <div class="col-12 col-lg-6 mb-32">
                        @include("design_1.panel.bookings.my_bookings.booking_card.index")
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            <div id="pagination" class="js-ajax-pagination" data-container-id="tableListContainer"
                 data-container-items=".js-page-bookings-lists">
                {!! $pagination !!}
            </div>
        </div>
    @else
        @include('design_1.panel.includes.no-result',[
            'file_name' => 'store_products.svg',
            'title' => trans('panel.you_not_have_any_booking'),
            'hint' =>  trans('panel.you_not_have_any_booking_hint') ,
            'btn' => ['url' => route('panel.bookings.create'),'text' => trans('panel.create_a_booking') ]
        ])
    @endif

@endsection

@push('scripts_bottom')

    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

@endpush

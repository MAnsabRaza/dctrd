@extends('design_1.panel.layouts.panel')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    <div class="bg-white p-16 rounded-24 mb-16">
        <div class="d-flex align-items-center justify-content-between flex-wrap gap-16">

            <div class="d-flex align-items-center gap-16">
                <img src="{{ $organization->avatar ? asset('storage/' . $organization->avatar) : asset('assets/default/img/icons/installment/meeting_default.svg') }}"
                     alt="{{ $organization->full_name }}"
                     class="rounded-12"
                     width="56" height="56">

                <div>
                    <h3 class="font-16 mb-4">{{ $organization->full_name }}</h3>
                    <div class="d-flex align-items-center gap-8 font-13 text-gray-500">
                        <span>{{ trans_choice('panel.bookings_count', $stats['bookings_count'], ['count' => $stats['bookings_count']]) }}</span>
                        <span>|</span>
                        <span>{{ trans_choice('panel.operators_count', $stats['operators_count'], ['count' => $stats['operators_count']]) }}</span>
                        <span>|</span>
                        <span>{{ trans_choice('panel.customers_count', $stats['customers_count'], ['count' => $stats['customers_count']]) }}</span>
                    </div>
                </div>
            </div>

            <a href="{{ url('/panel/organization/profile') }}" class="btn btn-primary">
                {{ trans('panel.organization_profile') }}
            </a>

        </div>
    </div>

    @if(!empty($bookings) and !$bookings->isEmpty())
        <div class="bg-white pt-16 rounded-24">
            <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
                <div class="">
                    <h3 class="font-16">{{ trans('panel.organization_bookings') }}</h3>
                </div>
            </div>

            {{-- Filters --}}
            @include('design_1.panel.bookings.organization.filters')

            {{-- Card Grid --}}
            <div id="tableListContainer" class="px-16 pb-16" data-view-data-path="/panel/bookings/organization">
                <div class="row js-table-body-lists">
                    @foreach($bookings as $bookingRow)
                        @include('design_1.panel.bookings.organization.card_item', ['booking' => $bookingRow])
                    @endforeach
                </div>

                {{-- Pagination --}}
                <div id="pagination" class="js-ajax-pagination" data-container-id="tableListContainer" data-container-items=".js-table-body-lists">
                    {!! $pagination !!}
                </div>
            </div>
        </div>
    @else
        @include('design_1.panel.includes.no-result',[
            'file_name' => 'store_product_comments.svg',
            'title' => trans('panel.organization_bookings_no_result'),
            'hint' =>  nl2br(trans('panel.organization_bookings_no_result_hint')),
            'extraClass' => 'mt-0',
        ])
    @endif

@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>
@endpush
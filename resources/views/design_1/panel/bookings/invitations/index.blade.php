@extends('design_1.panel.layouts.panel')

@section('content')

    {{-- Stats Cards --}}
    <div class="d-flex flex-wrap gap-16 mb-16">

        <div class="bg-white p-16 rounded-24 flex-fill d-flex align-items-center justify-content-between gap-16">
            <div>
                <div class="font-13 text-gray-500 mb-4">{{ trans('panel.total_bookings') }}</div>
                <h3 class="font-24 font-w-700 text-dark mb-0">{{ $stats['bookings_count'] }}</h3>
            </div>
            <div class="d-flex-center size-44 rounded-12 bg-light">
                <x-iconsax-bol-calendar-2 class="text-primary" width="20"/>
            </div>
        </div>

        <div class="bg-white p-16 rounded-24 flex-fill d-flex align-items-center justify-content-between gap-16">
            <div>
                <div class="font-13 text-gray-500 mb-4">{{ trans('panel.total_booking_sales') }}</div>
                <h3 class="font-24 font-w-700 text-dark mb-0">{{ number_format($stats['bookings_sales'], 2) }}</h3>
            </div>
            <div class="d-flex-center size-44 rounded-12 bg-dark">
                <x-iconsax-bol-wallet-3 class="text-white" width="20"/>
            </div>
        </div>

    </div>

    {{-- List / Empty State --}}
    @if(!empty($bookings) and !$bookings->isEmpty())
        <div class="bg-white pt-16 rounded-24">
            <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
                <h3 class="font-16">{{ trans('panel.invited_bookings') }}</h3>
            </div>

            <div id="tableListContainer" class="px-16 pb-16" data-view-data-path="/panel/bookings/invitations">
                <div class="row js-table-body-lists">
                    @foreach($bookings as $bookingRow)
                        @include('design_1.panel.bookings.invitations.card_item', ['booking' => $bookingRow])
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
            'title' => trans('panel.invited_bookings_no_result'),
            'hint' => nl2br(trans('panel.invited_bookings_no_result_hint')),
            'extraClass' => 'mt-0',
        ])
    @endif

@endsection

@push('scripts_bottom')
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>
@endpush
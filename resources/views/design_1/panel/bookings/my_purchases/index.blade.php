@extends('design_1.panel.layouts.panel')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    {{-- Top Stats --}}
    @include('design_1.panel.bookings.my_purchases.top_stats')

    @if(!empty($orders) and !$orders->isEmpty())
        <div class="bg-white pt-16 rounded-24 mt-20">
            <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
                <div class="">
                    <h3 class="font-16">{{ trans('update.booking_purchases_history') }}</h3>
                </div>
            </div>

            {{-- Filters --}}
            @include('design_1.panel.bookings.my_purchases.filters')

            {{-- List Table --}}
            <div id="tableListContainer" class="table-responsive-lg" data-view-data-path="/panel/bookings/purchases">
                <table class="table panel-table">
                    <thead>
                    <tr>
                        <th class="text-left">{{ trans('update.seller') }}</th>
                        <th class="text-center">{{ trans('update.order_id') }}</th>
                        <th class="text-center">{{ trans('public.price') }}</th>
                        <th class="text-center">{{ trans('public.discount') }}</th>
                        <th class="text-center">{{ trans('cart.tax') }}</th>
                        <th class="text-center">{{ trans('financial.total_amount') }}</th>
                        <th class="text-center">{{ trans('public.status') }}</th>
                        <th class="text-center">{{ trans('public.date') }}</th>
                        <th class="text-right">{{ trans('update.controls') }}</th>
                    </tr>
                    </thead>

                    <tbody class="js-table-body-lists">
                    @foreach($orders as $orderRow)
                        @include('design_1.panel.bookings.my_purchases.table_items', ['order' => $orderRow])
                    @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div id="pagination" class="js-ajax-pagination" data-container-id="tableListContainer" data-container-items=".js-table-body-lists">
                    {!! $pagination !!}
                </div>
            </div>
        </div>
    @else
        @include('design_1.panel.includes.no-result',[
           'file_name' => 'booking_purchases.svg',
           'title' => trans('update.booking_purchases_no_result'),
           'hint' => nl2br(trans('update.booking_purchases_no_result_hint')),
        ])
    @endif

@endsection

@push('scripts_bottom')
    <script>
        var viewBookingDetailsModalTitleLang = '{{ trans('update.view_booking_details') }}';
        var closeLang = '{{ trans('public.close') }}';
        var confirmLang = '{{ trans('update.confirm') }}';
        var setCompletedLang = '{{ trans('update.confirm_booking_completed') }}';
        var setCompletedConfirmTextLang = '{{ trans('update.confirm_booking_completed_text') }}';
        var setCompletedSaveSuccessLang = '{{ trans('update.confirm_booking_completed_success') }}';
        var setCompletedSaveErrorLang = '{{ trans('update.confirm_booking_completed_error') }}';
        var addressLang = '{{ trans('update.address') }}';
    </script>

    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

    {{-- Booking purchase page logic (inline, no separate build file needed) --}}
    <script>
        (function () {

            // ---- View booking details modal ----
            $(document).on('click', '.js-view-booking-details', function () {
                var saleId = $(this).data('sale-id');
                var orderId = $(this).data('order-id');

                $.ajax({
                    url: '/panel/bookings/purchases/' + saleId + '/getBookingOrder/' + orderId,
                    method: 'GET',
                    success: function (response) {
                        if (response && response.order) {
                            // TODO: render response.order (item title, specifications,
                            // message_to_seller, tracking_code, address) into your modal markup.
                            // Example skeleton if you have a modal with id="bookingDetailsModal":
                            //
                            // $('#bookingDetailsModalTitle').text(viewBookingDetailsModalTitleLang);
                            // $('#bookingDetailsModalBody').html(buildBookingDetailsHtml(response.order));
                            // $('#bookingDetailsModal').modal('show');
                        }
                    },
                    error: function () {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(setCompletedSaveErrorLang);
                        }
                    }
                });
            });

            // ---- Mark booking as completed (buyer confirms) ----
            $(document).on('click', '.js-set-completed', function () {
                var saleId = $(this).data('sale-id');
                var orderId = $(this).data('order-id');

                if (typeof swal !== 'undefined') {
                    swal({
                        title: setCompletedLang,
                        text: setCompletedConfirmTextLang,
                        type: 'warning',
                        showCancelButton: true,
                        confirmButtonText: confirmLang,
                        cancelButtonText: closeLang,
                    }).then(function (result) {
                        if (result.value) {
                            sendSetCompletedRequest(saleId, orderId);
                        }
                    });
                } else if (confirm(setCompletedConfirmTextLang)) {
                    sendSetCompletedRequest(saleId, orderId);
                }
            });

            function sendSetCompletedRequest(saleId, orderId) {
                $.ajax({
                    url: '/panel/bookings/purchases/' + saleId + '/orderItem/' + orderId + '/setCompleted',
                    method: 'GET',
                    success: function (response) {
                        if (response && response.code === 200) {
                            if (typeof toastr !== 'undefined') {
                                toastr.success(setCompletedSaveSuccessLang);
                            }
                            location.reload();
                        } else {
                            if (typeof toastr !== 'undefined') {
                                toastr.error(setCompletedSaveErrorLang);
                            }
                        }
                    },
                    error: function () {
                        if (typeof toastr !== 'undefined') {
                            toastr.error(setCompletedSaveErrorLang);
                        }
                    }
                });
            }

        })();
    </script>
@endpush
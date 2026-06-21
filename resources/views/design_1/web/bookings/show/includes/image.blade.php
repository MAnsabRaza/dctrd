@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("swiperjs") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("css_stars") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("reviews_and_comments") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("product_show") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("products_lists") }}">
    <style>
        .booking-slot-pill {
            display: inline-flex; align-items: center;
            padding: 6px 14px; border-radius: 8px;
            border: 1.5px solid #e2e8f0; cursor: pointer;
            font-size: 13px; font-weight: 500; color: #334155;
            transition: all .15s; background: #fff;
        }
        .booking-slot-pill:hover { border-color: #2563eb; color: #2563eb; }
        .booking-slot-pill.selected {
            border-color: #2563eb; background: rgba(37,99,235,.06);
            color: #2563eb; font-weight: 700;
        }
        .booking-slot-pill input[type="radio"] { display: none; }
        .slot-selected-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: #f0fdf4; border: 1px solid #86efac;
            border-radius: 10px; padding: 8px 14px;
            font-size: 13px; color: #166534; font-weight: 600;
            margin-top: 12px;
        }
    </style>
@endpush

@section('content')
    <div class="container pb-80 mt-120">
        <div class="row">

            {{-- ── IMAGE ── --}}
            <div class="col-12 col-lg-6">
                @include('design_1.web.bookings.show.includes.image')
            </div>

            {{-- ── MAIN INFO + SLOT PANEL ── --}}
            <div class="col-12 col-lg-6 mt-32 mt-lg-0">
                @include('design_1.web.bookings.show.includes.main_info')
                @include('design_1.web.bookings.show.includes.slot_panel')
            </div>
        </div>

        {{-- ── TABS ── --}}
        <div class="custom-tabs mt-16">
            <div class="product-show-tabs-card position-relative">
                <div class="product-show-tabs-card__mask"></div>
                <div class="position-relative product-show-tabs-card__items d-flex align-items-center gap-20 gap-lg-40 bg-white px-20 rounded-12 z-index-2 w-100">
                    <div class="navbar-item d-flex-center cursor-pointer active" data-tab-toggle data-tab-href="#bookingDescriptionTab">
                        <span>{{ trans('public.description') }}</span>
                    </div>
                    <div class="navbar-item d-flex-center cursor-pointer" data-tab-toggle data-tab-href="#bookingInfoTab">
                        <span>{{ trans('update.details') }}</span>
                    </div>
                    @if(!empty($booking->specifications) and count($booking->specifications))
                        <div class="navbar-item d-flex-center cursor-pointer" data-tab-toggle data-tab-href="#bookingSpecificationsTab">
                            <span>{{ trans('update.product_specifications') }}</span>
                        </div>
                    @endif
                    <div class="navbar-item d-flex-center cursor-pointer" data-tab-toggle data-tab-href="#bookingReviewsTab">
                        <span>{{ trans('product.reviews') }}</span>
                    </div>
                    <div class="navbar-item d-flex-center cursor-pointer" data-tab-toggle data-tab-href="#bookingCommentsTab">
                        <span>{{ trans('panel.comments') }}</span>
                    </div>
                    <div class="navbar-item d-flex-center cursor-pointer" data-tab-toggle data-tab-href="#bookingProviderTab">
                        <span>{{ trans('update.provider') }}</span>
                    </div>
                </div>
            </div>

            <div class="custom-tabs-body mt-16">

                {{-- Description --}}
                <div class="custom-tabs-content active" id="bookingDescriptionTab">
                    @include('design_1.web.bookings.show.includes.tabs.description')
                </div>

                {{-- Info --}}
                <div class="custom-tabs-content" id="bookingInfoTab">
                    @include('design_1.web.bookings.show.includes.tabs.info')
                </div>

                {{-- Specifications --}}
                @if(!empty($booking->specifications) and count($booking->specifications))
                    <div class="custom-tabs-content" id="bookingSpecificationsTab">
                        @include('design_1.web.bookings.show.includes.tabs.specifications')
                    </div>
                @endif

                {{-- Reviews --}}
                <div class="custom-tabs-content" id="bookingReviewsTab">
                    @include('design_1.web.bookings.show.includes.tabs.reviews')
                </div>

                {{-- Comments --}}
                <div class="custom-tabs-content" id="bookingCommentsTab">
                    @include('design_1.web.bookings.show.includes.tabs.comments')
                </div>

                {{-- Provider --}}
                <div class="custom-tabs-content" id="bookingProviderTab">
                    @include('design_1.web.bookings.show.includes.provider_card')
                </div>

            </div>
        </div>

        {{-- Related Bookings --}}
        @if(!empty($relatedBookings) and count($relatedBookings))
            <div class="mt-48">
                <h2 class="font-16 font-weight-bold">{{ trans('update.related_bookings') }}</h2>
                <div class="row">
                    @include('design_1.web.bookings.components.cards.grids.index', [
                        'bookings'          => $relatedBookings,
                        'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mt-16',
                    ])
                </div>
            </div>
        @endif
    </div>
@endsection

@push('scripts_bottom')
    <script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
    <script src="/assets/default/vendors/simplebar/simplebar.min.js"></script>
    <script src="{{ getDesign1ScriptPath("swiper_slider") }}"></script>
    <script>
    (function ($) {
        'use strict';

        var bookingSlug = '{{ $booking->slug }}';
        var bookingId   = {{ $booking->id }};
        var $bookBtn    = $('#bookingAddToCartBtn');
        var selectedSlot = null;

        function updateSlotSummary() {
            if (selectedSlot && selectedSlot.date && selectedSlot.start_time) {
                var d    = new Date(selectedSlot.date);
                var dLbl = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
                var tLbl = selectedSlot.start_time + (selectedSlot.end_time ? ' - ' + selectedSlot.end_time : '');
                $('#selectedSlotText').text(dLbl + ' · ' + tLbl);
                $('#selectedSlotSummary').show();
            } else {
                $('#selectedSlotSummary').hide();
            }
        }

        $(document).on('change', 'input[name="selected_slot"]', function () {
            var $r = $(this);
            selectedSlot = { date: $r.data('date'), start_time: $r.val(), end_time: $r.data('end') };
            $('.booking-slot-pill').removeClass('selected');
            $r.closest('.booking-slot-pill').addClass('selected');
            if (selectedSlot.date) { $('#slotDateInput').val(selectedSlot.date); }
            updateSlotSummary();
        });

        $('#slotDateInput').on('change', function () {
            if (selectedSlot && selectedSlot.date !== $(this).val()) {
                selectedSlot = null;
                $('.booking-slot-pill').removeClass('selected');
                $('input[name="selected_slot"]').prop('checked', false);
                updateSlotSummary();
            }
        });

        $('#checkSlotsBtn').on('click', function () {
            var date       = $('#slotDateInput').val();
            var resourceId = $('#slotResourceId').val() || '';
            if (!date) { showToast('error', 'Error', 'Please select a date'); return; }

            var $btn = $(this).addClass('loadingbar').prop('disabled', true);
            selectedSlot = null;
            updateSlotSummary();

            $.ajax({
                url: '/bookings/' + bookingSlug + '/slots',
                method: 'GET',
                data: { date: date, resource_id: resourceId },
                dataType: 'json'
            }).done(function (res) {
                var slots = res.slots || [];
                var html  = '<h4 class="font-14 font-weight-bold">' + '{{ trans("update.available_slots") }}' + '</h4>';
                if (slots.length) {
                    html += '<div class="d-flex align-items-center flex-wrap gap-8 mt-12">';
                    slots.forEach(function (slot) {
                        html += '<label class="booking-slot-pill">'
                              + '<input type="radio" name="selected_slot"'
                              + ' value="' + slot.start_time + '"'
                              + ' data-end="' + slot.end_time + '"'
                              + ' data-date="' + date + '">'
                              + slot.start_time + ' - ' + slot.end_time
                              + '</label>';
                    });
                    html += '</div>';
                    html += '<p class="font-12 text-gray-500 mt-8">{{ trans("update.select_slot_then_book") }}</p>';
                } else {
                    html += '<div class="mt-12 text-gray-500">{{ trans("update.no_slots_available") }}</div>';
                }
                $('#slotsContainer').html(html);
            }).fail(function (xhr) {
                var err = xhr.responseJSON;
                showToast('error', 'Error', err && err.message ? err.message : 'Could not fetch slots');
            }).always(function () {
                $btn.removeClass('loadingbar').prop('disabled', false);
            });
        });

        $bookBtn.on('click', function () {
            $bookBtn.addClass('loadingbar').prop('disabled', true);
            $.post('/cart/store', {
                _token:     '{{ csrf_token() }}',
                item_id:    bookingId,
                item_name:  'booking_id',
                item_type:  'booking',
                slot_date:  selectedSlot ? selectedSlot.date       : '',
                slot_start: selectedSlot ? selectedSlot.start_time : '',
                slot_end:   selectedSlot ? selectedSlot.end_time   : '',
            }, function (result) {
                if (result && result.title) {
                    showToast(result.status || 'success', result.title, result.msg || '');
                }
                if ($('.js-view-cart-drawer').length) {
                    $('.js-view-cart-drawer').trigger('click');
                } else {
                    setTimeout(function () { window.location.href = '/cart'; }, 800);
                }
            }).fail(function (err) {
                $bookBtn.removeClass('loadingbar').prop('disabled', false);
                var errors = err.responseJSON;
                if (errors && errors.toast_alert) {
                    showToast('error', errors.toast_alert.title, errors.toast_alert.msg);
                } else {
                    showToast('error', 'Error', 'Something went wrong');
                }
            });
        });

        $('body').on('click', '#bookingFavoriteBtn, .bookingFavoriteBtn', function (e) {
            e.preventDefault(); e.stopPropagation();
            var $btn     = $(this);
            var slug     = $btn.data('slug');
            var loginUrl = $btn.data('login-url');
            if (loginUrl) { window.location = loginUrl; return; }
            if (!slug) return;

            $.ajax({ url: '/bookings/' + slug + '/favorite-toggle', method: 'GET', dataType: 'json' })
                .done(function (res) {
                    var added = res && res.status === 'added';
                    $btn.find('.js-empty-fav').toggleClass('d-none', added);
                    $btn.find('.js-full-fav').toggleClass('d-none', !added);
                    var $span = $btn.find('span');
                    if ($span.length) { $span.text(added ? '{{ trans("update.favorited") }}' : '{{ trans("update.add_to_favorites") }}'); }
                }).fail(function (xhr) {
                    if ([401, 302, 419].includes(xhr.status)) { window.location = '/login'; }
                    else { showToast('error', 'Error', 'Could not update favorite'); }
                });
        });
    })(jQuery);
    </script>
@endpush
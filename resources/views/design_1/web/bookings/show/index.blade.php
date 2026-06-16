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

        /* Selected slot summary badge */
        .slot-selected-badge {
            display: inline-flex; align-items: center; gap-6px;
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

            {{-- ════ IMAGE ════ --}}
            <div class="col-12 col-lg-6">
                <div class="product-show-thumbnail-card position-relative bg-gray-100 rounded-24">
                    <img src="{{ $booking->cover_url }}" alt="{{ $booking->title }}"
                         class="img-cover rounded-24 p-16">
                </div>
            </div>

            {{-- ════ DETAIL PANEL ════ --}}
            <div class="col-12 col-lg-6 mt-32 mt-lg-0">
                <div class="card-with-mask">
                    <div class="mask-8-white"></div>
                    <div class="position-relative bg-white p-16 rounded-24 z-index-2">

                        {{-- Favourite --}}
                        <div class="position-absolute" style="top:24px;right:24px;z-index:10;">
                            <div class="bookingFavoriteBtn d-flex align-items-center justify-content-center rounded-circle bg-white border border-gray-200"
                                 style="width:42px;height:42px;cursor:pointer;"
                                 data-slug="{{ $booking->slug }}"
                                 @if(auth()->guest()) data-login-url="/login" @endif>
                                <x-iconsax-lin-heart class="icons js-empty-fav text-gray-500 {{ !empty($isFavorited) ? 'd-none' : '' }}" width="22px" height="22px"/>
                                <x-iconsax-bol-heart class="icons js-full-fav text-danger {{ !empty($isFavorited) ? '' : 'd-none' }}" width="22px" height="22px"/>
                            </div>
                        </div>

                        {{-- Breadcrumb --}}
                        <div class="breadcrumb d-flex align-items-center">
                            <a href="/" class="breadcrumb-item font-14 text-gray-500">{{ getPlatformName() }}</a>
                            <x-iconsax-lin-arrow-right-1 class="icons text-gray-500 mx-8" width="14px" height="14px"/>
                            <a href="/bookings" class="breadcrumb-item font-14 text-gray-500">Booking</a>
                            @if(!empty($booking->category))
                                <x-iconsax-lin-arrow-right-1 class="icons text-gray-500 mx-8" width="14px" height="14px"/>
                                <span class="breadcrumb-item font-14 text-gray-500">{{ $booking->category->title }}</span>
                            @endif
                        </div>

                        {{-- Title --}}
                        <div class="d-flex align-items-center flex-wrap gap-12 mt-12">
                            <h1 class="course-hero__title font-24 font-weight-bold text-dark text-ellipsis">{{ $booking->title }}</h1>
                            @if($booking->featured)
                                <div class="d-flex-center gap-4 p-4 pr-8 rounded-32 bg-warning text-white">
                                    <x-iconsax-bol-star-1 class="icons text-white" width="18px" height="18px"/>
                                    <span class="font-12">Featured</span>
                                </div>
                            @endif
                        </div>

                        {{-- Meta --}}
                        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-lg-between mt-12">
                            <div class="d-flex align-items-center flex-wrap gap-24">
                                @include('design_1.web.components.rate', [
                                    'rate'          => $booking->getRate(),
                                    'rateCount'     => $booking->getRateCount(),
                                    'rateClassName' => '',
                                    'rateCountFont' => 'font-12',
                                ])
                                @if(!empty($booking->creator))
                                    <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank"
                                       class="d-flex align-items-center text-gray-500">
                                        <x-iconsax-lin-profile class="icons text-gray-500" width="16px" height="16px"/>
                                        <span class="ml-4 font-12 font-weight-bold">{{ truncate($booking->creator->full_name, 15) }}</span>
                                    </a>
                                @endif
                                @if($booking->location_enabled && $booking->city)
                                    <div class="d-flex align-items-center text-gray-500">
                                        <x-iconsax-lin-location class="icons text-gray-500" width="16px" height="16px"/>
                                        <span class="ml-4 font-12 font-weight-bold">{{ $booking->city }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Price --}}
                        <div class="d-flex align-items-center font-24 font-weight-bold text-primary mt-24">
                            <span>{{ $booking->price_label }}</span>
                            @if(!empty($booking->price_unit))
                                <span class="font-14 font-weight-400 text-gray-500 ml-8">/ {{ $booking->price_unit }}</span>
                            @endif
                        </div>

                        {{-- Buttons --}}
                        <div class="d-flex align-items-center gap-12 flex-wrap mt-16">
                            <button type="button" id="bookingAddToCartBtn" class="btn btn-primary btn-lg">
                                <x-iconsax-lin-calendar-2 class="icons text-white" width="24px" height="24px"/>
                                <span class="ml-4 text-white">Book Now</span>
                            </button>
                            <button id="bookingFavoriteBtn" type="button"
                                    class="btn btn-outline-secondary btn-lg ml-2 d-flex align-items-center"
                                    data-slug="{{ $booking->slug }}"
                                    @if(auth()->guest()) data-login-url="/login" @endif>
                                <x-iconsax-lin-heart class="js-empty-fav icons text-gray-500 mr-2 {{ (!empty($isFavorited) && $isFavorited) ? 'd-none' : '' }}" width="20px" height="20px"/>
                                <x-iconsax-bol-heart class="js-full-fav icons text-danger mr-2 {{ (!empty($isFavorited) && $isFavorited) ? '' : 'd-none' }}" width="20px" height="20px"/>
                                <span class="font-14">{{ $isFavorited ? 'Favorited' : 'Add to favorites' }}</span>
                            </button>
                        </div>

                        {{-- Selected slot summary (initially hidden) --}}
                        <div id="selectedSlotSummary" style="display:none;" class="mt-12">
                            <div class="slot-selected-badge">
                                <x-iconsax-lin-calendar-2 class="icons" width="14px" height="14px"/>
                                <span id="selectedSlotText" class="ml-6"></span>
                            </div>
                        </div>

                    </div>
                </div>

                {{-- ════ CHECK BOOKING / SLOT PANEL ════ --}}
                <div id="bookingRequest" class="bg-white p-16 rounded-24 mt-24">
                    <h3 class="font-16 font-weight-bold">Check Booking</h3>
                    <p class="font-12 text-gray-500 mt-4 mb-0">Select a date and check available slots.</p>

                    <form method="get" action="{{ $booking->getUrl() }}" class="mt-16" id="slotCheckForm">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="form-group-label">Date</label>
                                    <input type="date" name="date" id="slotDateInput"
                                           class="form-control"
                                           value="{{ request()->get('date', now()->toDateString()) }}"
                                           min="{{ now()->toDateString() }}">
                                </div>
                            </div>
                            @if(!empty($booking->resources) && count($booking->resources))
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="form-group-label">Resource</label>
                                        <select name="resource_id" id="slotResourceId" class="form-control">
                                            <option value="">Any resource</option>
                                            @foreach($booking->resources as $resource)
                                                <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button type="button" id="checkSlotsBtn" class="btn btn-outline-primary btn-lg">
                            Check slots
                        </button>
                    </form>

                    {{-- Slots container --}}
                    <div class="mt-16" id="slotsContainer">
                        @if(!is_null($availableSlots))
                            <h4 class="font-14 font-weight-bold">Available slots</h4>
                            @if(count($availableSlots))
                                <div class="d-flex align-items-center flex-wrap gap-8 mt-12" id="slotPillsWrap">
                                    @foreach($availableSlots as $slot)
                                        <label class="booking-slot-pill">
                                            <input type="radio" name="selected_slot"
                                                   value="{{ $slot['start_time'] }}"
                                                   data-end="{{ $slot['end_time'] }}"
                                                   data-date="{{ request()->get('date') }}">
                                            {{ $slot['start_time'] }} - {{ $slot['end_time'] }}
                                        </label>
                                    @endforeach
                                </div>
                                <p class="font-12 text-gray-500 mt-8">
                                    Select a slot above, then click <strong>Book Now</strong>.
                                </p>
                            @else
                                <div class="mt-12 text-gray-500">No slots are available for this date.</div>
                            @endif
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- ════ TABS ════ --}}
        <div class="custom-tabs mt-16">
            <div class="product-show-tabs-card position-relative">
                <div class="product-show-tabs-card__mask"></div>
                <div class="position-relative product-show-tabs-card__items d-flex align-items-center gap-20 gap-lg-40 bg-white px-20 rounded-12 z-index-2 w-100">
                    <div class="navbar-item d-flex-center cursor-pointer active"
                         data-tab-toggle data-tab-href="#bookingDescriptionTab">
                        <span>{{ trans('public.description') }}</span>
                    </div>
                    <div class="navbar-item d-flex-center cursor-pointer"
                         data-tab-toggle data-tab-href="#bookingInfoTab">
                        <span>Details</span>
                    </div>
                    <div class="navbar-item d-flex-center cursor-pointer"
                         data-tab-toggle data-tab-href="#bookingReviewsTab">
                        <span>{{ trans('product.reviews') }}</span>
                    </div>
                    <div class="navbar-item d-flex-center cursor-pointer"
                         data-tab-toggle data-tab-href="#bookingCommentsTab">
                        <span>{{ trans('panel.comments') }}</span>
                    </div>
                    <div class="navbar-item d-flex-center cursor-pointer"
                         data-tab-toggle data-tab-href="#bookingProviderTab">
                        <span>{{ trans('update.provider') }}</span>
                    </div>
                </div>
            </div>

            <div class="custom-tabs-body mt-16">

                {{-- Description --}}
                <div class="custom-tabs-content active" id="bookingDescriptionTab">
                    <div class="bg-white p-16 rounded-24">
                        <h3 class="font-16">About this booking</h3>
                        @if($booking->description)
                            <div class="product-show__description mt-12">{!! $booking->description !!}</div>
                        @endif
                        @if($booking->requirements)
                            <h3 class="font-16 mt-24">Requirements</h3>
                            <div class="product-show__description mt-12">{!! $booking->requirements !!}</div>
                        @endif
                    </div>
                </div>

                {{-- Info --}}
                <div class="custom-tabs-content" id="bookingInfoTab">
                    <div class="bg-white p-16 rounded-24">
                        <div class="row">
                            @foreach([
                                'Type'            => $booking->booking_type,
                                'Capacity'        => $booking->capacity,
                                'Minimum persons' => $booking->min_persons,
                                'Maximum persons' => $booking->max_persons,
                                'Duration'        => $booking->duration_minutes ? $booking->duration_minutes.' minutes' : null,
                                'Address'         => $booking->full_address,
                                'Instant booking' => $booking->instant_booking ? 'Yes' : 'No',
                            ] as $label => $value)
                                @if(!empty($value))
                                    <div class="col-12 col-md-6 mt-16">
                                        <div class="p-12 rounded-12 border-gray-200">
                                            <div class="font-12 text-gray-500">{{ $label }}</div>
                                            <div class="font-14 font-weight-bold text-dark mt-4">{{ $value }}</div>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>

                        @if($booking->location_enabled && !empty($booking->lat) && !empty($booking->lng))
                            @php
                                $mapLat   = (float) $booking->lat;
                                $mapLng   = (float) $booking->lng;
                                $mapDelta = 0.01;
                                $mapBbox  = implode(',', [
                                    $mapLng - $mapDelta, $mapLat - $mapDelta,
                                    $mapLng + $mapDelta, $mapLat + $mapDelta,
                                ]);
                            @endphp
                            <div class="mt-16 rounded-16 overflow-hidden border-gray-200" style="height:320px;">
                                <iframe title="{{ $booking->title }} map" width="100%" height="100%"
                                        frameborder="0" loading="lazy"
                                        referrerpolicy="no-referrer-when-downgrade"
                                        src="https://www.openstreetmap.org/export/embed.html?bbox={{ $mapBbox }}&layer=mapnik&marker={{ $mapLat }},{{ $mapLng }}">
                                </iframe>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Reviews --}}
                <div class="custom-tabs-content" id="bookingReviewsTab">
                    <div class="bg-white p-16 rounded-24">
                        <h3 class="font-16">{{ trans('product.reviews') }}</h3>
                        @if(!empty($booking->reviews) && count($booking->reviews))
                            @foreach($booking->reviews as $review)
                                <div class="p-12 rounded-12 border-gray-200 {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="font-14 font-weight-bold">{{ $review->customer->full_name ?? 'User' }}</div>
                                        @include('design_1.web.components.rate', ['rate' => $review->average_rating, 'rateCount' => 0, 'rateClassName' => ''])
                                    </div>
                                    <div class="mt-8 text-gray-500">{{ $review->comment }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="mt-12 text-gray-500">No reviews yet.</div>
                        @endif
                    </div>
                </div>

                {{-- Comments --}}
                <div class="custom-tabs-content" id="bookingCommentsTab">
                    <div class="bg-white p-16 rounded-24">
                        <h3 class="font-16">{{ trans('panel.comments') }}</h3>
                        @if(!empty($booking->comments) && count($booking->comments))
                            @foreach($booking->comments as $comment)
                                <div class="p-12 rounded-12 border-gray-200 {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                                    <div class="font-14 font-weight-bold">{{ $comment->user->full_name ?? 'User' }}</div>
                                    <div class="mt-8 text-gray-500">{{ $comment->comment }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="mt-12 text-gray-500">No comments yet.</div>
                        @endif
                    </div>
                </div>

                {{-- Provider --}}
                <div class="custom-tabs-content" id="bookingProviderTab">
                    <div class="bg-white p-16 rounded-24">
                        @if(!empty($booking->creator))
                            <div class="d-flex align-items-center">
                                <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank" class="size-64 rounded-circle">
                                    <img src="{{ $booking->creator->getAvatar(64) }}"
                                         alt="{{ $booking->creator->full_name }}"
                                         class="img-cover rounded-circle">
                                </a>
                                <div class="ml-12">
                                    <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank"
                                       class="font-16 font-weight-bold text-dark">{{ $booking->creator->full_name }}</a>
                                    @if(!empty($booking->creator->bio))
                                        <div class="mt-4 font-12 text-gray-500">{{ $booking->creator->bio }}</div>
                                    @endif
                                </div>
                            </div>
                            @if(!empty($booking->creator->about))
                                <div class="product-show__description mt-16">{!! $booking->creator->about !!}</div>
                            @endif
                        @else
                            <div class="text-gray-500">Provider information is not available.</div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

        {{-- Related --}}
        @if(!empty($relatedBookings) && count($relatedBookings))
            <div class="mt-48">
                <h2 class="font-16 font-weight-bold">Related bookings</h2>
                <div class="row">
                    @include('design_1.web.bookings.components.cards.grids.index', [
                        'bookings'          => $relatedBookings,
                        'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mt-16'
                    ])
                </div>
            </div>
        @endif

    </div>
@endsection

@push('scripts_bottom')
<script src="/assets/default/vendors/swiper/swiper-bundle.min.js"></script>
<script type="text/javascript" src="/assets/default/vendors/simplebar/simplebar.min.js"></script>
<script src="{{ getDesign1ScriptPath("swiper_slider") }}"></script>
<script>
(function ($) {
    'use strict';

    var bookingId   = {{ $booking->id }};
    var bookingSlug = '{{ $booking->slug }}';
    var $bookBtn    = $('#bookingAddToCartBtn');

    // Selected slot object — date, start_time, end_time
    var selectedSlot = null;

    /* ════════════════════════════════════════
       HELPER: selected slot summary badge update
    ════════════════════════════════════════ */
    function updateSlotSummary() {
        if (selectedSlot && selectedSlot.date && selectedSlot.start_time) {
            var d     = new Date(selectedSlot.date);
            var dLbl  = d.toLocaleDateString('en-GB', { day: '2-digit', month: 'short', year: 'numeric' });
            var tLbl  = selectedSlot.start_time + (selectedSlot.end_time ? ' - ' + selectedSlot.end_time : '');
            $('#selectedSlotText').text(dLbl + ' · ' + tLbl);
            $('#selectedSlotSummary').show();
        } else {
            $('#selectedSlotSummary').hide();
        }
    }

    /* ════════════════════════════════════════
       SLOT PILL SELECTION
       — date input auto-sync + summary badge
    ════════════════════════════════════════ */
    $(document).on('change', 'input[name="selected_slot"]', function () {
        var $radio = $(this);
        selectedSlot = {
            date:       $radio.data('date'),
            start_time: $radio.val(),
            end_time:   $radio.data('end'),
        };

        // Pill highlight
        $('.booking-slot-pill').removeClass('selected');
        $radio.closest('.booking-slot-pill').addClass('selected');

        // ── Date input ko selected slot ki date se sync karo ──
        if (selectedSlot.date) {
            $('#slotDateInput').val(selectedSlot.date);
        }

        // Summary badge update
        updateSlotSummary();
    });

    /* ════════════════════════════════════════
       DATE INPUT CHANGE — slot selection reset
       (agar user date change kare toh purana slot clear ho)
    ════════════════════════════════════════ */
    $('#slotDateInput').on('change', function () {
        // Sirf tab clear karo agar selected slot ki date alag ho
        if (selectedSlot && selectedSlot.date !== $(this).val()) {
            selectedSlot = null;
            $('.booking-slot-pill').removeClass('selected');
            $('input[name="selected_slot"]').prop('checked', false);
            updateSlotSummary();
        }
    });

    /* ════════════════════════════════════════
       AJAX SLOT CHECK
    ════════════════════════════════════════ */
    $('#checkSlotsBtn').on('click', function () {
        var date       = $('#slotDateInput').val();
        var resourceId = $('#slotResourceId').val() || '';

        if (!date) { showToast('error', 'Error', 'Please select a date'); return; }

        var $btn = $(this).addClass('loadingbar').prop('disabled', true);

        // Re-check par purana selection clear karo
        selectedSlot = null;
        updateSlotSummary();

        $.ajax({
            url: '/bookings/' + bookingSlug + '/slots',
            method: 'GET',
            data: { date: date, resource_id: resourceId },
            dataType: 'json'
        }).done(function (res) {
            var slots = res.slots || [];
            var html  = '<h4 class="font-14 font-weight-bold">Available slots</h4>';

            if (slots.length) {
                html += '<div class="d-flex align-items-center flex-wrap gap-8 mt-12" id="slotPillsWrap">';
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
                html += '<p class="font-12 text-gray-500 mt-8">Select a slot above, then click <strong>Book Now</strong>.</p>';
            } else {
                html += '<div class="mt-12 text-gray-500">No slots are available for this date.</div>';
            }

            $('#slotsContainer').html(html);

        }).fail(function (xhr) {
            var err = xhr.responseJSON;
            showToast('error', 'Error', err && err.message ? err.message : 'Could not fetch slots');
        }).always(function () {
            $btn.removeClass('loadingbar').prop('disabled', false);
        });
    });

    /* ════════════════════════════════════════
       BOOK NOW — cart mein add karo
    ════════════════════════════════════════ */
    $bookBtn.on('click', function () {
        $bookBtn.addClass('loadingbar').prop('disabled', true);

        $.post('/cart/store', {
            _token:     '{{ csrf_token() }}',
            item_id:    bookingId,
            item_name:  'booking_id',
            item_type:  'booking',
            // Slot selected hai toh bhejo, warna empty strings
            slot_date:  selectedSlot ? selectedSlot.date       : '',
            slot_start: selectedSlot ? selectedSlot.start_time : '',
            slot_end:   selectedSlot ? selectedSlot.end_time   : '',
        }, function (result) {
            if (result && result.title) {
                showToast(result.status || 'success', result.title, result.msg || '');
            }
            if ($('.js-view-cart-drawer').length) {
                $('.js-view-cart-drawer').trigger('click');
            } else if ($('.cart-drawer').length) {
                $('.cart-drawer').addClass('show');
            } else {
                setTimeout(function () { window.location.href = '/cart'; }, 800);
            }
        }).fail(function (err) {
            $bookBtn.removeClass('loadingbar').prop('disabled', false);
            var errors = err.responseJSON;
            if (errors && errors.toast_alert) {
                showToast('error', errors.toast_alert.title, errors.toast_alert.msg);
            } else if (errors && errors.msg) {
                showToast('error', errors.title, errors.msg);
            } else {
                showToast('error', 'Error', 'Something went wrong');
            }
        });
    });

    /* ════════════════════════════════════════
       FAVOURITE TOGGLE
    ════════════════════════════════════════ */
    $('body').on('click', '#bookingFavoriteBtn, .bookingFavoriteBtn', function (e) {
        e.preventDefault(); e.stopPropagation();
        var $btn      = $(this);
        var slug      = $btn.data('slug');
        var loginUrl  = $btn.data('login-url');
        var $emptyIco = $btn.find('.js-empty-fav');
        var $fullIco  = $btn.find('.js-full-fav');

        if (loginUrl) { window.location = loginUrl; return; }
        if (!slug) return;

        $.ajax({ url: '/bookings/' + slug + '/favorite-toggle', method: 'GET', dataType: 'json' })
            .done(function (res) {
                if (res && res.status === 'added') {
                    $emptyIco.addClass('d-none'); $fullIco.removeClass('d-none');
                    $btn.find('span').text('Favorited');
                } else {
                    $emptyIco.removeClass('d-none'); $fullIco.addClass('d-none');
                    $btn.find('span').text('Add to favorites');
                }
            }).fail(function (xhr) {
                if ([401, 302, 419].includes(xhr.status)) { window.location = '/login'; }
                else { showToast('error', 'Error', 'Could not update favorite'); }
            });
    });

})(jQuery);
</script>
@endpush
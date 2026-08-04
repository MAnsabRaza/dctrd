@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("swiperjs") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("css_stars") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("reviews_and_comments") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("product_show") }}">
@endpush

@section('content')
    <div class="container pb-80 mt-120">
        <div class="row">

            {{-- IMAGE --}}
            <div class="col-12 col-lg-6">
                @include('design_1.web.bookings.show.includes.booking_image')
            </div>

          {{-- MAIN INFO + SLOT PANEL --}}
<div class="col-12 col-lg-6 mt-32 mt-lg-0">
    @include('design_1.web.bookings.show.includes.main_info')
    @include('design_1.web.bookings.show.includes.slot_panel')
    @include('partials.qr-share-box', ['item' => $booking])
</div>
        </div>

        {{-- TABS --}}
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

                    @if(!empty($bookingSpecifications) and count($bookingSpecifications))
                        <div class="navbar-item d-flex-center cursor-pointer"
                             data-tab-toggle data-tab-href="#bookingSpecificationsTab">
                            <span>{{ trans('update.product_specifications') }}</span>
                        </div>
                    @endif

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

                <div class="custom-tabs-content active" id="bookingDescriptionTab">
                    @include('design_1.web.bookings.show.includes.tabs.description')
                </div>

                <div class="custom-tabs-content" id="bookingInfoTab">
                    @include('design_1.web.bookings.show.includes.tabs.info')
                </div>

                @if(!empty($bookingSpecifications) and count($bookingSpecifications))
                    <div class="custom-tabs-content" id="bookingSpecificationsTab">
                        @include('design_1.web.bookings.show.includes.tabs.specifications')
                    </div>
                @endif

                <div class="custom-tabs-content" id="bookingReviewsTab">
                    @include('design_1.web.bookings.show.includes.tabs.reviews')
                </div>

                <div class="custom-tabs-content" id="bookingCommentsTab">
                    @include('design_1.web.bookings.show.includes.tabs.comments')
                </div>

                <div class="custom-tabs-content" id="bookingProviderTab">
                    @include('design_1.web.bookings.show.includes.provider_card')
                </div>

            </div>
        </div>

        {{-- Related --}}
        @if(!empty($relatedBookings) and count($relatedBookings))
            <div class="mt-48">
                <h2 class="font-16 font-weight-bold">Related bookings</h2>
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

    var bookingSlug  = '{{ $booking->slug }}';
    var bookingId    = {{ $booking->id }};
    var selectedSlot = null;
    var bookingDateForSlots = '{{ request()->get('date', \Carbon\Carbon::now($bookingTimezone ?? config('app.timezone'))->toDateString()) }}';
    var serverNowTs = {{ \Carbon\Carbon::now(function_exists('getGeneralSettings') ? (getGeneralSettings('default_time_zone') ?: config('app.timezone')) : config('app.timezone'))->timestamp * 1000 }};
    var clientLoadTs = Date.now();

    function disableExpiredSlots() {
        var todayStr = new Date(serverNowTs + (Date.now() - clientLoadTs))
            .toISOString().slice(0, 10);

        // Sirf "today" ke liye chalao — future dates ka koi slot expire nahi hota
        var selectedDate = $('#slotDateInput').val() || bookingDateForSlots;
        if (selectedDate !== todayStr) return;

        var nowMs = serverNowTs + (Date.now() - clientLoadTs);

        $('input[name="selected_slot"]').each(function () {
            var $radio = $(this);
            var slotDate = $radio.data('date');
            var startTime = $radio.val();
            if (!slotDate || !startTime) return;

            var slotStartMs = new Date(slotDate + 'T' + startTime + ':00').getTime();

            if (slotStartMs <= nowMs) {
                var $pill = $radio.closest('.booking-slot-pill');
                $pill.addClass('disabled').css({ opacity: 0.4, pointerEvents: 'none' });
                $radio.prop('disabled', true);

                if ($radio.prop('checked')) {
                    clearSelectedSlot();
                    showBookingFieldError('slot_start', 'This slot has passed. Please select another slot.');
                }
            }
        });
    }

    // Har 30 second check karo taake time guzarte hi slot disable ho jaye
    setInterval(disableExpiredSlots, 30000);
    disableExpiredSlots(); // page load pe bhi ek dafa chala do

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

    function clearSelectedSlot() {
        selectedSlot = null;
        $('.booking-slot-pill').removeClass('selected');
        $('input[name="selected_slot"]').prop('checked', false);
        updateSlotSummary();
    }

    function clearDisplayedSlots(message) {
        clearSelectedSlot();
        $('#slotsContainer').html(message ? '<div class="mt-12 text-gray-500">' + message + '</div>' : '');
    }

    function clearBookingErrors() {
        $('.js-booking-field-error').text('').hide();
        $('#slotDateInput, #slotResourceId').removeClass('is-invalid');
        $('#availabilityMessage').hide().removeClass('alert alert-danger alert-success').text('');
    }

    function showBookingFieldError(field, message) {
        var normalizedField = field === 'date' ? 'slot_date' : field;
        var $error = $('.js-booking-field-error[data-field="' + normalizedField + '"]');

        if (!$error.length && (normalizedField === 'slot_end' || normalizedField === 'selected_slot')) {
            $error = $('.js-booking-field-error[data-field="slot_start"]');
        }

        if ($error.length) {
            $error.text(message).show();
        }

        if (normalizedField === 'slot_date') {
            $('#slotDateInput').addClass('is-invalid');
        } else if (normalizedField === 'resource_id') {
            $('#slotResourceId').addClass('is-invalid');
        }
    }

    function setBookButtonLoading($btn, isLoading) {
        if (!$btn.data('original-text')) {
            $btn.data('original-text', $btn.html());
        }

        if (isLoading) {
            $btn.addClass('loadingbar').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-8" role="status" aria-hidden="true"></span><span class="text-white">Loading...</span>');
        } else {
            $btn.removeClass('loadingbar').prop('disabled', false).html($btn.data('original-text'));
        }
    }

    function showBookingMessage(type, message) {
        $('#availabilityMessage')
            .removeClass('alert-success alert-danger')
            .addClass('alert alert-' + type)
            .text(message)
            .show();
    }

    // Slot radio select hone par summary update
    $(document).on('change', 'input[name="selected_slot"]', function () {
        var $r = $(this);
        selectedSlot = { date: $r.data('date'), start_time: $r.val(), end_time: $r.data('end'), resource_id: $r.data('resource') || '' };
        $('.booking-slot-pill').removeClass('selected');
        $r.closest('.booking-slot-pill').addClass('selected');
        if (selectedSlot.date) { $('#slotDateInput').val(selectedSlot.date); }
        updateSlotSummary();
    });

    // Date change hone par purani selection reset
    $('#slotDateInput').on('change', function () {
        clearDisplayedSlots('Please check slots for the selected date.');
    });

    $('#slotResourceId').on('change', function () {
        clearBookingErrors();
        clearDisplayedSlots('Please check slots for the selected resource.');

        if ($('#slotDateInput').val() && $(this).val()) {
            $('#checkSlotsBtn').trigger('click');
        }
    });

    // Check Slots button
    $('#checkSlotsBtn').on('click', function () {
        var date       = $('#slotDateInput').val();
        var resourceId = $('#slotResourceId').val() || '';
        clearBookingErrors();
        if (!date) { showToast('error', 'Error', 'Please select a date'); return; }
        if ($('#slotResourceId').length && !resourceId) {
            showBookingFieldError('resource_id', 'Please select a resource.');
            clearDisplayedSlots('Select a resource to see available slots.');
            return;
        }

        var $btn = $(this).addClass('loadingbar').prop('disabled', true);
        clearSelectedSlot();

        $.ajax({
            url: '/bookings/' + bookingSlug + '/slots',
            method: 'GET',
            data: { date: date, resource_id: resourceId },
            dataType: 'json'
        }).done(function (res) {
            var slots = res.slots || [];
            var html  = '<h4 class="font-14 font-weight-bold">Available slots</h4>';
            if (slots.length) {
                html += '<div class="d-flex align-items-center flex-wrap gap-8 mt-12">';
                slots.forEach(function (slot) {
                    html += '<label class="booking-slot-pill">'
                          + '<input type="radio" name="selected_slot"'
                          + ' value="' + slot.start_time + '"'
                          + ' data-end="' + slot.end_time + '"'
                          + ' data-date="' + date + '"'
                          + ' data-resource="' + resourceId + '">'
                          + slot.start_time + ' - ' + slot.end_time
                          + '</label>';
                });
                html += '</div>';
            } else {
                html += '<div class="mt-12 text-gray-500">No slots available for this date.</div>';
            }
           $('#slotsContainer').html(html);
           disableExpiredSlots(); 
        }).fail(function (xhr) {
            var err = xhr.responseJSON;
            showToast('error', 'Error', err && err.message ? err.message : 'Could not fetch slots');
        }).always(function () {
            $btn.removeClass('loadingbar').prop('disabled', false);
        });
    });

    // ✅ SIRF YEH EK BUTTON — "Book Now"
    // Agar slot select nahi hai -> panel tak scroll karo.
    // Agar slot select hai -> poori booking flow (check-availability + cart + redirect) chalao.
    $('#bookingAddToCartBtn').on('click', function () {
        var $bookBtn = $(this);
        if ($bookBtn.prop('disabled')) {
            return;
        }

        clearBookingErrors();

        if (!selectedSlot || !selectedSlot.date || !selectedSlot.start_time) {
            showBookingFieldError('slot_start', 'Please select an available slot.');
            document.getElementById('bookingSlotPanel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        if ($('#slotResourceId').length && !$('#slotResourceId').val()) {
            showBookingFieldError('resource_id', 'Please select a resource.');
            document.getElementById('bookingSlotPanel')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            return;
        }

        if ($('#slotResourceId').length && selectedSlot.resource_id != ($('#slotResourceId').val() || '')) {
            clearDisplayedSlots('Please check slots for the selected resource.');
            showBookingFieldError('slot_start', 'Please select an available slot for this resource.');
            return;
        }

        setBookButtonLoading($bookBtn, true);

        $.ajax({
            url: '/bookings/' + bookingSlug + '/check-availability',
            method: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                date: selectedSlot.date,
                start_time: selectedSlot.start_time,
                end_time: selectedSlot.end_time,
                resource_id: $('#slotResourceId').val() || ''
            },
            dataType: 'json'
        }).done(function (res) {

            if (!res.available) {
                showBookingMessage('danger', res.message || 'This time slot is no longer available. Please select another slot.');
                setBookButtonLoading($bookBtn, false);
                return;
            }

            showBookingMessage('success', res.message || 'This slot is available.');

            $.post('/bookings/direct-payment', {
                _token:      '{{ csrf_token() }}',
                item_id:     bookingId,
                item_name:   'booking_id',
                item_type:   'booking',
                slot_date:   selectedSlot.date,
                slot_start:  selectedSlot.start_time,
                slot_end:    selectedSlot.end_time,
                resource_id: $('#slotResourceId').val() || '',
                quantity:    1
            }, function (result) {
                if (result && result.title) {
                    showToast(result.status || 'success', result.title, result.msg || '');
                }
                if (result && result.status === 'error') {
                    showBookingMessage('danger', result.msg || 'Could not add this booking to cart.');
                    setBookButtonLoading($bookBtn, false);
                    return;
                }
                showBookingMessage('success', result.msg || 'Booking added to cart.');
                window.location.href = (result && result.redirect_to) ? result.redirect_to : '/cart';
            }).fail(function (err) {
                setBookButtonLoading($bookBtn, false);
                var response = err.responseJSON || {};

                if (err.status === 401 && response.redirect_to) {
                    window.location.href = response.redirect_to;
                    return;
                }

                if (response.errors) {
                    Object.keys(response.errors).forEach(function (field) {
                        showBookingFieldError(field, response.errors[field][0]);
                    });
                    showBookingMessage('danger', response.message || 'Please fix the highlighted fields.');
                } else if (response.toast_alert) {
                    showToast('error', response.toast_alert.title, response.toast_alert.msg);
                    showBookingMessage('danger', response.toast_alert.msg || 'Could not add this booking to cart.');
                } else {
                    showToast('error', 'Error', response.message || 'Something went wrong. Please try again.');
                    showBookingMessage('danger', response.message || 'Something went wrong. Please try again.');
                }
            });

        }).fail(function (xhr) {
            setBookButtonLoading($bookBtn, false);
            var response = xhr.responseJSON || {};

            if (response.errors) {
                Object.keys(response.errors).forEach(function (field) {
                    showBookingFieldError(field, response.errors[field][0]);
                });
            }

            showBookingMessage('danger', response.message || 'Could not verify availability, please try again.');
            showToast('error', 'Error', response.message || 'Could not verify availability, please try again.');
        });
    });

    // Favourite toggle
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
                if ($span.length) {
                    $span.text(added ? 'Favorited' : 'Add to favorites');
                }
            }).fail(function (xhr) {
                if ([401, 302, 419].includes(xhr.status)) { window.location = '/login'; }
                else { showToast('error', 'Error', 'Could not update favorite'); }
            });
    });

})(jQuery);
</script>
@endpush

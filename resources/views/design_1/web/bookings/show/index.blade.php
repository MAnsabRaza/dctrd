@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="/assets/default/vendors/swiper/swiper-bundle.min.css">
    <link rel="stylesheet" href="/assets/default/vendors/simplebar/simplebar.css">
    <link rel="stylesheet" href="{{ getDesign1StylePath("swiperjs") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("css_stars") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("reviews_and_comments") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("product_show") }}">
    <link rel="stylesheet" href="{{ getDesign1StylePath("products_lists") }}">
@endpush

@section('content')
    <div class="container pb-80 mt-120">
        <div class="row">
            <div class="col-12 col-lg-6">
                <div class="product-show-thumbnail-card position-relative bg-gray-100 rounded-24">
                    <img src="{{ $booking->cover_url }}" alt="{{ $booking->title }}" class="img-cover rounded-24 p-16">
                </div>
            </div>

            <div class="col-12 col-lg-6 mt-32 mt-lg-0">
                <div class="card-with-mask">
                    <div class="mask-8-white"></div>

                    <div class="position-relative bg-white p-16 rounded-24 z-index-2">
                        <div class="breadcrumb d-flex align-items-center">
                            <a href="/" class="breadcrumb-item font-14 text-gray-500">{{ getPlatformName() }}</a>
                            <x-iconsax-lin-arrow-right-1 class="icons text-gray-500 mx-8" width="14px" height="14px"/>
                            <a href="/bookings" class="breadcrumb-item font-14 text-gray-500">Booking</a>

                            @if(!empty($booking->category))
                                <x-iconsax-lin-arrow-right-1 class="icons text-gray-500 mx-8" width="14px" height="14px"/>
                                <span class="breadcrumb-item font-14 text-gray-500">{{ $booking->category->title }}</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center flex-wrap gap-12 mt-12">
                            <h1 class="course-hero__title font-24 font-weight-bold text-dark text-ellipsis">{{ $booking->title }}</h1>

                            @if($booking->featured)
                                <div class="d-flex-center gap-4 p-4 pr-8 rounded-32 bg-warning text-white">
                                    <x-iconsax-bol-star-1 class="icons text-white" width="18px" height="18px"/>
                                    <span class="font-12">Featured</span>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex flex-column flex-lg-row align-items-start align-items-lg-center justify-content-lg-between mt-12">
                            <div class="d-flex align-items-center flex-wrap gap-24">
                                @include('design_1.web.components.rate', [
                                    'rate' => $booking->getRate(),
                                    'rateCount' => $booking->getRateCount(),
                                    'rateClassName' => '',
                                    'rateCountFont' => 'font-12',
                                ])

                                @if(!empty($booking->creator))
                                    <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank" class="d-flex align-items-center text-gray-500">
                                        <x-iconsax-lin-profile class="icons text-gray-500" width="16px" height="16px"/>
                                        <span class="ml-4 font-12 font-weight-bold">{{ truncate($booking->creator->full_name, 15) }}</span>
                                    </a>
                                @endif

                                @if($booking->location_enabled and $booking->city)
                                    <div class="d-flex align-items-center text-gray-500">
                                        <x-iconsax-lin-location class="icons text-gray-500" width="16px" height="16px"/>
                                        <span class="ml-4 font-12 font-weight-bold">{{ $booking->city }}</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="d-flex align-items-center font-24 font-weight-bold text-primary mt-24">
                            <span>{{ $booking->price_label }}</span>
                            @if(!empty($booking->price_unit))
                                <span class="font-14 font-weight-400 text-gray-500 ml-8">/ {{ $booking->price_unit }}</span>
                            @endif
                        </div>

                        <div class="d-flex align-items-center gap-12 flex-wrap mt-16">
                            <form action="/cart/store" method="post" id="bookingAddToCartForm">
                                {{ csrf_field() }}

                                <input type="hidden" name="item_id" value="{{ $booking->id }}">
                                <input type="hidden" name="item_name" value="booking_id">
                                <input type="hidden" name="item_type" value="booking">

                                <button type="submit" class="btn btn-primary btn-lg">
                                    <x-iconsax-lin-calendar-2 class="icons text-white" width="24px" height="24px"/>
                                    <span class="ml-4 text-white">Book Now</span>
                                </button>
                            </form>

                            <button id="bookingFavoriteBtn" type="button" class="btn btn-outline-secondary btn-lg ml-2 d-flex align-items-center" data-slug="{{ $booking->slug }}">
                                <x-iconsax-lin-heart class="js-empty-fav icons text-gray-500 mr-2 {{ (!empty($isFavorited) && $isFavorited) ? 'd-none' : '' }}" width="20px" height="20px"/>
                                <x-iconsax-bol-heart class="js-full-fav icons text-danger mr-2 {{ (!empty($isFavorited) && $isFavorited) ? '' : 'd-none' }}" width="20px" height="20px"/>
                                <span class="font-14">{{ $isFavorited ? 'Favorited' : 'Add to favorites' }}</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div id="bookingRequest" class="bg-white p-16 rounded-24 mt-24">
                    <h3 class="font-16 font-weight-bold">Check Booking</h3>
                    <form method="get" action="{{ $booking->getUrl() }}" class="mt-16">
                        <div class="row">
                            <div class="col-12 col-md-6">
                                <div class="form-group">
                                    <label class="form-group-label">Date</label>
                                    <input type="date" name="date" class="form-control" value="{{ request()->get('date', now()->toDateString()) }}">
                                </div>
                            </div>

                            @if(!empty($booking->resources) and count($booking->resources))
                                <div class="col-12 col-md-6">
                                    <div class="form-group">
                                        <label class="form-group-label">Resource</label>
                                        <select name="resource_id" class="form-control">
                                            <option value="">Any resource</option>
                                            @foreach($booking->resources as $resource)
                                                <option value="{{ $resource->id }}">{{ $resource->name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                            @endif
                        </div>

                        <button type="submit" class="btn btn-outline-primary btn-lg">Check slots</button>
                    </form>

                    @if(!is_null($availableSlots))
                        <div class="mt-16">
                            <h4 class="font-14 font-weight-bold">Available slots</h4>

                            @if(count($availableSlots))
                                <div class="d-flex align-items-center flex-wrap gap-8 mt-12">
                                    @foreach($availableSlots as $slot)
                                        <div class="px-12 py-8 rounded-8 border-gray-200 font-12 text-dark">
                                            {{ $slot['start_time'] }} - {{ $slot['end_time'] }}
                                        </div>
                                    @endforeach
                                </div>
                            @else
                                <div class="mt-12 text-gray-500">No slots are available for this date.</div>
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="custom-tabs mt-16">
            <div class="product-show-tabs-card position-relative">
                <div class="product-show-tabs-card__mask"></div>

                <div class="position-relative product-show-tabs-card__items d-flex align-items-center gap-20 gap-lg-40 bg-white px-20 rounded-12 z-index-2 w-100">
                    <div class="navbar-item d-flex-center cursor-pointer active" data-tab-toggle data-tab-href="#bookingDescriptionTab">
                        <span>{{ trans('public.description') }}</span>
                    </div>

                    <div class="navbar-item d-flex-center cursor-pointer" data-tab-toggle data-tab-href="#bookingInfoTab">
                        <span>Details</span>
                    </div>

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

                <div class="custom-tabs-content" id="bookingInfoTab">
                    <div class="bg-white p-16 rounded-24">
                        <div class="row">
                            @foreach([
                                'Type' => $booking->booking_type,
                                'Capacity' => $booking->capacity,
                                'Minimum persons' => $booking->min_persons,
                                'Maximum persons' => $booking->max_persons,
                                'Duration' => $booking->duration_minutes ? $booking->duration_minutes.' minutes' : null,
                                'Address' => $booking->full_address,
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

                        @if($booking->location_enabled and !empty($booking->lat) and !empty($booking->lng))
                            @php
                                $mapLat = (float) $booking->lat;
                                $mapLng = (float) $booking->lng;
                                $mapDelta = 0.01;
                                $mapBbox = implode(',', [
                                    $mapLng - $mapDelta,
                                    $mapLat - $mapDelta,
                                    $mapLng + $mapDelta,
                                    $mapLat + $mapDelta,
                                ]);
                            @endphp

                            <div class="mt-16 rounded-16 overflow-hidden border-gray-200" style="height: 320px;">
                                <iframe
                                    title="{{ $booking->title }} map"
                                    width="100%"
                                    height="100%"
                                    frameborder="0"
                                    loading="lazy"
                                    referrerpolicy="no-referrer-when-downgrade"
                                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $mapBbox }}&layer=mapnik&marker={{ $mapLat }},{{ $mapLng }}">
                                </iframe>
                            </div>
                        @endif
                    </div>
                </div>

                <div class="custom-tabs-content" id="bookingReviewsTab">
                    <div class="bg-white p-16 rounded-24">
                        <h3 class="font-16">{{ trans('product.reviews') }}</h3>

                        @if(!empty($booking->reviews) and count($booking->reviews))
                            @foreach($booking->reviews as $review)
                                <div class="p-12 rounded-12 border-gray-200 {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                                    <div class="d-flex align-items-center justify-content-between">
                                        <div class="font-14 font-weight-bold">{{ $review->customer->full_name ?? 'User' }}</div>
                                        @include('design_1.web.components.rate', [
                                            'rate' => $review->average_rating,
                                            'rateCount' => 0,
                                            'rateClassName' => '',
                                        ])
                                    </div>
                                    <div class="mt-8 text-gray-500">{{ $review->comment }}</div>
                                </div>
                            @endforeach
                        @else
                            <div class="mt-12 text-gray-500">No reviews yet.</div>
                        @endif
                    </div>
                </div>

                <div class="custom-tabs-content" id="bookingCommentsTab">
                    <div class="bg-white p-16 rounded-24">
                        <h3 class="font-16">{{ trans('panel.comments') }}</h3>

                        @if(!empty($booking->comments) and count($booking->comments))
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

                <div class="custom-tabs-content" id="bookingProviderTab">
                    <div class="bg-white p-16 rounded-24">
                        @if(!empty($booking->creator))
                            <div class="d-flex align-items-center">
                                <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank" class="size-64 rounded-circle">
                                    <img src="{{ $booking->creator->getAvatar(64) }}" alt="{{ $booking->creator->full_name }}" class="img-cover rounded-circle">
                                </a>

                                <div class="ml-12">
                                    <a href="{{ $booking->creator->getProfileUrl() }}" target="_blank" class="font-16 font-weight-bold text-dark">{{ $booking->creator->full_name }}</a>
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

        @if(!empty($relatedBookings) and count($relatedBookings))
            <div class="mt-48">
                <h2 class="font-16 font-weight-bold">Related bookings</h2>
                <div class="row">
                    @include('design_1.web.bookings.components.cards.grids.index', ['bookings' => $relatedBookings, 'gridCardClassName' => 'col-12 col-md-6 col-lg-4 mt-16'])
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

            $('body').on('submit', '#bookingAddToCartForm', function (e) {
                e.preventDefault();

                var $form = $(this);
                var $btn = $form.find('button[type="submit"]');

                $btn.addClass('loadingbar').prop('disabled', true);

                $.post($form.attr('action'), $form.serialize(), function (result) {
                    // show toast if available
                    if (result && result.title) {
                        showToast(result.status || 'success', result.title, result.msg || '');
                    }

                    // open cart drawer if drawer exists
                    if ($('.js-view-cart-drawer').length) {
                        $('.js-view-cart-drawer').trigger('click');
                    } else if ($('.cart-drawer').length) {
                        $('.cart-drawer').addClass('show');
                    } else {
                        // fallback: reload to show cart
                        setTimeout(function () { window.location.reload(); }, 800);
                    }
                }).fail(function (err) {
                    $btn.removeClass('loadingbar').prop('disabled', false);
                    var errors = err.responseJSON;
                    if (errors && errors.toast_alert) {
                        showToast('error', errors.toast_alert.title, errors.toast_alert.msg)
                    } else if (errors && errors.msg) {
                        showToast('error', errors.title, errors.msg);
                    } else {
                        showToast('error', 'Error', 'Something went wrong');
                    }
                });
            });

            // Favorite toggle
            $('body').on('click', '#bookingFavoriteBtn', function (e) {
                e.preventDefault();

                var $btn = $(this);
                var slug = $btn.data('slug');
                var $emptyIcon = $btn.find('.js-empty-fav');
                var $fullIcon = $btn.find('.js-full-fav');

                if (!slug) {
                    return;
                }

                $.get('/bookings/' + slug + '/favorite-toggle')
                    .done(function (res) {
                        if (res && res.status === 'added') {
                            $emptyIcon.addClass('d-none');
                            $fullIcon.removeClass('d-none');
                            $btn.find('span').text('Favorited');
                        } else {
                            $emptyIcon.removeClass('d-none');
                            $fullIcon.addClass('d-none');
                            $btn.find('span').text('Add to favorites');
                        }
                    })
                    .fail(function (xhr) {
                        if (xhr.status === 401 || xhr.status === 302) {
                            // redirect to login
                            window.location = '/login';
                        } else {
                            showToast('error', 'Error', 'Could not update favorite');
                        }
                    });
            });

        })(jQuery)
    </script>
@endpush

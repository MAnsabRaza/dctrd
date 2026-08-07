<div class="bg-white p-16 rounded-24">
    @php
        $reviewOptions = ['booking_quality', 'provider_quality', 'value_for_money', 'location_quality'];
    @endphp

    {{-- Rate summary --}}
    @include('design_1.web.components.reviews.rate_card', [
        'itemRow'       => $booking,
        'reviewOptions' => $reviewOptions,
    ])

    {{-- Submit form --}}
    @include('design_1.web.components.reviews.submit_form', [
        'itemRow'        => $booking,
        'itemName'       => 'booking_id',
        'reviewOptions'  => $reviewOptions,
        'reviewFormPath' => '/bookings/reviews/store',
        'hasBought'      => method_exists($booking, 'checkUserHasBought') ? $booking->checkUserHasBought() : false,
        'authUser'       => auth()->user(),
    ])

    {{-- Reviews list --}}
    <div class="js-all-reviews-container mt-16" data-load-more-url="/bookings/{{ $booking->slug }}/reviews/load-more" data-page="1">
        @include('design_1.web.components.reviews.all_cards', [
            'reviews' => $booking->reviews()
                            ->where('status', 'active')
                            ->with([
                                'comments' => fn ($q) => $q->where('status', 'active'),
                                'creator',
                            ])
                            ->orderBy('created_at', 'desc')
                            ->limit(10)
                            ->get(),
            'deleteUrlPrefix' => '/bookings/reviews',
        ])
    </div>

    @php
        $bookingReviewsCount = $booking->reviews()->where('status', 'active')->count();
    @endphp

    @if($bookingReviewsCount > 10)
        <button type="button" class="js-load-more-booking-reviews btn btn-lg btn-outline-primary mt-16 w-100">
            {{ trans('public.load_more') }}
        </button>
    @endif

    @if($bookingReviewsCount < 1)
        <div class="mt-12 text-gray-500 js-no-reviews-msg">{{ trans('update.no_reviews_yet') }}</div>
    @endif
</div>

@push('scripts_bottom')
<script>
(function ($) {
    'use strict';
    $(document).on('click', '.js-load-more-booking-reviews', function () {
        var $btn = $(this);
        var $container = $('.js-all-reviews-container');
        var url = $container.data('load-more-url');
        var page = parseInt($container.data('page'), 10) + 1;
        $btn.prop('disabled', true).addClass('loadingbar');
        $.ajax({
            url: url,
            method: 'POST',
            data: { page: page, _token: '{{ csrf_token() }}' },
            dataType: 'json'
        }).done(function (res) {
            if (res.html) {
                $container.append(res.html);
                $container.data('page', page);
            }
            if (!res.has_more) {
                $btn.remove();
            }
        }).always(function () {
            $btn.prop('disabled', false).removeClass('loadingbar');
        });
    });
})(jQuery);
</script>
@endpush
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
        'hasBought'      => $booking->checkUserHasBought(),
    ])

    {{-- Reviews list --}}
    @if(!empty($bookingReviews) and count($bookingReviews['reviews']))
        <div class="js-course-reviews-container">
            @include('design_1.web.components.reviews.all_cards', [
                'reviews' => $bookingReviews['reviews'],
                'deleteUrlPrefix' => '/bookings/reviews',
            ])
        </div>

        @if(!empty($bookingReviews['has_more']))
            <div class="d-flex-center mt-16">
                <button type="button" class="js-review-load-more-btn d-flex-center py-16 px-24 rounded-12 border-dashed border-gray-300 text-gray-500 bg-white bg-hover-gray-100 cursor-pointer" data-path="/bookings/{{ $booking->slug }}/reviews/load-more">
                    <x-iconsax-lin-rotate-left class="icons text-gray-500" width="16px" height="16px"/>
                    <span class="ml-4">{{ trans('update.load_more') }}</span>
                </button>
            </div>
        @endif
    @endif
</div>

<div class="js-reply-to-review-html d-none">
    @include('design_1.web.components.reviews.reply_form', [
        'itemId' => $booking->id,
        'itemName' => 'booking_id',
        'reviewReplyFormPath' => '/bookings/reviews/store-reply-comment',
    ])
</div>

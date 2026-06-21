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
        'itemRow'          => $booking,
        'itemName'         => 'booking_id',
        'reviewOptions'    => $reviewOptions,
        'reviewFormPath'   => '/bookings/reviews/store',
        'hasBought'        => method_exists($booking, 'checkUserHasBought') ? $booking->checkUserHasBought() : false,
    ])

    {{-- Reviews list --}}
    @if(!empty($booking->reviews) and count($booking->reviews))
        <div class="js-booking-reviews-container">
            @foreach($booking->reviews as $review)
                <div class="p-12 rounded-12 border-gray-200 {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="font-14 font-weight-bold">
                            {{ optional($review->reviewer)->full_name ?? optional($review->customer)->full_name ?? trans('public.user') }}
                        </div>
                        @include('design_1.web.components.rate', [
                            'rate'          => $review->average_rating ?? $review->rates ?? 0,
                            'rateCount'     => 0,
                            'rateClassName' => '',
                        ])
                    </div>
                    @if(!empty($review->comment))
                        <div class="mt-8 text-gray-500">{{ $review->comment }}</div>
                    @endif
                    <div class="mt-8 font-12 text-gray-400">{{ \Carbon\Carbon::createFromTimestamp($review->created_at)->diffForHumans() }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-12 text-gray-500">{{ trans('update.no_reviews_yet') }}</div>
    @endif
</div>
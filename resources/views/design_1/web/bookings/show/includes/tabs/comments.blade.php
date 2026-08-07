<div class="bg-white p-16 rounded-24">

    {{-- Form --}}
    @include('design_1.web.components.comments.submit_form', [
        'commentForItemId' => $booking->id,
        'commentForItemName' => 'booking_id',
    ])

    {{-- Comments Lists --}}
    @if(!empty($bookingComments) and count($bookingComments['comments']))
        <div class="js-course-comments-container">
            @include('design_1.web.components.comments.all_cards', [
                'comments' => $bookingComments['comments'],
                'commentForItemId' => $booking->id,
                'commentForItemName' => 'booking_id',
            ])
        </div>

        @if(!empty($bookingComments['has_more']))
            <div class="d-flex-center mt-16">
                <button type="button" class="js-comments-load-more-btn d-flex-center py-16 px-24 rounded-12 border-dashed border-gray-300 text-gray-500 bg-white bg-hover-gray-100 cursor-pointer" data-path="/comments/lists/booking/{{ $booking->id }}">
                    <x-iconsax-lin-rotate-left class="icons text-gray-500" width="16px" height="16px"/>
                    <span class="ml-4">{{ trans('update.load_more') }}</span>
                </button>
            </div>
        @endif
    @endif

</div>

<div class="js-reply-to-comment-html d-none">
    @include('design_1.web.components.comments.reply_form')
</div>

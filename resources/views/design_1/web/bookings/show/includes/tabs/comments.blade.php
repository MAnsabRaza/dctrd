<div class="bg-white p-16 rounded-24">
    {{-- Submit form --}}
    @include('design_1.web.components.comments.submit_form', [
        'commentForItemId'   => $booking->id,
        'commentForItemName' => 'booking_id',
    ])

    {{-- Comments list --}}
    @if(!empty($booking->comments) and count($booking->comments))
        <div class="js-booking-comments-container mt-16">
            @foreach($booking->comments as $comment)
                <div class="p-12 rounded-12 border-gray-200 {{ $loop->first ? 'mt-16' : 'mt-12' }}">
                    <div class="font-14 font-weight-bold">
                        {{ optional($comment->user)->full_name ?? trans('public.user') }}
                    </div>
                    <div class="mt-8 text-gray-500">{{ $comment->comment }}</div>
                    <div class="mt-8 font-12 text-gray-400">{{ \Carbon\Carbon::createFromTimestamp($comment->created_at)->diffForHumans() }}</div>
                </div>
            @endforeach
        </div>
    @else
        <div class="mt-12 text-gray-500">{{ trans('update.no_comments_yet') }}</div>
    @endif
</div>

<div class="js-reply-to-comment-html d-none">
    @include('design_1.web.components.comments.reply_form')
</div>
<div class="actions-dropdown position-relative d-flex justify-content-end align-items-center">
    <div class="d-flex-center size-40 bg-white border-gray-200 rounded-8 cursor-pointer">
        <x-iconsax-lin-more class="icons text-gray-500" width="24px" height="24px"/>
    </div>

    <div class="actions-dropdown__dropdown-menu dropdown-menu-width-220">
        <ul class="my-8">

            <li class="actions-dropdown__dropdown-menu-item">
                <a href="{{ route('panel.bookings.edit', ['id' => $booking->id]) }}" class="">{{ trans('public.edit') }}</a>
            </li>

            @if($booking->creator_id == $authUser->id)
                <li class="actions-dropdown__dropdown-menu-item">
                    @include('design_1.panel.includes.content_delete_btn', [
                        'deleteContentUrl' => route('panel.bookings.destroy', ['id' => $booking->id]),
                        'deleteContentClassName' => 'text-danger',
                        'deleteContentItem' => $booking,
                        'deleteContentItemType' => "booking",
                    ])
                </li>
            @endif

        </ul>
    </div>
</div>

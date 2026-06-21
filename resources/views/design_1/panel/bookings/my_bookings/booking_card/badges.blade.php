<div class="panel-product-card-1__badges-lists d-flex flex-wrap align-items-center gap-8">
    @if(!empty($booking->inventory) and !$booking->unlimited_inventory and $booking->getAvailability() < 1)
        <div class="badge bg-danger">
            <x-iconsax-bul-more-circle class="icons text-white" width="20px" height="20px"/>
            <span class="">{{ trans('update.out_of_stock') }}</span>
        </div>
    @elseif($hasDiscount)
        <div class="badge bg-danger">
            <x-iconsax-bul-more-circle class="icons text-white" width="20px" height="20px"/>
            <span class="">{{ trans('public.offer',['off' => $hasDiscount->percent]) }}</span>
        </div>
    @else
        @switch($booking->status)
            @case('published')
                <div class="badge bg-primary">
                    <x-iconsax-bul-tick-circle class="icons text-white" width="20px" height="20px"/>
                    <span class="">{{ trans('public.active') }}</span>
                </div>
                @break
            @case('draft')
                <div class="badge bg-danger">
                    <x-iconsax-bul-note-2 class="icons text-white" width="20px" height="20px"/>
                    <span class="">{{ trans('public.draft') }}</span>
                </div>
                @break
            @case('pending')
                <div class="badge bg-warning">
                    <x-iconsax-bul-more-circle class="icons text-white" width="20px" height="20px"/>
                    <span class="">{{ trans('public.waiting') }}</span>
                </div>
                @break
            @case('inactive')
                <div class="badge bg-danger">
                    <x-iconsax-bul-more-circle class="icons text-white" width="20px" height="20px"/>
                    <span class="">{{ trans('public.rejected') }}</span>
                </div>
                @break
        @endswitch
    @endif
</div>

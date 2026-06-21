<div class="col-12 col-md-6 col-lg-4 col-xl-3 mb-24 js-table-row">
    <div class="border border-gray-100 rounded-16 overflow-hidden h-100 d-flex flex-column">

        {{-- Cover + badge + play icon --}}
        <a href="{{ $booking->getUrl() }}" target="_blank" class="position-relative d-block">
            <img src="{{ $booking->cover_url }}" alt="{{ $booking->title }}" class="w-100" style="height: 160px; object-fit: cover;">

            <span class="position-absolute d-inline-flex-center px-8 py-4 rounded-8 font-12 bg-white"
                  style="top: 10px; left: 10px;">
                @if($booking->status == 'published')
                    <span class="size-8 rounded-circle bg-success mr-6"></span>{{ trans('public.published') }}
                @elseif($booking->status == 'pending')
                    <span class="size-8 rounded-circle bg-warning mr-6"></span>{{ trans('public.pending') }}
                @else
                    <span class="size-8 rounded-circle bg-gray-400 mr-6"></span>{{ trans('public.draft') }}
                @endif
            </span>

            <span class="position-absolute d-flex-center size-44 rounded-circle bg-white"
                  style="top: 50%; left: 50%; transform: translate(-50%, -50%);">
                <x-iconsax-bol-play class="text-dark" width="18"/>
            </span>
        </a>

        {{-- Body --}}
        <div class="p-16 d-flex flex-column flex-grow-1">

            <a href="{{ $booking->getUrl() }}" target="_blank" class="text-dark font-15 font-w-600 mb-8 d-block">
                {{ $booking->title }}
            </a>

            <div class="d-flex align-items-center gap-4 mb-12">
                @php $rate = $booking->getRate(); @endphp
                @for($i = 1; $i <= 5; $i++)
                    <x-iconsax-bol-star class="{{ $i <= round($rate) ? 'text-warning' : 'text-gray-200' }}" width="14"/>
                @endfor
                <span class="font-12 text-gray-500 ml-4">({{ $booking->getRateCount() }})</span>
            </div>

            <div class="d-flex align-items-center gap-8 mb-16">
                <img src="{{ $booking->creator->avatar ? asset('storage/' . $booking->creator->avatar) : asset('assets/default/img/icons/installment/meeting_default.svg') }}"
                     alt="{{ $booking->creator->full_name }}"
                     class="rounded-circle" width="28" height="28" style="object-fit: cover;">
                <div>
                    <div class="font-13 font-w-500 text-dark">{{ $booking->creator->full_name }}</div>
                    @if(!empty($booking->creator->headline))
                        <div class="font-11 text-gray-500">{{ $booking->creator->headline }}</div>
                    @endif
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mt-auto pt-8 border-top-gray-100">
                <span class="font-15 font-w-600 {{ $booking->effective_price > 0 ? 'text-dark' : 'text-success' }}">
                    {{ $booking->price_label }}
                </span>

                @if(!empty($booking->duration_minutes))
                    <span class="d-inline-flex-center font-12 text-gray-500">
                        <x-iconsax-lin-clock class="mr-4" width="14"/>
                        {{ $booking->duration_minutes >= 60 ? round($booking->duration_minutes / 60, 1) . ' ' . trans('public.hours') : $booking->duration_minutes . ' ' . trans('public.minutes') }}
                    </span>
                @endif
            </div>

        </div>
    </div>
</div>
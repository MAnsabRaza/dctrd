@php
    $bookingData = [
        'id'               => $booking->id,
        'title'            => $booking->title,
        'slug'             => $booking->slug,
        'category_id'      => $booking->category_id,
        'category_name'    => optional($booking->category)->title,
        'description'      => $booking->description,
        'price'            => $booking->price,
        'discount_price'   => $booking->discount_price,
        'capacity'         => $booking->capacity,
        'min_persons'      => $booking->min_persons,
        'max_persons'      => $booking->max_persons,
        'duration_minutes' => $booking->duration_minutes,
        'status'           => $booking->status,
        'featured'         => (bool) $booking->featured,
        'address_line'     => $booking->address_line,
        'city'             => $booking->city,
        'state'            => $booking->state,
        'country'          => $booking->country,
        'postal_code'      => $booking->postal_code,
        'lat'              => $booking->lat,
        'lng'              => $booking->lng,
        'meta'             => $booking->meta,
    ];
    $isActive = in_array($booking->status, ['published', 'active']);
@endphp

<tr id="bookingRow{{ $booking->id }}"
    data-booking="{{ urlencode(json_encode($bookingData, JSON_UNESCAPED_UNICODE)) }}">

    {{-- Title --}}
    <td class="text-left">
        <span class="font-weight-500">{{ $booking->title }}</span>
    </td>

    {{-- Category --}}
    <td class="text-left">
        {{ optional($booking->category)->title ?? '—' }}
    </td>

    {{-- Price --}}
    <td class="text-center">
        @if(!is_null($booking->discount_price) && $booking->discount_price > 0)
            <del class="text-muted font-12">{{ number_format($booking->price ?? 0, 2) }}</del>
            <span class="text-danger font-weight-500 d-block">{{ number_format($booking->discount_price, 2) }}</span>
        @else
            {{ number_format($booking->price ?? 0, 2) }}
        @endif
    </td>

    {{-- Capacity --}}
    <td class="text-center">
        {{ $booking->capacity ?? '—' }}
    </td>

    {{-- Status --}}
    <td class="text-center">
        @if($isActive)
            <span class="badge badge-success">{{ trans('public.active') }}</span>
        @else
            <span class="badge badge-secondary">{{ trans('public.inactive') }}</span>
        @endif

        @if($booking->featured)
            <span class="badge badge-warning ml-1">{{ trans('panel.featured') }}</span>
        @endif
    </td>

    {{-- Date --}}
    <td class="text-center">
        <span class="font-12 text-muted">
            {{ optional($booking->created_at)->format('Y-m-d') ?? '—' }}
        </span>
    </td>

    {{-- Actions --}}
    <td class="text-right">
        <div class="dropdown">
            <button type="button" class="btn btn-sm btn-outline-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                {{ trans('update.controls') }}
            </button>

            <div class="dropdown-menu dropdown-menu-right">
                @if(!empty($booking->getUrl()))
                    <a href="{{ $booking->getUrl() }}" target="_blank" class="dropdown-item">
                        View booking
                    </a>
                @endif

                @can('panel_bookings_calendar')
                    <a href="{{ route('panel.bookings.calendar', ['booking_id' => $booking->id]) }}" class="dropdown-item">
                        Calendar
                    </a>
                @endcan

                @can('panel_bookings_edit')
                    <a href="{{ route('panel.bookings.edit', ['id' => $booking->id]) }}"
                       class="dropdown-item btn-edit-booking"
                       data-id="{{ $booking->id }}">
                        {{ trans('public.edit') }}
                    </a>
                @endcan

                @can('panel_bookings_delete')
                    <button type="button"
                            class="dropdown-item text-danger btn-delete-booking"
                            data-id="{{ $booking->id }}"
                            data-title="{{ $booking->title }}">
                        {{ trans('panel.delete') }}
                    </button>
                @endcan
            </div>
        </div>
    </td>
</tr>

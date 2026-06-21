<div class="bg-white p-16 rounded-24">
    <h3 class="font-16 font-weight-bold">{{ trans('update.details') }}</h3>

    <div class="row mt-8">
        @foreach([
            trans('update.booking_type')     => $booking->booking_type ? ucfirst($booking->booking_type) : null,
            trans('update.capacity')         => $booking->capacity,
            trans('update.minimum_persons')  => $booking->min_persons,
            trans('update.maximum_persons')  => $booking->max_persons,
            trans('update.duration')         => $booking->duration_minutes ? $booking->duration_minutes . ' ' . trans('update.minutes') : null,
            trans('update.address')          => $booking->full_address ?? null,
            trans('update.instant_booking')  => $booking->instant_booking ? trans('update.yes') : trans('update.no'),
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

    {{-- Map --}}
    @if($booking->location_enabled and !empty($booking->lat) and !empty($booking->lng))
        @php
            $mapLat   = (float) $booking->lat;
            $mapLng   = (float) $booking->lng;
            $mapDelta = 0.01;
            $mapBbox  = implode(',', [
                $mapLng - $mapDelta, $mapLat - $mapDelta,
                $mapLng + $mapDelta, $mapLat + $mapDelta,
            ]);
        @endphp
        <div class="mt-16 rounded-16 overflow-hidden border-gray-200" style="height: 320px;">
            <iframe title="{{ $booking->title }} map"
                    width="100%" height="100%"
                    frameborder="0" loading="lazy"
                    referrerpolicy="no-referrer-when-downgrade"
                    src="https://www.openstreetmap.org/export/embed.html?bbox={{ $mapBbox }}&layer=mapnik&marker={{ $mapLat }},{{ $mapLng }}">
            </iframe>
        </div>
    @endif
</div>
@if(!empty($bookings) and count($bookings))
    @foreach($bookings as $booking)
        <div class="{{ $gridCardClassName ?? 'col-12 col-md-6 col-lg-4 mt-24' }}">
            @include('design_1.web.bookings.components.cards.grids.grid_card_1', ['booking' => $booking])
        </div>
    @endforeach
@else
    <div class="col-12 mt-24">
        @include('design_1.panel.includes.no-result', [
            'file_name' => 'meeting_packages.svg',
            'title' => 'No bookings found',
            'hint' => 'Please change filters or search another booking.'
        ])
    </div>
@endif

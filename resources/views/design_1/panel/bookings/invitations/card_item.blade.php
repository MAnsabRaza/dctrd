<div class="col-md-6 col-lg-4">
    <div class="booking-card">
        <a href="{{ url('booking/' . $booking->slug ?? $booking->id) }}">
            <img src="{{ $booking->getMainImageUrl() ?? asset('design_1/img/no-image.png') }}"
                 alt="{{ $booking->title }}"
                 class="booking-card-img" />
        </a>
        <div class="booking-card-body">
            <h6 class="booking-card-title">{{ $booking->title }}</h6>
            <span class="badge bg-light-primary">{{ ucfirst($booking->status) }}</span>
        </div>
    </div>
</div>
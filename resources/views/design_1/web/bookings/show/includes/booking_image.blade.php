@php
    $bookingMainImage = !empty($booking->cover) ? $booking->cover_url : $booking->thumbnail_url;
@endphp

<div class="product-show-thumbnail-card position-relative bg-gray-100 rounded-24">
    <img src="{{ $bookingMainImage }}"
         alt="{{ $booking->title }}"
         class="js-product-main-image img-cover rounded-24 p-16">
</div>

@if(!empty($booking->cover) and $booking->cover_url !== $booking->thumbnail_url)
    <div class="product-show__slide-images-card mt-16" data-simplebar @if((!empty($isRtl))) data-simplebar-direction="rtl" @endif>
        <div class="d-flex align-items-center gap-16">
            <div class="js-product-other-image position-relative product-show__slide-image-item d-flex-center rounded-24 bg-gray-100 cursor-pointer">
                <img src="{{ $booking->cover_url }}" alt="{{ $booking->title }}" class="img-cover rounded-24">
            </div>

            <div class="js-product-other-image position-relative product-show__slide-image-item d-flex-center rounded-24 bg-gray-100 cursor-pointer">
                <img src="{{ $booking->thumbnail_url }}" alt="{{ $booking->title }}" class="img-cover rounded-24">
            </div>
        </div>
    </div>
@endif

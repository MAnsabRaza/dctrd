<!-- <div class="bg-white p-16 rounded-24 mt-24">
    <h3 class="font-16">{{ trans('update.product_specifications') }}</h3>

    <div class="">
        @if(!empty($selectedSpecifications) and count($selectedSpecifications))
            @foreach($selectedSpecifications as $selectedSpecification)
                <div class="product-show__specification-item d-flex align-items-start mt-16 {{ (!$loop->first) ? 'pt-16 border-top-gray-200' : '' }} ">
                    <div class="specification-item-name font-weight-bold text-gray-500 ">
                        {{ $selectedSpecification->specification->title }}
                    </div>

                    <div class="specification-item-value flex-grow-1 px-16">
                        @if($selectedSpecification->type == 'textarea')
                            {!! nl2br($selectedSpecification->value) !!}
                        @elseif(!empty($selectedSpecification->selectedMultiValues))
                            @foreach($selectedSpecification->selectedMultiValues as $selectedSpecificationValue)
                                @if(!empty($selectedSpecificationValue->multiValue))
                                    <span class="d-block">{{ $selectedSpecificationValue->multiValue->title }}</span>
                                @endif
                            @endforeach
                        @endif
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div> -->

@extends('design_1.web.layouts.app')

@push('styles_top')
    <link rel="stylesheet" href="{{ getDesign1StylePath("product_show") }}">
@endpush

@section('content')
    <div class="container mt-160 mb-160">

        {{-- Advertising Banners --}}
        @if(!empty($advertisingBanners) and count($advertisingBanners))
            <div class="mb-16">
                @foreach($advertisingBanners as $advertisingBanner)
                    <a href="{{ $advertisingBanner->url }}" target="_blank">
                        <img src="{{ $advertisingBanner->image }}" alt="{{ $advertisingBanner->title }}" class="img-fluid rounded-24">
                    </a>
                @endforeach
            </div>
        @endif

        <div class="row">

            {{-- Images --}}
            <div class="col-12 col-lg-5">
                @include('design_1.web.products.show.includes.images', ['product' => $product])
            </div>

            {{-- Main Info (title, price, quantity, add to cart, buy with points, installments) --}}
            <div class="col-12 col-lg-7 mt-24 mt-lg-0">
             @include('design_1.web.products.show.includes.main_info', [
    'product' => $product,
    'user' => $user,
    'selectableSpecifications' => $selectableSpecifications,
    'hasInstallments' => $hasInstallments,
])
            </div>

        </div>

        {{-- Promotions (gift card / cashback / instructor discounts) --}}
        @include('design_1.web.products.show.includes.promotions', [
            'product' => $product,
            'productAvailability' => $productAvailability,
            'cashbackRules' => $cashbackRules,
            'instructorDiscounts' => $instructorDiscounts,
        ])

        {{-- About / Description / FAQ --}}
        @include('design_1.web.products.show.tabs.about', ['product' => $product])

        {{-- Specifications --}}
        @include('design_1.web.products.show.includes.main_info.tabs.specifications', [
            'selectedSpecifications' => $selectedSpecifications,
        ])

        {{-- Files (only for products that have downloadable files) --}}
        @if(!empty($product->files) and count($product->files))
            <div class="mt-24">
                @include('design_1.web.products.show.tabs.files', ['product' => $product])
            </div>
        @endif

        {{-- Seller / Instructor Card --}}
        @include('design_1.web.products.show.tabs.seller', [
            'product' => $product,
            'seller' => $seller,
            'sellerBadges' => $sellerBadges,
            'sellerRates' => $sellerRates,
            'sellerFollowers' => $sellerFollowers,
            'sellerFollowing' => $sellerFollowing,
            'authUserIsFollower' => $authUserIsFollower,
        ])

        {{-- Reviews --}}
        <div class="mt-24">
            @include('design_1.web.products.show.tabs.reviews', [
                'product' => $product,
                'productReviews' => $productReviews,
            ])
        </div>

        {{-- Comments --}}
        <div class="mt-24">
            @include('design_1.web.products.show.tabs.comments', [
                'product' => $product,
                'productComments' => $productComments,
            ])
        </div>

    </div>
@endsection
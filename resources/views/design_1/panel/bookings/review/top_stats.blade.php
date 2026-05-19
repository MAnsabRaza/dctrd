<div class="row">

    {{-- TOTAL REVIEWS --}}
    <div class="col-12 col-lg-4">

        <div class="bg-white p-16 rounded-24">

            <div class="d-flex align-items-start justify-content-between">

                <span class="text-gray-500 mt-8">
                    Reviews
                </span>

                <div class="size-48 d-flex-center bg-primary-30 rounded-12">

                    <x-iconsax-bul-star
                        class="icons text-primary"
                        width="24px"
                        height="24px"/>

                </div>

            </div>

            <h5 class="font-24 mt-12 line-height-1">

                {{ $allReviewsCount }}

            </h5>

        </div>

    </div>

    {{-- ACTIVE REVIEWS --}}
    <div class="col-12 col-lg-4 mt-16 mt-md-0">

        <div class="bg-white p-16 rounded-24">

            <div class="d-flex align-items-start justify-content-between">

                <span class="text-gray-500 mt-8">
                    Active Reviews
                </span>

                <div class="size-48 d-flex-center bg-success-30 rounded-12">

                    <x-iconsax-bul-star-1
                        class="icons text-success"
                        width="24px"
                        height="24px"/>

                </div>

            </div>

            <h5 class="font-24 mt-12 line-height-1">

                {{ $activeReviewsCount }}

            </h5>

        </div>

    </div>

    {{-- PENDING REVIEWS --}}
    <div class="col-12 col-lg-4 mt-16 mt-md-0">

        <div class="bg-white p-16 rounded-24">

            <div class="d-flex align-items-start justify-content-between">

                <span class="text-gray-500 mt-8">
                    Pending Reviews
                </span>

                <div class="size-48 d-flex-center bg-warning-30 rounded-12">

                    <x-iconsax-bul-star-slash
                        class="icons text-warning"
                        width="24px"
                        height="24px"/>

                </div>

            </div>

            <h5 class="font-24 mt-12 line-height-1">

                {{ $pendingReviewsCount }}

            </h5>

        </div>

    </div>

</div>
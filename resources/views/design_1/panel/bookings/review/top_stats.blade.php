<div class="row">

    <div class="col-12 col-lg-6">

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

    <div class="col-12 col-lg-6">

        <div class="bg-white p-16 rounded-24">

            <div class="d-flex align-items-start justify-content-between">

                <span class="text-gray-500 mt-8">
                    Approved Reviews
                </span>

                <div class="size-48 d-flex-center bg-success-30 rounded-12">

                    <x-iconsax-bul-star-1
                        class="icons text-success"
                        width="24px"
                        height="24px"/>

                </div>

            </div>

            <h5 class="font-24 mt-12 line-height-1">
                {{ $approvedReviewsCount }}
            </h5>

        </div>

    </div>

</div>
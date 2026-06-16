@extends("design_1.web.layouts.app")

@push("styles_top")
    <link rel="stylesheet" href="{{ getDesign1StylePath("search") }}">
@endpush

@section("content")
    <main class="pb-80">
        <section class="search-hero position-relative">
            <img src="{{ getThemePageBackgroundSettings('search') }}" class="img-cover" alt="{{ trans('public.search') }}"/>
            <div class="search-hero__mask"></div>

            <div class="container position-relative d-flex-center flex-column z-index-3">
                <h1 class="font-24 font-weight-bold text-white">{{ trans('update.search_results') }}</h1>

                @if(!empty(request()->get('search')))
                    <div class="mt-8 font-12 text-white opacity-75">{{ trans('update.n_results_found_for_search', ['count' => $resultCount, 'search' => request()->get('search')]) }}</div>
                @endif

                <div class="row justify-content-center w-100">
                    <div class="col-12 col-lg-8">
                        <div class="search-form-box bg-white p-12 mt-20 rounded-16 w-100">
                            <form action="/search" method="get" id="searchForm">
                                <div class="form-group d-flex align-items-center mb-0">
                                            <input type="text" name="search" class="form-control border-0 p-12" value="{{ request()->get('search','') }}" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                            <button type="submit" class="btn btn-primary btn-lg">{{ trans('public.search') }}</button>
                                </div>
                                                <div class="mt-12">
                                    <div class="form-group">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <label class="input-label">{{ trans('update.categories') }}</label>
                                            <div class="d-flex align-items-center gap-8">
                                                <button type="button" class="btn btn-sm btn-outline-secondary js-select-all-cats" title="Select all">✓ All</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary js-clear-all-cats" title="Clear all">✗ Clear</button>
                                                <button type="button" class="btn btn-sm btn-outline-secondary js-toggle-all-cats" title="Expand/Collapse">⊕ Toggle</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                @include('partials._location_picker', [
                                    'locationModel' => null,
                                    'addressName' => 'address',
                                    'showAjaxSave' => false,
                                    'pickerId' => 'searchLocationPicker'
                                ])

                                <div class="form-group mt-8">
                                    <label class="input-label">Radius (km)</label>
                                    <input type="number" name="radius_km" value="{{ request()->get('radius_km', 50) }}" class="form-control" step="1" min="1">
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        {{-- Main Content Area with Sidebar --}}
        <div class="container mt-48">
            <div class="row">
                {{-- Left Sidebar: Hierarchical Categories --}}
                <div class="col-12 col-lg-3">
                    <div class="bg-white rounded border p-16">
                        <div class="font-14 font-weight-bold mb-12">{{ trans('update.categories') }}</div>

                        <form id="filterForm" onchange="document.getElementById('searchForm').submit();">
                            <div class="search-categories" style="max-height: calc(100vh - 300px); overflow-y: auto;">
                                {{-- Course Categories --}}
                                <div class="mb-16">
                                    <div class="font-12 font-weight-600 mb-8 text-uppercase opacity-75">Courses</div>
                                    @forelse($categories as $category)
                                        <div class="mb-8">
                                            <div class="d-flex align-items-center">
                                                <input type="checkbox" name="categories[]" id="cat{{ $category->id }}" value="{{ $category->id }}" @if(in_array($category->id, request()->get('categories', []))) checked @endif class="js-cat-checkbox mr-8">
                                                <label for="cat{{ $category->id }}" class="mb-0 cursor-pointer font-13">{{ $category->title }}</label>
                                            </div>

                                            @if(!empty($category->subCategories) and count($category->subCategories))
                                                <div class="ml-3 mt-6 subcats-list">
                                                    @foreach($category->subCategories as $sub)
                                                        <div class="d-flex align-items-center mb-4">
                                                            <input type="checkbox" name="categories[]" id="cat{{ $sub->id }}" value="{{ $sub->id }}" @if(in_array($sub->id, request()->get('categories', []))) checked @endif class="js-cat-checkbox mr-8">
                                                            <label for="cat{{ $sub->id }}" class="mb-0 cursor-pointer font-12">{{ $sub->title }}</label>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            @endif
                                        </div>
                                    @empty
                                        <div class="font-12 text-muted">No course categories</div>
                                    @endforelse
                                </div>

                                {{-- Booking Categories --}}
                                @if(!empty($bookingCategories) and count($bookingCategories))
                                    <div class="mt-16 pt-12 border-top">
                                        <div class="font-12 font-weight-600 mb-8 text-uppercase opacity-75">Services</div>
                                        @foreach($bookingCategories as $bcat)
                                            <div class="mb-8">
                                                <div class="d-flex align-items-center">
                                                    <input type="checkbox" name="booking_categories[]" id="bcat{{ $bcat->id }}" value="{{ $bcat->id }}" @if(in_array($bcat->id, request()->get('booking_categories', []))) checked @endif class="js-bcat-checkbox mr-8">
                                                    <label for="bcat{{ $bcat->id }}" class="mb-0 cursor-pointer font-13">{{ $bcat->title }}</label>
                                                </div>

                                                @if(!empty($bcat->children) and count($bcat->children))
                                                    <div class="ml-3 mt-6 subcats-list">
                                                        @foreach($bcat->children as $bsub)
                                                            <div class="d-flex align-items-center mb-4">
                                                                <input type="checkbox" name="booking_categories[]" id="bcat{{ $bsub->id }}" value="{{ $bsub->id }}" @if(in_array($bsub->id, request()->get('booking_categories', []))) checked @endif class="js-bcat-checkbox mr-8">
                                                                <label for="bcat{{ $bsub->id }}" class="mb-0 cursor-pointer font-12">{{ $bsub->title }}</label>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                @endif
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                {{-- Right Content: Search Results --}}
                <div class="col-12 col-lg-9">
                    {{-- Bookings Section --}}
                    @if(!empty($bookings) and $bookings->isNotEmpty())
                        <section id="sectionBookings" class="mb-48">
                            <div class="d-flex align-items-center justify-content-between mb-16">
                                <h3 class="font-24 font-weight-bold mb-0">{{ trans('home.bookings') ?? 'Bookings' }} <span class="badge badge-primary">{{ count($bookings) }}</span></h3>
                            </div>
                            <div class="row">
                                @foreach($bookings as $booking)
                                    <div class="col-12 col-md-6 col-lg-4 mb-16">
                                        <div class="card h-100 border">
                                            <div class="card-body">
                                                <h5 class="card-title font-16 font-weight-bold">{{ $booking->title }}</h5>
                                                <p class="card-text font-13 text-muted">{{ Str::limit($booking->description, 80) }}</p>
                                                <div class="mt-8">
                                                    @if($booking->category)
                                                        <span class="badge badge-light">{{ $booking->category->title }}</span>
                                                    @endif
                                                </div>
                                                <a href="{{ route('booking.show', $booking->id) }}" class="btn btn-sm btn-primary mt-8">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Booking Bundles Section --}}
                    @if(!empty($bookingBundles) and $bookingBundles->isNotEmpty())
                        <section id="sectionBookingBundles" class="mb-48">
                            <div class="d-flex align-items-center justify-content-between mb-16">
                                <h3 class="font-24 font-weight-bold mb-0">{{ trans('home.booking_bundles') ?? 'Booking Bundles' }} <span class="badge badge-primary">{{ count($bookingBundles) }}</span></h3>
                            </div>
                            <div class="row">
                                @foreach($bookingBundles as $bundle)
                                    <div class="col-12 col-md-6 col-lg-4 mb-16">
                                        <div class="card h-100 border">
                                            <div class="card-body">
                                                <h5 class="card-title font-16 font-weight-bold">{{ $bundle->title }}</h5>
                                                <p class="card-text font-13 text-muted">{{ Str::limit($bundle->description, 80) }}</p>
                                                <a href="{{ route('bookingBundle.show', $bundle->id) }}" class="btn btn-sm btn-primary mt-8">View Details</a>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif

                    {{-- Courses Section --}}
                    @if(!empty($webinars) and $webinars->isNotEmpty())
                        <section id="sectionWebinars" class="mb-48">
                            <div class="d-flex align-items-center justify-content-between mb-16">
                                <h3 class="font-24 font-weight-bold mb-0">{{ trans('update.courses') }} <span class="badge badge-primary">{{ count($webinars) }}</span></h3>
                            </div>
                            <div class="row">
                                @include('design_1.web.courses.components.cards.grids.index',['courses' => $webinars, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mb-16"])
                            </div>
                        </section>
                    @endif

                    {{-- Bundles Section --}}
                    @if(!empty($bundles) and $bundles->isNotEmpty())
                        <section id="sectionBundles" class="mb-48">
                            <div class="d-flex align-items-center justify-content-between mb-16">
                                <h3 class="font-24 font-weight-bold mb-0">{{ trans('update.bundles') }} <span class="badge badge-primary">{{ count($bundles) }}</span></h3>
                            </div>
                            <div class="row">
                                @include('design_1.web.bundles.components.cards.grids.index',['bundles' => $bundles, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mb-16"])
                            </div>
                        </section>
                    @endif

                    {{-- Products Section --}}
                    @if(!empty($products) and $products->isNotEmpty())
                        <section id="sectionProducts" class="mb-48">
                            <div class="d-flex align-items-center justify-content-between mb-16">
                                <h3 class="font-24 font-weight-bold mb-0">{{ trans('update.store_products') }} <span class="badge badge-primary">{{ count($products) }}</span></h3>
                            </div>
                            <div class="row">
                                @include('design_1.web.products.components.cards.grids.index',['products' => $products, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mb-16"])
                            </div>
                        </section>
                    @endif

                    {{-- Upcoming Courses Section --}}
                    @if(!empty($upcomingCourses) and $upcomingCourses->isNotEmpty())
                        <section id="sectionUpcomingCourses" class="mb-48">
                            <div class="d-flex align-items-center justify-content-between mb-16">
                                <h3 class="font-24 font-weight-bold mb-0">{{ trans('update.upcoming_courses') }} <span class="badge badge-primary">{{ count($upcomingCourses) }}</span></h3>
                            </div>
                            <div class="row">
                                @include('design_1.web.upcoming_courses.components.cards.grids.index',['upcomingCourses' => $upcomingCourses, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mb-16"])
                            </div>
                        </section>
                    @endif

                    {{-- Blog Posts Section --}}
                    @if(!empty($posts) and $posts->isNotEmpty())
                        <section id="sectionPosts" class="mb-48">
                            <div class="d-flex align-items-center justify-content-between mb-16">
                                <h3 class="font-24 font-weight-bold mb-0">{{ trans('update.blog_posts') }} <span class="badge badge-primary">{{ count($posts) }}</span></h3>
                            </div>
                            <div class="row">
                                @include('design_1.web.blog.components.cards.grids.index',['posts' => $posts, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mb-16"])
                            </div>
                        </section>
                    @endif

                    {{-- Instructors & Organizations Section --}}
                    @if((!empty($instructors) and count($instructors)) or (!empty($organizations) and count($organizations)))
                        <section id="sectionUsers">
                            @if(!empty($organizations) and count($organizations))
                                <div class="mb-48">
                                    <div class="d-flex align-items-center justify-content-between mb-16">
                                        <h3 class="font-24 font-weight-bold mb-0">{{ trans('home.organizations') }} <span class="badge badge-primary">{{ count($organizations) }}</span></h3>
                                    </div>
                                    <div class="row">
                                        @foreach($organizations as $organ)
                                            @include('design_1.web.search.includes.user_card',['userCard' => $organ])
                                        @endforeach
                                    </div>
                                </div>
                            @endif

                            @if(!empty($instructors) and count($instructors))
                                <div class="mb-48">
                                    <div class="d-flex align-items-center justify-content-between mb-16">
                                        <h3 class="font-24 font-weight-bold mb-0">{{ trans('home.instructors') }} <span class="badge badge-primary">{{ count($instructors) }}</span></h3>
                                    </div>
                                    <div class="row">
                                        @foreach($instructors as $instructor)
                                            @include('design_1.web.search.includes.user_card',['userCard' => $instructor])
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </section>
                    @endif

                    @if(empty($webinars) and empty($bundles) and empty($products) and empty($upcomingCourses) and empty($posts) and empty($instructors) and empty($organizations) and empty($bookings) and empty($bookingBundles))
                        <div class="alert alert-info">
                            <p>{{ trans('update.no_results_found') ?? 'No results found. Try another search.' }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <script>
            (function () {
                // Auto-submit form when category checkboxes change
                const filterForm = document.getElementById('filterForm');
                const searchForm = document.getElementById('searchForm');
                
                if (filterForm) {
                    filterForm.addEventListener('change', function () {
                        searchForm.submit();
                    });
                }

                // select all / clear / toggle handlers for categories
                document.addEventListener('click', function (e) {
                    if (!e.target) return;

                    if (e.target.matches('.js-select-all-cats')) {
                        document.querySelectorAll('.js-cat-checkbox').forEach(function (el) { el.checked = true; });
                        document.querySelectorAll('.js-bcat-checkbox').forEach(function (el) { el.checked = true; });
                        if (filterForm) filterForm.dispatchEvent(new Event('change'));
                    }

                    if (e.target.matches('.js-clear-all-cats')) {
                        document.querySelectorAll('.js-cat-checkbox').forEach(function (el) { el.checked = false; });
                        document.querySelectorAll('.js-bcat-checkbox').forEach(function (el) { el.checked = false; });
                        if (filterForm) filterForm.dispatchEvent(new Event('change'));
                    }

                    if (e.target.matches('.js-toggle-all-cats')) {
                        document.querySelectorAll('.subcats-list').forEach(function (el) {
                            el.style.display = (el.style.display === 'none' || getComputedStyle(el).display === 'none') ? 'block' : 'none';
                        });
                    }
                }, false);
            })();
        </script>
    </main>
@endsection

@push('scripts_bottom')
    <script src="{{ getDesign1ScriptPath("search") }}"></script>
@endpush

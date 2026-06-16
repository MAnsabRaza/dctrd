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
                    <div class="col-12 col-lg-6">
                        <div class="search-form-box bg-white p-12 mt-20 rounded-16 w-100">
                            <form action="/search" method="get">
                                <div class="form-group d-flex align-items-center mb-0">
                                            <input type="text" name="search" class="form-control border-0 p-12" value="{{ request()->get('search','') }}" placeholder="{{ trans('home.slider_search_placeholder') }}"/>
                                            <button type="submit" class="btn btn-primary btn-lg">{{ trans('public.search') }}</button>
                                </div>
                                                <div class="mt-12">
                                                    <div class="form-group">
                                                        <label class="input-label">{{ trans('update.categories') }}</label>

                                                        <div class="d-flex align-items-center mb-8 gap-8">
                                                            <button type="button" class="btn btn-sm btn-outline-secondary js-select-all-cats">Select all</button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary js-clear-all-cats">Clear</button>
                                                            <button type="button" class="btn btn-sm btn-outline-secondary js-toggle-all-cats">Expand/Collapse</button>
                                                        </div>

                                                        <div class="search-categories p-12 bg-white border rounded" style="max-height:220px;overflow:auto;">
                                                            <div class="font-12 font-weight-600 mb-8">Course Categories</div>
                                                            @foreach($categories as $category)
                                                                <div class="mb-8">
                                                                    <div class="d-flex align-items-center">
                                                                        <input type="checkbox" name="categories[]" id="cat{{ $category->id }}" value="{{ $category->id }}" @if(in_array($category->id, request()->get('categories', []))) checked @endif class="js-cat-checkbox mr-8">
                                                                        <label for="cat{{ $category->id }}">{{ $category->title }}</label>
                                                                        @if(!empty($category->subCategories) and count($category->subCategories))
                                                                            <button type="button" class="btn btn-sm btn-link js-toggle-subcats ml-8" data-target="#subcats{{ $category->id }}">Toggle</button>
                                                                        @endif
                                                                    </div>

                                                                    @if(!empty($category->subCategories) and count($category->subCategories))
                                                                        <div id="subcats{{ $category->id }}" class="ml-4 mt-8 subcats-list">
                                                                            @foreach($category->subCategories as $sub)
                                                                                <div class="d-flex align-items-center mb-4">
                                                                                    <input type="checkbox" name="categories[]" id="cat{{ $sub->id }}" value="{{ $sub->id }}" @if(in_array($sub->id, request()->get('categories', []))) checked @endif class="js-cat-checkbox mr-8">
                                                                                    <label for="cat{{ $sub->id }}">{{ $sub->title }}</label>
                                                                                </div>
                                                                            @endforeach
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            @endforeach

                                                            @if(!empty($bookingCategories) and count($bookingCategories))
                                                                <div class="mt-12">
                                                                    <div class="font-12 font-weight-600 mb-8">Booking Categories</div>
                                                                    @foreach($bookingCategories as $bcat)
                                                                        <div class="mb-8">
                                                                            <div class="d-flex align-items-center">
                                                                                <input type="checkbox" name="booking_categories[]" id="bcat{{ $bcat->id }}" value="{{ $bcat->id }}" @if(in_array($bcat->id, request()->get('booking_categories', []))) checked @endif class="js-bcat-checkbox mr-8">
                                                                                <label for="bcat{{ $bcat->id }}">{{ $bcat->title }}</label>
                                                                                @if(!empty($bcat->children) and count($bcat->children))
                                                                                    <button type="button" class="btn btn-sm btn-link js-toggle-subcats ml-8" data-target="#bsubcats{{ $bcat->id }}">Toggle</button>
                                                                                @endif
                                                                            </div>

                                                                            @if(!empty($bcat->children) and count($bcat->children))
                                                                                <div id="bsubcats{{ $bcat->id }}" class="ml-4 mt-8 subcats-list">
                                                                                    @foreach($bcat->children as $bsub)
                                                                                        <div class="d-flex align-items-center mb-4">
                                                                                            <input type="checkbox" name="booking_categories[]" id="bcat{{ $bsub->id }}" value="{{ $bsub->id }}" @if(in_array($bsub->id, request()->get('booking_categories', []))) checked @endif class="js-bcat-checkbox mr-8">
                                                                                            <label for="bcat{{ $bsub->id }}">{{ $bsub->title }}</label>
                                                                                        </div>
                                                                                    @endforeach
                                                                                </div>
                                                                            @endif
                                                                        </div>
                                                                    @endforeach
                                                                </div>
                                                            @endif
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
                                        </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="d-flex-center gap-16 mt-24 w-100">
                    @if(!empty($webinars) and $webinars->isNotEmpty())
                        <div class="js-content-anchor search-hero__content-anchor p-10 rounded-8 cursor-pointer font-12 text-white" data-anchor-id="sectionWebinars">{{ trans('update.courses') }} ({{ count($webinars) }})</div>
                    @endif

                    @if(!empty($bundles) and $bundles->isNotEmpty())
                        <div class="js-content-anchor search-hero__content-anchor p-10 rounded-8 cursor-pointer font-12 text-white" data-anchor-id="sectionBundles">{{ trans('update.bundles') }} ({{ count($bundles) }})</div>
                    @endif

                    @if(!empty($products) and $products->isNotEmpty())
                        <div class="js-content-anchor search-hero__content-anchor p-10 rounded-8 cursor-pointer font-12 text-white" data-anchor-id="sectionProducts">{{ trans('update.products') }} ({{ count($products) }})</div>
                    @endif

                    @if(!empty($upcomingCourses) and $upcomingCourses->isNotEmpty())
                        <div class="js-content-anchor search-hero__content-anchor p-10 rounded-8 cursor-pointer font-12 text-white" data-anchor-id="sectionUpcomingCourses">{{ trans('update.upcoming_courses') }} ({{ count($upcomingCourses) }})</div>
                    @endif

                    @if(!empty($posts) and $posts->isNotEmpty())
                        <div class="js-content-anchor search-hero__content-anchor p-10 rounded-8 cursor-pointer font-12 text-white" data-anchor-id="sectionPosts">{{ trans('update.posts') }} ({{ count($posts) }})</div>
                    @endif

                    @if(!empty($instructors) and !empty($organizations) and (count($instructors) + count($organizations)) > 0)
                        <div class="js-content-anchor search-hero__content-anchor p-10 rounded-8 cursor-pointer font-12 text-white" data-anchor-id="sectionUsers">{{ trans('panel.users') }} ({{ (count($instructors) + count($organizations)) }})</div>
                    @endif

                </div>

            </div>

        </section>

        {{-- Courses Section --}}
        @if(!empty($webinars) and $webinars->isNotEmpty())
            <section id="sectionWebinars" class="container mt-48">
                <h3 class="font-24 font-weight-bold">{{ trans('update.courses') }}</h3>

                <div class="row">
                    @include('design_1.web.courses.components.cards.grids.index',['courses' => $webinars, 'gridCardClassName' => "col-12 col-md-6 col-lg-3 mt-16"])
                </div>
            </section>
        @endif

        {{-- Bundles Section --}}
        @if(!empty($bundles) and $bundles->isNotEmpty())
            <section id="sectionBundles" class="container mt-48">
                <h3 class="font-24 font-weight-bold">{{ trans('update.bundles') }}</h3>

                <div class="row">
                    @include('design_1.web.bundles.components.cards.grids.index',['bundles' => $bundles, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-16"])
                </div>
            </section>
        @endif

        {{-- Products Section --}}
        @if(!empty($products) and $products->isNotEmpty())
            <section id="sectionProducts" class="container mt-48">
                <h3 class="font-24 font-weight-bold">{{ trans('update.store_products') }}</h3>

                <div class="row">
                    @include('design_1.web.products.components.cards.grids.index',['products' => $products, 'gridCardClassName' => "col-12 col-md-6 col-lg-4 mt-16"])
                </div>
            </section>
        @endif

        {{-- Upcoming Courses Section --}}
        @if(!empty($upcomingCourses) and $upcomingCourses->isNotEmpty())
            <section id="sectionUpcomingCourses" class="container mt-48">
                <h3 class="font-24 font-weight-bold">{{ trans('update.upcoming_courses') }}</h3>

                <div class="row">
                    @include('design_1.web.upcoming_courses.components.cards.grids.index',['upcomingCourses' => $upcomingCourses, 'gridCardClassName' => "col-12 col-md-6 col-lg-3 mt-16"])
                </div>
            </section>
        @endif

        {{-- Blog Posts Section --}}
        @if(!empty($posts) and $posts->isNotEmpty())
            <section id="sectionPosts" class="container mt-48">
                <h3 class="font-24 font-weight-bold">{{ trans('update.blog_posts') }}</h3>

                <div class="row">
                    @include('design_1.web.blog.components.cards.grids.index',['posts' => $posts, 'gridCardClassName' => "col-12 col-md-6 col-lg-3 mt-16"])
                </div>
            </section>
        @endif

        {{-- Users Section --}}
        @if((!empty($instructors) and count($instructors)) or (!empty($organizations) and count($organizations)))
            <section id="sectionUsers" class="container">

                @if(!empty($organizations) and count($organizations))
                    <div class="mt-48">
                        <h3 class="font-24 font-weight-bold">{{ trans('home.organizations') }}</h3>

                        <div class="row">
                            @foreach($organizations as $organ)
                                @include('design_1.web.search.includes.user_card',['userCard' => $organ])
                            @endforeach
                        </div>
                    </div>
                @endif

                @if(!empty($instructors) and count($instructors))
                    <div class="mt-48">
                        <h3 class="font-24 font-weight-bold">{{ trans('home.instructors') }}</h3>

                        <div class="row">
                            @foreach($instructors as $instructor)
                                @include('design_1.web.search.includes.user_card',['userCard' => $instructor])
                            @endforeach
                        </div>
                    </div>
                @endif

            </section>
        @endif
    </main>
@endsection

@push('scripts_bottom')

    <script src="{{ getDesign1ScriptPath("search") }}"></script>

    <script>
        (function () {
            // select all / clear / toggle handlers for categories
            document.addEventListener('click', function (e) {
                if (!e.target) return;

                if (e.target.matches('.js-select-all-cats')) {
                    document.querySelectorAll('.js-cat-checkbox').forEach(function (el) { el.checked = true; });
                    document.querySelectorAll('.js-bcat-checkbox').forEach(function (el) { el.checked = true; });
                }

                if (e.target.matches('.js-clear-all-cats')) {
                    document.querySelectorAll('.js-cat-checkbox').forEach(function (el) { el.checked = false; });
                    document.querySelectorAll('.js-bcat-checkbox').forEach(function (el) { el.checked = false; });
                }

                if (e.target.matches('.js-toggle-all-cats')) {
                    document.querySelectorAll('.subcats-list').forEach(function (el) {
                        el.style.display = (el.style.display === 'none' || getComputedStyle(el).display === 'none') ? 'block' : 'none';
                    });
                }

                if (e.target.matches('.js-toggle-subcats')) {
                    var target = e.target.getAttribute('data-target');
                    var el = document.querySelector(target);
                    if (el) el.style.display = (el.style.display === 'none' || getComputedStyle(el).display === 'none') ? 'block' : 'none';
                }
            }, false);
        })();
    </script>
@endpush

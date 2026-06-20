@if(
        $authUser->can('admin_blog') or
        $authUser->can('admin_pages') or
        $authUser->can('admin_additional_pages') or
        $authUser->can('admin_testimonials') or
        $authUser->can('admin_tags') or
        $authUser->can('admin_regions') or
        $authUser->can('admin_store') or
        $authUser->can('admin_forms') or
        $authUser->can('admin_ai_contents') or
        $authUser->can('admin_content_delete_requests_lists') or
        $authUser->can('admin_instructor_finder')
    )
    <li class="menu-header">{{ trans('admin/main.content') }}</li>
@endif

@can('admin_store')
    <li
        class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/store*', false)) or request()->is(getAdminPanelUrl('/comments/products*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-shop class="icons" width="24px" height="24px" />
            <span>{{ trans('update.store') }}</span>
        </a>
        <ul class="dropdown-menu">

            @can('admin_store_new_product')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/products/create', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/store/products/create">{{ trans('update.new_product') }}</a>
                </li>
            @endcan()

            @can('admin_store_products')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/products', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/store/products">{{ trans('update.products') }}</a>
                </li>
            @endcan()

            @can('admin_store_in_house_products')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/in-house-products', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/store/in-house-products">{{ trans('update.in-house-products') }}</a>
                </li>
            @endcan()

            @can('admin_store_products_orders')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/orders', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/store/orders">{{ trans('update.orders') }}</a>
                </li>
            @endcan()

            @can('admin_store_in_house_orders')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/in-house-orders', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/store/in-house-orders">{{ trans('update.in-house-orders') }}</a>
                </li>
            @endcan()

            @can('admin_store_products_sellers')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/sellers', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/store/sellers">{{ trans('update.sellers') }}</a>
                </li>
            @endcan()

            @can('admin_store_categories_list')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/categories', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/store/categories">{{ trans('admin/main.categories') }}</a>
                </li>
            @endcan()

            @can('admin_store_filters_list')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/filters', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/store/filters">{{ trans('update.filters') }}</a>
                </li>
            @endcan()

            @can('admin_store_specifications')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/specifications', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/store/specifications">{{ trans('update.specifications') }}</a>
                </li>
            @endcan()

            @can('admin_store_discounts')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/discounts', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/store/discounts">{{ trans('admin/main.discounts') }}</a>
                </li>
            @endcan()

            @can('admin_store_products_comments')
                <li class="{{ (request()->is(getAdminPanelUrl('/comments/products*', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/comments/products">{{ trans('admin/main.comments') }}</a>
                </li>
            @endcan()

            @can('admin_products_comments_reports')
                <li class="{{ (request()->is(getAdminPanelUrl('/comments/products/reports', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/comments/products/reports">{{ trans('admin/main.comments_reports') }}</a>
                </li>
            @endcan

            @can('admin_store_products_reviews')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/reviews', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/store/reviews">{{ trans('admin/main.reviews') }}</a>
                </li>
            @endcan

            @can('admin_store_top_categories')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/top-categories', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/store/top-categories">{{ trans('update.top_categories') }}</a>
                </li>
            @endcan

            @can('admin_store_featured_products')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/featured-products', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/store/featured-products">{{ trans('update.featured_products') }}</a>
                </li>
            @endcan

            @can('admin_store_featured_categories')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/featured-categories', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/store/featured-categories">{{ trans('update.featured_categories') }}</a>
                </li>
            @endcan

            @can('admin_store_settings')
                <li class="{{ (request()->is(getAdminPanelUrl('/store/settings', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/store/settings">{{ trans('admin/main.settings') }}</a>
                </li>
            @endcan
        </ul>
    </li>
@endcan

@can('admin_booking')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/booking*', false)) or request()->is(getAdminPanelUrl('/comments/bookings*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-calendar class="icons" width="24px" height="24px" />
            <span>{{ trans('admin/main.booking') }}</span>
        </a>
        <ul class="dropdown-menu">
            @can('admin_booking')
                <li class="{{ request()->is(getAdminPanelUrl('/booking', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking">
                        {{ trans('admin/main.new_booking') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/list', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/list">
                        {{ trans('admin/main.booking_list') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_in_house')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/in-house-bookings', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/in-house-bookings">
                        {{ trans('update.in-house-bookings') ?? 'In House Bookings' }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_orders')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/order', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/order">
                        {{ trans('admin/main.admin_booking_orders') }}
                    </a>
                </li>
            @endcan

            @can('admin_booking_in_house_orders')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/order/in-house-orders', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/order/in-house-orders">
                        {{ trans('update.in-house-booking-orders') ?? 'In House Booking Orders' }}
                    </a>
                </li>
            @endcan

            @can('admin_booking_sellers')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/sellers', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/sellers">
                        {{ trans('update.booking_sellers') ?? 'Booking Sellers' }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_categories')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/categories*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/categories">
                        {{ trans('admin/main.booking_categories') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_filters')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/filters*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/filters">
                        {{ trans('admin/main.booking_filters') ?? 'Booking Filters' }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_specification')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/specification*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/specification">
                        {{ trans('admin/main.admin_booking_specification') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_discounts')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/discounts*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/discounts">
                        {{ trans('admin/main.booking_discounts') ?? 'Discounts' }}
                    </a>
                </li>
            @endcan

            @can('admin_booking_comments')
                <li class="{{ (request()->is(getAdminPanelUrl('/comments/bookings*', false)) and !request()->is(getAdminPanelUrl('/comments/bookings/reports', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/comments/bookings">{{ trans('admin/main.comments') }}</a>
                </li>
            @endcan

            @can('admin_comments_reports')
                <li class="{{ (request()->is(getAdminPanelUrl('/comments/bookings/reports', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/comments/bookings/reports">{{ trans('admin/main.comments_reports') }}</a>
                </li>
            @endcan

            @can('admin_booking_review')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/review*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/review">
                        {{ trans('admin/main.admin_booking_review') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_top_categories')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/top-categories*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/top-categories">
                        {{ trans('admin/main.booking_top_categories') ?? 'Booking Top Categories' }}
                    </a>
                </li>
            @endcan

            @can('admin_booking_feature_categories')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/feature-categories*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/feature-categories">
                        {{ trans('admin/main.booking_feature_categories') ?? 'Booking Feature Categories' }}
                    </a>
                </li>
            @endcan

            @can('admin_booking_featured_bookings')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/featured-bookings*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/featured-bookings">
                        Featured Bookings
                    </a>
                </li>
            @endcan

            @can('admin_booking_settings')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/settings*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/settings">
                        {{ trans('admin/main.settings') }}
                    </a>
                </li>
            @endcan


            @can('admin_booking_resources')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/resources*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/resources">
                        {{ trans('admin/main.booking_resources') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_rate_plan')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/rate*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/rate">
                        {{ trans('admin/main.booking_rate_plan') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_season')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/season*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/season">
                        {{ trans('admin/main.admin_booking_season') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_availability')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/availability*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/availability">
                        {{ trans('admin/main.admin_booking_availability') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_polices')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/policy*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/policy">
                        {{ trans('admin/main.admin_booking_polices') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_variants')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/variant*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/variant">
                        {{ trans('admin/main.admin_booking_variants') }}
                    </a>
                </li>
            @endcan

            @can('admin_booking_bundle')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/bundle*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/bundle">
                        {{ trans('admin/main.admin_booking_bundle') }}
                    </a>
                </li>
            @endcan

            @can('admin_booking')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/package*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/package">
                        {{ trans('admin/main.admin_booking_packages') }}
                    </a>
                </li>
            @endcan



            @can('admin_booking_time_slots')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/time-slot*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/time-slot">
                        {{ trans('admin/main.admin_booking_time_slots') }}
                    </a>
                </li>
            @endcan
            @can('admin_booking_imports')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/import*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/import">
                        {{ trans('admin/main.admin_booking_imports') }}
                    </a>
                </li>
            @endcan


            @can('admin_booking_favorite')
                <li class="{{ request()->is(getAdminPanelUrl('/booking/favorite*', false)) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/favorite">
                        {{ trans('admin/main.admin_booking_favorite') }}
                    </a>
                </li>
            @endcan

            @php
                $bookingModuleSidebarItems = [
                    'rules' => ['permission' => 'admin_booking_rules', 'title' => 'Booking Rules'],
                    'coupons' => ['permission' => 'admin_booking_coupons', 'title' => 'Booking Coupons'],
                    'assets' => ['permission' => 'admin_booking_assets', 'title' => 'Booking Assets'],
                    'reports' => ['permission' => 'admin_booking_reports', 'title' => 'Booking Reports'],
                    // 'featured' => ['permission' => 'admin_booking_featured', 'title' => 'Featured Bookings'],
                    'waitlists' => ['permission' => 'admin_booking_waitlists', 'title' => 'Booking Waitlists'],
                    'calendar-integrations' => ['permission' => 'admin_booking_calendar_integrations', 'title' => 'Calendar Integrations'],
                ];
            @endphp

            @foreach($bookingModuleSidebarItems as $moduleKey => $moduleItem)
                @can($moduleItem['permission'])
                    <li
                        class="{{ request()->is(getAdminPanelUrl('/booking/modules/' . $moduleKey . '*', false)) ? 'active' : '' }}">
                        <a class="nav-link" href="{{ getAdminPanelUrl() }}/booking/modules/{{ $moduleKey }}">
                            {{ $moduleItem['title'] }}
                        </a>
                    </li>
                @endcan
            @endforeach
        </ul>
    </li>
@endcan

@can('admin_blog')
    <li
        class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/blog*', false)) and !request()->is(getAdminPanelUrl('/blog/comments', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-brush class="icons" width="24px" height="24px" />
            <span>{{ trans('admin/main.blog') }}</span>
        </a>
        <ul class="dropdown-menu">

            @can('admin_blog_create')
                <li class="{{ (request()->is(getAdminPanelUrl('/blog/create', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/blog/create">{{ trans('admin/main.new_post') }}</a>
                </li>
            @endcan

            @can('admin_blog_lists')
                <li class="{{ (request()->is(getAdminPanelUrl('/blog', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/blog">{{ trans('site.posts') }}</a>
                </li>
            @endcan

            @can('admin_blog_categories')
                <li class="{{ (request()->is(getAdminPanelUrl('/blog/categories', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/blog/categories">{{ trans('admin/main.categories') }}</a>
                </li>
            @endcan

            @can('admin_blog_featured_categories')
                <li class="{{ (request()->is(getAdminPanelUrl('/blog/featured-categories', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/blog/featured-categories">{{ trans('update.featured_categories') }}</a>
                </li>
            @endcan

            @can('admin_blog_featured_contents')
                <li class="{{ (request()->is(getAdminPanelUrl('/blog/featured-contents', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/blog/featured-contents">{{ trans('update.featured_contents') }}</a>
                </li>
            @endcan
        </ul>
    </li>
@endcan()

@can('admin_pages')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/pages*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-3square class="icons" width="24px" height="24px" />
            <span>{{ trans('admin/main.pages') }}</span>
        </a>

        <ul class="dropdown-menu">

            @can('admin_pages_create')
                <li class="{{ (request()->is(getAdminPanelUrl('/pages/create', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/pages/create">{{ trans('admin/main.new_page') }}</a>
                </li>
            @endcan()

            @can('admin_pages_list')
                <li class="{{ (request()->is(getAdminPanelUrl('/pages', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/pages">{{ trans('admin/main.list') }}</a>
                </li>
            @endcan()

        </ul>
    </li>
@endcan

@can('admin_additional_pages')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/additional_page*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown">
            <x-iconsax-bul-monitor class="icons" width="24px" height="24px" />
            <span>{{ trans('admin/main.additional_pages_title') }}</span></a>
        <ul class="dropdown-menu">

            @can('admin_additional_pages_errors')
                @php
                    $isErrorPageActive = (request()->is(getAdminPanelUrl('/additional_page/404', false)) or request()->is(getAdminPanelUrl('/additional_page/500', false)) or request()->is(getAdminPanelUrl('/additional_page/419', false)) or request()->is(getAdminPanelUrl('/additional_page/403', false)))
                @endphp

                <li class="{{ $isErrorPageActive ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/additional_page/404">{{ trans('admin/main.errors_settings') }}</a>
                </li>
            @endcan()

            @can('admin_additional_pages_contact_us')
                <li class="{{ (request()->is(getAdminPanelUrl('/additional_page/contact_us', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/additional_page/contact_us">{{ trans('admin/main.contact_us') }}</a>
                </li>
            @endcan()

            @can('admin_additional_pages_navbar_links')
                <li class="{{ (request()->is(getAdminPanelUrl('/additional_page/navbar_links', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/additional_page/navbar_links">{{ trans('admin/main.top_navbar') }}</a>
                </li>
            @endcan()
        </ul>
    </li>
@endcan

@can('admin_testimonials')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/testimonials*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-smileys class="icons" width="24px" height="24px" />
            <span>{{ trans('admin/main.testimonials') }}</span>
        </a>
        <ul class="dropdown-menu">

            @can('admin_testimonials_create')
                <li class="{{ (request()->is(getAdminPanelUrl('/testimonials/create', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/testimonials/create">{{ trans('admin/main.new') }}</a>
                </li>
            @endcan()

            @can('admin_testimonials_list')
                <li class="{{ (request()->is(getAdminPanelUrl('/testimonials', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/testimonials">{{ trans('admin/main.list') }}</a>
                </li>
            @endcan()

        </ul>
    </li>
@endcan

@can('admin_tags')
    <li class="{{ (request()->is(getAdminPanelUrl('/tags', false))) ? 'active' : '' }}">
        <a href="{{ getAdminPanelUrl() }}/tags" class="nav-link">
            <x-iconsax-bul-tag-2 class="icons" width="24px" height="24px" />
            <span>{{ trans('admin/main.tags') }}</span>
        </a>
    </li>
@endcan()

@can('admin_regions')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/regions*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-map class="icons" width="24px" height="24px" />
            <span>{{ trans('update.regions') }}</span>
        </a>
        <ul class="dropdown-menu">
            @can('admin_regions_countries')
                <li class="{{ (request()->is(getAdminPanelUrl('/regions/countries', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/regions/countries">{{ trans('update.countries') }}</a>
                </li>
            @endcan()

            @can('admin_regions_provinces')
                <li class="{{ (request()->is(getAdminPanelUrl('/regions/provinces', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/regions/provinces">{{ trans('update.provinces') }}</a>
                </li>
            @endcan()

            @can('admin_regions_cities')
                <li class="{{ (request()->is(getAdminPanelUrl('/regions/cities', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/regions/cities">{{ trans('update.cities') }}</a>
                </li>
            @endcan()

            @can('admin_regions_districts')
                <li class="{{ (request()->is(getAdminPanelUrl('/regions/districts', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/regions/districts">{{ trans('update.districts') }}</a>
                </li>
            @endcan()
        </ul>
    </li>
@endcan

@can('admin_forms')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/forms*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-note-2 class="icons" width="24px" height="24px" />
            <span>{{ trans('update.form_builder') }}</span>
        </a>
        <ul class="dropdown-menu">
            @can('admin_forms_create')
                <li class="{{ (request()->is(getAdminPanelUrl('/forms/create', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/forms/create">{{ trans('admin/main.new') }}</a>
                </li>
            @endcan()

            @can('admin_forms_lists')
                <li class="{{ (request()->is(getAdminPanelUrl('/forms', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/forms">{{ trans('admin/main.list') }}</a>
                </li>
            @endcan()

            @can('admin_forms_submissions')
                <li class="{{ (request()->is(getAdminPanelUrl('/forms/submissions', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/forms/submissions">{{ trans('update.submissions') }}</a>
                </li>
            @endcan()

        </ul>
    </li>
@endcan

@can('admin_checkout_modules')
    <li class="nav-item {{ request()->is(getAdminPanelUrl('/checkout-modules*', false)) ? 'active' : '' }}">
        <a href="{{ getAdminPanelUrl() }}/checkout-modules" class="nav-link">
            <x-iconsax-bul-bag-2 class="icons" width="24px" height="24px" />
            <span>{{ trans('admin/main.checkout_modules') }}</span>
        </a>
    </li>
@endcan

@can('admin_ai_contents')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/ai-contents*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-cpu-charge class="icons" width="24px" height="24px" />
            <span>{{ trans('update.ai_contents') }}</span>
        </a>
        <ul class="dropdown-menu">

            @can('admin_ai_contents_templates_create')
                <li class="{{ (request()->is(getAdminPanelUrl('/ai-contents/templates/create', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/ai-contents/templates/create">{{ trans('update.new_template') }}</a>
                </li>
            @endcan()

            @can('admin_ai_contents_templates_lists')
                <li class="{{ (request()->is(getAdminPanelUrl('/ai-contents/templates', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/ai-contents/templates">{{ trans('update.service_template') }}</a>
                </li>
            @endcan()

            @can('admin_ai_contents_lists')
                <li class="{{ (request()->is(getAdminPanelUrl('/ai-contents/lists', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/ai-contents/lists">{{ trans('update.generated_contents') }}</a>
                </li>
            @endcan()

            @can('admin_ai_contents_settings')
                <li class="{{ (request()->is(getAdminPanelUrl('/ai-contents/settings', false))) ? 'active' : '' }}">
                    <a class="nav-link" href="{{ getAdminPanelUrl() }}/ai-contents/settings">{{ trans('update.settings') }}</a>
                </li>
            @endcan()

        </ul>
    </li>
@endcan

@can('admin_content_delete_requests_lists')
    <li class="nav-item {{ (request()->is(getAdminPanelUrl('/content-delete-requests*', false))) ? 'active' : '' }}">
        <a href="{{ getAdminPanelUrl() }}/content-delete-requests" class="nav-link">
            <x-iconsax-bul-video-remove class="icons" width="24px" height="24px" />
            <span>{{ trans('update.content_delete_requests') }}</span>
        </a>
    </li>
@endcan

@can('admin_instructor_finder')
    <li class="nav-item dropdown {{ (request()->is(getAdminPanelUrl('/instructor-finder*', false))) ? 'active' : '' }}">
        <a href="#" class="nav-link has-dropdown" data-toggle="dropdown">
            <x-iconsax-bul-user-search class="icons" width="24px" height="24px" />
            <span>{{ trans('update.instructor_finder') }}</span>
        </a>
        <ul class="dropdown-menu">

            @can('admin_instructor_finder_settings')
                <li class="{{ (request()->is(getAdminPanelUrl('/instructor-finder/settings', false))) ? 'active' : '' }}">
                    <a class="nav-link"
                        href="{{ getAdminPanelUrl() }}/instructor-finder/settings">{{ trans('update.settings') }}</a>
                </li>
            @endcan()

        </ul>
    </li>
@endcan
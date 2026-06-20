@extends('admin.layouts.app')

@push('styles_top')

@endpush

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.reviews') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.reviews') }}</div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="row">
            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card-statistic">
                    <div class="card-statistic__mask"></div>
                    <div class="card-statistic__wrap">
                        <div class="d-flex align-items-start justify-content-between">
                            <span class="text-gray-500 mt-8">{{ trans('admin/main.total_reviews') }}</span>
                            <div class="d-flex-center size-48 bg-primary-30 rounded-12">
                                <x-iconsax-bul-star class="icons text-primary" width="24px" height="24px"/>
                            </div>
                        </div>
                        <h5 class="font-24 mt-12 line-height-1 text-black">{{ $totalReviews }}</h5>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card-statistic">
                    <div class="card-statistic__mask"></div>
                    <div class="card-statistic__wrap">
                        <div class="d-flex align-items-start justify-content-between">
                            <span class="text-gray-500 mt-8">{{ trans('admin/main.published_reviews') }}</span>
                            <div class="d-flex-center size-48 bg-success-30 rounded-12">
                                <x-iconsax-bul-star class="icons text-success" width="24px" height="24px"/>
                            </div>
                        </div>
                        <h5 class="font-24 mt-12 line-height-1 text-black">{{ $publishedReviews }}</h5>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card-statistic">
                    <div class="card-statistic__mask"></div>
                    <div class="card-statistic__wrap">
                        <div class="d-flex align-items-start justify-content-between">
                            <span class="text-gray-500 mt-8">{{ trans('admin/main.rates_average') }}</span>
                            <div class="d-flex-center size-48 bg-secondary-30 rounded-12">
                                <x-iconsax-bul-calculator class="icons text-secondary" width="24px" height="24px"/>
                            </div>
                        </div>
                        <h5 class="font-24 mt-12 line-height-1 text-black">{{ $ratesAverage }}</h5>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6 col-sm-6 col-12">
                <div class="card-statistic">
                    <div class="card-statistic__mask"></div>
                    <div class="card-statistic__wrap">
                        <div class="d-flex align-items-start justify-content-between">
                            <span class="text-gray-500 mt-8">{{ trans('update.bookings_without_review') }}</span>
                            <div class="d-flex-center size-48 bg-danger-30 rounded-12">
                                <x-iconsax-bul-calendar class="icons text-danger" width="24px" height="24px"/>
                            </div>
                        </div>
                        <h5 class="font-24 mt-12 line-height-1 text-black">{{ $bookingsWithoutReview }}</h5>
                    </div>
                </div>
            </div>
        </div>

        <div class="section-body">

            {{-- Filters --}}
            <section class="card mt-32">
                <div class="card-body pb-4">
                    <form method="get" class="mb-0">
                        <div class="row">

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.search') }}</label>
                                    <input type="text" class="form-control" name="search"
                                           value="{{ request()->get('search') }}">
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.start_date') }}</label>
                                    <div class="input-group">
                                        <input type="date" id="fsdate" class="text-center form-control"
                                               name="from" value="{{ request()->get('from') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.end_date') }}</label>
                                    <div class="input-group">
                                        <input type="date" id="lsdate" class="text-center form-control"
                                               name="to" value="{{ request()->get('to') }}">
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('update.bookings') }}</label>
                                    <select name="booking_ids[]" multiple="multiple"
                                            class="form-control search-booking-select2"
                                            data-placeholder="{{ trans('update.search_booking') }}">
                                        @if(!empty($bookings) && $bookings->count() > 0)
                                            @foreach($bookings as $booking)
                                                <option value="{{ $booking->id }}" selected>{{ $booking->title }}</option>
                                            @endforeach
                                        @endif
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2">
                                <div class="form-group">
                                    <label class="input-label">{{ trans('admin/main.status') }}</label>
                                    <select name="status" class="form-control populate">
                                        <option value="">{{ trans('admin/main.all_status') }}</option>
                                        <option value="active"
                                            @if(request()->get('status') == 'active') selected @endif>
                                            {{ trans('admin/main.published') }}
                                        </option>
                                        <option value="pending"
                                            @if(request()->get('status') == 'pending') selected @endif>
                                            {{ trans('admin/main.hidden') }}
                                        </option>
                                    </select>
                                </div>
                            </div>

                            <div class="col-md-2 d-flex align-items-center">
                                <button type="submit" class="btn btn-primary btn-block btn-lg">
                                    {{ trans('admin/main.show_results') }}
                                </button>
                            </div>

                        </div>
                    </form>
                </div>
            </section>

            {{-- Table --}}
            <section class="card">
                <div class="card-body">
                    <table class="table custom-table font-14" id="datatable-details">
                        <tr>
                            <th class="text-left">{{ trans('update.booking') }}</th>
                            <th class="text-left">{{ trans('update.customer') }}</th>
                            <th>{{ trans('admin/main.comment') }}</th>
                            <th>{{ trans('admin/main.reply') }}</th>
                            <th>{{ trans('admin/main.rate') }} (5)</th>
                            <th>{{ trans('admin/main.created_at') }}</th>
                            <th>{{ trans('admin/main.status') }}</th>
                            <th>{{ trans('admin/main.actions') }}</th>
                        </tr>

                        @foreach($reviews as $review)
                            <tr>
                                {{-- Booking Title --}}
                                <td class="text-left">
                                    @if(!empty($review->bookings))
                                        <a class="text-dark"
                                           href="{{ $review->bookings->getUrl() }}"
                                           target="_blank">
                                            {{ $review->bookings->title }}
                                        </a>
                                    @else
                                        <span class="text-gray-500">{{ trans('update.deleted_item') }}</span>
                                    @endif
                                </td>

                                {{-- Creator --}}
                                <td class="text-left text-dark">
                                    {{ $review->creator->full_name ?? '-' }}
                                </td>

                                {{-- Comment (show modal) --}}
                                <td>
                                    <button type="button"
                                            class="js-show-description btn btn-sm btn-outline-primary">
                                        {{ trans('admin/main.show') }}
                                    </button>
                                    <input type="hidden" value="{{ nl2br($review->description) }}">
                                </td>

                                {{-- Reply count --}}
                                <td>{{ $review->comments_count }}</td>

                                {{-- Rate --}}
                                <td>{{ $review->rates }}</td>

                                {{-- Date --}}
                                <td>{{ dateTimeFormat($review->created_at, 'j M Y | H:i') }}</td>

                                {{-- Status badge --}}
                                <td>
                                    @if($review->status == 'active')
                                        <span class="badge-status text-success bg-success-30">
                                            {{ trans('admin/main.published') }}
                                        </span>
                                    @else
                                        <span class="badge-status text-warning bg-warning-30">
                                            {{ trans('admin/main.hidden') }}
                                        </span>
                                    @endif
                                </td>

                                {{-- Actions dropdown --}}
                                <td width="50">
                                    <div class="btn-group dropdown table-actions position-relative">
                                        <button type="button" class="btn-transparent dropdown-toggle"
                                                data-toggle="dropdown">
                                            <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                        </button>

                                        <div class="dropdown-menu dropdown-menu-right">

                                            @can('admin_booking_review_status_toggle')
                                                <a href="{{ getAdminPanelUrl() }}/booking/review/{{ $review->id }}/toggleStatus"
                                                   class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                    @if($review->status == 'active')
                                                        <x-iconsax-lin-eye-slash class="icons text-warning mr-2" width="18px" height="18px"/>
                                                        <span class="text-warning">{{ trans('admin/main.hidden') }}</span>
                                                    @else
                                                        <x-iconsax-lin-eye class="icons text-success mr-2" width="18px" height="18px"/>
                                                        <span class="text-success">{{ trans('admin/main.publish') }}</span>
                                                    @endif
                                                </a>
                                            @endcan

                                            @can('admin_booking_review_detail_show')
                                                <input type="hidden" class="js-product_quality" value="{{ $review->product_quality }}">
                                                <input type="hidden" class="js-purchase_worth" value="{{ $review->purchase_worth }}">
                                                <input type="hidden" class="js-delivery_quality" value="{{ $review->delivery_quality }}">
                                                <input type="hidden" class="js-seller_quality" value="{{ $review->seller_quality }}">

                                                <button type="button"
                                                        class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4 js-show-product-review-details">
                                                    <x-iconsax-lin-star class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                    <span class="text-gray-500 font-14">{{ trans('admin/main.view_rates_details') }}</span>
                                                </button>
                                            @endcan

                                            @can('admin_booking_reviews_reply')
                                                <a href="{{ getAdminPanelUrl() }}/booking/review/{{ $review->id }}/reply"
                                                   target="_blank"
                                                   class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                    <x-iconsax-lin-messages-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                    <span class="text-gray-500 font-14">{{ trans('admin/main.reply') }}</span>
                                                </a>
                                            @endcan

                                            @can('admin_booking_review_delete')
                                                @include('admin.includes.delete_button', [
                                                    'url'       => getAdminPanelUrl().'/booking/review/'.$review->id.'/delete',
                                                    'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                    'btnText'   => trans('admin/main.delete'),
                                                    'btnIcon'   => 'trash',
                                                    'iconType'  => 'lin',
                                                    'iconClass' => 'text-danger mr-2',
                                                ])
                                            @endcan

                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </table>
                </div>

                <div class="card-footer text-center">
                    {{ $reviews->appends(request()->input())->links() }}
                </div>
            </section>

        </div>
    </section>

    {{-- Rate Details Modal --}}
    <div class="modal fade" id="reviewRateDetail" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('admin/main.view_rates_details') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                        <span class="font-weight-bold">{{ trans('update.product') }}:</span>
                        <span class="js-product_quality"></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                        <span class="font-weight-bold">{{ trans('product.purchase_worth') }}:</span>
                        <span class="js-purchase_worth"></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                        <span class="font-weight-bold">{{ trans('update.delivery') }}:</span>
                        <span class="js-delivery_quality"></span>
                    </div>
                    <div class="d-flex align-items-center justify-content-between border-bottom py-2">
                        <span class="font-weight-bold">{{ trans('update.seller') }}:</span>
                        <span class="js-seller_quality"></span>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                </div>
            </div>
        </div>
    </div>

    {{-- Comment Show Modal --}}
    <div class="modal fade" id="contactMessage" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">{{ trans('admin/main.message') }}</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body"></div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary"
                            data-dismiss="modal">{{ trans('admin/main.close') }}</button>
                </div>
            </div>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    {{-- Reuse same reviews JS (handles js-show-description & js-show-product-review-details) --}}
    <script src="/assets/admin/js/parts/reviews.min.js"></script>
@endpush
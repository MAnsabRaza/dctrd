@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.admin_booking_review') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_review') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @php
                                $reviewEditActive = (
                                    (!empty($errors) && $errors->any()) ||
                                    !empty($editReview)
                                );
                            @endphp

                            <ul class="nav nav-pills" id="reviewTab" role="tablist">
                                @can('admin_booking_review')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $reviewEditActive ? '' : 'active' }}"
                                           id="reviews-tab" data-toggle="tab" href="#reviews" role="tab">
                                            {{ trans('admin/main.admin_booking_review') }}
                                        </a>
                                    </li>
                                @endcan

                                @if(!empty($editReview))
                                    @can('admin_booking_review_edit')
                                        <li class="nav-item">
                                            <a class="nav-link {{ $reviewEditActive ? 'active' : '' }}"
                                               id="editReview-tab" data-toggle="tab" href="#editReview" role="tab">
                                                {{ trans('admin/main.edit_booking_review') }}
                                            </a>
                                        </li>
                                    @endcan
                                @endif
                            </ul>

                            <div class="tab-content mt-3">

                                {{-- LIST TAB --}}
                                @can('admin_booking_review')
                                    <div class="tab-pane fade {{ $reviewEditActive ? '' : 'active show' }}"
                                         id="reviews" role="tabpanel">

                                        @if(!empty($reviews) && $reviews->count())
                                            <div class="table-responsive">
                                                <table class="table custom-table font-14">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th>{{ trans('admin/main.booking') }}</th>
                                                            <th>{{ trans('admin/main.customer') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.rating') }}</th>
                                                            <th>{{ trans('admin/main.comment') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.replied') }}</th>
                                                            <th>{{ trans('admin/main.action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($reviews as $review)
                                                            <tr>
                                                                <td>{{ $review->id }}</td>
                                                                <td>
                                                                    @if($review->booking)
                                                                        #{{ $review->booking->id }} - {{ $review->booking->title }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td>
                                                                    @if($review->customer)
                                                                        {{ $review->customer->full_name ?? $review->customer->name }}
                                                                    @else
                                                                        -
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    <span class="badge badge-warning text-white">
                                                                        ★ {{ $review->rating }}/5
                                                                    </span>
                                                                </td>
                                                                <td style="max-width:200px; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                                                                    {{ $review->comment }}
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($review->status === 'active')
                                                                        <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                                                    @elseif($review->status === 'rejected')
                                                                        <span class="badge badge-danger">{{ trans('admin/main.rejected') }}</span>
                                                                    @else
                                                                        <span class="badge badge-warning">{{ trans('admin/main.pending') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($review->reply)
                                                                        <span class="badge badge-info">{{ trans('admin/main.yes') }}</span>
                                                                    @else
                                                                        <span class="badge badge-secondary">{{ trans('admin/main.no') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td width="80px">
                                                                    <div class="btn-group dropdown table-actions position-relative">
                                                                        <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                            <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                        </button>
                                                                        <div class="dropdown-menu dropdown-menu-right">
                                                                            @can('admin_booking_review_edit')
                                                                                <a href="{{ getAdminPanelUrl() }}/booking/review/{{ $review->id }}/edit"
                                                                                   class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                    <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                    <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                                </a>
                                                                            @endcan

                                                                            @can('admin_booking_review_delete')
                                                                                @include('admin.includes.delete_button', [
                                                                                    'url'       => getAdminPanelUrl() . '/booking/review/' . $review->id . '/delete',
                                                                                    'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                                    'btnText'   => trans('admin/main.delete'),
                                                                                    'btnIcon'   => 'trash',
                                                                                    'iconType'  => 'lin',
                                                                                    'iconClass' => 'text-danger mr-2'
                                                                                ])
                                                                            @endcan
                                                                        </div>
                                                                    </div>
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>

                                            {{ $reviews->links() }}

                                        @else
                                            <div class="text-center text-gray-500 mt-30">
                                                {{ trans('admin/main.no_result') }}
                                            </div>
                                        @endif
                                    </div>
                                @endcan

                                {{-- EDIT TAB --}}
                                @if(!empty($editReview))
                                    @can('admin_booking_review_edit')
                                        <div class="tab-pane fade {{ $reviewEditActive ? 'active show' : '' }}"
                                             id="editReview" role="tabpanel">
                                            <div class="row">
                                                <div class="col-12 col-md-6">

                                                    {{-- Review Info (Read Only) --}}
                                                    <div class="card bg-light mb-3">
                                                        <div class="card-body">
                                                            <h6 class="font-weight-bold mb-3">{{ trans('admin/main.review_details') }}</h6>
                                                            <p class="mb-1">
                                                                <strong>{{ trans('admin/main.customer') }}:</strong>
                                                                {{ $editReview->customer->full_name ?? $editReview->customer->name ?? '-' }}
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>{{ trans('admin/main.booking') }}:</strong>
                                                                @if($editReview->booking)
                                                                    #{{ $editReview->booking->id }} - {{ $editReview->booking->title }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </p>
                                                            <p class="mb-1">
                                                                <strong>{{ trans('admin/main.rating') }}:</strong>
                                                                ★ {{ $editReview->rating }}/5
                                                            </p>
                                                            @if($editReview->value_rating)
                                                                <p class="mb-1"><strong>Value Rating:</strong> ★ {{ $editReview->value_rating }}/5</p>
                                                            @endif
                                                            @if($editReview->delivery_rating)
                                                                <p class="mb-1"><strong>Delivery Rating:</strong> ★ {{ $editReview->delivery_rating }}/5</p>
                                                            @endif
                                                            @if($editReview->seller_rating)
                                                                <p class="mb-1"><strong>Seller Rating:</strong> ★ {{ $editReview->seller_rating }}/5</p>
                                                            @endif
                                                            <p class="mb-1">
                                                                <strong>{{ trans('admin/main.comment') }}:</strong>
                                                                {{ $editReview->comment }}
                                                            </p>
                                                            <p class="mb-0">
                                                                <strong>{{ trans('admin/main.date') }}:</strong>
                                                                {{ $editReview->created_at->format('Y-m-d H:i') }}
                                                            </p>
                                                        </div>
                                                    </div>

                                                    {{-- Edit Form --}}
                                                    <form action="{{ getAdminPanelUrl() }}/booking/review/{{ $editReview->id }}/update"
                                                          method="post">
                                                        {{ csrf_field() }}

                                                        {{-- Status --}}
                                                        <div class="form-group">
                                                            <label>{{ trans('admin/main.status') }}</label>
                                                            <select name="status" class="form-control @error('status') is-invalid @enderror">
                                                                <option value="pending"  {{ $editReview->status === 'pending'  ? 'selected' : '' }}>
                                                                    {{ trans('admin/main.pending') }}
                                                                </option>
                                                                <option value="active"   {{ $editReview->status === 'active'   ? 'selected' : '' }}>
                                                                    {{ trans('admin/main.active') }}
                                                                </option>
                                                                <option value="rejected" {{ $editReview->status === 'rejected' ? 'selected' : '' }}>
                                                                    {{ trans('admin/main.rejected') }}
                                                                </option>
                                                            </select>
                                                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                        </div>

                                                        {{-- Admin Reply --}}
                                                        <div class="form-group">
                                                            <label>{{ trans('admin/main.reply') }}</label>
                                                            <textarea name="reply" rows="4"
                                                                      class="form-control @error('reply') is-invalid @enderror"
                                                                      placeholder="{{ trans('admin/main.write_reply') }}">{{ old('reply', $editReview->reply) }}</textarea>
                                                            @error('reply') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                            @if($editReview->replied_at)
                                                                <small class="text-muted">
                                                                    {{ trans('admin/main.last_replied') }}: {{ $editReview->replied_at->format('Y-m-d H:i') }}
                                                                </small>
                                                            @endif
                                                        </div>

                                                        <div class="text-right col-12 mt-3">
                                                            <a href="{{ getAdminPanelUrl() }}/booking/review"
                                                               class="btn btn-secondary mr-2">
                                                                {{ trans('admin/main.cancel') }}
                                                            </a>
                                                            <button type="submit" class="btn btn-primary">
                                                                {{ trans('admin/main.save_change') }}
                                                            </button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        </div>
                                    @endcan
                                @endif

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
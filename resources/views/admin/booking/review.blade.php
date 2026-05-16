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
                            $createActive = (
                                (!empty($errors) && $errors->any()) ||
                                !empty($editReview) ||
                                (empty($reviews) || !$reviews->count())
                            );
                        @endphp

                        <ul class="nav nav-pills" id="reviewTab" role="tablist">

                            @can('admin_booking_review')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       id="list-tab" data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_review') }}
                                    </a>
                                </li>
                            @endcan

                            @can('admin_booking_review_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="create-tab" data-toggle="tab" href="#createTab" role="tab">
                                        {{ !empty($editReview) ? trans('admin/main.edit_booking_review') : trans('admin/main.create_booking_review') }}
                                    </a>
                                </li>
                            @endcan

                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
                            @can('admin_booking_review')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                     id="listTab" role="tabpanel">

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
                                                                    <span class="text-muted">-</span>
                                                                @endif
                                                            </td>
                                                            <td>
                                                                @if($review->customer)
                                                                    {{ $review->customer->full_name ?? $review->customer->name }}
                                                                @else
                                                                    <span class="text-muted">-</span>
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

                            {{-- CREATE / EDIT TAB --}}
                            @can('admin_booking_review_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="{{ getAdminPanelUrl() }}/booking/review/{{ !empty($editReview) ? $editReview->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                {{-- Booking --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                    @php $selectedBooking = !empty($editReview) ? $editReview->booking_id : old('booking_id'); @endphp
                                                    <select name="booking_id" class="form-control @error('booking_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($bookings as $booking)
                                                            <option value="{{ $booking->id }}"
                                                                {{ (string)$selectedBooking === (string)$booking->id ? 'selected' : '' }}>
                                                                #{{ $booking->id }} - {{ $booking->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Customer --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.customer') }} <span class="text-danger">*</span></label>
                                                    @php $selectedUser = !empty($editReview) ? $editReview->customer_id : old('customer_id'); @endphp
                                                    <select name="customer_id" class="form-control @error('customer_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($users as $user)
                                                            <option value="{{ $user->id }}"
                                                                {{ (string)$selectedUser === (string)$user->id ? 'selected' : '' }}>
                                                                #{{ $user->id }} - {{ $user->full_name ?? $user->name }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('customer_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Rating --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.rating') }} (1-5) <span class="text-danger">*</span></label>
                                                    <input type="number" name="rating" min="1" max="5"
                                                           class="form-control @error('rating') is-invalid @enderror"
                                                           value="{{ !empty($editReview) ? $editReview->rating : old('rating', 5) }}"/>
                                                    @error('rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Comment --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.comment') }} <span class="text-danger">*</span></label>
                                                    <textarea name="comment" rows="3"
                                                              class="form-control @error('comment') is-invalid @enderror"
                                                              placeholder="{{ trans('admin/main.write_comment') }}">{{ !empty($editReview) ? $editReview->comment : old('comment') }}</textarea>
                                                    @error('comment') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Value Rating --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.value_rating') }} (1-5)
                                                        <small class="text-muted">{{ trans('admin/main.optional') }}</small>
                                                    </label>
                                                    <input type="number" name="value_rating" min="1" max="5"
                                                           class="form-control @error('value_rating') is-invalid @enderror"
                                                           value="{{ !empty($editReview) ? $editReview->value_rating : old('value_rating') }}"/>
                                                    @error('value_rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Delivery Rating --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.delivery_rating') }} (1-5)
                                                        <small class="text-muted">{{ trans('admin/main.optional') }}</small>
                                                    </label>
                                                    <input type="number" name="delivery_rating" min="1" max="5"
                                                           class="form-control @error('delivery_rating') is-invalid @enderror"
                                                           value="{{ !empty($editReview) ? $editReview->delivery_rating : old('delivery_rating') }}"/>
                                                    @error('delivery_rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Seller Rating --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.seller_rating') }} (1-5)
                                                        <small class="text-muted">{{ trans('admin/main.optional') }}</small>
                                                    </label>
                                                    <input type="number" name="seller_rating" min="1" max="5"
                                                           class="form-control @error('seller_rating') is-invalid @enderror"
                                                           value="{{ !empty($editReview) ? $editReview->seller_rating : old('seller_rating') }}"/>
                                                    @error('seller_rating') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Status --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.status') }} <span class="text-danger">*</span></label>
                                                    @php $selectedStatus = !empty($editReview) ? $editReview->status : old('status', 'pending'); @endphp
                                                    <select name="status" class="form-control @error('status') is-invalid @enderror">
                                                        <option value="pending"  {{ $selectedStatus === 'pending'  ? 'selected' : '' }}>{{ trans('admin/main.pending') }}</option>
                                                        <option value="active"   {{ $selectedStatus === 'active'   ? 'selected' : '' }}>{{ trans('admin/main.active') }}</option>
                                                        <option value="rejected" {{ $selectedStatus === 'rejected' ? 'selected' : '' }}>{{ trans('admin/main.rejected') }}</option>
                                                    </select>
                                                    @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                </div>

                                                {{-- Admin Reply --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.reply') }}
                                                        <small class="text-muted">{{ trans('admin/main.optional') }}</small>
                                                    </label>
                                                    <textarea name="reply" rows="3"
                                                              class="form-control @error('reply') is-invalid @enderror"
                                                              placeholder="{{ trans('admin/main.write_reply') }}">{{ !empty($editReview) ? $editReview->reply : old('reply') }}</textarea>
                                                    @error('reply') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    @if(!empty($editReview) && $editReview->replied_at)
                                                        <small class="text-muted">
                                                            {{ trans('admin/main.last_replied') }}: {{ $editReview->replied_at->format('Y-m-d H:i') }}
                                                        </small>
                                                    @endif
                                                </div>

                                                <div class="text-right col-12 mt-3">
                                                    @if(!empty($editReview))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/review"
                                                           class="btn btn-secondary mr-2">
                                                            {{ trans('admin/main.cancel') }}
                                                        </a>
                                                    @endif
                                                    <button type="submit" class="btn btn-primary">
                                                        {{ !empty($editReview) ? trans('admin/main.update_booking_review') : trans('admin/main.create_booking_review') }}
                                                    </button>
                                                </div>

                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan

                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>
@endsection
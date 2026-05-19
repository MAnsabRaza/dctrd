@extends('admin.layouts.app')

@section('content')
<section class="section">

    <div class="section-header">
        <h1>Booking Comments</h1>

        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">
                    {{ trans('admin/main.dashboard') }}
                </a>
            </div>

            <div class="breadcrumb-item">
                Booking Comments
            </div>
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
                                !empty($editComment) ||
                                (empty($comments) || !$comments->count())
                            );
                        @endphp

                        {{-- TABS --}}
                        <ul class="nav nav-pills" id="commentTab" role="tablist">

                            <li class="nav-item">
                                <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                   id="list-tab"
                                   data-toggle="tab"
                                   href="#listTab"
                                   role="tab">

                                    Booking Comments

                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                   id="create-tab"
                                   data-toggle="tab"
                                   href="#createTab"
                                   role="tab">

                                    {{ !empty($editComment) ? 'Edit Comment' : 'Create Comment' }}

                                </a>
                            </li>

                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
                            <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                 id="listTab"
                                 role="tabpanel">

                                @if(!empty($comments) && $comments->count())

                                    <div class="table-responsive">

                                        <table class="table custom-table font-14">

                                            <thead>
                                                <tr>
                                                    <th>Booking</th>
                                                    <th>User</th>
                                                    <th>Comment</th>
                                                    <th class="text-center">Status</th>
                                                    <th class="text-center">Date</th>
                                                    <th>{{ trans('admin/main.action') }}</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                @foreach($comments as $comment)

                                                    <tr>

                                                        <td>
                                                            @if($comment->booking)
                                                                #{{ $comment->booking->id }}
                                                                —
                                                                {{ $comment->booking->title }}
                                                            @endif
                                                        </td>

                                                        <td>
                                                            {{ optional($comment->user)->full_name }}
                                                        </td>

                                                        <td width="300">
                                                            {{ $comment->comment }}
                                                        </td>

                                                        <td class="text-center">

                                                            @if($comment->is_active)
                                                                <span class="badge badge-success">
                                                                    Active
                                                                </span>
                                                            @else
                                                                <span class="badge badge-danger">
                                                                    Inactive
                                                                </span>
                                                            @endif

                                                        </td>

                                                        <td class="text-center">
                                                            {{ dateTimeFormat($comment->created_at, 'j M Y') }}
                                                        </td>

                                                        <td width="80px">

                                                            <div class="btn-group dropdown table-actions position-relative">

                                                                <button type="button"
                                                                        class="btn-transparent dropdown-toggle"
                                                                        data-toggle="dropdown">

                                                                    <x-iconsax-lin-more
                                                                        class="icons text-gray-500"
                                                                        width="20px"
                                                                        height="20px"/>

                                                                </button>

                                                                <div class="dropdown-menu dropdown-menu-right">

                                                                    <a href="{{ getAdminPanelUrl() }}/booking/comment/{{ $comment->id }}/edit"
                                                                       class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">

                                                                        <x-iconsax-lin-edit-2
                                                                            class="icons text-gray-500 mr-2"
                                                                            width="18px"
                                                                            height="18px"/>

                                                                        <span class="text-gray-500 font-14">
                                                                            {{ trans('admin/main.edit') }}
                                                                        </span>

                                                                    </a>

                                                                    @include('admin.includes.delete_button', [
                                                                        'url' => getAdminPanelUrl() . '/booking/comment/' . $comment->id . '/delete',
                                                                        'btnClass' => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                        'btnText' => trans('admin/main.delete'),
                                                                        'btnIcon' => 'trash',
                                                                        'iconType' => 'lin',
                                                                        'iconClass' => 'text-danger mr-2'
                                                                    ])

                                                                </div>

                                                            </div>

                                                        </td>

                                                    </tr>

                                                @endforeach

                                            </tbody>

                                        </table>

                                    </div>

                                    {{ $comments->links() }}

                                @else

                                    <div class="text-center text-gray-500 mt-30">
                                        {{ trans('admin/main.no_result') }}
                                    </div>

                                @endif

                            </div>

                            {{-- CREATE / EDIT TAB --}}
                            <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                 id="createTab"
                                 role="tabpanel">

                                <div class="row">
                                    <div class="col-12 col-md-6">

                                        <form action="{{ getAdminPanelUrl() }}/booking/comment/{{ !empty($editComment) ? $editComment->id . '/update' : 'store' }}"
                                              method="post">

                                            {{ csrf_field() }}

                                            {{-- BOOKING --}}
                                            <div class="form-group">

                                                <label>
                                                    Booking
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <select name="booking_id"
                                                        class="form-control @error('booking_id') is-invalid @enderror">

                                                    <option value="">
                                                        Select Booking
                                                    </option>

                                                    @foreach($bookings as $booking)

                                                        <option value="{{ $booking->id }}"
                                                            {{ (!empty($editComment) && $editComment->booking_id == $booking->id) ? 'selected' : '' }}>

                                                            #{{ $booking->id }}
                                                            —
                                                            {{ $booking->title }}

                                                        </option>

                                                    @endforeach

                                                </select>

                                                @error('booking_id')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror

                                            </div>

                                            {{-- COMMENT --}}
                                            <div class="form-group">

                                                <label>
                                                    Comment
                                                    <span class="text-danger">*</span>
                                                </label>

                                                <textarea name="comment"
                                                          rows="5"
                                                          class="form-control @error('comment') is-invalid @enderror">{{ !empty($editComment) ? $editComment->comment : old('comment') }}</textarea>

                                                @error('comment')
                                                    <div class="invalid-feedback">
                                                        {{ $message }}
                                                    </div>
                                                @enderror

                                            </div>

                                            {{-- STATUS --}}
                                            <div class="form-group">

                                                <div class="custom-control custom-switch">

                                                    <input type="checkbox"
                                                           name="is_active"
                                                           class="custom-control-input"
                                                           id="is_active"
                                                           {{ (empty($editComment) || (!empty($editComment) && $editComment->is_active)) ? 'checked' : '' }}>

                                                    <label class="custom-control-label"
                                                           for="is_active">

                                                        Active

                                                    </label>

                                                </div>

                                            </div>

                                            {{-- ACTIONS --}}
                                            <div class="text-right col-12 mt-3">

                                                @if(!empty($editComment))

                                                    <a href="{{ getAdminPanelUrl() }}/booking/comment"
                                                       class="btn btn-secondary mr-2">

                                                        {{ trans('admin/main.cancel') }}

                                                    </a>

                                                @endif

                                                <button type="submit"
                                                        class="btn btn-primary">

                                                    {{ trans('admin/main.save_change') }}

                                                </button>

                                            </div>

                                        </form>

                                    </div>
                                </div>

                            </div>

                        </div>{{-- tab-content --}}

                    </div>
                </div>

            </div>
        </div>

    </div>

</section>
@endsection
@extends('admin.layouts.app')

@section('content')
<section class="section">

    <div class="section-header">
        <h1>Booking Favorites</h1>

        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">
                    {{ trans('admin/main.dashboard') }}
                </a>
            </div>

            <div class="breadcrumb-item">
                Booking Favorites
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
                                !empty($editFavorite) ||
                                (empty($favorites) || !$favorites->count())
                            );
                        @endphp

                        {{-- TABS --}}
                        <ul class="nav nav-pills" id="favoriteTab" role="tablist">

                            <li class="nav-item">
                                <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                   id="list-tab"
                                   data-toggle="tab"
                                   href="#listTab"
                                   role="tab">

                                    Booking Favorites

                                </a>
                            </li>

                            <li class="nav-item">
                                <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                   id="create-tab"
                                   data-toggle="tab"
                                   href="#createTab"
                                   role="tab">

                                    {{ !empty($editFavorite) ? 'Edit Favorite' : 'Create Favorite' }}

                                </a>
                            </li>

                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
                            <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                 id="listTab"
                                 role="tabpanel">

                                @if(!empty($favorites) && $favorites->count())

                                    <div class="table-responsive">

                                        <table class="table custom-table font-14">

                                            <thead>
                                                <tr>
                                                    <th>Booking</th>
                                                    <th>User</th>
                                                    <th class="text-center">Date</th>
                                                    <th>{{ trans('admin/main.action') }}</th>
                                                </tr>
                                            </thead>

                                            <tbody>

                                                @foreach($favorites as $favorite)

                                                    <tr>

                                                        <td>
                                                            @if($favorite->booking)
                                                                #{{ $favorite->booking->id }}
                                                                —
                                                                {{ $favorite->booking->title }}
                                                            @endif
                                                        </td>

                                                        <td>
                                                            {{ optional($favorite->user)->full_name }}
                                                        </td>

                                                        <td class="text-center">
                                                            {{ dateTimeFormat($favorite->created_at, 'j M Y') }}
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

                                                                    <a href="{{ getAdminPanelUrl() }}/booking/favorite/{{ $favorite->id }}/edit"
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
                                                                        'url' => getAdminPanelUrl() . '/booking/favorite/' . $favorite->id . '/delete',
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

                                    {{ $favorites->links() }}

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

                                        <form action="{{ getAdminPanelUrl() }}/booking/favorite/{{ !empty($editFavorite) ? $editFavorite->id . '/update' : 'store' }}"
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
                                                            {{ (!empty($editFavorite) && $editFavorite->booking_id == $booking->id) ? 'selected' : '' }}>

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

                                            {{-- ACTIONS --}}
                                            <div class="text-right col-12 mt-3">

                                                @if(!empty($editFavorite))

                                                    <a href="{{ getAdminPanelUrl() }}/booking/favorite"
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
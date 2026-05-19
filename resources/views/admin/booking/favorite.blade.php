@extends('admin.layouts.app')

@section('content')

<section class="section">

    <div class="section-header">
        <h1>Booking Favorites</h1>
    </div>

    <div class="section-body">

        <div class="card">
            <div class="card-body">

                {{-- TABS --}}
                <ul class="nav nav-pills mb-3">

                    <li class="nav-item">
                        <a class="nav-link active"
                           data-toggle="tab"
                           href="#listTab">
                            Favorites List
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                           data-toggle="tab"
                           href="#createTab">
                            {{ !empty($editFavorite) ? 'Edit Favorite' : 'Create Favorite' }}
                        </a>
                    </li>

                </ul>

                <div class="tab-content">

                    {{-- LIST TAB --}}
                    <div class="tab-pane fade show active" id="listTab">

                        <div class="table-responsive">

                            <table class="table custom-table">

                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>User</th>
                                        <th>Booking</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach($favorites as $favorite)

                                        <tr>

                                            <td>{{ $favorite->id }}</td>

                                            <td>
                                                @if($favorite->user)
                                                    {{ $favorite->user->full_name }}
                                                @endif
                                            </td>

                                            <td>
                                                @if($favorite->booking)
                                                    #{{ $favorite->booking->id }}
                                                    —
                                                    {{ $favorite->booking->title }}
                                                @endif
                                            </td>

                                            <td>
                                                {{ dateTimeFormat($favorite->created_at, 'j M Y') }}
                                            </td>

                                            <td width="120">

                                                <a href="{{ getAdminPanelUrl() }}/booking/favorite/{{ $favorite->id }}/edit"
                                                   class="btn btn-sm btn-primary">
                                                    Edit
                                                </a>

                                                @include('admin.includes.delete_button', [
                                                    'url' => getAdminPanelUrl() . '/booking/favorite/' . $favorite->id . '/delete',
                                                    'btnClass' => 'btn btn-sm btn-danger',
                                                    'btnText' => 'Delete'
                                                ])

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        {{ $favorites->links() }}

                    </div>

                    {{-- CREATE / EDIT TAB --}}
                    <div class="tab-pane fade" id="createTab">

                        <div class="row">
                            <div class="col-md-6">

                                <form method="post"
                                      action="{{ getAdminPanelUrl() }}/booking/favorite/{{ !empty($editFavorite) ? $editFavorite->id . '/update' : 'store' }}">

                                    {{ csrf_field() }}

                                    {{-- USER --}}
                                    <div class="form-group">

                                        <label>User</label>

                                        <select name="user_id"
                                                class="form-control">

                                            <option value="">Select User</option>

                                            @foreach($users as $user)

                                                <option value="{{ $user->id }}"
                                                    {{ (!empty($editFavorite) && $editFavorite->user_id == $user->id) ? 'selected' : '' }}>

                                                    {{ $user->full_name }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                    {{-- BOOKING --}}
                                    <div class="form-group">

                                        <label>Booking</label>

                                        <select name="booking_id"
                                                class="form-control">

                                            <option value="">Select Booking</option>

                                            @foreach($bookings as $booking)

                                                <option value="{{ $booking->id }}"
                                                    {{ (!empty($editFavorite) && $editFavorite->booking_id == $booking->id) ? 'selected' : '' }}>

                                                    #{{ $booking->id }}
                                                    —
                                                    {{ $booking->title }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                    <button class="btn btn-primary">
                                        Save
                                    </button>

                                </form>

                            </div>
                        </div>

                    </div>

                </div>

            </div>
        </div>

    </div>

</section>

@endsection
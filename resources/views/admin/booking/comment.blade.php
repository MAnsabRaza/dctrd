@extends('admin.layouts.app')

@section('content')

<section class="section">

    <div class="section-header">
        <h1>Booking Comments</h1>
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
                            Comments List
                        </a>
                    </li>

                    <li class="nav-item">
                        <a class="nav-link"
                           data-toggle="tab"
                           href="#createTab">
                            {{ !empty($editComment) ? 'Edit Comment' : 'Create Comment' }}
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
                                        <th>Comment</th>
                                        <th>Status</th>
                                        <th>Date</th>
                                        <th>Action</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    @foreach($comments as $comment)

                                        <tr>

                                            <td>{{ $comment->id }}</td>

                                            <td>
                                                @if($comment->user)
                                                    {{ $comment->user->full_name }}
                                                @endif
                                            </td>

                                            <td>
                                                @if($comment->booking)
                                                    #{{ $comment->booking->id }}
                                                    —
                                                    {{ $comment->booking->title }}
                                                @endif
                                            </td>

                                            <td width="300">
                                                {{ $comment->comment }}
                                            </td>

                                            <td>

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

                                            <td>
                                                {{ dateTimeFormat($comment->created_at, 'j M Y') }}
                                            </td>

                                            <td width="120">

                                                <a href="{{ getAdminPanelUrl() }}/booking/comment/{{ $comment->id }}/edit"
                                                   class="btn btn-sm btn-primary">
                                                    Edit
                                                </a>

                                                @include('admin.includes.delete_button', [
                                                    'url' => getAdminPanelUrl() . '/booking/comment/' . $comment->id . '/delete',
                                                    'btnClass' => 'btn btn-sm btn-danger',
                                                    'btnText' => 'Delete'
                                                ])

                                            </td>

                                        </tr>

                                    @endforeach

                                </tbody>

                            </table>

                        </div>

                        {{ $comments->links() }}

                    </div>

                    {{-- CREATE / EDIT TAB --}}
                    <div class="tab-pane fade" id="createTab">

                        <div class="row">
                            <div class="col-md-6">

                                <form method="post"
                                      action="{{ getAdminPanelUrl() }}/booking/comment/{{ !empty($editComment) ? $editComment->id . '/update' : 'store' }}">

                                    {{ csrf_field() }}

                                    {{-- BOOKING --}}
                                    <div class="form-group">

                                        <label>Booking</label>

                                        <select name="booking_id"
                                                class="form-control">

                                            <option value="">Select Booking</option>

                                            @foreach($bookings as $booking)

                                                <option value="{{ $booking->id }}"
                                                    {{ (!empty($editComment) && $editComment->booking_id == $booking->id) ? 'selected' : '' }}>

                                                    #{{ $booking->id }}
                                                    —
                                                    {{ $booking->title }}

                                                </option>

                                            @endforeach

                                        </select>

                                    </div>

                                    {{-- COMMENT --}}
                                    <div class="form-group">

                                        <label>Comment</label>

                                        <textarea name="comment"
                                                  rows="5"
                                                  class="form-control">{{ !empty($editComment) ? $editComment->comment : old('comment') }}</textarea>

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
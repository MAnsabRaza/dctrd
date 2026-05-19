<form action="" method="get" class="px-16">

    <div class="row mt-24">

        {{-- FROM --}}
        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    From
                </label>

                <input type="date"
                       name="from"
                       class="form-control"
                       value="{{ request()->get('from') }}">

            </div>

        </div>

        {{-- TO --}}
        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    To
                </label>

                <input type="date"
                       name="to"
                       class="form-control"
                       value="{{ request()->get('to') }}">

            </div>

        </div>

        {{-- BOOKING --}}
        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    Booking
                </label>

                <select name="booking_id"
                        class="form-control select2">

                    <option value="">
                        All
                    </option>

                    @foreach($allBookingsLists as $booking)

                        <option value="{{ $booking->id }}"
                            {{ request()->get('booking_id') == $booking->id ? 'selected' : '' }}>

                            {{ $booking->title }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

        {{-- SEARCH --}}
        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    Search
                </label>

                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Search review"
                       value="{{ request()->get('search') }}">

            </div>

        </div>

        {{-- STATUS --}}
        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    Status
                </label>

                <select name="status"
                        class="form-control select2">

                    <option value="">
                        All
                    </option>

                    <option value="pending"
                        {{ request()->get('status') == 'pending' ? 'selected' : '' }}>

                        Pending

                    </option>

                    <option value="approved"
                        {{ request()->get('status') == 'approved' ? 'selected' : '' }}>

                        Approved

                    </option>

                </select>

            </div>

        </div>

        {{-- SORT --}}
        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    Sort
                </label>

                <select name="sort"
                        class="form-control select2">

                    <option value="">
                        Latest
                    </option>

                    <option value="create_date_asc"
                        {{ request()->get('sort') == 'create_date_asc' ? 'selected' : '' }}>

                        Oldest

                    </option>

                    <option value="create_date_desc"
                        {{ request()->get('sort') == 'create_date_desc' ? 'selected' : '' }}>

                        Latest

                    </option>

                </select>

            </div>

        </div>

        {{-- FILTER BUTTON --}}
        <div class="col-12 col-lg-3 ml-auto">

            <button type="button"
                    data-container-id="tableListContainer"
                    class="js-get-view-data-by-form btn btn-primary btn-lg btn-block">

                Filter

            </button>

        </div>

    </div>

</form>
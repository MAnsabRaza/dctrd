<form action="" method="get" class="px-16">

    <div class="row mt-24">

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

                        <option value="{{ $booking->id }}">

                            {{ $booking->title }}

                        </option>

                    @endforeach

                </select>

            </div>

        </div>

        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    Search
                </label>

                <input type="text"
                       name="search"
                       class="form-control"
                       placeholder="Search comment">

            </div>

        </div>

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

                    <option value="create_date_asc">
                        Oldest
                    </option>

                    <option value="create_date_desc">
                        Latest
                    </option>

                </select>

            </div>

        </div>

        <div class="col-12 col-lg-3 ml-auto">

            <button type="button"
                    data-container-id="tableListContainer"
                    class="js-get-view-data-by-form btn btn-primary btn-lg btn-block">

                Filter

            </button>

        </div>

    </div>

</form>
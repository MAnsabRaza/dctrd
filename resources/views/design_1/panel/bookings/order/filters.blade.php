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

        <div class="col-12 col-lg-2">

            <div class="form-group">

                <label class="form-group-label">
                    Order ID
                </label>

                <input type="number"
                       name="order_id"
                       class="form-control"
                       value="{{ request()->get('order_id') }}">

            </div>

        </div>

        <div class="col-12 col-lg-2">

            <div class="form-group">

                <label class="form-group-label">
                    Status
                </label>

                <select name="status"
                        class="form-control">

                    <option value="">
                        All
                    </option>

                    <option value="pending">
                        Pending
                    </option>

                    <option value="confirmed">
                        Confirmed
                    </option>

                    <option value="cancelled">
                        Cancelled
                    </option>

                    <option value="completed">
                        Completed
                    </option>

                </select>

            </div>

        </div>

        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    Sort
                </label>

                <select name="sort"
                        class="form-control">

                    <option value="">
                        Latest
                    </option>

                    <option value="total_asc">
                        Total ASC
                    </option>

                    <option value="total_desc">
                        Total DESC
                    </option>

                </select>

            </div>

        </div>

        <div class="col-12 col-lg-2 ml-auto">

            <button type="submit"
                    class="btn btn-primary btn-lg btn-block">

                Filter

            </button>

        </div>

    </div>

</form>
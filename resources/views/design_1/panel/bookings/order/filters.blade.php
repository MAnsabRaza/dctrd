<form action="" method="get" class="px-16">

    <div class="row mt-24">

        {{-- FROM --}}
        <div class="col-12 col-lg-3">

            <div class="form-group">

                <span class="has-translation bg-transparent">
                    <x-iconsax-lin-calendar-2
                        class="text-gray-border"
                        width="24px"
                        height="24px"/>
                </span>

                <label class="form-group-label">
                    From
                </label>

                <input type="text"
                       name="from"
                       class="form-control datepicker js-default-init-date-picker"
                       data-format="YYYY/MM/DD"
                       value="{{ request()->get('from') }}">

            </div>

        </div>

        {{-- TO --}}
        <div class="col-12 col-lg-3">

            <div class="form-group">

                <span class="has-translation bg-transparent">
                    <x-iconsax-lin-calendar-2
                        class="text-gray-border"
                        width="24px"
                        height="24px"/>
                </span>

                <label class="form-group-label">
                    To
                </label>

                <input type="text"
                       name="to"
                       class="form-control datepicker js-default-init-date-picker"
                       data-format="YYYY/MM/DD"
                       value="{{ request()->get('to') }}">

            </div>

        </div>

        {{-- ORDER ID --}}
        <div class="col-12 col-lg-2">

            <div class="form-group">

                <label class="form-group-label">
                    Order ID
                </label>

                <input type="number"
                       name="order_id"
                       class="form-control"
                       placeholder="Search Order ID"
                       value="{{ request()->get('order_id') }}">

            </div>

        </div>

        {{-- STATUS --}}
        <div class="col-12 col-lg-2">

            <div class="form-group">

                <label class="form-group-label">
                    Status
                </label>

                <select name="status"
                        class="form-control select2"
                        data-minimum-results-for-search="Infinity">

                    <option value="">
                        All
                    </option>

                    <option value="pending"
                        {{ request()->get('status') == 'pending' ? 'selected' : '' }}>
                        Pending
                    </option>

                    <option value="confirmed"
                        {{ request()->get('status') == 'confirmed' ? 'selected' : '' }}>
                        Confirmed
                    </option>

                    <option value="completed"
                        {{ request()->get('status') == 'completed' ? 'selected' : '' }}>
                        Completed
                    </option>

                    <option value="cancelled"
                        {{ request()->get('status') == 'cancelled' ? 'selected' : '' }}>
                        Cancelled
                    </option>

                </select>

            </div>

        </div>

        {{-- PAYMENT STATUS --}}
        <div class="col-12 col-lg-2">

            <div class="form-group">

                <label class="form-group-label">
                    Payment
                </label>

                <select name="payment_status"
                        class="form-control select2"
                        data-minimum-results-for-search="Infinity">

                    <option value="">
                        All
                    </option>

                    <option value="paid"
                        {{ request()->get('payment_status') == 'paid' ? 'selected' : '' }}>
                        Paid
                    </option>

                    <option value="unpaid"
                        {{ request()->get('payment_status') == 'unpaid' ? 'selected' : '' }}>
                        Unpaid
                    </option>

                    <option value="refunded"
                        {{ request()->get('payment_status') == 'refunded' ? 'selected' : '' }}>
                        Refunded
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

                    <option value="total_asc"
                        {{ request()->get('sort') == 'total_asc' ? 'selected' : '' }}>
                        Total ASC
                    </option>

                    <option value="total_desc"
                        {{ request()->get('sort') == 'total_desc' ? 'selected' : '' }}>
                        Total DESC
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

        {{-- BUTTON --}}
        <div class="col-12 col-lg-3 ml-auto">

            <button type="button"
                    data-container-id="tableListContainer"
                    class="js-get-view-data-by-form btn btn-primary btn-lg btn-block">

                Filter

            </button>

        </div>

    </div>

</form>
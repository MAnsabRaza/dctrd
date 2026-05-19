<form action=""
      method="get"
      class="mb-20">

    <div class="row">

        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    Search
                </label>

                <input type="text"
                       name="search"
                       class="form-control"
                       value="{{ request()->get('search') }}">

            </div>

        </div>

        <div class="col-12 col-lg-3">

            <div class="form-group">

                <label class="form-group-label">
                    Category
                </label>

                <select name="category_id"
                        class="form-control select2">

                    <option value="">
                        All
                    </option>

                    @foreach($allCategoryLists as $category)

                        <option value="{{ $category->id }}"
                            {{ request()->get('category_id') == $category->id ? 'selected' : '' }}>

                            {{ $category->title }}

                        </option>

                    @endforeach

                </select>

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

                    <option value="active">
                        Active
                    </option>

                    <option value="draft">
                        Draft
                    </option>

                </select>

            </div>

        </div>

        <div class="col-12 col-lg-2">

            <div class="form-group">

                <label class="form-group-label">
                    Sort
                </label>

                <select name="sort"
                        class="form-control">

                    <option value="">
                        Latest
                    </option>

                    <option value="price_asc">
                        Price ASC
                    </option>

                    <option value="price_desc">
                        Price DESC
                    </option>

                </select>

            </div>

        </div>

        <div class="col-12 col-lg-2 d-flex align-items-end">

            <button type="submit"
                    class="btn btn-primary btn-block">

                Filter

            </button>

        </div>

    </div>

</form>
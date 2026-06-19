@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle ?? trans('admin/main.booking_filters') }}</h1>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-header">
                            <ul class="nav nav-tabs card-header-tabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link active" data-toggle="tab" href="#listTab">{{ trans('admin/main.list') }}</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" data-toggle="tab" href="#newTab">{{ trans('admin/main.new') }}</a>
                                </li>
                            </ul>
                        </div>

                        <div class="card-body">
                            <div class="tab-content">
                                <div id="listTab" class="tab-pane active">
                                    <div class="table-responsive">
                                        <table class="table custom-table">
                                            <thead>
                                            <tr>
                                                <th>{{ trans('admin/main.title') }}</th>
                                                <th>{{ trans('admin/main.category') }}</th>
                                                <th>{{ trans('admin/main.options') }}</th>
                                                <th>{{ trans('admin/main.action') }}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            @foreach($filters as $filter)
                                                <tr>
                                                    <td>{{ $filter->title }}</td>
                                                    <td>{{ optional($filter->category)->title }}</td>
                                                    <td>
                                                        @foreach($filter->options as $opt)
                                                            <div>{{ $opt->name }}</div>
                                                        @endforeach
                                                    </td>
                                                    <td>
                                                        <a href="{{ getAdminPanelUrl('/booking/filters/'.$filter->id.'/edit') }}" class="btn btn-sm btn-primary">{{ trans('admin/main.edit') }}</a>
                                                        <a href="{{ getAdminPanelUrl('/booking/filters/'.$filter->id.'/delete') }}" class="btn btn-sm btn-danger">{{ trans('admin/main.delete') }}</a>
                                                    </td>
                                                </tr>
                                            @endforeach
                                            </tbody>
                                        </table>

                                        {{ $filters->links() }}
                                    </div>
                                </div>

                                <div id="newTab" class="tab-pane">
                                    <form action="{{ !empty($editItem) ? getAdminPanelUrl('/booking/filters/'.$editItem->id.'/update') : getAdminPanelUrl('/booking/filters/store') }}" method="post">
                                        {{ csrf_field() }}

                                        <div class="form-group">
                                            <label>{{ trans('admin/main.category') }}</label>
                                            <select name="category_id" class="form-control">
                                                @foreach($categories as $category)
                                                    <option value="{{ $category->id }}" @if(!empty($editItem) && $editItem->category_id == $category->id) selected @endif>{{ $category->title }}</option>
                                                    @foreach($category->subCategories as $sub)
                                                        <option value="{{ $sub->id }}" @if(!empty($editItem) && $editItem->category_id == $sub->id) selected @endif>-- {{ $sub->title }}</option>
                                                    @endforeach
                                                @endforeach
                                            </select>
                                        </div>

                                        <div class="form-group">
                                            <label>{{ trans('admin/main.title') }}</label>
                                            <input type="text" name="title" class="form-control" value="{{ $editItem->title ?? old('title') }}">
                                        </div>

                                        <div id="optionsWrapper">
                                            <label>{{ trans('admin/main.options') }}</label>
                                            @php
                                                $existing = $filterOptions ?? [];
                                            @endphp
                                            <div id="optionRows">
                                                @if(!empty($existing) && count($existing))
                                                    @foreach($existing as $opt)
                                                        <div class="input-group mb-2 option-row">
                                                            <input type="text" name="sub_filters[][name]" class="form-control" value="{{ $opt->name }}">
                                                            <div class="input-group-append">
                                                                <button type="button" class="btn btn-danger js-remove-option">{{ trans('admin/main.delete') }}</button>
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                @endif
                                            </div>

                                            <div class="text-right mt-2">
                                                <button type="button" class="btn btn-primary js-add-option">{{ trans('admin/main.add_new') }}</button>
                                            </div>
                                        </div>

                                        <div class="form-group mt-3 text-right">
                                            <button class="btn btn-success">{{ trans('admin/main.submit') }}</button>
                                        </div>
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

@push('scripts_bottom')
<script>
document.addEventListener('click', function (e) {
    if (e.target.closest('.js-add-option')) {
        const wrapper = document.getElementById('optionRows');
        if (!wrapper) return;
        const row = document.createElement('div');
        row.className = 'input-group mb-2 option-row';
        row.innerHTML = '<input type="text" name="sub_filters[][name]" class="form-control" placeholder="Option name"><div class="input-group-append"><button type="button" class="btn btn-danger js-remove-option">'+("{{ trans('admin/main.delete') }}")+'</button></div>';
        wrapper.appendChild(row);
    }

    if (e.target.closest('.js-remove-option')) {
        const row = e.target.closest('.option-row');
        if (row) row.remove();
    }
});
</script>
@endpush

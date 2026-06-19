{{-- resources/views/admin/booking/specification.blade.php --}}

@extends('admin.layouts.app')

@push('styles_top')
<style>
    .booking-spec-card { border: 0; border-radius: 8px; box-shadow: 0 8px 28px rgba(31, 45, 61, 0.04); }
    .booking-spec-card .card-header { min-height: 72px; border-bottom: 1px solid #f1f3f8; }
    .booking-spec-icon { width: 28px; height: 28px; display: inline-flex; align-items: center; justify-content: center; border-radius: 8px; background: #f6f8fc; }
    .booking-spec-icon img { width: 18px; height: 18px; object-fit: contain; }
    .booking-spec-table th { color: #8a94a6; font-size: 12px; font-weight: 600; border-top: 0; }
    .booking-spec-table td { height: 58px; vertical-align: middle; border-top: 0; }
    .booking-category-grid { display: grid; grid-template-columns: repeat(3, minmax(180px, 1fr)); gap: 14px 28px; }
    .booking-category-children { display: grid; gap: 10px; padding-left: 18px; }
    .booking-value-row { display: grid; grid-template-columns: minmax(0, 1fr) 42px; gap: 8px; align-items: center; }
    @media (max-width: 991px) { .booking-category-grid { grid-template-columns: 1fr; } }
</style>
@endpush

@section('content')
@php
    $formMode = ((!empty($errors) && $errors->any()) || !empty($editSpecification) || request()->get('tab') === 'create' || (empty($specifications) || !$specifications->count()));
    $formTitle = !empty($editSpecification) ? trans('admin/main.edit') . ' Specification' : trans('admin/main.create_booking_specification');
@endphp

<section class="section">
    <div class="section-header">
        <h1>{{ $formMode ? $formTitle : 'Bookings Specifications' }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
            <div class="breadcrumb-item"><a href="{{ getAdminPanelUrl() }}/booking/specification">Specifications</a></div>
            @if($formMode)
                <div class="breadcrumb-item">{{ !empty($editSpecification) ? 'Edit Specification' : 'Add Specification' }}</div>
            @endif
        </div>
    </div>

    <div class="section-body">
        @if(!$formMode)
            @can('admin_booking_specification')
                <div class="row">
                    <div class="col-12">
                        <div class="card booking-spec-card">
                            <div class="card-header justify-content-between">
                                <div>
                                    <h5 class="font-14 mb-0">Booking Specifications</h5>
                                    <p class="font-12 mt-4 mb-0 text-gray-500">Easily manage and review all related items in this list.</p>
                                </div>

                                @can('admin_booking_specification_create')
                                    <a href="{{ getAdminPanelUrl() }}/booking/specification?tab=create" class="btn btn-primary">
                                        <x-iconsax-lin-add class="icons text-white" width="18px" height="18px"/>
                                        <span class="ml-4 font-12">Add New</span>
                                    </a>
                                @endcan
                            </div>

                            <div class="card-body px-30 pt-20 pb-35">
                                @if(!empty($specifications) && $specifications->count())
                                    <div class="table-responsive">
                                        <table class="table booking-spec-table custom-table font-14 mb-0">
                                            <thead>
                                                <tr>
                                                    <th class="text-left">Title</th>
                                                    <th class="text-center">Icon</th>
                                                    <th class="text-center">Type</th>
                                                    <th class="text-center">Categories</th>
                                                    <th class="text-center">Action</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach($specifications as $spec)
                                                    <tr>
                                                        <td class="text-left text-dark-blue font-weight-500">{{ $spec->title }}</td>
                                                        <td class="text-center">
                                                            <span class="booking-spec-icon">
                                                                @if(!empty($spec->icon))
                                                                    <img src="{{ $spec->icon }}" alt="{{ $spec->title }}">
                                                                @else
                                                                    <x-iconsax-lin-link class="icons text-gray-500" width="18px" height="18px"/>
                                                                @endif
                                                            </span>
                                                        </td>
                                                        <td class="text-center">{{ $spec->type === 'multi_value' ? 'Multi-value' : 'Textarea' }}</td>
                                                        <td class="text-center">{{ $spec->categories->count() ?: 1 }}</td>
                                                        <td class="text-center" width="80px">
                                                            <div class="btn-group dropdown table-actions position-relative">
                                                                <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                    <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                </button>
                                                                <div class="dropdown-menu dropdown-menu-right">
                                                                    @can('admin_booking_specification_edit')
                                                                        <a href="{{ getAdminPanelUrl() }}/booking/specification/{{ $spec->id }}/edit" class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                            <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                            <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                        </a>
                                                                    @endcan
                                                                    @can('admin_booking_specification_delete')
                                                                        @include('admin.includes.delete_button', [
                                                                            'url'       => getAdminPanelUrl() . '/booking/specification/' . $spec->id . '/delete',
                                                                            'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                            'btnText'   => trans('admin/main.delete'),
                                                                            'btnIcon'   => 'trash',
                                                                            'iconType'  => 'lin',
                                                                            'iconClass' => 'text-danger mr-2',
                                                                        ])
                                                                    @endcan
                                                                </div>
                                                            </div>
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @else
                                    <div class="text-center text-gray-500 mt-30">{{ trans('admin/main.no_result') }}</div>
                                @endif
                            </div>

                            @if(!empty($specifications) && $specifications->count())
                                <div class="card-footer text-center">{{ $specifications->appends(request()->input())->links() }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            @endcan
        @else
            @can('admin_booking_specification_create')
                <div class="row">
                    <div class="col-12">
                        <div class="card booking-spec-card">
                            <div class="card-body p-30">
                                <form action="{{ getAdminPanelUrl() }}/booking/specification/{{ !empty($editSpecification) ? $editSpecification->id . '/update' : 'store' }}" method="post">
                                    {{ csrf_field() }}

                                    <div class="row">
                                        <div class="col-12 col-lg-6">
                                            <div class="form-group">
                                                <label class="input-label">Language</label>
                                                <select class="form-control" name="locale">
                                                    @foreach(getUserLanguagesLists() as $lang => $language)
                                                        <option value="{{ $lang }}" @if(mb_strtolower(request()->get('locale', app()->getLocale())) == mb_strtolower($lang)) selected @endif>{{ $language }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-6">
                                            <div class="form-group">
                                                <label class="input-label">Icon URL</label>
                                                <input type="text" name="icon" class="form-control @error('icon') is-invalid @enderror" value="{{ !empty($editSpecification) ? $editSpecification->icon : old('icon') }}" placeholder="https://example.com/icon.svg">
                                                @error('icon')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label">{{ trans('admin/main.title') }}</label>
                                        <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" value="{{ !empty($editSpecification) ? $editSpecification->title : old('title') }}" placeholder="Size">
                                        @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="form-group">
                                        <label class="input-label">Categories</label>
                                        @php
                                            $selectedCats = !empty($editSpecification) ? $editSpecification->categories->pluck('id')->toArray() : (old('category_ids') ?? []);
                                            $parents = $categories->filter(fn($c) => empty($c->parent_id) || $c->parent_id == 0);
                                        @endphp

                                        <div class="booking-category-grid">
                                            @foreach($parents as $parent)
                                                <div>
                                                    @php $children = $categories->where('parent_id', $parent->id); @endphp
                                                    @if($children->count())
                                                        <div class="custom-control custom-checkbox mb-10">
                                                            <input type="checkbox" class="custom-control-input" id="parent_{{ $parent->id }}" disabled>
                                                            <label class="custom-control-label font-weight-bold text-dark-blue" for="parent_{{ $parent->id }}">{{ $parent->title }}</label>
                                                        </div>

                                                        <div class="booking-category-children">
                                                            @foreach($children as $child)
                                                                <div class="custom-control custom-checkbox">
                                                                    <input type="checkbox" name="category_ids[]" value="{{ $child->id }}" id="cat_{{ $child->id }}" class="custom-control-input" {{ in_array($child->id, $selectedCats) ? 'checked' : '' }}>
                                                                    <label class="custom-control-label font-weight-bold text-dark-blue" for="cat_{{ $child->id }}">{{ $child->title }}</label>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    @else
                                                        <div class="custom-control custom-checkbox">
                                                            <input type="checkbox" name="category_ids[]" value="{{ $parent->id }}" id="cat_{{ $parent->id }}" class="custom-control-input" {{ in_array($parent->id, $selectedCats) ? 'checked' : '' }}>
                                                            <label class="custom-control-label font-weight-bold text-dark-blue" for="cat_{{ $parent->id }}">{{ $parent->title }}</label>
                                                        </div>
                                                    @endif
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('category_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    @php $selType = !empty($editSpecification) ? $editSpecification->type : old('type', 'multi_value'); @endphp
                                    <div class="form-group">
                                        <label class="input-label">Input Type:</label>
                                        <div class="d-flex align-items-center flex-wrap">
                                            <div class="custom-control custom-radio mr-20">
                                                <input type="radio" name="type" value="textbox" id="type_textbox" class="custom-control-input" {{ $selType === 'textbox' ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark-blue" for="type_textbox">Textarea</label>
                                            </div>
                                            <div class="custom-control custom-radio">
                                                <input type="radio" name="type" value="multi_value" id="type_multi_value" class="custom-control-input" {{ $selType === 'multi_value' ? 'checked' : '' }}>
                                                <label class="custom-control-label font-weight-bold text-dark-blue" for="type_multi_value">Multi-value</label>
                                            </div>
                                        </div>
                                        @error('type')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="form-group" id="values-section" style="{{ $selType === 'textbox' ? 'display:none' : '' }}">
                                        <div class="d-flex align-items-center justify-content-between mb-10">
                                            <label class="input-label mb-0">Multi-value</label>
                                            <button type="button" id="add-value" class="btn btn-success btn-sm">
                                                <x-iconsax-lin-add class="icons text-white" width="14px" height="14px"/>
                                                <span class="ml-4">Add</span>
                                            </button>
                                        </div>

                                        <div id="values-wrapper">
                                            @php
                                                $existingValues = [];
                                                if (!empty($editSpecification)) {
                                                    $existingValues = $editSpecification->bookingValues->pluck('value')->toArray();
                                                } else {
                                                    $existingValues = old('values') ?? [''];
                                                }
                                                if (empty($existingValues)) {
                                                    $existingValues = [''];
                                                }
                                            @endphp

                                            @foreach($existingValues as $val)
                                                <div class="booking-value-row mb-15 value-row">
                                                    <input type="text" name="values[]" class="form-control" value="{{ $val }}" placeholder="Small">
                                                    <button type="button" class="btn btn-danger remove-value"><span aria-hidden="true">&times;</span></button>
                                                </div>
                                            @endforeach
                                        </div>
                                        @error('values')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="row">
                                        <div class="col-12 col-lg-3">
                                            <div class="form-group">
                                                <label class="input-label">{{ trans('admin/main.sort_order') }}</label>
                                                <input type="number" name="sort_order" min="0" class="form-control @error('sort_order') is-invalid @enderror" value="{{ !empty($editSpecification) ? $editSpecification->sort_order : old('sort_order', $nextSortOrder) }}">
                                                @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                            </div>
                                        </div>

                                        <div class="col-12 col-lg-3 d-flex align-items-center">
                                            <div class="custom-control custom-switch mt-10">
                                                <input type="checkbox" name="status" class="custom-control-input" id="status" {{ (empty($editSpecification) || (!empty($editSpecification) && $editSpecification->status)) ? 'checked' : '' }}>
                                                <label class="custom-control-label" for="status">{{ trans('admin/main.active') }}</label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-end align-items-center mt-20">
                                        @if(!empty($editSpecification))
                                            <a href="{{ getAdminPanelUrl() }}/booking/specification" class="btn btn-outline-secondary mr-10">{{ trans('admin/main.cancel') }}</a>
                                        @endif
                                        <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endcan
        @endif
    </div>
</section>
@endsection

@push('scripts_bottom')
<script>
(function () {
    const valuesSection = document.getElementById('values-section');
    const valuesWrapper = document.getElementById('values-wrapper');
    const typeInputs = document.querySelectorAll('input[name="type"]');
    const addButton = document.getElementById('add-value');

    if (!valuesSection || !valuesWrapper) {
        return;
    }

    typeInputs.forEach(function (input) {
        input.addEventListener('change', function () {
            valuesSection.style.display = this.value === 'multi_value' ? '' : 'none';
        });
    });

    if (addButton) {
        addButton.addEventListener('click', function () {
            const row = document.createElement('div');
            row.className = 'booking-value-row mb-15 value-row';
            row.innerHTML = '<input type="text" name="values[]" class="form-control" placeholder="Small">' + '<button type="button" class="btn btn-danger remove-value"><span aria-hidden="true">&times;</span></button>';
            valuesWrapper.appendChild(row);
            row.querySelector('input').focus();
        });
    }

    valuesWrapper.addEventListener('click', function (e) {
        const btn = e.target.closest('.remove-value');
        if (!btn) return;

        const rows = valuesWrapper.querySelectorAll('.value-row');
        if (rows.length > 1) {
            btn.closest('.value-row').remove();
        } else {
            btn.closest('.value-row').querySelector('input').value = '';
        }
    });
})();
</script>
@endpush

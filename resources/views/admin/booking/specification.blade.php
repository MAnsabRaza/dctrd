{{-- resources/views/admin/booking/specification.blade.php --}}

@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.admin_booking_specification') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_specification') }}</div>
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
                                !empty($editSpecification) ||
                                (empty($specifications) || !$specifications->count())
                            );
                        @endphp

                        <ul class="nav nav-pills" role="tablist">
                            @can('admin_booking_specification')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_specification') }}
                                    </a>
                                </li>
                            @endcan
                            @can('admin_booking_specification_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       data-toggle="tab" href="#createTab" role="tab">
                                        {{ !empty($editSpecification) ? trans('admin/main.edit') : trans('admin/main.create_booking_specification') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
                            @can('admin_booking_specification')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}" id="listTab" role="tabpanel">
                                    @if(!empty($specifications) && $specifications->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{ trans('admin/main.title') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.type') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.values') }}</th>
                                                        <th class="text-center">Categories</th>
                                                        <th class="text-center">{{ trans('admin/main.sort_order') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($specifications as $spec)
                                                        <tr>
                                                            <td>{{ $spec->id }}</td>
                                                            <td>{{ $spec->title }}</td>
                                                            <td class="text-center">
                                                                <span class="badge badge-info">{{ ucfirst(str_replace('_', ' ', $spec->type)) }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($spec->type === 'multi_value' && $spec->bookingValues->count())
                                                                    @foreach($spec->bookingValues as $bv)
                                                                        <span class="badge badge-secondary mr-1">{{ $bv->value }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if($spec->categories->count())
                                                                    @foreach($spec->categories as $cat)
                                                                        <span class="badge badge-primary mr-1">{{ $cat->title }}</span>
                                                                    @endforeach
                                                                @else
                                                                    <span class="badge badge-success">All</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-primary">{{ $spec->sort_order }}</span>
                                                            </td>
                                                            <td class="text-center">
                                                                @if($spec->status)
                                                                    <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                                                @else
                                                                    <span class="badge badge-danger">{{ trans('admin/main.inactive') }}</span>
                                                                @endif
                                                            </td>
                                                            <td width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
                                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        @can('admin_booking_specification_edit')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/specification/{{ $spec->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
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
                                        {{ $specifications->links() }}
                                    @else
                                        <div class="text-center text-gray-500 mt-30">{{ trans('admin/main.no_result') }}</div>
                                    @endif
                                </div>
                            @endcan

                            {{-- CREATE / EDIT TAB --}}
                            @can('admin_booking_specification_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}" id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-7">
                                            <form action="{{ getAdminPanelUrl() }}/booking/specification/{{ !empty($editSpecification) ? $editSpecification->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                {{-- Title --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.title') }} <span class="text-danger">*</span></label>
                                                    <input type="text" name="title"
                                                           class="form-control @error('title') is-invalid @enderror"
                                                           value="{{ !empty($editSpecification) ? $editSpecification->title : old('title') }}"
                                                           placeholder="e.g. Amenities, Features"/>
                                                    @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Type --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.type') }} <span class="text-danger">*</span></label>
                                                    @php $selType = !empty($editSpecification) ? $editSpecification->type : old('type', 'multi_value'); @endphp
                                                    <select name="type" id="spec-type"
                                                            class="form-control @error('type') is-invalid @enderror">
                                                        <option value="multi_value" {{ $selType === 'multi_value' ? 'selected' : '' }}>Multi Value</option>
                                                        <option value="textbox"     {{ $selType === 'textbox'     ? 'selected' : '' }}>Textbox</option>
                                                    </select>
                                                    @error('type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Values (صرف multi_value میں دکھیں) --}}
                                                <div class="form-group" id="values-section"
                                                     style="{{ $selType === 'textbox' ? 'display:none' : '' }}">
                                                    <label>{{ trans('admin/main.values') }}
                                                        <small class="text-muted">— add selectable options</small>
                                                    </label>
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
                                                        @foreach($existingValues as $i => $val)
                                                            <div class="input-group mb-2 value-row">
                                                                <input type="text" name="values[]"
                                                                       class="form-control"
                                                                       value="{{ $val }}"
                                                                       placeholder="e.g. WiFi, Pool, Parking"/>
                                                                <div class="input-group-append">
                                                                    <button type="button" class="btn btn-outline-danger remove-value">
                                                                        <i class="fas fa-times"></i>
                                                                    </button>
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @error('values')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                                    <button type="button" id="add-value" class="btn btn-outline-secondary btn-sm mt-1">
                                                        <i class="fas fa-plus mr-1"></i> Add Value
                                                    </button>
                                                </div>

                                                {{-- Categories --}}
                                                <div class="form-group">
                                                    <label>Apply to Categories
                                                        <small class="text-muted">— leave empty = apply to all</small>
                                                    </label>
                                                    @php
                                                        $selectedCats = !empty($editSpecification)
                                                            ? $editSpecification->categories->pluck('id')->toArray()
                                                            : (old('category_ids') ?? []);
                                                        $parents = $categories->filter(fn($c) => empty($c->parent_id) || $c->parent_id == 0);
                                                    @endphp

                                                    <div class="mb-2">
                                                        @foreach($parents as $parent)
                                                            <div class="mt-2">
                                                                <strong class="d-block">{{ $parent->title }}</strong>
                                                                @php $children = $categories->where('parent_id', $parent->id); @endphp
                                                                <div class="pl-3">
                                                                    @if($children->count())
                                                                        @foreach($children as $child)
                                                                            <div class="custom-control custom-checkbox d-inline-block mr-3">
                                                                                <input type="checkbox" name="category_ids[]" value="{{ $child->id }}" id="cat_{{ $child->id }}" class="custom-control-input" {{ in_array($child->id, $selectedCats) ? 'checked' : '' }}>
                                                                                <label class="custom-control-label" for="cat_{{ $child->id }}">{{ $child->title }}</label>
                                                                            </div>
                                                                        @endforeach
                                                                    @else
                                                                        <div class="custom-control custom-checkbox d-inline-block mr-3">
                                                                            <input type="checkbox" name="category_ids[]" value="{{ $parent->id }}" id="cat_{{ $parent->id }}" class="custom-control-input" {{ in_array($parent->id, $selectedCats) ? 'checked' : '' }}>
                                                                            <label class="custom-control-label" for="cat_{{ $parent->id }}">{{ $parent->title }}</label>
                                                                        </div>
                                                                    @endif
                                                                </div>
                                                            </div>
                                                        @endforeach
                                                    </div>
                                                    @error('category_ids')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Sort Order --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.sort_order') }}
                                                        <small class="text-muted">
                                                            @if(empty($editSpecification))
                                                                — next: <strong>{{ $nextSortOrder }}</strong>
                                                            @endif
                                                        </small>
                                                    </label>
                                                    <input type="number" name="sort_order" min="0"
                                                           class="form-control @error('sort_order') is-invalid @enderror"
                                                           value="{{ !empty($editSpecification) ? $editSpecification->sort_order : old('sort_order', $nextSortOrder) }}"/>
                                                    @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Status --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="status"
                                                               class="custom-control-input" id="status"
                                                               {{ (empty($editSpecification) || (!empty($editSpecification) && $editSpecification->status)) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="status">{{ trans('admin/main.active') }}</label>
                                                    </div>
                                                </div>

                                                <div class="text-right col-12 mt-3">
                                                    @if(!empty($editSpecification))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/specification" class="btn btn-secondary mr-2">
                                                            {{ trans('admin/main.cancel') }}
                                                        </a>
                                                    @endif
                                                    <button type="submit" class="btn btn-primary">{{ trans('admin/main.save_change') }}</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>
                            @endcan

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
(function () {
    const typeSelect    = document.getElementById('spec-type');
    const valuesSection = document.getElementById('values-section');
    const valuesWrapper = document.getElementById('values-wrapper');

    // Type change → values section toggle
    typeSelect.addEventListener('change', function () {
        valuesSection.style.display = this.value === 'multi_value' ? '' : 'none';
    });

    // Add value row
    document.getElementById('add-value').addEventListener('click', function () {
        const row = document.createElement('div');
        row.className = 'input-group mb-2 value-row';
        row.innerHTML =
            '<input type="text" name="values[]" class="form-control" placeholder="e.g. WiFi, Pool"/>' +
            '<div class="input-group-append">' +
                '<button type="button" class="btn btn-outline-danger remove-value"><i class="fas fa-times"></i></button>' +
            '</div>';
        valuesWrapper.appendChild(row);
        row.querySelector('input').focus();
    });

    // Remove value row
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
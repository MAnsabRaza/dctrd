{{-- resources/views/admin/booking/specificationValue.blade.php --}}

@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.admin_booking_specification_value') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_specification_value') }}</div>
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
                                !empty($editSpecificationValue) ||
                                (empty($specificationValues) || !$specificationValues->count())
                            );
                        @endphp

                        <ul class="nav nav-pills" role="tablist">
                            @can('admin_booking_specification_value')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_specification_value') }}
                                    </a>
                                </li>
                            @endcan
                            @can('admin_booking_specification_value_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       data-toggle="tab" href="#createTab" role="tab">
                                        {{ !empty($editSpecificationValue) ? trans('admin/main.edit') : trans('admin/main.create_booking_specification_value') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
                            @can('admin_booking_specification_value')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}" id="listTab" role="tabpanel">
                                    @if(!empty($specificationValues) && $specificationValues->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('admin/main.booking') }}</th>
                                                        <th>Specification</th>
                                                        <th>Value</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($specificationValues as $sv)
                                                        <tr>
                                                            <td>
                                                                @if($sv->booking)
                                                                    #{{ $sv->booking->id }} — {{ $sv->booking->title }}
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>
                                                            <td>{{ $sv->specification?->title ?? '—' }}</td>
                                                            <td>{{ $sv->value }}</td>
                                                            <td width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
                                                                    <button type="button" class="btn-transparent dropdown-toggle" data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        @can('admin_booking_specification_value_edit')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/specificationValue/{{ $sv->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                            </a>
                                                                        @endcan
                                                                        @can('admin_booking_specification_value_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'       => getAdminPanelUrl() . '/booking/specificationValue/' . $sv->id . '/delete',
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
                                        {{ $specificationValues->links() }}
                                    @else
                                        <div class="text-center text-gray-500 mt-30">{{ trans('admin/main.no_result') }}</div>
                                    @endif
                                </div>
                            @endcan

                            {{-- CREATE / EDIT TAB --}}
                            @can('admin_booking_specification_value_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}" id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">
                                            <form action="{{ getAdminPanelUrl() }}/booking/specificationValue/{{ !empty($editSpecificationValue) ? $editSpecificationValue->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                {{-- Booking --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                    @php $selBooking = !empty($editSpecificationValue) ? $editSpecificationValue->booking_id : old('booking_id'); @endphp
                                                    <select name="booking_id" class="form-control @error('booking_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($bookings as $b)
                                                            <option value="{{ $b->id }}" {{ (string)$selBooking === (string)$b->id ? 'selected' : '' }}>
                                                                #{{ $b->id }} — {{ $b->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Specification --}}
                                                <div class="form-group">
                                                    <label>Specification <span class="text-danger">*</span></label>
                                                    @php $selSpec = !empty($editSpecificationValue) ? $editSpecificationValue->specification_id : old('specification_id'); @endphp
                                                    <select name="specification_id" class="form-control @error('specification_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($specifications as $spec)
                                                            <option value="{{ $spec->id }}" {{ (string)$selSpec === (string)$spec->id ? 'selected' : '' }}>
                                                                {{ $spec->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('specification_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Value --}}
                                                <div class="form-group">
                                                    <label>Value <span class="text-danger">*</span></label>
                                                    <input type="text" name="value"
                                                           class="form-control @error('value') is-invalid @enderror"
                                                           value="{{ !empty($editSpecificationValue) ? $editSpecificationValue->value : old('value') }}"
                                                           placeholder="Enter value"/>
                                                    @error('value')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="text-right col-12 mt-3">
                                                    @if(!empty($editSpecificationValue))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/specificationValue" class="btn btn-secondary mr-2">
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
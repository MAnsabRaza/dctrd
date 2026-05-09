@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ trans('admin/main.admin_booking_polices') }}</h1>
        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
            </div>
            <div class="breadcrumb-item">{{ trans('admin/main.admin_booking_polices') }}</div>
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
                                !empty($editPolicy) ||
                                ((empty($policies) || !$policies->count()) &&
                                  auth()->user()->can('admin_booking_polices_create'))
                            );
                        @endphp

                        <ul class="nav nav-pills" id="policyTab" role="tablist">
                            @can('admin_booking_polices')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       id="list-tab" data-toggle="tab" href="#listTab" role="tab">
                                        {{ trans('admin/main.admin_booking_polices') }}
                                    </a>
                                </li>
                            @endcan

                            @can('admin_booking_polices_create')
                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="create-tab" data-toggle="tab" href="#createTab" role="tab">
                                        {{ trans('admin/main.create_booking_policy') }}
                                    </a>
                                </li>
                            @endcan
                        </ul>

                        <div class="tab-content mt-3">

                            {{-- LIST TAB --}}
                            @can('admin_booking_polices')
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                     id="listTab" role="tabpanel">

                                    @if(!empty($policies) && $policies->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th>{{ trans('admin/main.booking') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.cancellation_type') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.free_cancel_hours') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.reschedule_allowed') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.deposit_required') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.deposit_percent') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.noshow_fee_percent') }}</th>
                                                        <th>{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($policies as $policy)
                                                        <tr>
                                                            <td>
                                                                @if($policy->booking)
                                                                    #{{ $policy->booking->id }} - {{ $policy->booking->title }}
                                                                @else
                                                                    -
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                <span class="badge badge-info">
                                                                    {{ ucfirst(str_replace('_', ' ', $policy->cancellation_type)) }}
                                                                </span>
                                                            </td>
                                                            <td class="text-center">{{ $policy->free_cancel_hours }}h</td>
                                                            <td class="text-center">
                                                                @if($policy->reschedule_allowed)
                                                                    <span class="badge badge-success">{{ trans('admin/main.yes') }}</span>
                                                                @else
                                                                    <span class="badge badge-danger">{{ trans('admin/main.no') }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">
                                                                @if($policy->deposit_required)
                                                                    <span class="badge badge-warning">{{ trans('admin/main.yes') }}</span>
                                                                @else
                                                                    <span class="badge badge-secondary">{{ trans('admin/main.no') }}</span>
                                                                @endif
                                                            </td>
                                                            <td class="text-center">{{ $policy->deposit_percent }}%</td>
                                                            <td class="text-center">{{ $policy->noshow_fee_percent }}%</td>
                                                            <td width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
                                                                    <button type="button"
                                                                            class="btn-transparent dropdown-toggle"
                                                                            data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">
                                                                        @can('admin_booking_polices_edit')
                                                                            <a href="{{ getAdminPanelUrl() }}/booking/policy/{{ $policy->id }}/edit"
                                                                               class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                            </a>
                                                                        @endcan

                                                                        @can('admin_booking_polices_delete')
                                                                            @include('admin.includes.delete_button', [
                                                                                'url'       => getAdminPanelUrl() . '/booking/policy/' . $policy->id . '/delete',
                                                                                'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                                'btnText'   => trans('admin/main.delete'),
                                                                                'btnIcon'   => 'trash',
                                                                                'iconType'  => 'lin',
                                                                                'iconClass' => 'text-danger mr-2'
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

                                        {{ $policies->links() }}

                                    @else
                                        <div class="text-center text-gray-500 mt-30">
                                            {{ trans('admin/main.no_result') }}
                                        </div>
                                    @endif
                                </div>
                            @endcan

                            {{-- CREATE / EDIT TAB --}}
                            @can('admin_booking_polices_create')
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="createTab" role="tabpanel">
                                    <div class="row">
                                        <div class="col-12 col-md-6">

                                            <form action="{{ getAdminPanelUrl() }}/booking/policy/{{ !empty($editPolicy) ? $editPolicy->id . '/update' : 'store' }}"
                                                  method="post">
                                                {{ csrf_field() }}

                                                {{-- Booking --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                    @php $selBooking = !empty($editPolicy) ? $editPolicy->booking_id : old('booking_id'); @endphp
                                                    <select name="booking_id" class="form-control @error('booking_id') is-invalid @enderror">
                                                        <option value="">{{ trans('admin/main.select') }}</option>
                                                        @foreach($bookings as $booking)
                                                            <option value="{{ $booking->id }}" {{ (string)$selBooking === (string)$booking->id ? 'selected' : '' }}>
                                                                #{{ $booking->id }} - {{ $booking->title }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('booking_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Cancellation Type --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.cancellation_type') }} <span class="text-danger">*</span></label>
                                                    @php $selType = !empty($editPolicy) ? $editPolicy->cancellation_type : old('cancellation_type', 'flexible'); @endphp
                                                    <select name="cancellation_type" class="form-control @error('cancellation_type') is-invalid @enderror">
                                                        <option value="flexible"       {{ $selType === 'flexible'       ? 'selected' : '' }}>Flexible</option>
                                                        <option value="moderate"       {{ $selType === 'moderate'       ? 'selected' : '' }}>Moderate</option>
                                                        <option value="strict"         {{ $selType === 'strict'         ? 'selected' : '' }}>Strict</option>
                                                        <option value="non_refundable" {{ $selType === 'non_refundable' ? 'selected' : '' }}>Non Refundable</option>
                                                    </select>
                                                    @error('cancellation_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Free Cancel Hours --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.free_cancel_hours') }}</label>
                                                    <input type="number" name="free_cancel_hours" min="0"
                                                           class="form-control @error('free_cancel_hours') is-invalid @enderror"
                                                           value="{{ !empty($editPolicy) ? $editPolicy->free_cancel_hours : old('free_cancel_hours', 24) }}"/>
                                                    @error('free_cancel_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Cancellation Fee % --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.cancellation_fee_percent') }}</label>
                                                    <input type="number" name="cancellation_fee_percent" min="0" max="100" step="0.01"
                                                           class="form-control @error('cancellation_fee_percent') is-invalid @enderror"
                                                           value="{{ !empty($editPolicy) ? $editPolicy->cancellation_fee_percent : old('cancellation_fee_percent', 0) }}"/>
                                                    @error('cancellation_fee_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Reschedule Allowed --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="reschedule_allowed"
                                                               class="custom-control-input" id="reschedule_allowed"
                                                               {{ (!empty($editPolicy) && $editPolicy->reschedule_allowed) || empty($editPolicy) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="reschedule_allowed">
                                                            {{ trans('admin/main.reschedule_allowed') }}
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Reschedule Before Hours --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.reschedule_before_hours') }}</label>
                                                    <input type="number" name="reschedule_before_hours" min="0"
                                                           class="form-control @error('reschedule_before_hours') is-invalid @enderror"
                                                           value="{{ !empty($editPolicy) ? $editPolicy->reschedule_before_hours : old('reschedule_before_hours', 24) }}"/>
                                                    @error('reschedule_before_hours')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Max Reschedules --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.max_reschedules') }}</label>
                                                    <input type="number" name="max_reschedules" min="0"
                                                           class="form-control @error('max_reschedules') is-invalid @enderror"
                                                           value="{{ !empty($editPolicy) ? $editPolicy->max_reschedules : old('max_reschedules', 2) }}"/>
                                                    @error('max_reschedules')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- No-show Fee % --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.noshow_fee_percent') }}</label>
                                                    <input type="number" name="noshow_fee_percent" min="0" max="100" step="0.01"
                                                           class="form-control @error('noshow_fee_percent') is-invalid @enderror"
                                                           value="{{ !empty($editPolicy) ? $editPolicy->noshow_fee_percent : old('noshow_fee_percent', 100) }}"/>
                                                    @error('noshow_fee_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Deposit Required --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="deposit_required"
                                                               class="custom-control-input" id="deposit_required"
                                                               {{ (!empty($editPolicy) && $editPolicy->deposit_required) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="deposit_required">
                                                            {{ trans('admin/main.deposit_required') }}
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- Deposit % --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.deposit_percent') }}</label>
                                                    <input type="number" name="deposit_percent" min="0" max="100" step="0.01"
                                                           class="form-control @error('deposit_percent') is-invalid @enderror"
                                                           value="{{ !empty($editPolicy) ? $editPolicy->deposit_percent : old('deposit_percent', 20) }}"/>
                                                    @error('deposit_percent')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Deposit Due Days --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.deposit_due_days') }} <small class="text-muted">(0 = at booking)</small></label>
                                                    <input type="number" name="deposit_due_days" min="0"
                                                           class="form-control @error('deposit_due_days') is-invalid @enderror"
                                                           value="{{ !empty($editPolicy) ? $editPolicy->deposit_due_days : old('deposit_due_days', 0) }}"/>
                                                    @error('deposit_due_days')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Balance Due Days Before --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.balance_due_days_before') }} <small class="text-muted">(0 = at check-in)</small></label>
                                                    <input type="number" name="balance_due_days_before" min="0"
                                                           class="form-control @error('balance_due_days_before') is-invalid @enderror"
                                                           value="{{ !empty($editPolicy) ? $editPolicy->balance_due_days_before : old('balance_due_days_before', 0) }}"/>
                                                    @error('balance_due_days_before')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                {{-- Policy Text --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/main.policy_text') }}</label>
                                                    <textarea name="policy_text" rows="5"
                                                              class="form-control @error('policy_text') is-invalid @enderror"
                                                              placeholder="{{ trans('admin/main.policy_text') }}">{{ !empty($editPolicy) ? $editPolicy->policy_text : old('policy_text') }}</textarea>
                                                    @error('policy_text')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                                </div>

                                                <div class="text-right col-12 mt-3">
                                                    @if(!empty($editPolicy))
                                                        <a href="{{ getAdminPanelUrl() }}/booking/policy"
                                                           class="btn btn-secondary mr-2">
                                                            {{ trans('admin/main.cancel') }}
                                                        </a>
                                                    @endif
                                                    <button type="submit" class="btn btn-primary">
                                                        {{ trans('admin/main.save_change') }}
                                                    </button>
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
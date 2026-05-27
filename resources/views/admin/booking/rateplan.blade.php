{{-- resources/views/admin/booking/rateplan.blade.php --}}

@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.booking_rate_plan') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">
                    <a href="{{ getAdminPanelUrl('/booking') }}">{{ trans('admin/main.booking') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.booking_rate_plan') }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @php
                                $ratePlanFormActive = (
                                    (!empty($errors) && $errors->any()) ||
                                    !empty($editRatePlan) ||
                                    (
                                        (empty($bookingRatePlans) || !$bookingRatePlans->count()) &&
                                        auth()->user()->can('admin_booking_rate_plan_create')
                                    )
                                );
                            @endphp

                            <ul class="nav nav-pills" id="ratePlanTab" role="tablist">

                                @can('admin_booking_rate_plan')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $ratePlanFormActive ? '' : 'active' }}"
                                           id="rateplans-tab" data-toggle="tab" href="#rateplans"
                                           role="tab" aria-controls="rateplans" aria-selected="true">
                                            {{ trans('admin/main.rate_plans') }}
                                        </a>
                                    </li>
                                @endcan

                                @can('admin_booking_rate_plan_create')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $ratePlanFormActive ? 'active' : '' }}"
                                           id="newRatePlan-tab" data-toggle="tab" href="#newRatePlan"
                                           role="tab" aria-controls="newRatePlan" aria-selected="false">
                                            {{ !empty($editRatePlan) ? trans('admin/main.edit_rate_plan') : trans('admin/main.create_rate_plan') }}
                                        </a>
                                    </li>
                                @endcan

                            </ul>

                            <div class="tab-content" id="ratePlanTabContent">

                                {{-- ===================== LIST TAB ===================== --}}
                                @can('admin_booking_rate_plan')
                                    <div class="tab-pane mt-3 fade {{ $ratePlanFormActive ? '' : 'active show' }}"
                                         id="rateplans" role="tabpanel" aria-labelledby="rateplans-tab">

                                        @if(!empty($bookingRatePlans) && $bookingRatePlans->count())
                                            <div class="table-responsive">
                                                <table class="table custom-table font-14">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th class="text-left">{{ trans('admin/main.name') }}</th>
                                                            <th class="text-left">{{ trans('admin/main.booking') }}</th>
                                                            <th class="text-left">{{ trans('admin/main.type') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.price') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.price_unit') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.calculation_type') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.priority') }}</th>
                                                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                            <th>{{ trans('admin/main.action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($bookingRatePlans as $ratePlan)
                                                            <tr>
                                                                <td>{{ $ratePlan->id }}</td>
                                                                <td class="text-left">{{ $ratePlan->name }}</td>
                                                                <td class="text-left">
                                                                    {{ optional($ratePlan->booking)->title ?? trans('admin/main.no_booking') }}
                                                                </td>
                                                                <td class="text-left">{{ $ratePlan->type ?? '-' }}</td>
                                                                <td class="text-center">
                                                                    {{ $ratePlan->price !== null ? number_format($ratePlan->price, 2) : '-' }}
                                                                </td>
                                                                <td class="text-center">{{ $ratePlan->price_unit ?? '-' }}</td>
                                                                <td class="text-center">{{ $ratePlan->calculation_type ?? 0 }}</td>
                                                                <td class="text-center">{{ $ratePlan->priority ?? '-' }}</td>
                                                                <td class="text-center">
                                                                    @if($ratePlan->status)
                                                                        <span class="badge badge-success">{{ trans('admin/main.active') }}</span>
                                                                    @else
                                                                        <span class="badge badge-danger">{{ trans('admin/main.inactive') }}</span>
                                                                    @endif
                                                                </td>
                                                                <td width="80px">
                                                                    <div class="btn-group dropdown table-actions position-relative">
                                                                        <button type="button"
                                                                                class="btn-transparent dropdown-toggle"
                                                                                data-toggle="dropdown">
                                                                            <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                        </button>
                                                                        <div class="dropdown-menu dropdown-menu-right">

                                                                            @can('admin_booking_rate_plan_edit')
                                                                                <a href="{{ getAdminPanelUrl() }}/booking/rate/{{ $ratePlan->id }}/edit"
                                                                                   class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                    <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                    <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                                </a>
                                                                            @endcan

                                                                            @can('admin_booking_rate_plan_delete')
                                                                                @include('admin.includes.delete_button', [
                                                                                    'url'       => getAdminPanelUrl() . '/booking/rate/' . $ratePlan->id . '/delete',
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

                                            {{-- Pagination --}}
                                            <div class="mt-3">
                                                {{ $bookingRatePlans->links() }}
                                            </div>

                                        @else
                                            <div class="text-center text-gray-500 mt-30">
                                                {{ trans('admin/main.no_result') }}
                                            </div>
                                        @endif

                                    </div>
                                @endcan

                                {{-- ===================== FORM TAB ===================== --}}
                                @can('admin_booking_rate_plan_create')
                                    <div class="tab-pane mt-3 fade {{ $ratePlanFormActive ? 'active show' : '' }}"
                                         id="newRatePlan" role="tabpanel" aria-labelledby="newRatePlan-tab">

                                        <div class="row">
                                            <div class="col-12 col-md-8">

                                                <form action="{{ getAdminPanelUrl() }}/booking/rate/{{ !empty($editRatePlan) ? $editRatePlan->id . '/update' : 'store' }}"
                                                      method="POST">
                                                    @csrf

                                                    {{-- Name --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.name') }} <span class="text-danger">*</span></label>
                                                        <input type="text" name="name"
                                                               class="form-control @error('name') is-invalid @enderror"
                                                               value="{{ !empty($editRatePlan) ? $editRatePlan->name : old('name') }}"
                                                               placeholder="{{ trans('admin/main.choose_name') }}"/>
                                                        @error('name')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Booking (dropdown) --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.booking') }}</label>
                                                        <select name="booking_id"
                                                                class="form-control @error('booking_id') is-invalid @enderror">
                                                            <option value="">— {{ trans('admin/main.no_booking') }} —</option>
                                                            @foreach($bookings as $booking)
                                                                <option value="{{ $booking->id }}"
                                                                    {{ (!empty($editRatePlan) && $editRatePlan->booking_id == $booking->id) || old('booking_id') == $booking->id ? 'selected' : '' }}>
                                                                    #{{ $booking->id }} — {{ $booking->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('booking_id')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Type --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.type') }}</label>
                                                        <input type="text" name="type"
                                                               class="form-control @error('type') is-invalid @enderror"
                                                               value="{{ !empty($editRatePlan) ? $editRatePlan->type : old('type') }}"
                                                               placeholder="{{ trans('admin/main.booking_type') }}"/>
                                                        @error('type')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    <div class="row">
                                                        {{-- Price --}}
                                                        <div class="col-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ trans('admin/main.price') }}</label>
                                                                <input type="number" name="price" min="0" step="0.01"
                                                                       class="form-control @error('price') is-invalid @enderror"
                                                                       value="{{ !empty($editRatePlan) ? $editRatePlan->price : old('price') }}"
                                                                       placeholder="0.00"/>
                                                                @error('price')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        {{-- Price Unit --}}
                                                        <div class="col-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ trans('admin/main.price_unit') }}</label>
                                                                <input type="text" name="price_unit" min="0"
                                                                       class="form-control @error('price_unit') is-invalid @enderror"
                                                                       value="{{ !empty($editRatePlan) ? $editRatePlan->price_unit : old('price_unit') }}"
                                                                       placeholder="0"/>
                                                                @error('price_unit')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        {{-- Calculation Type --}}
                                                        <div class="col-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ trans('admin/main.calculation_type') }}</label>
                                                                <select name="calculation_type"
                                                                        class="form-control @error('calculation_type') is-invalid @enderror">
                                                                    @php
                                                                        $calcTypes = [
                                                                            0 => trans('admin/main.fixed'),
                                                                            1 => trans('admin/main.percentage'),
                                                                            2 => trans('admin/main.per_person'),
                                                                            3 => trans('admin/main.per_night'),
                                                                        ];
                                                                        $currentCalcType = !empty($editRatePlan) ? $editRatePlan->calculation_type : old('calculation_type', 0);
                                                                    @endphp
                                                                    @foreach($calcTypes as $val => $label)
                                                                        <option value="{{ $val }}" {{ $currentCalcType == $val ? 'selected' : '' }}>
                                                                            {{ $label }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('calculation_type')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>

                                                        {{-- Priority --}}
                                                        <div class="col-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ trans('admin/main.priority') }}</label>
                                                                <input type="number" name="priority" min="0"
                                                                       class="form-control @error('priority') is-invalid @enderror"
                                                                       value="{{ !empty($editRatePlan) ? $editRatePlan->priority : old('priority', 0) }}"
                                                                       placeholder="0"/>
                                                                @error('priority')
                                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                                @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- Conditions (JSON array as textarea helper) --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.conditions') }}</label>
                                                        <textarea name="conditions_raw" rows="3"
                                                                  class="form-control @error('conditions') is-invalid @enderror"
                                                                  placeholder='["condition1","condition2"]'>{{ !empty($editRatePlan) && $editRatePlan->conditions ? json_encode($editRatePlan->conditions) : old('conditions_raw') }}</textarea>
                                                        <div class="text-gray-500 text-small mt-1">
                                                            {{ trans('admin/main.conditions_hint') }}
                                                        </div>
                                                        @error('conditions')
                                                            <div class="invalid-feedback">{{ $message }}</div>
                                                        @enderror
                                                    </div>

                                                    {{-- Status --}}
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" name="status"
                                                                   class="custom-control-input" id="status"
                                                                   {{ (!empty($editRatePlan) && $editRatePlan->status) || empty($editRatePlan) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="status">
                                                                {{ trans('admin/main.active') }}
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="text-right col-12 mt-3">
                                                        @if(!empty($editRatePlan))
                                                            <a href="{{ getAdminPanelUrl('/booking/rate') }}"
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

@push('scripts_bottom')
<script>
    // Auto-parse conditions textarea into hidden array inputs on form submit
    document.querySelectorAll('form').forEach(function(form) {
        form.addEventListener('submit', function(e) {
            var condRaw = form.querySelector('[name="conditions_raw"]');
            if (!condRaw) return;

            var raw = condRaw.value.trim();
            if (!raw) return;

            try {
                var parsed = JSON.parse(raw);
                if (Array.isArray(parsed)) {
                    condRaw.removeAttribute('name'); // detach raw textarea
                    parsed.forEach(function(val) {
                        var hidden = document.createElement('input');
                        hidden.type = 'hidden';
                        hidden.name = 'conditions[]';
                        hidden.value = val;
                        form.appendChild(hidden);
                    });
                }
            } catch (err) {
                e.preventDefault();
                alert('{{ trans("admin/main.invalid_json_conditions") }}');
            }
        });
    });
</script>
@endpush
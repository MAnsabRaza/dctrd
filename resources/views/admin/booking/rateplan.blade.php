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

                                // Build existing condition rows for edit mode
                                $existingConditions = [];
                                if (!empty($editRatePlan) && !empty($editRatePlan->conditions)) {
                                    foreach ($editRatePlan->conditions as $k => $v) {
                                        $existingConditions[] = [
                                            'key'   => $k,
                                            'value' => is_array($v) ? json_encode($v) : $v,
                                        ];
                                    }
                                }
                                if (empty($existingConditions)) {
                                    $existingConditions[] = ['key' => '', 'value' => ''];
                                }
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
                                                                <td class="text-center">{{ $ratePlan->calculation_type ?? '-' }}</td>
                                                                <td class="text-center">{{ $ratePlan->priority ?? '0' }}</td>
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
                                            <div class="mt-3">{{ $bookingRatePlans->links() }}</div>
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
                                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Booking --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.booking') }} <span class="text-danger">*</span></label>
                                                        <select name="booking_id"
                                                                class="form-control @error('booking_id') is-invalid @enderror">
                                                            <option value="">— {{ trans('admin/main.select_booking') ?? 'Select Booking' }} —</option>
                                                            @foreach($bookings as $booking)
                                                                <option value="{{ $booking->id }}"
                                                                    {{ (!empty($editRatePlan) && $editRatePlan->booking_id == $booking->id) || old('booking_id') == $booking->id ? 'selected' : '' }}>
                                                                    #{{ $booking->id }} — {{ $booking->title }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('booking_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Type --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.type') }} <span class="text-danger">*</span></label>
                                                        <input type="text" name="type"
                                                               class="form-control @error('type') is-invalid @enderror"
                                                               value="{{ !empty($editRatePlan) ? $editRatePlan->type : old('type') }}"
                                                               placeholder="base, seasonal, dow, promo, pax"/>
                                                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    <div class="row">
                                                        {{-- Price --}}
                                                        <div class="col-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ trans('admin/main.price') }} <span class="text-danger">*</span></label>
                                                                <input type="number" name="price" min="0" step="0.01"
                                                                       class="form-control @error('price') is-invalid @enderror"
                                                                       value="{{ !empty($editRatePlan) ? $editRatePlan->price : old('price') }}"
                                                                       placeholder="0.00"/>
                                                                @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                            </div>
                                                        </div>

                                                        {{-- Price Unit --}}
                                                        <div class="col-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ trans('admin/main.price_unit') }} <span class="text-danger">*</span></label>
                                                                <select name="price_unit"
                                                                        class="form-control @error('price_unit') is-invalid @enderror">
                                                                    @php
                                                                        $priceUnits = ['day' => 'Day', 'night' => 'Night', 'hour' => 'Hour', 'person' => 'Person'];
                                                                        $currentUnit = !empty($editRatePlan) ? $editRatePlan->price_unit : old('price_unit', 'day');
                                                                    @endphp
                                                                    @foreach($priceUnits as $val => $label)
                                                                        <option value="{{ $val }}" {{ $currentUnit === $val ? 'selected' : '' }}>
                                                                            {{ $label }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('price_unit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    <div class="row">
                                                        {{-- Calculation Type — matches DB string values --}}
                                                        <div class="col-12 col-md-6">
                                                            <div class="form-group">
                                                                <label>{{ trans('admin/main.calculation_type') }} <span class="text-danger">*</span></label>
                                                                <select name="calculation_type"
                                                                        class="form-control @error('calculation_type') is-invalid @enderror">
                                                                    @php
                                                                        $calcTypes = [
                                                                            'fixed'            => 'Fixed',
                                                                            'percent_off'      => 'Percent Off',
                                                                            'percent_of_base'  => 'Percent of Base',
                                                                        ];
                                                                        $currentCalcType = !empty($editRatePlan)
                                                                            ? $editRatePlan->calculation_type
                                                                            : old('calculation_type', 'fixed');
                                                                    @endphp
                                                                    @foreach($calcTypes as $val => $label)
                                                                        <option value="{{ $val }}" {{ $currentCalcType === $val ? 'selected' : '' }}>
                                                                            {{ $label }}
                                                                        </option>
                                                                    @endforeach
                                                                </select>
                                                                @error('calculation_type') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                                                                @error('priority') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                            </div>
                                                        </div>
                                                    </div>

                                                    {{-- ====================================================== --}}
                                                    {{-- CONDITIONS — Key / Value rows                          --}}
                                                    {{-- Saves as JSON: {"days_of_week":[6,7],"min_nights":3}   --}}
                                                    {{-- ====================================================== --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.conditions') }}</label>

                                                        {{-- Quick-add presets --}}
                                                        <div class="mb-2">
                                                            <small class="text-gray-500 mr-2">Quick add:</small>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 js-preset"
                                                                    data-key="days_of_week" data-value="[1,2,3,4,5]">Weekdays</button>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 js-preset"
                                                                    data-key="days_of_week" data-value="[6,7]">Weekend</button>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 js-preset"
                                                                    data-key="min_nights" data-value="3">Min 3 Nights</button>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary mr-1 js-preset"
                                                                    data-key="min_persons" data-value="2">Min 2 Persons</button>
                                                            <button type="button" class="btn btn-xs btn-outline-secondary js-preset"
                                                                    data-key="max_persons" data-value="5">Max 5 Persons</button>
                                                        </div>

                                                        {{-- Rows --}}
                                                        <div id="conditionsWrap">
                                                            @foreach($existingConditions as $cond)
                                                                <div class="condition-row d-flex align-items-center mb-2">
                                                                    <input type="text"
                                                                           name="condition_key[]"
                                                                           class="form-control mr-2"
                                                                           style="max-width:190px"
                                                                           placeholder="Key  e.g. days_of_week"
                                                                           value="{{ $cond['key'] }}"/>
                                                                    <input type="text"
                                                                           name="condition_value[]"
                                                                           class="form-control mr-2"
                                                                           placeholder='Value  e.g. [6,7] or 3'
                                                                           value="{{ $cond['value'] }}"/>
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-outline-danger js-remove-row flex-shrink-0">
                                                                        &times;
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <button type="button" id="btnAddRow"
                                                                class="btn btn-sm btn-outline-primary mt-1">
                                                            + Add Condition
                                                        </button>

                                                        <div class="text-gray-500 text-small mt-2">
                                                            <strong>Key</strong> examples: <code>days_of_week</code>, <code>min_nights</code>, <code>min_persons</code>, <code>max_persons</code><br>
                                                            <strong>Value</strong> examples: <code>[6,7]</code> &nbsp;·&nbsp; <code>3</code> &nbsp;·&nbsp; <code>"weekend"</code>
                                                        </div>
                                                        @error('conditions') <div class="text-danger text-small mt-1">{{ $message }}</div> @enderror
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
(function () {

    var wrap = document.getElementById('conditionsWrap');

    // Add empty row
    document.getElementById('btnAddRow').addEventListener('click', function () {
        appendRow('', '');
    });

    // Preset buttons
    document.querySelectorAll('.js-preset').forEach(function (btn) {
        btn.addEventListener('click', function () {
            appendRow(this.dataset.key, this.dataset.value);
        });
    });

    // Remove row (delegated)
    wrap.addEventListener('click', function (e) {
        if (!e.target.classList.contains('js-remove-row')) return;
        var rows = wrap.querySelectorAll('.condition-row');
        if (rows.length > 1) {
            e.target.closest('.condition-row').remove();
        } else {
            // Last row — just clear it
            e.target.closest('.condition-row').querySelectorAll('input').forEach(function (i) { i.value = ''; });
        }
    });

    function appendRow(key, value) {
        var div = document.createElement('div');
        div.className = 'condition-row d-flex align-items-center mb-2';
        div.innerHTML =
            '<input type="text" name="condition_key[]" class="form-control mr-2" style="max-width:190px" placeholder="Key  e.g. days_of_week" value="' + esc(key) + '"/>' +
            '<input type="text" name="condition_value[]" class="form-control mr-2" placeholder="Value  e.g. [6,7] or 3" value="' + esc(value) + '"/>' +
            '<button type="button" class="btn btn-sm btn-outline-danger js-remove-row flex-shrink-0">&times;</button>';
        wrap.appendChild(div);
    }

    function esc(s) {
        return String(s).replace(/&/g,'&amp;').replace(/"/g,'&quot;').replace(/</g,'&lt;').replace(/>/g,'&gt;');
    }

})();
</script>
@endpush
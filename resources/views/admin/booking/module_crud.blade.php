@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $pageTitle }}</h1>

        <div class="section-header-breadcrumb">
            <div class="breadcrumb-item active">
                <a href="{{ getAdminPanelUrl() }}">
                    {{ trans('admin/main.dashboard') }}
                </a>
            </div>

            <div class="breadcrumb-item">
                {{ $pageTitle }}
            </div>
        </div>
    </div>

    <div class="section-body">
        <div class="row">

            {{-- TABLE SECTION --}}
            <div class="col-12 col-lg-8">
                <div class="card">

                    <div class="card-header">
                        <h5>{{ $pageTitle }}</h5>
                    </div>

                    <div class="card-body">

                        @if(!empty($config['help']))
                            <div class="alert alert-info">
                                {{ $config['help'] }}
                            </div>
                        @endif

                        <div class="table-responsive">
                            <table class="table custom-table font-14">

                                <tr>

                                    @foreach($config['columns'] as $column)

                                        <th>
                                            {{ ucwords(str_replace('_', ' ', $column)) }}
                                        </th>

                                    @endforeach

                                    <th>
                                        {{ trans('admin/main.action') }}
                                    </th>

                                </tr>

                                @foreach($items as $item)

                                    <tr>

                                        @foreach($config['columns'] as $column)

                                            @php
                                                $value = data_get($item, $column);
                                            @endphp

                                            <td>

                                                @if(is_bool($value))

                                                    <span class="badge badge-{{ $value ? 'success' : 'danger' }}">
                                                        {{ $value ? trans('admin/main.active') : trans('admin/main.inactive') }}
                                                    </span>

                                                @elseif(is_array($value))

                                                    <code>
                                                        {{ json_encode($value) }}
                                                    </code>

                                                @elseif(isset($selectOptions[$column]) && !empty($value))

                                                    @php
                                                        $selectedOption = collect($selectOptions[$column])->firstWhere('id', $value);
                                                        $selectedLabel  = $selectedOption['title'] ?? $value;
                                                    @endphp

                                                    {{ $selectedLabel }}

                                                @else

                                                    {{ $value ?? '-' }}

                                                @endif

                                            </td>

                                        @endforeach

                                        {{-- ACTIONS --}}
                                        <td width="80px">

                                            <div class="btn-group dropdown table-actions position-relative">

                                                <button type="button"
                                                        class="btn-transparent dropdown-toggle"
                                                        data-toggle="dropdown">

                                                    <x-iconsax-lin-more
                                                        class="icons text-gray-500"
                                                        width="20px"
                                                        height="20px"/>
                                                </button>

                                                <div class="dropdown-menu dropdown-menu-right">

                                                    @can($config['permission'] . '_edit')

                                                        <a href="{{ getAdminPanelUrl("/booking/modules/{$resource}/{$item->id}/edit") }}"
                                                           class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">

                                                            <x-iconsax-lin-edit-2
                                                                class="icons text-gray-500 mr-2"
                                                                width="18px"
                                                                height="18px"/>

                                                            <span class="text-gray-500 font-14">
                                                                {{ trans('admin/main.edit') }}
                                                            </span>

                                                        </a>

                                                    @endcan

                                                    @can($config['permission'] . '_delete')

                                                        @include('admin.includes.delete_button', [
                                                            'url'       => getAdminPanelUrl("/booking/modules/{$resource}/{$item->id}/delete"),
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

                            </table>
                        </div>

                        {{ $items->links() }}

                    </div>
                </div>
            </div>

            {{-- FORM SECTION --}}
            @php
                $formPermission = !empty($editItem)
                    ? $config['permission'] . '_edit'
                    : $config['permission'] . '_create';
            @endphp

            @can($formPermission)

                <div class="col-12 col-lg-4">

                    <div class="card">

                        <div class="card-header">
                            <h5>
                                {{ !empty($editItem)
                                    ? trans('admin/main.edit')
                                    : trans('admin/main.create') }}
                            </h5>
                        </div>

                        <div class="card-body">

                            <form method="POST"
                                  action="{{ !empty($editItem)
                                        ? getAdminPanelUrl("/booking/modules/{$resource}/{$editItem->id}/update")
                                        : getAdminPanelUrl("/booking/modules/{$resource}/store") }}">

                                @csrf

                                @foreach($config['fields'] as $field)

                                    @php
                                        $value = old(
                                            $field,
                                            !empty($editItem)
                                                ? data_get($editItem, $field)
                                                : null
                                        );

                                        $validationRule = $config['validation'][$field] ?? '';

                                        $isRequired = str_contains($validationRule, 'required');
                                    @endphp

                                    <div class="form-group">

                                        <label>

                                            {{ ucwords(str_replace('_', ' ', $field)) }}

                                            @if($isRequired)
                                                <span class="text-danger">*</span>
                                            @endif

                                        </label>

                                        {{-- DYNAMIC JSON FIELDS --}}
                                        @if(in_array($field, ['conditions', 'actions', 'meta']))

                                            @php

                                                $rows = [];

                                                $oldKeys = old($field . '_keys');

                                                if (is_array($oldKeys)) {

                                                    $oldValues = old($field . '_values', []);

                                                    foreach ($oldKeys as $idx => $k) {

                                                        $rows[] = [
                                                            'key'   => $k,
                                                            'value' => $oldValues[$idx] ?? '',
                                                        ];
                                                    }

                                                } elseif (!empty($editItem)) {

                                                    $existingData = data_get($editItem, $field);

                                                    if (is_string($existingData)) {
                                                        $existingData = json_decode($existingData, true) ?: [];
                                                    }

                                                    if (is_array($existingData)) {

                                                        foreach ($existingData as $k => $v) {

                                                            $rows[] = [
                                                                'key'   => $k,
                                                                'value' => is_array($v) || is_bool($v)
                                                                    ? json_encode($v)
                                                                    : $v,
                                                            ];
                                                        }
                                                    }
                                                }

                                                if (empty($rows)) {

                                                    $rows[] = [
                                                        'key'   => '',
                                                        'value' => '',
                                                    ];
                                                }

                                            @endphp

                                            <div id="{{ $field }}Wrapper">

                                                @foreach($rows as $row)

                                                    <div class="row align-items-center mb-2 js-{{ $field }}-row">

                                                        <div class="col-5">

                                                            <input type="text"
                                                                   name="{{ $field }}_keys[]"
                                                                   class="form-control"
                                                                   value="{{ $row['key'] }}"
                                                                   placeholder="Key">

                                                        </div>

                                                        <div class="col-5">

                                                            <input type="text"
                                                                   name="{{ $field }}_values[]"
                                                                   class="form-control"
                                                                   value="{{ $row['value'] }}"
                                                                   placeholder="Value">

                                                        </div>

                                                        <div class="col-2 text-right">

                                                            <button type="button"
                                                                    class="btn btn-sm btn-danger js-remove-{{ $field }}-row">

                                                                &times;

                                                            </button>

                                                        </div>

                                                    </div>

                                                @endforeach

                                            </div>

                                            <button type="button"
                                                    class="btn btn-sm btn-primary mt-2 js-add-{{ $field }}-row">

                                                + {{ trans('admin/main.add') }}

                                            </button>

                                        {{-- OPTIONS --}}
                                        @elseif($field === 'options')

                                            @php

                                                $optionRows = [];

                                                $oldOptionKeys = old('option_keys');

                                                if (is_array($oldOptionKeys)) {

                                                    $oldOptionValues = old('option_values', []);

                                                    foreach ($oldOptionKeys as $idx => $k) {

                                                        $optionRows[] = [
                                                            'key'   => $k,
                                                            'value' => $oldOptionValues[$idx] ?? '',
                                                        ];
                                                    }

                                                } elseif (!empty($editItem)) {

                                                    $existingOptions = data_get($editItem, 'options');

                                                    if (is_string($existingOptions)) {
                                                        $existingOptions = json_decode($existingOptions, true) ?: [];
                                                    }

                                                    if (is_array($existingOptions)) {

                                                        foreach ($existingOptions as $k => $v) {

                                                            $optionRows[] = [
                                                                'key'   => $k,
                                                                'value' => is_array($v) || is_bool($v)
                                                                    ? json_encode($v)
                                                                    : $v,
                                                            ];
                                                        }
                                                    }
                                                }

                                                if (empty($optionRows)) {

                                                    $optionRows[] = [
                                                        'key'   => '',
                                                        'value' => '',
                                                    ];
                                                }

                                            @endphp

                                            <div id="optionAttributesWrapper">

                                                @foreach($optionRows as $optRow)

                                                    <div class="row align-items-center mb-2 js-option-row">

                                                        <div class="col-5">

                                                            <input type="text"
                                                                   name="option_keys[]"
                                                                   class="form-control"
                                                                   value="{{ $optRow['key'] }}"
                                                                   placeholder="Key">

                                                        </div>

                                                        <div class="col-5">

                                                            <input type="text"
                                                                   name="option_values[]"
                                                                   class="form-control"
                                                                   value="{{ $optRow['value'] }}"
                                                                   placeholder="Value">

                                                        </div>

                                                        <div class="col-2 text-right">

                                                            <button type="button"
                                                                    class="btn btn-sm btn-danger js-remove-option-row">

                                                                &times;

                                                            </button>

                                                        </div>

                                                    </div>

                                                @endforeach

                                            </div>

                                            <button type="button"
                                                    class="btn btn-sm btn-primary mt-2 js-add-option-row">

                                                + {{ trans('admin/main.add') }}

                                            </button>

                                        {{-- BOOLEAN --}}
                                        @elseif(in_array($field, $config['booleans'] ?? []))

                                            <select name="{{ $field }}"
                                                    class="form-control @error($field) is-invalid @enderror">

                                                <option value="1"
                                                    {{ (string)$value === '1' ? 'selected' : '' }}>

                                                    {{ trans('admin/main.active') }}

                                                </option>

                                                <option value="0"
                                                    {{ (string)$value === '0' ? 'selected' : '' }}>

                                                    {{ trans('admin/main.inactive') }}

                                                </option>

                                            </select>

                                        {{-- SELECT --}}
                                        @elseif(isset($selectOptions[$field]))

                                            <select name="{{ $field }}"
                                                    data-plugin-selectTwo
                                                    class="form-control @error($field) is-invalid @enderror">

                                                <option value="">
                                                    {{ trans('admin/main.select') }}
                                                </option>

                                                @foreach($selectOptions[$field] as $option)

                                                    <option value="{{ $option['id'] }}"
                                                        {{ (string)$value === (string)$option['id'] ? 'selected' : '' }}>

                                                        {{ $option['title'] }}

                                                    </option>

                                                @endforeach

                                            </select>

                                        {{-- JSON --}}
                                        @elseif(in_array($field, $config['json'] ?? []))

                                            <textarea name="{{ $field }}"
                                                      rows="3"
                                                      class="form-control @error($field) is-invalid @enderror">{{ is_array($value) ? json_encode($value) : $value }}</textarea>

                                        {{-- DATETIME --}}
                                        @elseif(str_contains($field, '_at'))

                                            <input type="datetime-local"
                                                   name="{{ $field }}"
                                                   value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '' }}"
                                                   class="form-control @error($field) is-invalid @enderror">

                                        {{-- DATE --}}
                                        @elseif(str_contains($field, 'date'))

                                            <input type="date"
                                                   name="{{ $field }}"
                                                   value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : '' }}"
                                                   class="form-control @error($field) is-invalid @enderror">

                                        {{-- TIME --}}
                                        @elseif(str_contains($field, 'time'))

                                            <input type="time"
                                                   name="{{ $field }}"
                                                   value="{{ $value }}"
                                                   class="form-control @error($field) is-invalid @enderror">

                                        {{-- TEXTAREA --}}
                                        @elseif(in_array($field, ['message', 'description']))

                                            <textarea name="{{ $field }}"
                                                      rows="3"
                                                      class="form-control @error($field) is-invalid @enderror">{{ $value }}</textarea>

                                        {{-- DEFAULT --}}
                                        @else

                                            <input type="text"
                                                   name="{{ $field }}"
                                                   value="{{ $value }}"
                                                   class="form-control @error($field) is-invalid @enderror">

                                        @endif

                                        @error($field)

                                            <div class="invalid-feedback">
                                                {{ $message }}
                                            </div>

                                        @enderror

                                    </div>

                                @endforeach

                                <div class="d-flex justify-content-between align-items-center mt-3">

                                    @if(!empty($editItem))

                                        <a href="{{ getAdminPanelUrl("/booking/modules/{$resource}") }}"
                                           class="btn btn-secondary">

                                            {{ trans('admin/main.cancel') }}

                                        </a>

                                    @else

                                        <div></div>

                                    @endif

                                    <button type="submit"
                                            class="btn btn-primary">

                                        {{ trans('admin/main.save') }}

                                    </button>

                                </div>

                            </form>

                        </div>
                    </div>
                </div>

            @endcan

        </div>
    </div>
</section>
@endsection
@push('scripts')
<script>
(function () {

    function bindDynamicRows(field) {

        $(document).on('click', '.js-add-' + field + '-row', function () {

            $('#' + field + 'Wrapper').append(`
                <div class="row align-items-center mb-2 js-${field}-row">
                    <div class="col-5">
                        <input type="text"
                               name="${field}_keys[]"
                               class="form-control"
                               placeholder="Key">
                    </div>

                    <div class="col-5">
                        <input type="text"
                               name="${field}_values[]"
                               class="form-control"
                               placeholder="Value">
                    </div>

                    <div class="col-2 text-right">
                        <button type="button"
                                class="btn btn-sm btn-danger js-remove-${field}-row">
                            &times;
                        </button>
                    </div>
                </div>
            `);

        });

        $(document).on('click', '.js-remove-' + field + '-row', function () {

            $(this).closest('.js-' + field + '-row').remove();

        });
    }

    bindDynamicRows('conditions');
    bindDynamicRows('actions');
    bindDynamicRows('meta');

    // Options

    $(document).on('click', '.js-add-option-row', function () {

        $('#optionAttributesWrapper').append(`
            <div class="row align-items-center mb-2 js-option-row">
                <div class="col-5">
                    <input type="text"
                           name="option_keys[]"
                           class="form-control"
                           placeholder="Key">
                </div>

                <div class="col-5">
                    <input type="text"
                           name="option_values[]"
                           class="form-control"
                           placeholder="Value">
                </div>

                <div class="col-2 text-right">
                    <button type="button"
                            class="btn btn-sm btn-danger js-remove-option-row">
                        &times;
                    </button>
                </div>
            </div>
        `);

    });

    $(document).on('click', '.js-remove-option-row', function () {

        $(this).closest('.js-option-row').remove();

    });

})();
</script>
@endpush
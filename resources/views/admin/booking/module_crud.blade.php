@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
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
                                            <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                                        @endforeach
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>

                                    @foreach($items as $item)
                                        <tr>
                                            @foreach($config['columns'] as $column)
                                                <td>
                                                    @php($value = data_get($item, $column))
                                                    @if(is_bool($value))
                                                        <span class="badge badge-{{ $value ? 'success' : 'danger' }}">
                                                            {{ $value ? trans('admin/main.active') : trans('admin/main.inactive') }}
                                                        </span>
                                                    @elseif(is_array($value))
                                                        <code>{{ json_encode($value) }}</code>
                                                    @elseif(isset($selectOptions[$column]) && !empty($value))
                                                        @php($selectedLabel = collect($selectOptions[$column])->firstWhere('id', $value)['title'] ?? null)
                                                        {{ $selectedLabel ?? $value }}
                                                    @else
                                                        {{ $value ?? '-' }}
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td width="120">
                                                @can($config['permission'] . '_edit')
                                                    <a href="{{ getAdminPanelUrl("/booking/modules/{$resource}/{$item->id}/edit") }}"
                                                       class="btn btn-sm btn-primary">
                                                        {{ trans('admin/main.edit') }}
                                                    </a>
                                                @endcan

                                                @can($config['permission'] . '_delete')
                                                    @include('admin.includes.delete_button', [
                                                        'url'      => getAdminPanelUrl("/booking/modules/{$resource}/{$item->id}/delete"),
                                                        'btnClass' => 'btn btn-sm btn-danger',
                                                        'btnText'  => trans('admin/main.delete'),
                                                    ])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            {{ $items->links() }}
                        </div>
                    </div>
                </div>

                @php($formPermission = !empty($editItem) ? $config['permission'] . '_edit' : $config['permission'] . '_create')

                @can($formPermission)
                    <div class="col-12 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ !empty($editItem) ? trans('admin/main.edit') : trans('admin/main.create') }}</h5>
                            </div>
                            <div class="card-body">
                                <form method="post"
                                      action="{{ !empty($editItem)
                                          ? getAdminPanelUrl("/booking/modules/{$resource}/{$editItem->id}/update")
                                          : getAdminPanelUrl("/booking/modules/{$resource}/store") }}">
                                    {{ csrf_field() }}

                                    @foreach($config['fields'] as $field)
    @php
        $value = old($field, !empty($editItem) ? data_get($editItem, $field) : null);
        $isRequired = str_contains($config['validation'][$field] ?? '', 'required');
    @endphp

    <div class="form-group">
        <label>
            {{ ucwords(str_replace('_', ' ', $field)) }}
            @if($isRequired)<span class="text-danger">*</span>@endif
        </label>

        @if($field === 'options')
            @php
                $optionRows    = [];
                $oldOptionKeys = old('option_keys');

                if (is_array($oldOptionKeys)) {
                    $oldOptionValues = old('option_values', []);
                    foreach ($oldOptionKeys as $idx => $k) {
                        $optionRows[] = ['key' => $k, 'value' => $oldOptionValues[$idx] ?? ''];
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
                                'value' => is_array($v) || is_bool($v) ? json_encode($v) : $v,
                            ];
                        }
                    }
                }

                if (empty($optionRows)) {
                    $optionRows[] = ['key' => '', 'value' => ''];
                }
            @endphp

            <div id="optionAttributesWrapper">
                @foreach($optionRows as $optRow)
                    <div class="row align-items-center mb-2 js-option-row">
                        <div class="col-5">
                            <input type="text" name="option_keys[]" class="form-control"
                                   value="{{ $optRow['key'] }}" placeholder="Key (e.g. min)"/>
                        </div>
                        <div class="col-5">
                            <input type="text" name="option_values[]" class="form-control"
                                   value="{{ $optRow['value'] }}" placeholder="Value (e.g. 1000)"/>
                        </div>
                        <div class="col-2 text-right">
                            <button type="button" class="btn btn-sm btn-danger js-remove-option-row">&times;</button>
                        </div>
                    </div>
                @endforeach
            </div>
            <button type="button" class="btn btn-sm btn-primary mt-2 js-add-option-row">
                + {{ trans('admin/main.add') }}
            </button>
        @elseif(in_array($field, $config['booleans'] ?? []))
            <select name="{{ $field }}" class="form-control @error($field) is-invalid @enderror">
                <option value="1" {{ (string) $value === '1' ? 'selected' : '' }}>{{ trans('admin/main.active') }}</option>
                <option value="0" {{ (string) $value === '0' ? 'selected' : '' }}>{{ trans('admin/main.inactive') }}</option>
            </select>
        @elseif(isset($selectOptions[$field]))
            <select name="{{ $field }}" data-plugin-selectTwo class="form-control @error($field) is-invalid @enderror">
                <option value="">{{ trans('admin/main.select') }}</option>
                @foreach($selectOptions[$field] as $option)
                    <option value="{{ $option['id'] }}" {{ (string) $value === (string) $option['id'] ? 'selected' : '' }}>
                        {{ $option['title'] }}
                    </option>
                @endforeach
            </select>
        @elseif(in_array($field, $config['json'] ?? []))
            <textarea name="{{ $field }}" rows="3" class="form-control @error($field) is-invalid @enderror">{{ is_array($value) ? json_encode($value) : $value }}</textarea>
        @elseif(str_contains($field, '_at'))
            <input type="datetime-local" name="{{ $field }}"
                   value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '' }}"
                   class="form-control @error($field) is-invalid @enderror">
        @elseif(str_contains($field, 'date'))
            <input type="date" name="{{ $field }}"
                   value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : '' }}"
                   class="form-control @error($field) is-invalid @enderror">
        @elseif(str_contains($field, 'time'))
            <input type="time" name="{{ $field }}" value="{{ $value }}"
                   class="form-control @error($field) is-invalid @enderror">
        @elseif(in_array($field, ['message', 'description']))
            <textarea name="{{ $field }}" rows="3" class="form-control @error($field) is-invalid @enderror">{{ $value }}</textarea>
        @else
            <input type="text" name="{{ $field }}" value="{{ $value }}"
                   class="form-control @error($field) is-invalid @enderror">
        @endif

        @error($field)
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
@endforeachs

                                    <div class="d-flex justify-content-between align-items-center mt-3">
                                        @if(!empty($editItem))
                                            <a href="{{ getAdminPanelUrl("/booking/modules/{$resource}") }}"
                                               class="btn btn-secondary">
                                                {{ trans('admin/main.cancel') }}
                                            </a>
                                        @else
                                            <div></div>
                                        @endif

                                        <button type="submit" class="btn btn-primary">
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

@push('scripts_bottom')
<script>
(function () {
    var wrapper = document.getElementById('optionAttributesWrapper');

    if (!wrapper) {
        return;
    }

    document.addEventListener('click', function (e) {

        // ── Row add karo ──────────────────────────────────────────
        if (e.target.closest('.js-add-option-row')) {
            var firstRow = wrapper.querySelector('.js-option-row');
            var clone    = firstRow.cloneNode(true);

            clone.querySelectorAll('input').forEach(function (input) {
                input.value = '';
            });

            wrapper.appendChild(clone);
        }

        // ── Row remove karo ───────────────────────────────────────
        if (e.target.closest('.js-remove-option-row')) {
            var allRows  = wrapper.querySelectorAll('.js-option-row');
            var thisRow  = e.target.closest('.js-option-row');

            if (allRows.length > 1) {
                thisRow.remove();
            } else {
                // Last row hai to sirf clear karo, remove mat karo
                thisRow.querySelectorAll('input').forEach(function (input) {
                    input.value = '';
                });
            }
        }
    });
})();
</script>
@endpush
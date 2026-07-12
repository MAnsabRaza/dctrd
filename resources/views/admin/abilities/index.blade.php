{{-- resources/views/admin/abilities/index.blade.php --}}

@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/main.abilities') ?? 'Abilities' }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/main.abilities') ?? 'Abilities' }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @php
                                $abilityFormActive = (
                                    (!empty($errors) && $errors->any()) ||
                                    !empty($editAbility) ||
                                    (
                                        (empty($abilities) || !$abilities->count()) &&
                                        auth()->user()->can('admin_abilities_create')
                                    )
                                );

                                // Existing field rows (edit mode) ya default ek khali row
                                $existingFields = [];
                                if (!empty($editAbility) && !empty($editAbility->schema_json['fields'])) {
                                    foreach ($editAbility->schema_json['fields'] as $f) {
                                        $existingFields[] = [
                                            'key'      => $f['key'] ?? '',
                                            'label'    => $f['label'] ?? '',
                                            'type'     => $f['type'] ?? 'text',
                                            'required' => !empty($f['required']),
                                            'options'  => $f['options'] ?? [],
                                        ];
                                    }
                                }
                                if (empty($existingFields)) {
                                    $existingFields[] = ['key' => '', 'label' => '', 'type' => 'text', 'required' => false, 'options' => []];
                                }
                            @endphp

                            <ul class="nav nav-pills" id="abilityTab" role="tablist">

                                @can('admin_abilities')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $abilityFormActive ? '' : 'active' }}"
                                           id="abilities-tab" data-toggle="tab" href="#abilitiesList"
                                           role="tab" aria-controls="abilitiesList" aria-selected="true">
                                            {{ trans('admin/main.abilities') ?? 'Abilities' }}
                                        </a>
                                    </li>
                                @endcan

                                @can('admin_abilities_create')
                                    <li class="nav-item">
                                        <a class="nav-link {{ $abilityFormActive ? 'active' : '' }}"
                                           id="newAbility-tab" data-toggle="tab" href="#newAbility"
                                           role="tab" aria-controls="newAbility" aria-selected="false">
                                            {{ !empty($editAbility) ? (trans('admin/main.edit_ability') ?? 'Edit Ability') : (trans('admin/main.new_ability') ?? '+ New Ability') }}
                                        </a>
                                    </li>
                                @endcan

                            </ul>

                            <div class="tab-content" id="abilityTabContent">

                                {{-- ===================== LIST TAB ===================== --}}
                                @can('admin_abilities')
                                    <div class="tab-pane mt-3 fade {{ $abilityFormActive ? '' : 'active show' }}"
                                         id="abilitiesList" role="tabpanel" aria-labelledby="abilities-tab">

                                        @if(!empty($abilities) && $abilities->count())
                                            <div class="table-responsive">
                                                <table class="table custom-table font-14">
                                                    <thead>
                                                        <tr>
                                                            <th>#</th>
                                                            <th class="text-left">{{ trans('admin/main.name') }}</th>
                                                            <th class="text-left">{{ trans('admin/main.type') }}</th>
                                                            <th class="text-left">Driver Class</th>
                                                            <th class="text-center">Vendors Assigned</th>
                                                            <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                            <th>{{ trans('admin/main.action') }}</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        @foreach($abilities as $ability)
                                                            <tr>
                                                                <td>{{ $ability->id }}</td>
                                                                <td class="text-left">{{ $ability->name }}</td>
                                                                <td class="text-left">
                                                                    <span class="badge badge-info text-capitalize">{{ $ability->type }}</span>
                                                                </td>
                                                                <td class="text-left">
                                                                    <code class="font-12">{{ $ability->driver_class }}</code>
                                                                </td>
                                                                <td class="text-center">
                                                                    <a href="{{ getAdminPanelUrl() }}/abilities/{{ $ability->id }}/show">
                                                                        {{ $ability->vendor_abilities_count ?? 0 }}
                                                                    </a>
                                                                </td>
                                                                <td class="text-center">
                                                                    @if($ability->is_active)
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
                                                                            @can('admin_abilities_edit')
                                                                                <a href="{{ getAdminPanelUrl() }}/abilities/{{ $ability->id }}/edit"
                                                                                   class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                                    <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                                    <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                                </a>
                                                                            @endcan
                                                                            @can('admin_abilities_delete')
                                                                                @include('admin.includes.delete_button', [
                                                                                    'url'       => getAdminPanelUrl() . '/abilities/' . $ability->id . '/delete',
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
                                            <div class="mt-3">{{ $abilities->links() }}</div>
                                        @else
                                            <div class="text-center text-gray-500 mt-30">
                                                {{ trans('admin/main.no_result') }}
                                            </div>
                                        @endif

                                    </div>
                                @endcan

                                {{-- ===================== FORM TAB ===================== --}}
                                @can('admin_abilities_create')
                                    <div class="tab-pane mt-3 fade {{ $abilityFormActive ? 'active show' : '' }}"
                                         id="newAbility" role="tabpanel" aria-labelledby="newAbility-tab">

                                        <div class="row">
                                            <div class="col-12 col-md-8">

                                                <form action="{{ getAdminPanelUrl() }}/abilities/{{ !empty($editAbility) ? $editAbility->id . '/update' : 'store' }}"
                                                      method="POST">
                                                    @csrf

                                                    {{-- Name --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.name') }} <span class="text-danger">*</span></label>
                                                        <input type="text" name="name"
                                                               class="form-control @error('name') is-invalid @enderror"
                                                               value="{{ !empty($editAbility) ? $editAbility->name : old('name') }}"
                                                               placeholder="e.g. Perfex Export - Orders"/>
                                                        @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Type --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.type') }} <span class="text-danger">*</span></label>
                                                        @php
                                                            $types = [
                                                                'import'       => 'Import',
                                                                'export'       => 'Export',
                                                                'booking'      => 'Booking',
                                                                'dropshipping' => 'Dropshipping',
                                                            ];
                                                            $currentType = !empty($editAbility) ? $editAbility->type : old('type', 'import');
                                                        @endphp
                                                        <select name="type"
                                                                class="form-control @error('type') is-invalid @enderror">
                                                            @foreach($types as $val => $label)
                                                                <option value="{{ $val }}" {{ $currentType === $val ? 'selected' : '' }}>
                                                                    {{ $label }}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                        @error('type') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Driver Class --}}
                                                    <div class="form-group">
                                                        <label>Driver Class <span class="text-danger">*</span></label>
                                                        <input type="text" name="driver_class"
                                                               class="form-control @error('driver_class') is-invalid @enderror"
                                                               value="{{ !empty($editAbility) ? $editAbility->driver_class : old('driver_class') }}"
                                                               placeholder="App\Services\Abilities\PerfexExportAbility"/>
                                                        @error('driver_class') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Description --}}
                                                    <div class="form-group">
                                                        <label>{{ trans('admin/main.description') ?? 'Description' }}</label>
                                                        <textarea name="description"
                                                                  class="form-control @error('description') is-invalid @enderror"
                                                                  rows="2">{{ !empty($editAbility) ? $editAbility->description : old('description') }}</textarea>
                                                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- ====================================================== --}}
                                                    {{-- CONFIG FIELDS — key / label / type / options / required --}}
                                                    {{-- Saves as JSON: {"fields":[{...},{...}]}                --}}
                                                    {{-- ====================================================== --}}
                                                    <div class="form-group">
                                                        <label>Config Fields <span class="text-danger">*</span></label>
                                                        <div class="text-gray-500 text-small mb-2">
                                                            Ye fields hain jo vendor ko apni ability configure karte waqt bharne honge (e.g. API URL, API Key).
                                                            "Select" type ke liye Options field mein comma-separated values likhein (e.g. <code>hourly,daily,weekly</code>).
                                                        </div>

                                                        <div id="fieldsWrap">
                                                            @foreach($existingFields as $f)
                                                                <div class="field-row d-flex align-items-center flex-wrap mb-2">
                                                                    <input type="text" name="field_key[]"
                                                                           class="form-control mr-2 mb-2" style="max-width:150px"
                                                                           placeholder="Key e.g. api_key"
                                                                           value="{{ $f['key'] }}"/>
                                                                    <input type="text" name="field_label[]"
                                                                           class="form-control mr-2 mb-2" style="max-width:150px"
                                                                           placeholder="Label e.g. API Key"
                                                                           value="{{ $f['label'] }}"/>
                                                                    <select name="field_type[]" class="form-control mr-2 mb-2 js-field-type" style="max-width:130px">
                                                                        @foreach(['text' => 'Text', 'password' => 'Password', 'boolean' => 'Checkbox', 'select' => 'Select', 'textarea' => 'Textarea'] as $val => $label)
                                                                            <option value="{{ $val }}" {{ $f['type'] === $val ? 'selected' : '' }}>{{ $label }}</option>
                                                                        @endforeach
                                                                    </select>
                                                                    <input type="text" name="field_options[]"
                                                                           class="form-control mr-2 mb-2 js-field-options"
                                                                           style="max-width:170px; {{ ($f['type'] ?? 'text') === 'select' ? '' : 'display:none;' }}"
                                                                           placeholder="Options: hourly,daily,weekly"
                                                                           value="{{ !empty($f['options']) ? implode(',', $f['options']) : '' }}"/>
                                                                    <div class="custom-control custom-checkbox mr-2 mb-2 flex-shrink-0">
                                                                        <input type="checkbox" name="field_required[{{ $loop->index }}]" value="1"
                                                                               class="custom-control-input" id="fieldRequired{{ $loop->index }}"
                                                                               {{ $f['required'] ? 'checked' : '' }}>
                                                                        <label class="custom-control-label" for="fieldRequired{{ $loop->index }}">Required</label>
                                                                    </div>
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-outline-danger js-remove-field flex-shrink-0 mb-2">
                                                                        &times;
                                                                    </button>
                                                                </div>
                                                            @endforeach
                                                        </div>

                                                        <button type="button" id="btnAddField"
                                                                class="btn btn-sm btn-outline-primary mt-1">
                                                            + Add Field
                                                        </button>

                                                        @error('fields') <div class="text-danger text-small mt-1">{{ $message }}</div> @enderror
                                                    </div>

                                                    {{-- Status --}}
                                                    <div class="form-group">
                                                        <div class="custom-control custom-switch">
                                                            <input type="checkbox" name="status"
                                                                   class="custom-control-input" id="status"
                                                                   {{ (!empty($editAbility) && $editAbility->is_active) || empty($editAbility) ? 'checked' : '' }}>
                                                            <label class="custom-control-label" for="status">
                                                                {{ trans('admin/main.active') }}
                                                            </label>
                                                        </div>
                                                    </div>

                                                    <div class="text-right col-12 mt-3">
                                                        @if(!empty($editAbility))
                                                            <a href="{{ getAdminPanelUrl() }}/abilities"
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

    var wrap = document.getElementById('fieldsWrap');
    var rowIndex = document.querySelectorAll('#fieldsWrap .field-row').length;

    document.getElementById('btnAddField').addEventListener('click', function () {
        appendRow();
    });

    wrap.addEventListener('click', function (e) {
        if (!e.target.classList.contains('js-remove-field')) return;
        var rows = wrap.querySelectorAll('.field-row');
        if (rows.length > 1) {
            e.target.closest('.field-row').remove();
        } else {
            e.target.closest('.field-row').querySelectorAll('input[type="text"]').forEach(function (i) { i.value = ''; });
            e.target.closest('.field-row').querySelector('input[type="checkbox"]').checked = false;
            var optionsInput = e.target.closest('.field-row').querySelector('.js-field-options');
            if (optionsInput) optionsInput.style.display = 'none';
        }
    });

    // field_type change hone par options input show/hide
    wrap.addEventListener('change', function (e) {
        if (!e.target.classList.contains('js-field-type')) return;
        var row = e.target.closest('.field-row');
        var optionsInput = row.querySelector('.js-field-options');
        optionsInput.style.display = (e.target.value === 'select') ? '' : 'none';
    });

    function appendRow() {
        var div = document.createElement('div');
        div.className = 'field-row d-flex align-items-center flex-wrap mb-2';
        div.innerHTML =
            '<input type="text" name="field_key[]" class="form-control mr-2 mb-2" style="max-width:150px" placeholder="Key e.g. api_key"/>' +
            '<input type="text" name="field_label[]" class="form-control mr-2 mb-2" style="max-width:150px" placeholder="Label e.g. API Key"/>' +
            '<select name="field_type[]" class="form-control mr-2 mb-2 js-field-type" style="max-width:130px">' +
                '<option value="text">Text</option>' +
                '<option value="password">Password</option>' +
                '<option value="boolean">Checkbox</option>' +
                '<option value="select">Select</option>' +
                '<option value="textarea">Textarea</option>' +
            '</select>' +
            '<input type="text" name="field_options[]" class="form-control mr-2 mb-2 js-field-options" style="max-width:170px; display:none;" placeholder="Options: hourly,daily,weekly"/>' +
            '<div class="custom-control custom-checkbox mr-2 mb-2 flex-shrink-0">' +
                '<input type="checkbox" name="field_required[' + rowIndex + ']" value="1" class="custom-control-input" id="fieldRequired' + rowIndex + '">' +
                '<label class="custom-control-label" for="fieldRequired' + rowIndex + '">Required</label>' +
            '</div>' +
            '<button type="button" class="btn btn-sm btn-outline-danger js-remove-field flex-shrink-0 mb-2">&times;</button>';
        wrap.appendChild(div);
        rowIndex++;
    }

})();
</script>
@endpush
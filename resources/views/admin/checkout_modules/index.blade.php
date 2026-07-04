{{-- resources/views/admin/pages/checkout_modules/index.blade.php --}}
@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ trans('admin/pages/checkout_modules.list_title') }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ trans('admin/pages/checkout_modules.list_title') }}</div>
            </div>
        </div>

        <div class="section-body">

            {{-- Flash Messages --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            @php
                                // Open create/edit tab when: errors exist, edit mode, or explicitly requested
                                $openFormTab = (
                                    (!empty($errors) && $errors->any()) ||
                                    !empty($editModule) ||
                                    !empty($openCreateTab)
                                );
                            @endphp

                            {{-- ── TABS ── --}}
                            <ul class="nav nav-pills mb-3" id="checkoutModuleTabs" role="tablist">
                                <li class="nav-item">
                                    <a class="nav-link {{ $openFormTab ? '' : 'active' }}"
                                       id="tab-list" data-toggle="tab" href="#pane-list" role="tab">
                                        {{ trans('admin/pages/checkout_modules.list_title') }}
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link {{ $openFormTab ? 'active' : '' }}"
                                       id="tab-form" data-toggle="tab" href="#pane-form" role="tab">
                                        {{ !empty($editModule)
                                            ? trans('admin/pages/checkout_modules.edit_title')
                                            : trans('admin/pages/checkout_modules.create_title') }}
                                    </a>
                                </li>
                            </ul>

                            <div class="tab-content" id="checkoutModuleTabContent">

                                {{-- ══════════════════════════════════
                                     TAB 1 : LIST
                                ══════════════════════════════════ --}}
                                <div class="tab-pane fade {{ $openFormTab ? '' : 'show active' }}"
                                     id="pane-list" role="tabpanel">

                                    @if($modules->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th class="text-left">{{ trans('admin/pages/checkout_modules.name') }}</th>
                                                        <th class="text-center">{{ trans('admin/pages/checkout_modules.input_type') }}</th>
                                                        <th class="text-center">{{ trans('admin/pages/checkout_modules.order') }}</th>
                                                       
                                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($modules as $module)
                                                        <tr id="module-row-{{ $module->id }}">

                                                            {{-- Name + translated label --}}
                                                            <td class="text-left">
                                                                <span class="font-weight-bold">{{ $module->name }}</span>
                                                                @if($module->translated_label !== $module->name)
                                                                    <br><small class="text-muted">{{ $module->translated_label }}</small>
                                                                @endif
                                                            </td>

                                                            {{-- Input Type --}}
                                                            <td class="text-center">
                                                                <span class="badge badge-info font-12">{{ $module->input_type }}</span>
                                                            </td>

                                                            {{-- Order --}}
                                                            <td class="text-center">{{ $module->order_index }}</td>

                                                         

                                                            {{-- Active Toggle (AJAX) --}}
                                                            <td class="text-center">
                                                                <div class="custom-control custom-switch">
                                                                    <input type="checkbox"
                                                                           class="custom-control-input module-toggle"
                                                                           id="toggle-{{ $module->id }}"
                                                                           data-id="{{ $module->id }}"
                                                                           {{ $module->is_active ? 'checked' : '' }}>
                                                                    <label class="custom-control-label"
                                                                           for="toggle-{{ $module->id }}"></label>
                                                                </div>
                                                            </td>

                                                            {{-- Actions --}}
                                                            <td class="text-center" width="80px">
                                                                <div class="btn-group dropdown table-actions">
                                                                    <button type="button"
                                                                            class="btn-transparent dropdown-toggle"
                                                                            data-toggle="dropdown">
                                                                        <x-iconsax-lin-more class="icons text-gray-500" width="20px" height="20px"/>
                                                                    </button>
                                                                    <div class="dropdown-menu dropdown-menu-right">

                                                                        {{-- Edit --}}
                                                                        <a href="{{ getAdminPanelUrl() }}/checkout-modules/{{ $module->id }}/edit"
                                                                           class="dropdown-item d-flex align-items-center mb-3 py-3 px-0 gap-4">
                                                                            <x-iconsax-lin-edit-2 class="icons text-gray-500 mr-2" width="18px" height="18px"/>
                                                                            <span class="text-gray-500 font-14">{{ trans('admin/main.edit') }}</span>
                                                                        </a>

                                                                        {{-- Delete --}}
                                                                        @include('admin.includes.delete_button', [
                                                                            'url'       => getAdminPanelUrl() . '/checkout-modules/' . $module->id . '/delete',
                                                                            'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                            'btnText'   => trans('admin/main.delete'),
                                                                            'btnIcon'   => 'trash',
                                                                            'iconType'  => 'lin',
                                                                            'iconClass' => 'text-danger mr-2',
                                                                            'method'    => 'GET',
                                                                        ])

                                                                    </div>
                                                                </div>
                                                            </td>

                                                        </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>

                                        <div class="mt-3">{{ $modules->links() }}</div>

                                    @else
                                        <div class="text-center text-gray-500 mt-30 py-4">
                                            {{ trans('admin/main.no_result') }}
                                        </div>
                                    @endif

                                </div>
                                {{-- END TAB 1 --}}

                                {{-- ══════════════════════════════════
                                     TAB 2 : CREATE / EDIT FORM
                                ══════════════════════════════════ --}}
                                <div class="tab-pane fade {{ $openFormTab ? 'show active' : '' }}"
                                     id="pane-form" role="tabpanel">

                                    <div class="row">
                                        <div class="col-12 col-md-8">

                                            @php
                                                $formAction = !empty($editModule)
                                                    ? getAdminPanelUrl() . '/checkout-modules/' . $editModule->id
                                                    : getAdminPanelUrl() . '/checkout-modules';
                                            @endphp

                                            <form action="{{ $formAction }}" method="POST" id="checkoutModuleForm">
                                                @csrf
                                                @if(!empty($editModule))
                                                    @method('PUT')
                                                @endif

                                                {{-- ── Name ── --}}
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        {{ trans('admin/pages/checkout_modules.name') }}
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <input type="text" name="name"
                                                           class="form-control @error('name') is-invalid @enderror"
                                                           value="{{ !empty($editModule) ? $editModule->name : old('name') }}"
                                                           placeholder="e.g. days, hours, extra_services"
                                                           {{ !empty($editModule) ? 'readonly' : 'required' }}/>
                                                    <small class="text-muted">
                                                        {{ trans('admin/pages/checkout_modules.name_hint') }}
                                                    </small>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Input Type ── --}}
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        {{ trans('admin/pages/checkout_modules.input_type') }}
                                                        <span class="text-danger">*</span>
                                                    </label>
                                                    <select name="input_type"
                                                            class="form-control @error('input_type') is-invalid @enderror"
                                                            required>
                                                        <option value="">— {{ trans('admin/main.select') }} —</option>
                                                        @foreach($inputTypes as $value => $label)
                                                            <option value="{{ $value }}"
                                                                {{ ((!empty($editModule) && $editModule->input_type === $value) || old('input_type') === $value) ? 'selected' : '' }}>
                                                                {{ $label }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('input_type')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Config (Key/Value rows only — no raw textarea) ── --}}
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        {{ trans('admin/pages/checkout_modules.config') }}
                                                    </label>
                                                    <small class="d-block text-muted mb-2">
                                                        {{ trans('admin/pages/checkout_modules.config_hint') }}
                                                    </small>

                                                    <div id="configAttributes">
                                                        @php
                                                            $configInit = [];
                                                            if (!empty($editModule) && $editModule->config) {
                                                                $configInit = (array) $editModule->config;
                                                            } elseif (old('config')) {
                                                                try { $configInit = json_decode(old('config'), true) ?? []; } catch(\Exception $e) { $configInit = []; }
                                                            }
                                                        @endphp

                                                        @if(!empty($configInit))
                                                            @foreach($configInit as $k => $v)
                                                                <div class="d-flex mb-2 js-config-row">
                                                                    <input type="text" name="config_keys[]"
                                                                           class="form-control mr-2"
                                                                           placeholder="Key"
                                                                           value="{{ $k }}">
                                                                    <input type="text" name="config_values[]"
                                                                           class="form-control mr-2"
                                                                           placeholder="Value"
                                                                           value="{{ is_scalar($v) ? $v : json_encode($v) }}">
                                                                    <button type="button"
                                                                            class="btn btn-sm btn-danger js-remove-config">&minus;</button>
                                                                </div>
                                                            @endforeach
                                                        @else
                                                            <div class="d-flex mb-2 js-config-row">
                                                                <input type="text" name="config_keys[]"
                                                                       class="form-control mr-2"
                                                                       placeholder="Key">
                                                                <input type="text" name="config_values[]"
                                                                       class="form-control mr-2"
                                                                       placeholder="Value">
                                                                <button type="button"
                                                                        class="btn btn-sm btn-danger js-remove-config">&minus;</button>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <button type="button" class="btn btn-sm btn-outline-primary js-add-config">
                                                        + {{ trans('admin/main.add') }}
                                                    </button>

                                                    {{-- Hidden textarea — serialized by JS on submit --}}
                                                    <input type="hidden" name="config" id="config_hidden"
                                                           value="{{ !empty($editModule) && $editModule->config ? json_encode($editModule->config) : old('config') }}">
                                                    @error('config')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Price Rule (Key/Value rows only) ── --}}
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        {{ trans('admin/pages/checkout_modules.price_rule') }}
                                                    </label>
                                                    <small class="d-block text-muted mb-2">
                                                        {{ trans('admin/pages/checkout_modules.price_rule_hint') }}
                                                    </small>

                                                    <div id="priceAttributes">
                                                        @php
                                                            $priceInit = [];
                                                            if (!empty($editModule) && $editModule->price_rule) {
                                                                $priceInit = (array) $editModule->price_rule;
                                                            } elseif (old('price_rule')) {
                                                                try { $priceInit = json_decode(old('price_rule'), true) ?? []; } catch(\Exception $e) { $priceInit = []; }
                                                            }
                                                            if (empty($priceInit)) {
                                                                $priceInit = ['type' => 'none'];
                                                            }
                                                        @endphp

                                                        @foreach($priceInit as $k => $v)
                                                            <div class="d-flex mb-2 js-price-row">
                                                                <input type="text" name="price_keys[]"
                                                                       class="form-control mr-2"
                                                                       placeholder="Key"
                                                                       value="{{ $k }}">
                                                                <input type="text" name="price_values[]"
                                                                       class="form-control mr-2"
                                                                       placeholder="Value"
                                                                       value="{{ is_scalar($v) ? $v : json_encode($v) }}">
                                                                <button type="button"
                                                                        class="btn btn-sm btn-danger js-remove-price">&minus;</button>
                                                            </div>
                                                        @endforeach
                                                    </div>

                                                    <button type="button" class="btn btn-sm btn-outline-primary js-add-price">
                                                        + {{ trans('admin/main.add') }}
                                                    </button>

                                                    {{-- Hidden — serialized by JS --}}
                                                    <input type="hidden" name="price_rule" id="price_hidden"
                                                           value="{{ !empty($editModule) && $editModule->price_rule ? json_encode($editModule->price_rule) : old('price_rule') }}">
                                                    @error('price_rule')
                                                        <div class="text-danger small mt-1">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Order Index ── --}}
                                                <div class="form-group">
                                                    <label class="font-weight-bold">
                                                        {{ trans('admin/pages/checkout_modules.order') }}
                                                    </label>
                                                    <input type="number" name="order_index" min="0"
                                                           class="form-control @error('order_index') is-invalid @enderror"
                                                           value="{{ !empty($editModule) ? $editModule->order_index : old('order_index', 0) }}"
                                                           required style="max-width:160px;"/>
                                                    @error('order_index')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Toggles ── --}}
                                                <div class="form-group">
                                                  
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_active"
                                                               class="custom-control-input" id="is_active"
                                                               value="1"
                                                               {{ (empty($editModule) || $editModule->is_active) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_active">
                                                            {{ trans('admin/main.active') }}
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- ── Translations ── --}}
                                                <div class="form-group mt-4">
                                                    <label class="font-weight-bold">
                                                        {{ trans('admin/pages/checkout_modules.translations') }}
                                                    </label>
                                                    <div class="border rounded p-3">
                                                        @foreach($locales as $locale => $localeName)
                                                            @php $existing = $translationsByLocale[$locale] ?? null; @endphp

                                                            <div class="{{ $loop->last ? '' : 'mb-3' }}">
                                                                <label class="text-muted font-12 mb-1">
                                                                    {{ $localeName }} ({{ strtoupper($locale) }})
                                                                </label>
                                                                <input type="hidden"
                                                                       name="translations[{{ $loop->index }}][locale]"
                                                                       value="{{ $locale }}">
                                                                <input type="text"
                                                                       name="translations[{{ $loop->index }}][label]"
                                                                       class="form-control mb-1"
                                                                       value="{{ $existing ? $existing->label : '' }}"
                                                                       placeholder="{{ trans('admin/pages/checkout_modules.label_placeholder') }} ({{ $localeName }})"/>
                                                                <input type="text"
                                                                       name="translations[{{ $loop->index }}][help_text]"
                                                                       class="form-control"
                                                                       value="{{ $existing ? ($existing->help_text ?? '') : '' }}"
                                                                       placeholder="{{ trans('admin/pages/checkout_modules.help_text_placeholder') }} ({{ $localeName }})"/>
                                                            </div>

                                                            @if(!$loop->last)
                                                                <hr class="my-3">
                                                            @endif
                                                        @endforeach
                                                    </div>
                                                </div>

                                                {{-- ── Submit Buttons ── --}}
                                                <div class="d-flex justify-content-end mt-4">
                                                    @if(!empty($editModule))
                                                        <a href="{{ getAdminPanelUrl() }}/checkout-modules"
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
                                {{-- END TAB 2 --}}

                            </div>
                            {{-- end tab-content --}}

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
    'use strict';

    // ── Helpers ──────────────────────────────────────────────

    function escHtml(str) {
        return String(str || '')
            .replace(/&/g, '&amp;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#39;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;');
    }

    function addRow(containerId, keyName, valueName, removeClass, rowClass) {
        var c = document.getElementById(containerId);
        if (!c) return;
        var d = document.createElement('div');
        d.className = 'd-flex mb-2 ' + rowClass;
        d.innerHTML =
            '<input type="text" name="' + keyName + '" class="form-control mr-2" placeholder="Key">' +
            '<input type="text" name="' + valueName + '" class="form-control mr-2" placeholder="Value">' +
            '<button type="button" class="btn btn-sm btn-danger ' + removeClass + '">&minus;</button>';
        c.appendChild(d);
    }

    // ── Click delegation for add/remove rows ─────────────────
    document.addEventListener('click', function (e) {
        var t = e.target;

        if (t.classList.contains('js-add-config')) {
            addRow('configAttributes', 'config_keys[]', 'config_values[]', 'js-remove-config', 'js-config-row');
        } else if (t.classList.contains('js-add-price')) {
            addRow('priceAttributes', 'price_keys[]', 'price_values[]', 'js-remove-price', 'js-price-row');
        } else if (t.classList.contains('js-remove-config')) {
            var row = t.closest('.js-config-row');
            if (row) row.remove();
        } else if (t.classList.contains('js-remove-price')) {
            var row = t.closest('.js-price-row');
            if (row) row.remove();
        }
    });

    // ── Serialize key-value rows → hidden JSON inputs on submit ──
    var form = document.getElementById('checkoutModuleForm');
    if (form) {
        form.addEventListener('submit', function () {

            // Config
            var configObj = {};
            document.querySelectorAll('#configAttributes .js-config-row').forEach(function (row) {
                var k = (row.querySelector('input[name="config_keys[]"]').value || '').trim();
                var v = (row.querySelector('input[name="config_values[]"]').value || '').trim();
                if (k) {
                    try { configObj[k] = JSON.parse(v); } catch (e) { configObj[k] = v; }
                }
            });
            document.getElementById('config_hidden').value = JSON.stringify(configObj);

            // Price Rule
            var priceObj = {};
            document.querySelectorAll('#priceAttributes .js-price-row').forEach(function (row) {
                var k = (row.querySelector('input[name="price_keys[]"]').value || '').trim();
                var v = (row.querySelector('input[name="price_values[]"]').value || '').trim();
                if (k) {
                    try { priceObj[k] = JSON.parse(v); } catch (e) { priceObj[k] = v; }
                }
            });
            document.getElementById('price_hidden').value = JSON.stringify(priceObj);
        });
    }

    // ── AJAX Toggle active/inactive ──────────────────────────
    document.querySelectorAll('.module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var id  = this.dataset.id;
            var chk = this;

            fetch('{{ getAdminPanelUrl() }}/checkout-modules/' + id + '/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(function (r) { return r.json(); })
            .then(function (data) {
                if (data.success) {
                    if (typeof toastr !== 'undefined') toastr.success(data.message);
                } else {
                    chk.checked = !chk.checked;
                    if (typeof toastr !== 'undefined') toastr.error('{{ trans('admin/pages/checkout_modules.toggle_failed') }}');
                }
            })
            .catch(function () {
                chk.checked = !chk.checked;
                if (typeof toastr !== 'undefined') toastr.error('{{ trans('admin/pages/checkout_modules.toggle_failed') }}');
            });
        });
    });

})();
</script>
@endpush
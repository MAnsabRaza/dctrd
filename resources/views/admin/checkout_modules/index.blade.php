{{-- resources/views/admin/checkout_modules/index.blade.php --}}
{{-- Same style as admin/booking/categories.blade.php --}}
{{-- List + Create + Edit sab ek hi page pe --}}

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
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">

                            {{-- ===================== TABS ===================== --}}
                            @php
                                $createActive = (
                                    (!empty($errors) && $errors->any()) ||
                                    !empty($editModule) ||
                                    (empty($modules) || !$modules->count())
                                );
                            @endphp

                            <ul class="nav nav-pills" id="checkoutModuleTabs" role="tablist">

                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? '' : 'active' }}"
                                       id="modules-list-tab" data-toggle="tab" href="#modules-list"
                                       role="tab" aria-controls="modules-list" aria-selected="true">
                                        {{ trans('admin/pages/checkout_modules.list_title') }}
                                    </a>
                                </li>

                                <li class="nav-item">
                                    <a class="nav-link {{ $createActive ? 'active' : '' }}"
                                       id="module-form-tab" data-toggle="tab" href="#module-form"
                                       role="tab" aria-controls="module-form" aria-selected="false">
                                        {{ !empty($editModule) ? trans('admin/pages/checkout_modules.edit_title') : trans('admin/pages/checkout_modules.create_title') }}
                                    </a>
                                </li>

                            </ul>

                            <div class="tab-content mt-3" id="checkoutModuleTabContent">

                                {{-- ===================== TAB 1: LIST ===================== --}}
                                <div class="tab-pane fade {{ $createActive ? '' : 'active show' }}"
                                     id="modules-list" role="tabpanel" aria-labelledby="modules-list-tab">

                                    @if(!empty($modules) && $modules->count())
                                        <div class="table-responsive">
                                            <table class="table custom-table font-14">
                                                <thead>
                                                    <tr>
                                                        <th class="text-left">{{ trans('admin/pages/checkout_modules.name') }}</th>
                                                        <th class="text-center">{{ trans('admin/pages/checkout_modules.input_type') }}</th>
                                                        <th class="text-center">{{ trans('admin/pages/checkout_modules.order') }}</th>
                                                        <th class="text-center">{{ trans('admin/pages/checkout_modules.required') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.status') }}</th>
                                                        <th class="text-center">{{ trans('admin/main.action') }}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($modules as $module)
                                                        <tr id="module-row-{{ $module->id }}">

                                                            {{-- Name --}}
                                                            <td class="text-left">
                                                                <span class="font-weight-bold">{{ $module->name }}</span>
                                                                @if(!empty($module->translated_label))
                                                                    <br>
                                                                    <small class="text-muted">{{ $module->translated_label }}</small>
                                                                @endif
                                                            </td>

                                                            {{-- Input Type --}}
                                                            <td class="text-center">
                                                                <span class="badge badge-info font-12">{{ $module->input_type }}</span>
                                                            </td>

                                                            {{-- Order --}}
                                                            <td class="text-center">{{ $module->order_index }}</td>

                                                            {{-- Required --}}
                                                            <td class="text-center">
                                                                @if($module->is_required)
                                                                    <span class="badge badge-warning">{{ trans('admin/pages/checkout_modules.yes') }}</span>
                                                                @else
                                                                    <span class="text-muted">—</span>
                                                                @endif
                                                            </td>

                                                            {{-- Active Toggle --}}
                                                            <td class="text-center">
                                                                <div class="custom-control custom-switch">
                                                                    <input type="checkbox"
                                                                           class="custom-control-input module-toggle"
                                                                           id="toggle-{{ $module->id }}"
                                                                           data-id="{{ $module->id }}"
                                                                           {{ $module->is_active ? 'checked' : '' }}>
                                                                    <label class="custom-control-label" for="toggle-{{ $module->id }}"></label>
                                                                </div>
                                                            </td>

                                                            {{-- Actions --}}
                                                            <td class="text-center" width="80px">
                                                                <div class="btn-group dropdown table-actions position-relative">
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
                                                                            'url'       => getAdminPanelUrl() . '/checkout-modules/' . $module->id,
                                                                            'btnClass'  => 'dropdown-item text-danger mb-0 py-3 px-0 font-14',
                                                                            'btnText'   => trans('admin/main.delete'),
                                                                            'btnIcon'   => 'trash',
                                                                            'iconType'  => 'lin',
                                                                            'iconClass' => 'text-danger mr-2',
                                                                            'method'    => 'DELETE',
                                                                        ])

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
                                            {{ $modules->links() }}
                                        </div>

                                    @else
                                        <div class="text-center text-gray-500 mt-30">
                                            {{ trans('admin/main.no_result') }}
                                        </div>
                                    @endif

                                </div>

                                {{-- ===================== TAB 2: CREATE / EDIT FORM ===================== --}}
                                <div class="tab-pane fade {{ $createActive ? 'active show' : '' }}"
                                     id="module-form" role="tabpanel" aria-labelledby="module-form-tab">

                                    <div class="row">
                                        <div class="col-12 col-md-8">

                                            <form action="{{ !empty($editModule)
                                                    ? getAdminPanelUrl() . '/checkout-modules/' . $editModule->id
                                                    : getAdminPanelUrl() . '/checkout-modules' }}"
                                                  method="POST">
                                                @csrf
                                                @if(!empty($editModule))
                                                    @method('PUT')
                                                @endif

                                                {{-- ── Module Name ── --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/pages/checkout_modules.name') }} <span class="text-danger">*</span></label>
                                                    <input type="text" name="name"
                                                           class="form-control @error('name') is-invalid @enderror"
                                                           value="{{ !empty($editModule) ? $editModule->name : old('name') }}"
                                                           placeholder="{{ trans('admin/pages/checkout_modules.name_placeholder') }}"
                                                           {{ !empty($editModule) ? 'readonly' : '' }}/>
                                                    <div class="text-muted text-small mt-1">
                                                        {{ trans('admin/pages/checkout_modules.name_hint') }}
                                                    </div>
                                                    @error('name')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Input Type ── --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/pages/checkout_modules.input_type') }} <span class="text-danger">*</span></label>
                                                    <select name="input_type"
                                                            class="form-control @error('input_type') is-invalid @enderror">
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

                                                {{-- ── Config (JSON) ── --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/pages/checkout_modules.config') }}</label>
                                                    <textarea name="config" rows="5"
                                                              class="form-control font-monospace @error('config') is-invalid @enderror"
                                                              placeholder='{"min_days": 1, "max_days": 365}'>{{ !empty($editModule) && $editModule->config ? json_encode($editModule->config, JSON_PRETTY_PRINT) : old('config') }}</textarea>
                                                    <div class="text-muted text-small mt-1">
                                                        {{ trans('admin/pages/checkout_modules.config_hint') }}
                                                    </div>
                                                    @error('config')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Price Rule (JSON) ── --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/pages/checkout_modules.price_rule') }}</label>
                                                    <textarea name="price_rule" rows="3"
                                                              class="form-control font-monospace @error('price_rule') is-invalid @enderror"
                                                              placeholder='{"type": "per_day", "amount": 0}'>{{ !empty($editModule) && $editModule->price_rule ? json_encode($editModule->price_rule, JSON_PRETTY_PRINT) : old('price_rule') }}</textarea>
                                                    <div class="text-muted text-small mt-1">
                                                        {{ trans('admin/pages/checkout_modules.price_rule_hint') }}
                                                    </div>
                                                    @error('price_rule')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Order Index ── --}}
                                                <div class="form-group">
                                                    <label>{{ trans('admin/pages/checkout_modules.order') }}</label>
                                                    <input type="number" name="order_index" min="0"
                                                           class="form-control @error('order_index') is-invalid @enderror"
                                                           value="{{ !empty($editModule) ? $editModule->order_index : old('order_index', 0) }}"/>
                                                    @error('order_index')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>

                                                {{-- ── Is Required ── --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_required"
                                                               class="custom-control-input" id="is_required"
                                                               value="1"
                                                               {{ (!empty($editModule) && $editModule->is_required) ? 'checked' : '' }}>
                                                        <label class="custom-control-label" for="is_required">
                                                            {{ trans('admin/pages/checkout_modules.required') }}
                                                        </label>
                                                    </div>
                                                </div>

                                                {{-- ── Is Active ── --}}
                                                <div class="form-group">
                                                    <div class="custom-control custom-switch">
                                                        <input type="checkbox" name="is_active"
                                                               class="custom-control-input" id="is_active"
                                                               value="1"
                                                               {{ (empty($editModule) || (!empty($editModule) && $editModule->is_active)) ? 'checked' : '' }}>
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
                                                            @php
                                                                $existing = $translationsByLocale[$locale] ?? null;
                                                            @endphp

                                                            <div class="mb-3">
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
                                                                       value="{{ $existing ? $existing->help_text : '' }}"
                                                                       placeholder="{{ trans('admin/pages/checkout_modules.help_text_placeholder') }} ({{ $localeName }})"/>
                                                            </div>

                                                            @if(!$loop->last)
                                                                <hr class="my-2">
                                                            @endif
                                                        @endforeach

                                                    </div>
                                                </div>

                                                {{-- ── Buttons ── --}}
                                                <div class="text-right col-12 mt-3">
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
                            {{-- END tab-content --}}

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

    // ── Toggle Active/Inactive via AJAX ──────────────────────
    document.querySelectorAll('.module-toggle').forEach(function (toggle) {
        toggle.addEventListener('change', function () {
            var moduleId = this.dataset.id;
            var checkbox = this;

            fetch('{{ getAdminPanelUrl() }}/checkout-modules/' + moduleId + '/toggle', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            })
            .then(function (res) { return res.json(); })
            .then(function (data) {
                if (data.success) {
                    // Toast notification dikhao
                    toastr.success(data.message);
                } else {
                    // Fail hua toh toggle wapas karo
                    checkbox.checked = !checkbox.checked;
                    toastr.error('{{ trans('admin/pages/checkout_modules.toggle_failed') }}');
                }
            })
            .catch(function () {
                checkbox.checked = !checkbox.checked;
                toastr.error('{{ trans('admin/pages/checkout_modules.toggle_failed') }}');
            });
        });
    });

    // ── Edit button click hone pe form tab pe jao ────────────
    // (Edit link redirect karta hai — controller $editModule pass karta hai
    //  aur $createActive true ho jata hai — tab automatically switch hoti hai)

})();
</script>
@endpush
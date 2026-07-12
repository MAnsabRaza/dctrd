@php
    $abilities = $abilities ?? collect();
@endphp

<style>
    .ability-block {
        border: 1px solid #e5e9f0;
        border-radius: 12px;
        padding: 24px;
        background: #f8fafc;
        margin-bottom: 24px;
    }
    .ability-title {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 4px;
    }
    .ability-badge {
        font-size: 12.5px;
        font-weight: 600;
        padding: 4px 12px;
        border-radius: 999px;
    }
    .ability-badge-on  { background:#ecfdf3; color:#16a34a; }
    .ability-badge-off { background:#f1f5f9; color:#64748b; }
</style>

<div class="p-16">

    @forelse($abilities as $row)
        @php
            $ability = $row['ability'];
            $fields  = $row['fields'];
            $config  = $row['config'];
            $enabled = $row['enabled'];
        @endphp

        <div class="ability-title">
            <h5 class="mb-0">
                {{ $ability->name }}
                <span class="badge badge-info text-capitalize ml-2">{{ $ability->type }}</span>
            </h5>
            <span class="ability-badge {{ $enabled ? 'ability-badge-on' : 'ability-badge-off' }}">
                {{ $enabled ? (trans('panel.enabled') ?? 'Enabled') : (trans('panel.disabled') ?? 'Disabled') }}
            </span>
        </div>

        @if($ability->description)
            <p class="text-muted font-14 mb-3">{{ $ability->description }}</p>
        @endif

        {{-- data-save-url / data-disable-url — route() helper se, hardcoded path nahi --}}
        <div class="ability-block"
             id="ability-block-{{ $ability->id }}"
             data-save-url="{{ route('panel.setting.abilities.save', $ability->id) }}"
             data-disable-url="{{ route('panel.setting.abilities.disable', $ability->id) }}">

            <div class="form-row">
                @foreach($fields as $field)
                    <div class="form-group col-md-6">
                        <label>
                            {{ $field['label'] }}
                            @if(!empty($field['required']))<span class="text-danger">*</span>@endif
                        </label>

                        @if($field['type'] === 'textarea')
                            <textarea class="form-control ability-field" data-key="{{ $field['key'] }}" rows="3">{{ $config[$field['key']] ?? '' }}</textarea>

                        @elseif($field['type'] === 'boolean')
                            <div class="custom-control custom-switch mt-2">
                                <input type="checkbox" class="custom-control-input ability-field"
                                       data-key="{{ $field['key'] }}"
                                       id="af_{{ $ability->id }}_{{ $field['key'] }}"
                                       {{ !empty($config[$field['key']]) ? 'checked' : '' }}>
                                <label class="custom-control-label" for="af_{{ $ability->id }}_{{ $field['key'] }}"></label>
                            </div>

                        @elseif($field['type'] === 'select')
                            <select class="form-control ability-field" data-key="{{ $field['key'] }}">
                                @foreach(($field['options'] ?? []) as $opt)
                                    <option value="{{ $opt }}" {{ ($config[$field['key']] ?? '') == $opt ? 'selected' : '' }}>{{ $opt }}</option>
                                @endforeach
                            </select>

                        @else
                            <input type="{{ $field['type'] === 'password' ? 'password' : 'text' }}"
                                   class="form-control ability-field"
                                   data-key="{{ $field['key'] }}"
                                   value="{{ $field['type'] === 'password' ? '' : ($config[$field['key']] ?? '') }}"
                                   placeholder="{{ $field['type'] === 'password' ? '••••••••' : '' }}">
                        @endif
                    </div>
                @endforeach
            </div>

            <div class="mt-2">
                <button type="button" class="btn btn-primary mr-2" onclick="abilitySave({{ $ability->id }})">
                    {{ trans('admin/main.submit') }}
                </button>

                @if($enabled)
                    <button type="button" class="btn btn-outline-danger" onclick="abilityDisable({{ $ability->id }})">
                        {{ trans('panel.disable') ?? 'Disable' }}
                    </button>
                @endif
            </div>
        </div>

    @empty
        <div class="text-center text-gray-500 mt-30">{{ trans('admin/main.no_result') }}</div>
    @endforelse

</div>

<script>
    function abilityCsrfToken() {
        return document.querySelector('meta[name="csrf-token"]')?.content
            || document.querySelector('input[name="_token"]')?.value;
    }

    function abilityPost(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': abilityCsrfToken(),
            },
            body: JSON.stringify(data || {}),
        }).then(async (res) => {
            const body = await res.json().catch(() => ({}));
            if (!res.ok) {
                throw new Error(body.message || 'Request failed');
            }
            return body;
        });
    }

    function abilitySave(abilityId) {
        var block = document.getElementById('ability-block-' + abilityId);
        var config = {};

        block.querySelectorAll('.ability-field').forEach(function (el) {
            config[el.dataset.key] = (el.type === 'checkbox') ? el.checked : el.value;
        });

        abilityPost(block.dataset.saveUrl, { config: config })
            .then(function () { window.location.reload(); })
            .catch(function (err) { alert(err.message); });
    }

    function abilityDisable(abilityId) {
        var block = document.getElementById('ability-block-' + abilityId);

        abilityPost(block.dataset.disableUrl, {})
            .then(function () { window.location.reload(); })
            .catch(function (err) { alert(err.message); });
    }
</script>
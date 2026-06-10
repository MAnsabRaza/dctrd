@php
    $formId = $formId ?? 'checkoutOptionsForm';
    $formClass = $formClass ?? '';
    $saveUrl = $saveUrl ?? '#';
    $submitLabel = $submitLabel ?? trans('admin/main.save');
    $title = $title ?? trans('panel.checkout_options');
    $description = $description ?? trans('panel.checkout_options_hint');

    $moduleSettings = collect($moduleSettings ?? []);
    $totalModules = $moduleSettings->count();
    $enabledModules = $moduleSettings->where('enabled', true)->count();
    $requiredModules = $moduleSettings->where('is_required', true)->count();
    $optionalModules = max(0, $totalModules - $requiredModules);
@endphp

<div class="checkout-options-shell">
    <div class="checkout-options-hero">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between gap-3">
            <div>
                <div class="d-inline-flex align-items-center checkout-options-kicker mb-2">
                    <span class="checkout-options-kicker-dot"></span>
                    <span>{{ trans('panel.checkout_options') }}</span>
                </div>
                <h3 class="checkout-options-title mb-2">{{ $title }}</h3>
                <p class="checkout-options-description mb-0">{{ $description }}</p>
            </div>

            <div class="checkout-options-stats">
                <div class="checkout-options-stat">
                    <span class="checkout-options-stat-label">{{ trans('admin/main.active') }}</span>
                    <span class="checkout-options-stat-value">{{ $enabledModules }}</span>
                </div>
                <div class="checkout-options-stat">
                    <span class="checkout-options-stat-label">{{ trans('public.required') }}</span>
                    <span class="checkout-options-stat-value">{{ $requiredModules }}</span>
                </div>
                <div class="checkout-options-stat">
                    <span class="checkout-options-stat-label">{{ trans('admin/main.optional') }}</span>
                    <span class="checkout-options-stat-value">{{ $optionalModules }}</span>
                </div>
            </div>
        </div>
    </div>

    <form action="{{ $saveUrl }}" method="POST" id="{{ $formId }}" class="{{ trim($formClass) }}">
        @csrf

        <div class="checkout-options-card">
            @if($moduleSettings->isEmpty())
                <div class="alert alert-light border mb-0">
                    {{ trans('panel.checkout_options_empty') }}
                </div>
            @else
                <div class="checkout-options-grid">
                    @foreach($moduleSettings as $module)
                        @php
                            $moduleName = $module['name'];
                            $moduleId = 'checkout_module_' . preg_replace('/[^A-Za-z0-9_\\-]/', '_', $moduleName);
                            $helpText = $module['help_text'] ?? trans('panel.checkout_option_default_hint');
                        @endphp

                        <div class="checkout-option-item {{ !empty($module['is_required']) ? 'is-required' : '' }}">
                            <div class="d-flex align-items-start justify-content-between gap-3">
                                <div class="pr-2">
                                    <div class="d-flex flex-wrap align-items-center gap-2 mb-1">
                                        <h5 class="checkout-option-label mb-0">{{ $module['label'] }}</h5>

                                        @if(!empty($module['is_required']))
                                            <span class="badge badge-warning">{{ trans('public.required') }}</span>
                                        @endif

                                        @if(!empty($module['input_type']))
                                            <span class="badge badge-light text-muted">{{ $module['input_type'] }}</span>
                                        @endif
                                    </div>

                                    <p class="checkout-option-help mb-0">{{ $helpText }}</p>
                                </div>

                                <div class="checkout-option-switch">
                                    @if(!empty($module['is_required']))
                                        <input type="hidden" name="modules[{{ $moduleName }}]" value="1">
                                        <label class="custom-switch mb-0" for="{{ $moduleId }}">
                                            <input
                                                type="checkbox"
                                                class="custom-switch-input"
                                                id="{{ $moduleId }}"
                                                checked
                                                disabled
                                            >
                                            <span class="custom-switch-indicator"></span>
                                        </label>
                                    @else
                                        <input type="hidden" name="modules[{{ $moduleName }}]" value="0">
                                        <label class="custom-switch mb-0" for="{{ $moduleId }}">
                                            <input
                                                type="checkbox"
                                                name="modules[{{ $moduleName }}]"
                                                value="1"
                                                class="custom-switch-input"
                                                id="{{ $moduleId }}"
                                                {{ !empty($module['enabled']) ? 'checked' : '' }}
                                            >
                                            <span class="custom-switch-indicator"></span>
                                        </label>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="panel-bottom-bar d-flex align-items-center justify-content-end bg-white px-32 py-16 mt-3">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save mr-1"></i>{{ $submitLabel }}
            </button>
        </div>
    </form>
</div>

@push('styles_top')
    <style>
        .checkout-options-shell {
            background: linear-gradient(180deg, #f8fbff 0%, #ffffff 100%);
            border-radius: 22px;
            padding: 0;
        }

        .checkout-options-hero {
            border: 1px solid rgba(30, 64, 175, 0.08);
            border-radius: 22px;
            padding: 22px 24px;
            background: linear-gradient(135deg, #ffffff 0%, #f4f9ff 100%);
            box-shadow: 0 14px 40px rgba(15, 23, 42, 0.06);
        }

        .checkout-options-kicker {
            font-size: 12px;
            font-weight: 700;
            color: #2563eb;
            letter-spacing: 0.08em;
            text-transform: uppercase;
        }

        .checkout-options-kicker-dot {
            width: 8px;
            height: 8px;
            border-radius: 999px;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            margin-right: 8px;
        }

        .checkout-options-title {
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        .checkout-options-description {
            max-width: 760px;
            color: #64748b;
            line-height: 1.7;
        }

        .checkout-options-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(84px, 1fr));
            gap: 12px;
        }

        .checkout-options-stat {
            min-width: 96px;
            padding: 14px 16px;
            border-radius: 16px;
            background: #ffffff;
            border: 1px solid rgba(148, 163, 184, 0.18);
            box-shadow: 0 8px 24px rgba(15, 23, 42, 0.05);
            text-align: center;
        }

        .checkout-options-stat-label {
            display: block;
            font-size: 12px;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.06em;
        }

        .checkout-options-stat-value {
            display: block;
            margin-top: 4px;
            font-size: 24px;
            font-weight: 800;
            color: #0f172a;
        }

        .checkout-options-card {
            margin-top: 18px;
            padding: 18px;
            border-radius: 22px;
            border: 1px solid rgba(34, 197, 94, 0.2);
            background: #ffffff;
            box-shadow: 0 12px 30px rgba(15, 23, 42, 0.05);
        }

        .checkout-options-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 14px;
        }

        .checkout-option-item {
            position: relative;
            padding: 18px 18px 16px;
            border-radius: 18px;
            border: 1px solid #e2e8f0;
            background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
            transition: all 0.2s ease;
        }

        .checkout-option-item:hover {
            border-color: rgba(37, 99, 235, 0.35);
            box-shadow: 0 12px 28px rgba(37, 99, 235, 0.08);
            transform: translateY(-1px);
        }

        .checkout-option-item.is-required {
            border-color: rgba(245, 158, 11, 0.35);
            background: linear-gradient(180deg, #fffef8 0%, #ffffff 100%);
        }

        .checkout-option-label {
            font-size: 15px;
            font-weight: 800;
            color: #0f172a;
        }

        .checkout-option-help {
            font-size: 13px;
            color: #64748b;
            line-height: 1.65;
        }

        .checkout-option-switch {
            flex: 0 0 auto;
            margin-top: 2px;
        }

        .checkout-option-switch .custom-switch {
            padding-left: 0;
        }

        .checkout-option-switch .custom-switch-indicator {
            width: 46px;
            height: 24px;
        }

        .checkout-option-switch .custom-switch-indicator:before {
            width: 18px;
            height: 18px;
        }

        .checkout-option-item input[type="checkbox"][disabled] {
            cursor: not-allowed;
            opacity: 0.75;
        }

        @media (max-width: 767px) {
            .checkout-options-hero {
                padding: 18px;
            }

            .checkout-options-stats {
                width: 100%;
                grid-template-columns: repeat(3, minmax(0, 1fr));
            }

            .checkout-options-stat {
                padding: 12px 10px;
            }

            .checkout-options-stat-value {
                font-size: 20px;
            }
        }
    </style>
@endpush

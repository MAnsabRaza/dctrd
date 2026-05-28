@php
    $unitService = app(\App\Services\UnitConversionService::class);
    $authUser = $user ?? (auth()->check() ? auth()->user() : null);
    $isHeaderPicker = !empty($isHeaderPicker);
    $isDrawerPicker = !empty($isDrawerPicker);
    $isAutoSubmit = $isHeaderPicker || $isDrawerPicker;
    $formAction = $formAction ?? route('unitPreferences.update');
    $triggerClass = $triggerClass ?? 'bg-gray-100';
    $triggerIconClass = $triggerIconClass ?? 'text-gray-500';
    $menuClass = $menuClass ?? '';

    $unitTypes = [
        'length' => 'Length',
        'area' => 'Area',
        'mass' => 'Mass',
        'speed' => 'Speed',
        'temperature' => 'Temperature',
        'force' => 'Force',
        'volume' => 'Volume',
        'energy' => 'Energy, work, heat',
        'heat_flow_rate' => 'Heat flow rate',
    ];

    $unitPreferences = $unitPreferences ?? [];
    foreach ($unitTypes as $type => $title) {
        $unitPreferences[$type] = $unitPreferences[$type] ?? $unitService->getAvailableUnits($type);
    }

    $dateFormats = [
        'F j, Y' => ['label' => 'September 22, 2025', 'code' => 'F j, Y'],
        'Y-m-d' => ['label' => '2025-09-22', 'code' => 'Y-m-d'],
        'm/d/Y' => ['label' => '09/22/2025', 'code' => 'm/d/Y'],
        'd/m/Y' => ['label' => '22/09/2025', 'code' => 'd/m/Y'],
        'd.m.Y' => ['label' => '22.09.2025', 'code' => 'd.m.Y'],
    ];

    $timeFormats = [
        'g:i a' => ['label' => '5:25 pm', 'code' => 'g:i a'],
        'g:i A' => ['label' => '5:25 PM', 'code' => 'g:i A'],
        'H:i' => ['label' => '17:25', 'code' => 'H:i'],
    ];

    $weekDays = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];

    $selectedDateFormat = old('preferred_date_format', $authUser->preferred_date_format ?? session('preferred_date_format', request()->cookie('preferred_date_format', 'F j, Y')));
    $selectedTimeFormat = old('preferred_time_format', $authUser->preferred_time_format ?? session('preferred_time_format', request()->cookie('preferred_time_format', 'g:i a')));
    $selectedWeekStart = old('preferred_week_start', $authUser->preferred_week_start ?? session('preferred_week_start', request()->cookie('preferred_week_start', 'Monday')));
@endphp

@if($isHeaderPicker)
    <div class="js-unit-select {{ $dropdownClass ?? 'theme-header-2__dropdown' }} position-relative">
        <div class="size-32 d-flex-center {{ $triggerClass }} rounded-8 cursor-pointer" title="Unit Preferences">
            <x-iconsax-lin-setting-2 class="icons {{ $triggerIconClass }}" width="18px" height="18px"/>
        </div>

        <div class="{{ $menuClass }} js-common-unit-menu common-units-menu">
@endif

<div class="common-units-card bg-white">
    @if($isAutoSubmit)
        <form action="{{ $formAction }}" method="post" class="js-common-units-form">
            {{ csrf_field() }}

            <input type="hidden" name="previous_url" value="{{ url()->current() }}">
    @else
        <div class="js-common-units-form">
    @endif

        <h3 class="common-units-title text-center">Common units<br>and conversions</h3>

        <div class="common-unit-section">
            <h4>Date Format</h4>

            @foreach($dateFormats as $value => $item)
                <label class="common-unit-option">
                    <input type="radio" name="preferred_date_format" value="{{ $value }}" {{ $selectedDateFormat == $value ? 'checked' : '' }} {{ $isAutoSubmit ? 'data-auto-submit="true"' : '' }}>
                    <span>{{ $item['label'] }}</span>
                    <span class="common-unit-code">{{ $item['code'] }}</span>
                </label>
            @endforeach

            <label class="common-unit-option">
                <input type="radio" name="preferred_date_format" value="custom" {{ !array_key_exists($selectedDateFormat, $dateFormats) ? 'checked' : '' }}>
                <span>Custom:</span>
                <input type="text" name="preferred_custom_date_format" value="{{ !array_key_exists($selectedDateFormat, $dateFormats) ? $selectedDateFormat : 'F j, Y' }}" class="common-unit-custom-input">
            </label>

            <p class="common-unit-preview">Preview: September 22, 2025</p>
        </div>

        <div class="common-unit-section">
            <h4>Time Format</h4>

            @foreach($timeFormats as $value => $item)
                <label class="common-unit-option">
                    <input type="radio" name="preferred_time_format" value="{{ $value }}" {{ $selectedTimeFormat == $value ? 'checked' : '' }} {{ $isAutoSubmit ? 'data-auto-submit="true"' : '' }}>
                    <span>{{ $item['label'] }}</span>
                    <span class="common-unit-code">{{ $item['code'] }}</span>
                </label>
            @endforeach

            <label class="common-unit-option">
                <input type="radio" name="preferred_time_format" value="custom" {{ !array_key_exists($selectedTimeFormat, $timeFormats) ? 'checked' : '' }}>
                <span>Custom:</span>
                <input type="text" name="preferred_custom_time_format" value="{{ !array_key_exists($selectedTimeFormat, $timeFormats) ? $selectedTimeFormat : 'g:i a' }}" class="common-unit-custom-input">
            </label>

            <p class="common-unit-preview">Preview: 5:25 pm</p>
        </div>

        <div class="common-unit-section">
            <h4>Week Starts On</h4>
            <select name="preferred_week_start" class="common-unit-select" {{ $isAutoSubmit ? 'data-auto-submit="true"' : '' }}>
                @foreach($weekDays as $day)
                    <option value="{{ $day }}" {{ $selectedWeekStart == $day ? 'selected' : '' }}>{{ $day }}</option>
                @endforeach
            </select>
        </div>

        @foreach($unitTypes as $unitType => $title)
            @if(!empty($unitPreferences[$unitType]))
                @php
                    $fieldName = "preferred_{$unitType}_unit";
                    $selectedUnit = old($fieldName, $unitService->getPreferredUnit($unitType, $authUser));
                @endphp

                <div class="common-unit-section">
                    <h4>{{ $title }}</h4>

                    <div class="common-unit-grid">
                        @foreach($unitPreferences[$unitType] as $unit => $label)
                            <label class="common-unit-choice">
                                <input type="radio" name="{{ $fieldName }}" value="{{ $unit }}" {{ $selectedUnit == $unit ? 'checked' : '' }} {{ $isAutoSubmit ? 'data-auto-submit="true"' : '' }}>
                                <span>{{ config("units.short_labels.{$unit}", $unit) }}</span>
                            </label>
                        @endforeach
                    </div>

                    @error($fieldName)
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>
            @endif
        @endforeach
    @if($isAutoSubmit)
        </form>
    @else
        </div>
    @endif
</div>

@if($isHeaderPicker)
        </div>
    </div>
@endif

@once
    @push('styles_bottom')
        <style>
            .common-units-card {
                border: 1px solid #d9d9d9;
                border-radius: 8px;
                padding: 10px 14px 14px;
                color: #1f2933;
            }

            .common-units-menu {
                width: 278px;
                max-height: 70vh;
                overflow-y: auto;
                padding: 0;
            }

            .common-units-title {
                font-size: 20px;
                line-height: 1.05;
                font-weight: 800;
                margin: 0 0 14px;
            }

            .common-unit-section {
                margin-top: 18px;
            }

            .common-unit-section:first-of-type {
                margin-top: 0;
            }

            .common-unit-section h4 {
                font-size: 16px;
                line-height: 1.25;
                font-weight: 800;
                margin: 0 0 10px;
            }

            .common-unit-option,
            .common-unit-choice {
                display: flex;
                align-items: center;
                margin-bottom: 8px;
                cursor: pointer;
                color: #1f2933;
            }

            .common-unit-option {
                gap: 8px;
                font-size: 12px;
            }

            .common-unit-option input[type="radio"],
            .common-unit-choice input[type="radio"] {
                width: 13px;
                height: 13px;
                margin: 0;
            }

            .common-unit-code,
            .common-unit-custom-input {
                margin-left: auto;
                min-width: 46px;
                border: 1px solid #cfcfcf;
                border-radius: 2px;
                background: #f2f2f2;
                padding: 2px 5px;
                font-size: 11px;
                line-height: 1.2;
                color: #202124;
            }

            .common-unit-custom-input {
                background: #fff;
                max-width: 58px;
            }

            .common-unit-preview {
                font-size: 11px;
                color: #4b5563;
                margin: 6px 0 0 22px;
            }

            .common-unit-select {
                display: block;
                width: 80px;
                margin-left: auto;
                border: 1px solid #cfcfcf;
                border-radius: 2px;
                padding: 4px 6px;
                font-size: 12px;
                background: #fff;
            }

            .common-unit-grid {
                display: grid;
                grid-template-columns: repeat(3, minmax(0, 1fr));
                column-gap: 14px;
                row-gap: 6px;
            }

            .common-unit-choice {
                gap: 7px;
                min-height: 24px;
                margin-bottom: 0;
                font-size: 15px;
                font-weight: 700;
                white-space: nowrap;
            }

            @media (max-width: 575px) {
                .common-units-menu {
                    width: min(278px, calc(100vw - 32px));
                }
            }
        </style>
    @endpush

    @push('scripts_bottom')
        <script>
            (function ($) {
                "use strict";

                $('body').on('change', '.js-common-units-form [data-auto-submit="true"]', function () {
                    $(this).closest('form').trigger('submit');
                });

                $('body').on('change', '.js-common-units-form input[value="custom"]', function () {
                    $(this).closest('.common-unit-option').find('.common-unit-custom-input').trigger('focus');
                });
            })(jQuery);
        </script>
    @endpush
@endonce

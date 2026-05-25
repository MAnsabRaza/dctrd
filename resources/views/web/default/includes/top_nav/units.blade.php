@php
    $unitService = app(\App\Services\UnitConversionService::class);
    $unitPreferences = [];

    if ($unitService->isEnabled()) {
        foreach (['length', 'mass', 'area'] as $unitType) {
            $unitPreferences[$unitType] = $unitService->getAvailableUnits($unitType);
        }
    }

    $authUser = auth()->check() ? auth()->user() : null;
    $selectedUnits = [];

    foreach (['length', 'mass', 'area'] as $unitType) {
        $selectedUnits[$unitType] = $unitService->getPreferredUnit($unitType, $authUser);
    }

    $unitTitles = [
        'length' => 'Length Unit',
        'mass' => 'Mass Unit',
        'area' => 'Area Unit',
    ];
@endphp

@if(!empty($unitPreferences))
    <div class="js-unit-select custom-dropdown position-relative mr-10">
        <form action="{{ route('unitPreferences.update') }}" method="post">
            {{ csrf_field() }}

            @foreach(['length', 'mass', 'area'] as $unitType)
                <input type="hidden" name="preferred_{{ $unitType }}_unit" value="{{ $selectedUnits[$unitType] }}">
            @endforeach

            @if(!empty($previousUrl))
                <input type="hidden" name="previous_url" value="{{ $previousUrl }}">
            @endif

            <div class="custom-dropdown-toggle top-nav-icon-dropdown d-flex align-items-center justify-content-center cursor-pointer" title="Unit Preferences">
                <i data-feather="settings" width="16" height="16"></i>
            </div>
        </form>

        <div class="custom-dropdown-body unit-dropdown-body py-10">
            @foreach($unitPreferences as $unitType => $units)
                @if(!empty($units))
                    <div class="px-15 pt-5 pb-5 font-12 text-gray">{{ $unitTitles[$unitType] ?? ucfirst($unitType) }}</div>

                    @foreach($units as $unit => $label)
                        <div class="js-unit-dropdown-item custom-dropdown-body__item cursor-pointer {{ ($selectedUnits[$unitType] == $unit) ? 'active' : '' }}"
                             data-name="preferred_{{ $unitType }}_unit"
                             data-value="{{ $unit }}">
                            <div class="d-flex align-items-center justify-content-between w-100 px-15 py-5 text-gray bg-transparent">
                                <span class="font-14">{{ $label }}</span>
                                <span class="ml-20 font-12 text-gray">{{ config("units.short_labels.{$unit}", $unit) }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
@endif

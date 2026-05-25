@php
    $unitService = app(\App\Services\UnitConversionService::class);
    $unitPreferences = [];

    foreach (['length', 'mass', 'area'] as $unitType) {
        $unitPreferences[$unitType] = $unitService->getAvailableUnits($unitType);
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
    <div class="js-unit-select theme-header-2__dropdown position-relative">
        <form action="{{ route('unitPreferences.update') }}" method="post">
            {{ csrf_field() }}

            @foreach(['length', 'mass', 'area'] as $unitType)
                <input type="hidden" name="preferred_{{ $unitType }}_unit" value="{{ $selectedUnits[$unitType] }}">
            @endforeach

            <div class="size-32 d-flex-center bg-gray-100 rounded-8 cursor-pointer" title="Unit Preferences">
                <x-iconsax-lin-setting-2 class="icons text-gray-500" width="18px" height="18px"/>
            </div>
        </form>

        <div class="header-2-dropdown-menu py-8">
            @foreach($unitPreferences as $unitType => $units)
                @if(!empty($units))
                    <div class="py-8 px-16 font-12 text-gray-500">{{ $unitTitles[$unitType] ?? ucfirst($unitType) }}</div>

                    @foreach($units as $unit => $label)
                        <div class="js-unit-dropdown-item header-2-dropdown-menu__item cursor-pointer {{ ($selectedUnits[$unitType] == $unit) ? 'active' : '' }}"
                             data-name="preferred_{{ $unitType }}_unit"
                             data-value="{{ $unit }}">
                            <div class="d-flex align-items-center justify-content-between w-100 px-16 py-8 bg-transparent">
                                <span class="text-gray-500 text-dark">{{ $label }}</span>
                                <span class="font-12 text-gray-500">{{ config("units.short_labels.{$unit}", $unit) }}</span>
                            </div>
                        </div>
                    @endforeach
                @endif
            @endforeach
        </div>
    </div>
@endif

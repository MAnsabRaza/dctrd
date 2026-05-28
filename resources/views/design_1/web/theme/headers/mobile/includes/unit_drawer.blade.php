<div class="theme-header-mobile__drawer mobile-unit-drawer">
    <div class="theme-header-mobile__drawer-back-drop"></div>

    <div class="theme-header-mobile__drawer-body py-16">
        <div class="d-flex align-items-center justify-content-between px-16 mb-12">
            <h4 class="font-22">Units</h4>

            <div class="js-close-header-drawer d-flex-center size-48 rounded-12 border-gray-300">
                <x-iconsax-lin-add class="close-icon text-gray-500" width="24px" height="24px"/>
            </div>
        </div>

        <div class="px-16">
            @include('design_1.common.unit_preferences', ['isDrawerPicker' => true])
        </div>
    </div>
</div>

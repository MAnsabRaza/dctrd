<a href="/panel/setting/step/basic_information" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "basic_information") ? 'active' : '' }}">
    <div class="size-20">
        <x-iconsax-lin-note class="icons" width="20px" height="20px"/>
    </div>
    <span class="ml-4">{{ trans('public.basic_information') }}</span>
</a>

<a href="/panel/setting/step/extra_information" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "extra_information") ? 'active' : '' }}">
    <div class="size-20">
        <x-iconsax-lin-note-add class="icons" width="20px" height="20px"/>
    </div>
    <span class="ml-4">{{ trans('public.extra_information') }}</span>
</a>

<a href="/panel/setting/step/financial" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "financial") ? 'active' : '' }}">
    <div class="size-20">
        <x-iconsax-lin-receipt-search class="icons" width="20px" height="20px"/>
    </div>
    <span class="ml-4">{{ trans('public.identity_and_financial') }}</span>
</a>

@if($user->isTeacher() or $user->isOrganization())
    <a href="/panel/setting/step/checkout_options" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "checkout_options") ? 'active' : '' }}">
        <div class="size-20">
            <x-iconsax-lin-setting-2 class="icons" width="20px" height="20px"/>
        </div>
        <span class="ml-4">{{ trans('panel.checkout_options') }}</span>
    </a>
@endif

<a href="/panel/setting/step/external_connections" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "external_connections") ? 'active' : '' }}">
    <div class="size-20">
        <x-iconsax-lin-note class="icons" width="20px" height="20px"/>
    </div>
    <span class="ml-4">{{ trans('panel.external_connections') }}</span>
</a>
 <a href="/panel/setting/step/abilities" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "abilities") ? 'active' : '' }}">
        <div class="size-20">
            <x-iconsax-lin-flash class="icons" width="20px" height="20px"/>
        </div>
        <span class="ml-4">{{ trans('panel.abilities') ?? 'Abilities' }}</span>
    </a>


<a href="/panel/setting/step/images" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "images") ? 'active' : '' }}">
    <div class="size-20">
        <x-iconsax-lin-gallery class="icons" width="20px" height="20px"/>
    </div>
    <span class="ml-4">{{ trans('public.images') }}</span>
</a>

<a href="/panel/setting/step/about" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "about") ? 'active' : '' }}">
    <div class="size-20">
        <x-iconsax-lin-profile class="icons" width="20px" height="20px"/>
    </div>
    <span class="ml-4">{{ trans('public.about') }}</span>
</a>

{{--@if(!$user->isUser())
    <a href="/panel/setting/step/zoom" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "zoom") ? 'active' : '' }}">
        <div class="size-20">
            <x-iconsax-lin-video-octagon class="icons" width="20px" height="20px"/>
        </div>
        <span class="ml-4">{{ trans('update.zoom') }}</span>
    </a>
@endif--}}

<a href="/panel/setting/step/login_history" class="navbar-item navbar-item-h-52 d-inline-flex-center cursor-pointer {{ ($currentStep == "login_history") ? 'active' : '' }}">
    <div class="size-20">
        <x-iconsax-lin-shield-security class="icons" width="20px" height="20px"/>
    </div>
    <span class="ml-4">{{ trans('update.login_history') }}</span>
</a>

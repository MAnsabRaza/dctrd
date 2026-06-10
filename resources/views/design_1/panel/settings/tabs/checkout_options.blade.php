<div class="custom-tabs-content active">
    @include('partials.checkout_modules._settings_form', [
        'moduleSettings' => $moduleSettings ?? [],
        'title' => trans('panel.checkout_options'),
        'description' => trans('panel.checkout_options_hint'),
        'wrapForm' => false,
    ])
</div>

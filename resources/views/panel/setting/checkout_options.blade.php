@extends(getTemplate() . '.panel.layouts.panel_layout')

@section('content')
    <div class="container-fluid py-4">
        @include('partials.checkout_modules._settings_form', [
            'moduleSettings' => $moduleSettings ?? [],
            'saveUrl' => route('panel.checkout-settings.save'),
            'formId' => 'panelCheckoutOptionsForm',
            'formClass' => 'checkout-options-form ajax-form',
            'title' => $pageTitle ?? trans('panel.checkout_options'),
            'description' => trans('panel.checkout_options_hint'),
            'submitLabel' => trans('admin/main.save'),
        ])
    </div>
@endsection

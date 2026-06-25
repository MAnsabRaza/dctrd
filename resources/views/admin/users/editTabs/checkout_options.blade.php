<div class="tab-pane mt-3 fade {{ (request()->get('tab') == 'checkoutOptions') ? 'active show' : '' }}" id="checkoutOptions" role="tabpanel" aria-labelledby="checkoutOptions-tab">
    <div class="row">
        <div class="col-12">
            @include('partials.checkout_modules._settings_form', [
                'moduleSettings' => $moduleSettings ?? [],
                'saveUrl' => getAdminPanelUrl() . "/users/{$user->id}/checkout-options-update",
                'formId' => 'adminCheckoutOptionsForm',
                'formClass' => '',
                'title' => trans('panel.checkout_options'),
                'description' => trans('panel.checkout_options_hint'),
                'submitLabel' => trans('admin/main.submit'),
                'wrapForm' => true,
            ])
        </div>
    </div>
</div>
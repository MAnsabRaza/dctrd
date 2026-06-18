@php
    if (!empty($itemValue) and !is_array($itemValue)) {
        $itemValue = json_decode($itemValue, true);
    }

    if (empty($currency)) {
        $currency = currencySign(getDefaultCurrency());
    }

    $bookingCommissionSections = [
        [
            'title' => 'commission_booking',
            'help' => 'commission_booking_help',
            'type_key' => 'booking_commission_type',
            'value_key' => 'booking_commission_value',
            'default' => 30,
        ],
        [
            'title' => 'commission_real_estate',
            'help' => 'commission_real_estate_help',
            'type_key' => 'commission_real_estate_type',
            'value_key' => 'commission_real_estate_value',
            'default' => 20,
        ],
        [
            'title' => 'commission_lifestyle',
            'help' => 'commission_lifestyle_help',
            'type_key' => 'commission_lifestyle_type',
            'value_key' => 'commission_lifestyle_value',
            'default' => 20,
        ],
        [
            'title' => 'commission_healthcare',
            'help' => 'commission_healthcare_help',
            'type_key' => 'commission_healthcare_type',
            'value_key' => 'commission_healthcare_value',
            'default' => 20,
        ],
        [
            'title' => 'commission_automotive',
            'help' => 'commission_automotive_help',
            'type_key' => 'commission_automotive_type',
            'value_key' => 'commission_automotive_value',
            'default' => 20,
        ],
        [
            'title' => 'commission_tutoring',
            'help' => 'commission_tutoring_help',
            'type_key' => 'commission_tutoring_type',
            'value_key' => 'commission_tutoring_value',
            'default' => 20,
        ],
        [
            'title' => 'commission_consulting',
            'help' => 'commission_consulting_help',
            'type_key' => 'commission_consulting_type',
            'value_key' => 'commission_consulting_value',
            'default' => 30,
        ],
    ];
@endphp


<div class="tab-pane mt-3 fade @if(request()->get('tab') == "commissions") active show @endif" id="commissions" role="tabpanel" aria-labelledby="commissions-tab">
    <div class="row">
        <div class="col-12 col-md-6">
            <form action="{{ getAdminPanelUrl() }}/settings/main" method="post">
                {{ csrf_field() }}
                <input type="hidden" name="page" value="financial">
                <input type="hidden" name="name" value="{{ \App\Models\Setting::$commissionSettingsName }}">


                @foreach(\App\Models\UserCommission::$sources as $commissionSource)
                    <div class="form-group">
                        <label class="mb-0">{{ trans("update.{$commissionSource}_commission") }}</label>

                        <div class="row">
                            <div class="col-6">
                                <label class="">{{ trans("admin/main.type") }}</label>
                                <select name="value[{{ $commissionSource }}][type]" class="js-commission-type-input form-control" data-currency="{{ $currency }}">
                                    <option value="percent" {{ (!empty($itemValue) and !empty($itemValue[$commissionSource]) and !empty($itemValue[$commissionSource]['type']) and $itemValue[$commissionSource]['type'] == "percent") ? 'selected' : '' }}>{{ trans('update.percent') }}</option>
                                    <option value="fixed_amount" {{ (!empty($itemValue) and !empty($itemValue[$commissionSource]) and !empty($itemValue[$commissionSource]['type']) and $itemValue[$commissionSource]['type'] == "fixed_amount") ? 'selected' : '' }}>{{ trans('update.fixed_amount') }}</option>
                                </select>
                            </div>

                            <div class="col-6">
                                <div class="">
                                    <label class="">
                                        {{ trans("update.value") }}

                                        <span class="ml-1 js-commission-value-span">@if(!empty($itemValue) and !empty($itemValue[$commissionSource]) and !empty($itemValue[$commissionSource]['type'])) ({{ ($itemValue[$commissionSource]['type'] == "percent") ? '%' : $currency }}) @else (%)  @endif</span>
                                    </label>

                                    <input type="number" name="value[{{ $commissionSource }}][value]" value="{{ (!empty($itemValue) and !empty($itemValue[$commissionSource]) and !empty($itemValue[$commissionSource]['value'])) ? $itemValue[$commissionSource]['value'] : '' }}" class="js-commission-value-input form-control text-center" />
                                </div>
                            </div>
                        </div>

                        <div class="text-gray-500 text-small mt-1">{{ trans("update.{$commissionSource}_commission_hint") }}</div>
                    </div>
                @endforeach

                @foreach($bookingCommissionSections as $section)
                    @php
                        $selectedType = (!empty($itemValue) and !empty($itemValue[$section['type_key']])) ? $itemValue[$section['type_key']] : 'percent';
                        $selectedValue = (!empty($itemValue) and isset($itemValue[$section['value_key']])) ? $itemValue[$section['value_key']] : $section['default'];
                    @endphp

                    <div class="form-group">
                        <label class="mb-0">{{ trans("financial.{$section['title']}") }}</label>

                        <div class="row">
                            <div class="col-12 col-md-6">
                                <label class="">{{ trans("admin/main.type") }}</label>
                                <select name="value[{{ $section['type_key'] }}]" class="js-commission-type-input form-control" data-currency="{{ $currency }}">
                                    <option value="percent" {{ ($selectedType == "percent") ? 'selected' : '' }}>{{ trans('update.percent') }}</option>
                                    <option value="fixed_amount" {{ ($selectedType == "fixed_amount") ? 'selected' : '' }}>{{ trans('update.fixed_amount') }}</option>
                                </select>
                            </div>

                            <div class="col-12 col-md-6">
                                <div class="">
                                    <label class="">
                                        {{ trans("update.value") }}

                                        <span class="ml-1 js-commission-value-span">({{ ($selectedType == "percent") ? '%' : $currency }})</span>
                                    </label>

                                    <input type="number" name="value[{{ $section['value_key'] }}]" value="{{ $selectedValue }}" class="js-commission-value-input form-control text-center" min="0" max="100" step="0.01" placeholder="{{ $section['default'] }}" />
                                </div>
                            </div>
                        </div>

                        <div class="text-gray-500 text-small mt-1">{{ trans("financial.{$section['help']}") }}</div>
                    </div>
                @endforeach

                <button type="submit" class="btn btn-success">{{ trans('admin/main.save_change') }}</button>
            </form>
        </div>
    </div>
</div>

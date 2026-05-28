@php
    $checkoutUseGlobal = old('booking_checkout_use_global_availability', $user->booking_checkout_use_global_availability ?? '1');
    $checkoutLimited = old('booking_checkout_limited_availability', $user->booking_checkout_limited_availability ?? '0');
    $checkoutRanges = old('booking_checkout_ranges', json_decode($user->booking_checkout_ranges ?? '[]', true) ?: []);

    $useAllAssets = old('booking_use_all_assets', $user->booking_use_all_assets ?? '1');
    $assets = old('booking_assets', json_decode($user->booking_assets ?? '[]', true) ?: []);

    $useDefaultAssetAvailability = old('booking_use_default_asset_availability', $user->booking_use_default_asset_availability ?? '1');
    $assetAvailability = old('booking_asset_availability', json_decode($user->booking_asset_availability ?? '[]', true) ?: []);

    if (empty($checkoutRanges)) {
        $checkoutRanges = [['range_type' => '', 'from' => '', 'to' => '', 'available' => '1']];
    }

    if (empty($assets)) {
        $assets = [['name' => '', 'quantity' => '']];
    }

    if (empty($assetAvailability)) {
        $assetAvailability = [['asset' => '', 'range_type' => '', 'from' => '', 'to' => '', 'available' => '1']];
    }
@endphp

<div class="tab-pane mt-3 fade {{ (request()->get('tab') == 'additionalForms') ? 'active show' : '' }}" id="additional_forms" role="tabpanel" aria-labelledby="additional_forms-tab">
    <form action="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/booking-options-update" method="post">
        {{ csrf_field() }}

        <div class="admin-user-additional-forms">
            <div class="mb-3">
                <h5 class="font-16 mb-1">Check Out Options</h5>
                <p class="text-muted font-12 mb-0">Define booking checkout rules, asset use, and availability settings for this user.</p>
            </div>

            <div class="form-group custom-switches-stacked mb-2">
                <label class="custom-switch pl-0">
                    <input type="hidden" name="booking_checkout_use_global_availability" value="0">
                    <input type="checkbox" name="booking_checkout_use_global_availability" value="1" class="custom-switch-input" id="bookingGlobalAvailabilitySwitch" {{ $checkoutUseGlobal ? 'checked' : '' }}>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description mb-0 cursor-pointer">Global Availability</span>
                </label>
                <div class="text-gray-500 font-12">Use system/global availability rules. These rules can still be overridden on the product/booking level.</div>
            </div>

            <div class="form-group custom-switches-stacked mb-2">
                <label class="custom-switch pl-0">
                    <input type="hidden" name="booking_checkout_limited_availability" value="0">
                    <input type="checkbox" name="booking_checkout_limited_availability" value="1" class="custom-switch-input" id="bookingLimitedAvailabilitySwitch" {{ $checkoutLimited ? 'checked' : '' }}>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description mb-0 cursor-pointer">Limit the date to specified availability</span>
                </label>
                <div class="text-gray-500 font-12">Enable this to require checkout availability within configured date/time ranges.</div>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered mb-2 js-repeatable-table" data-row-template="checkoutRangeRowTemplate">
                    <thead>
                    <tr>
                        <th>Range Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Available</th>
                        <th width="48"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($checkoutRanges as $index => $range)
                        <tr>
                            <td>
                                <select name="booking_checkout_ranges[{{ $index }}][range_type]" class="form-control">
                                    @foreach(['date' => 'Date', 'time' => 'Time', 'date_time' => 'Date & Time'] as $value => $label)
                                        <option value="{{ $value }}" {{ ($range['range_type'] ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="booking_checkout_ranges[{{ $index }}][from]" value="{{ $range['from'] ?? '' }}" class="form-control"></td>
                            <td><input type="text" name="booking_checkout_ranges[{{ $index }}][to]" value="{{ $range['to'] ?? '' }}" class="form-control"></td>
                            <td>
                                <select name="booking_checkout_ranges[{{ $index }}][available]" class="form-control">
                                    <option value="1" {{ ($range['available'] ?? '1') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ ($range['available'] ?? '1') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger js-remove-repeatable-row">&times;</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-primary js-add-repeatable-row">Add</button>
            </div>

            <hr>

            <h5 class="font-16 mb-1">Asset</h5>
            <p class="text-muted font-12">Assets are physical resources which can be attached to a product/booking, such as rooms, cars, equipment, or seats.</p>

            <div class="form-group custom-switches-stacked mb-2">
                <label class="custom-switch pl-0">
                    <input type="hidden" name="booking_use_all_assets" value="0">
                    <input type="checkbox" name="booking_use_all_assets" value="1" class="custom-switch-input" id="bookingAllAssetsSwitch" {{ $useAllAssets ? 'checked' : '' }}>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description mb-0 cursor-pointer">Use all assets created in site?</span>
                </label>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered mb-2 js-repeatable-table" data-row-template="assetRowTemplate">
                    <thead>
                    <tr>
                        <th>Asset Name</th>
                        <th>Quantity</th>
                        <th width="48"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($assets as $index => $asset)
                        <tr>
                            <td><input type="text" name="booking_assets[{{ $index }}][name]" value="{{ $asset['name'] ?? '' }}" class="form-control"></td>
                            <td><input type="number" name="booking_assets[{{ $index }}][quantity]" value="{{ $asset['quantity'] ?? '' }}" min="0" class="form-control"></td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger js-remove-repeatable-row">&times;</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-primary js-add-repeatable-row">Add</button>
            </div>

            <hr>

            <h5 class="font-16 mb-1">Asset Availability</h5>
            <p class="text-muted font-12">Define availability for each asset.</p>

            <div class="form-group custom-switches-stacked mb-2">
                <label class="custom-switch pl-0">
                    <input type="hidden" name="booking_use_default_asset_availability" value="0">
                    <input type="checkbox" name="booking_use_default_asset_availability" value="1" class="custom-switch-input" id="bookingDefaultAssetAvailabilitySwitch" {{ $useDefaultAssetAvailability ? 'checked' : '' }}>
                    <span class="custom-switch-indicator"></span>
                    <span class="custom-switch-description mb-0 cursor-pointer">Use all assets availability by default</span>
                </label>
            </div>

            <div class="table-responsive mt-3">
                <table class="table table-bordered mb-2 js-repeatable-table" data-row-template="assetAvailabilityRowTemplate">
                    <thead>
                    <tr>
                        <th>Asset</th>
                        <th>Range Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th>Available</th>
                        <th width="48"></th>
                    </tr>
                    </thead>
                    <tbody>
                    @foreach($assetAvailability as $index => $range)
                        <tr>
                            <td><input type="text" name="booking_asset_availability[{{ $index }}][asset]" value="{{ $range['asset'] ?? '' }}" class="form-control"></td>
                            <td>
                                <select name="booking_asset_availability[{{ $index }}][range_type]" class="form-control">
                                    @foreach(['date' => 'Date', 'time' => 'Time', 'date_time' => 'Date & Time'] as $value => $label)
                                        <option value="{{ $value }}" {{ ($range['range_type'] ?? '') == $value ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td><input type="text" name="booking_asset_availability[{{ $index }}][from]" value="{{ $range['from'] ?? '' }}" class="form-control"></td>
                            <td><input type="text" name="booking_asset_availability[{{ $index }}][to]" value="{{ $range['to'] ?? '' }}" class="form-control"></td>
                            <td>
                                <select name="booking_asset_availability[{{ $index }}][available]" class="form-control">
                                    <option value="1" {{ ($range['available'] ?? '1') == '1' ? 'selected' : '' }}>Yes</option>
                                    <option value="0" {{ ($range['available'] ?? '1') == '0' ? 'selected' : '' }}>No</option>
                                </select>
                            </td>
                            <td class="text-center"><button type="button" class="btn btn-sm btn-danger js-remove-repeatable-row">&times;</button></td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
                <button type="button" class="btn btn-sm btn-primary js-add-repeatable-row">Add</button>
            </div>

            <div class="mt-4">
                <button class="btn btn-primary">{{ trans('admin/main.submit') }}</button>
            </div>
        </div>
    </form>
</div>

<template id="checkoutRangeRowTemplate">
    <tr>
        <td><select name="booking_checkout_ranges[__INDEX__][range_type]" class="form-control"><option value="date">Date</option><option value="time">Time</option><option value="date_time">Date & Time</option></select></td>
        <td><input type="text" name="booking_checkout_ranges[__INDEX__][from]" class="form-control"></td>
        <td><input type="text" name="booking_checkout_ranges[__INDEX__][to]" class="form-control"></td>
        <td><select name="booking_checkout_ranges[__INDEX__][available]" class="form-control"><option value="1">Yes</option><option value="0">No</option></select></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-danger js-remove-repeatable-row">&times;</button></td>
    </tr>
</template>

<template id="assetRowTemplate">
    <tr>
        <td><input type="text" name="booking_assets[__INDEX__][name]" class="form-control"></td>
        <td><input type="number" name="booking_assets[__INDEX__][quantity]" min="0" class="form-control"></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-danger js-remove-repeatable-row">&times;</button></td>
    </tr>
</template>

<template id="assetAvailabilityRowTemplate">
    <tr>
        <td><input type="text" name="booking_asset_availability[__INDEX__][asset]" class="form-control"></td>
        <td><select name="booking_asset_availability[__INDEX__][range_type]" class="form-control"><option value="date">Date</option><option value="time">Time</option><option value="date_time">Date & Time</option></select></td>
        <td><input type="text" name="booking_asset_availability[__INDEX__][from]" class="form-control"></td>
        <td><input type="text" name="booking_asset_availability[__INDEX__][to]" class="form-control"></td>
        <td><select name="booking_asset_availability[__INDEX__][available]" class="form-control"><option value="1">Yes</option><option value="0">No</option></select></td>
        <td class="text-center"><button type="button" class="btn btn-sm btn-danger js-remove-repeatable-row">&times;</button></td>
    </tr>
</template>

@push('styles_bottom')
    <style>
        .admin-user-additional-forms {
            border: 2px solid #28a745;
            border-radius: 8px;
            padding: 16px;
            background: #fff;
        }

        .admin-user-additional-forms .table th,
        .admin-user-additional-forms .table td {
            padding: 8px;
            vertical-align: middle;
        }

        .admin-user-additional-forms .form-control {
            height: 36px;
        }
    </style>
@endpush

@push('scripts_bottom')
    <script>
        (function ($) {
            "use strict";

            $('body').on('click', '.js-add-repeatable-row', function () {
                var $table = $(this).closest('.table-responsive').find('.js-repeatable-table');
                var templateId = $table.data('row-template');
                var template = $('#' + templateId).html();
                var index = $table.find('tbody tr').length + Date.now();

                $table.find('tbody').append(template.replace(/__INDEX__/g, index));
            });

            $('body').on('click', '.js-remove-repeatable-row', function () {
                var $tbody = $(this).closest('tbody');

                if ($tbody.find('tr').length > 1) {
                    $(this).closest('tr').remove();
                }
            });
        })(jQuery);
    </script>
@endpush

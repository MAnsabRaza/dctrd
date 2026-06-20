@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active"><a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a></div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12">
                    <div class="card">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-12 col-md-6 col-lg-4">
                                    <form action="{{ getAdminPanelUrl() }}/booking/settings" method="post">
                                        {{ csrf_field() }}

                                        <div class="form-group custom-switches-stacked">
                                            <label class="custom-switch pl-0 d-flex align-items-center">
                                                <input type="hidden" name="value[status]" value="0">
                                                <input type="checkbox" name="value[status]" id="bookingStatusSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['status']) and $itemValue['status']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                <span class="custom-switch-indicator"></span>
                                                <label class="custom-switch-description mb-0 cursor-pointer" for="bookingStatusSwitch">{{ trans('admin/main.active') }}</label>
                                            </label>
                                            <div class="text-gray-500 text-small">Enable this option to activate the Booking feature.</div>
                                        </div>

                                        <div class="form-group">
                                            <label>Real Estate & Home Commission</label>
                                            <input type="number" name="value[rental_commission]" value="{{ (!empty($itemValue) and isset($itemValue['rental_commission'])) ? $itemValue['rental_commission'] : old('rental_commission') }}" class="form-control text-center @error('value.rental_commission') is-invalid @enderror"/>
                                            @error('value.rental_commission')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="text-gray-500 text-small mt-1">Commission rate for real estate and home services.</div>
                                        </div>

                                        <div class="form-group">
                                            <label>Lifestyle & Events Commission</label>
                                            <input type="number" name="value[lifestyle_events_commission]" value="{{ (!empty($itemValue) and isset($itemValue['lifestyle_events_commission'])) ? $itemValue['lifestyle_events_commission'] : old('lifestyle_events_commission') }}" class="form-control text-center @error('value.lifestyle_events_commission') is-invalid @enderror"/>
                                            @error('value.lifestyle_events_commission')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <div class="text-gray-500 text-small mt-1">Commission rate for lifestyle and events bookings.</div>
                                        </div>

                                        <div class="form-group custom-switches-stacked">
                                            <label class="custom-switch pl-0 d-flex align-items-center">
                                                <input type="hidden" name="value[activate_automation]" value="0">
                                                <input type="checkbox" name="value[activate_automation]" id="activateAutomationSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['activate_automation']) and $itemValue['activate_automation']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                <span class="custom-switch-indicator"></span>
                                                <label class="custom-switch-description mb-0 cursor-pointer" for="activateAutomationSwitch">Enable Automation & Technical Services</label>
                                            </label>
                                            <div class="text-gray-500 text-small">Allow sellers to submit automation and technical booking services.</div>
                                        </div>

                                        <div class="form-group custom-switches-stacked">
                                            <label class="custom-switch pl-0 d-flex align-items-center">
                                                <input type="hidden" name="value[enable_tutoring]" value="0">
                                                <input type="checkbox" name="value[enable_tutoring]" id="enableTutoringSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['enable_tutoring']) and $itemValue['enable_tutoring']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                <span class="custom-switch-indicator"></span>
                                                <label class="custom-switch-description mb-0 cursor-pointer" for="enableTutoringSwitch">Enable Tutoring & Trainers Submission</label>
                                            </label>
                                            <div class="text-gray-500 text-small">Allow vendors to submit tutoring and trainer booking services.</div>
                                        </div>

                                        <div class="form-group custom-switches-stacked">
                                            <label class="custom-switch pl-0 d-flex align-items-center">
                                                <input type="hidden" name="value[enable_counselling]" value="0">
                                                <input type="checkbox" name="value[enable_counselling]" id="enableCounsellingSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['enable_counselling']) and $itemValue['enable_counselling']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                <span class="custom-switch-indicator"></span>
                                                <label class="custom-switch-description mb-0 cursor-pointer" for="enableCounsellingSwitch">Enable Counselling, Legal & Finance Submission</label>
                                            </label>
                                            <div class="text-gray-500 text-small">Allow vendors to submit counselling, legal and finance bookings.</div>
                                        </div>

                                        <div class="form-group custom-switches-stacked">
                                            <label class="custom-switch pl-0 d-flex align-items-center">
                                                <input type="hidden" name="value[optional_address]" value="0">
                                                <input type="checkbox" name="value[optional_address]" id="optionalAddressSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['optional_address']) and $itemValue['optional_address']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                <span class="custom-switch-indicator"></span>
                                                <label class="custom-switch-description mb-0 cursor-pointer" for="optionalAddressSwitch">Optional Address</label>
                                            </label>
                                            <div class="text-gray-500 text-small">Make address selection optional during checkout.</div>
                                        </div>

                                        <div class="form-group custom-switches-stacked">
                                            <label class="custom-switch pl-0 d-flex align-items-center">
                                                <input type="hidden" name="value[activate_comments]" value="0">
                                                <input type="checkbox" name="value[activate_comments]" id="activateCommentsSwitch" value="1" {{ (!empty($itemValue) and !empty($itemValue['activate_comments']) and $itemValue['activate_comments']) ? 'checked="checked"' : '' }} class="custom-switch-input"/>
                                                <span class="custom-switch-indicator"></span>
                                                <label class="custom-switch-description mb-0 cursor-pointer" for="activateCommentsSwitch">Enable Booking Comments</label>
                                            </label>
                                            <div class="text-gray-500 text-small">Allow users to leave comments on booking pages.</div>
                                        </div>

                                        <div class="text-right">
                                            <button type="submit" class="btn btn-primary mt-1">{{ trans('admin/main.submit') }}</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection

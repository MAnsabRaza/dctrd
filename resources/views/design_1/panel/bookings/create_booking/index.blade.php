{{--
    resources/views/design_1/panel/bookings/create_booking/index.blade.php

    Page-reload step flow, same pattern as create_product/index.blade.php:
    - Step 1 posts to panel.bookings.store (no booking exists yet)
    - Steps 2-8 post to panel.bookings.update with current_step + get_step/get_next/draft
    - Every submit causes a normal redirect to the next (or same) step URL
--}}
@extends('design_1.panel.layouts.panel')

@section('content')

<style>
.step-progress {
    display: flex;
    align-items: center;
    background: #fff;
    border-radius: 12px;
    padding: 16px 20px;
    margin-bottom: 20px;
    overflow-x: auto;
    gap: 4px;
}
.step-pill {
    flex: 1;
    min-width: 90px;
    text-align: center;
    padding: 8px 6px;
    border-radius: 8px;
    font-size: 12px;
    font-weight: 600;
    color: #98a2b3;
    background: #f3f4f6;
    white-space: nowrap;
}
.step-pill.is-active { background: #2563eb; color: #fff; }
.step-pill.is-done { background: #e7f6ec; color: #16a34a; }
.step-panel {
    background: #fff;
    border-radius: 12px;
    padding: 28px;
    min-height: 320px;
}
.step-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    background: #fff;
    border-radius: 12px;
    padding: 16px 24px;
}
.booking-switch-row { display: flex; align-items: center; gap: 12px; padding: 6px 0; }
.booking-switch { position: relative; display: inline-block; width: 48px; height: 26px; flex-shrink: 0; }
.booking-switch input { opacity: 0; width: 0; height: 0; position: absolute; }
.booking-switch-slider { position: absolute; inset: 0; background: #ccc; border-radius: 26px; cursor: pointer; transition: background .2s; }
.booking-switch-slider:before { content: ''; position: absolute; height: 20px; width: 20px; left: 3px; bottom: 3px; background: #fff; border-radius: 50%; transition: transform .2s; box-shadow: 0 1px 3px rgba(0,0,0,.2); }
.booking-switch input:checked + .booking-switch-slider { background: #2196F3; }
.booking-switch input:checked + .booking-switch-slider:before { transform: translateX(22px); }
.booking-switch-label { font-size: 14px; color: #495057; font-weight: 500; cursor: pointer; user-select: none; margin-bottom: 0; }
.booking-switch-label small { display: block; font-size: 12px; color: #999; font-weight: 400; }
.booking-map-preview { width: 100%; height: 220px; border: 1px solid #e1e5eb; border-radius: 12px; overflow: hidden; background: #f7f8fa; }
.booking-map-preview iframe { width: 100%; height: 100%; border: 0; }
</style>

@php
    $isEditing = isset($booking) && !is_null($booking);
    $stepLabels = [
        1 => 'General Info',
        2 => 'Pricing & Availability',
        3 => 'Participants & Resources',
        4 => 'Content',
        5 => 'Prerequisites & Related',
        6 => 'FAQ',
        7 => 'Location & Filters',
        8 => 'Review & Submit',
    ];
@endphp

<div class="container-fluid">

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="step-progress">
        @foreach($stepLabels as $num => $label)
            <div class="step-pill {{ $num == $currentStep ? 'is-active' : '' }} {{ $isEditing && $num < $currentStep ? 'is-done' : '' }}">
                {{ $num }}. {{ $label }}
            </div>
        @endforeach
    </div>

    {{--
        Step 1 with no booking yet -> POST to store()
        Any other step (or step 1 while editing) -> POST to update()

        IMPORTANT: panel.bookings.update is registered as a PUT route
        (Route::put('/{id}', ...)). A plain method="POST" form without
        @method('PUT') hits Laravel with a raw POST verb against a URI that
        only accepts PUT/DELETE -> MethodNotAllowedHttpException.
        @method('PUT') injects the hidden _method spoofing field so Laravel
        routes it correctly.
    --}}
    @if(!$isEditing)
        <form method="POST" action="{{ route('panel.bookings.store') }}" enctype="multipart/form-data" id="bookingStepForm">
            @csrf
    @else
        <form method="POST" action="{{ route('panel.bookings.update', $booking->id) }}" enctype="multipart/form-data" id="bookingStepForm">
            @csrf
            @method('PUT')
            <input type="hidden" name="current_step" value="{{ $currentStep }}">
    @endif

        <div class="step-panel">
            @include('design_1.panel.bookings.create_booking.steps.step' . $currentStep)
        </div>

        <div class="step-footer">
            <div>
                @if($currentStep > 1)
                    {{--
                        FIXED: must use 'panel.bookings.edit.step' (route /{id}/step/{step?}),
                        not 'panel.bookings.edit' (route /{id}/edit has no {step} parameter).
                        Passing 'step' to the wrong route silently turned into a query string
                        that the controller's edit($request, $id, $step = 1) never reads,
                        so "Back" always reopened step 1 instead of the previous step.
                    --}}
                    <a href="{{ route('panel.bookings.edit.step', ['id' => $booking->id, 'step' => $currentStep - 1]) }}" class="btn btn-outline-secondary">
                        <i class="fa fa-arrow-left mr-1"></i> Back
                    </a>
                @endif
            </div>
            <div>
                @if($isEditing)
                    <button type="submit" name="draft" value="1" class="btn btn-light mr-2">Save as Draft</button>
                @endif

                @if($currentStep < $stepCount)
                    <button type="submit" name="get_next" value="1" class="btn btn-primary">
                        Next <i class="fa fa-arrow-right ml-1"></i>
                    </button>
                @else
                    <button type="submit" class="btn btn-primary">
                        <i class="fa fa-paper-plane mr-1"></i> Submit for Review
                    </button>
                @endif
            </div>
        </div>
    </form>
</div>

@endsection
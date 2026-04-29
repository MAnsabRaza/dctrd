@php
    $idYes = $name . '_yes';
    $idNo = $name . '_no';
@endphp

<div class="form-group mb-30 mt-30 radio_style">
    <label class="input-label">{{ $label }}:</label>
    <div class="d-flex align-items-center">
        <div class="custom-control custom-radio">
            <input type="radio" name="{{ $name }}" value="1" id="{{ $idYes }}" class="custom-control-input" {{ $checked == 1 ? 'checked' : '' }}>
            <label class="custom-control-label font-14 cursor-pointer" for="{{ $idYes }}">{{ trans('update.Yes') }}</label>
        </div>
        <div class="custom-control custom-radio ml-15">
            <input type="radio" name="{{ $name }}" value="0" id="{{ $idNo }}" class="custom-control-input" {{ $checked == 0 ? 'checked' : '' }}>
            <label class="custom-control-label font-14 cursor-pointer" for="{{ $idNo }}">{{ trans('update.No') }}</label>
        </div>
    </div>
    <small>{{ $small }}</small>
</div>

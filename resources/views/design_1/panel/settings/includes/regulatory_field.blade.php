@php
    $type = $field['type'] ?? 'text';
    $fieldKey = $field['key'];
    $inputName = $inputName ?? $fieldKey;
@endphp

<div class="form-group">
    <label class="form-group-label">
        {{ $field['label'] }}
        @if(!empty($field['required']))
            <span class="text-danger">*</span>
        @endif
    </label>

    @if($type === 'textarea')
        <textarea name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" class="form-control" rows="3">{{ $value }}</textarea>
    @elseif($type === 'select')
        <select name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" class="form-control select2">
            <option value="">Select</option>
            @foreach($field['options'] ?? [] as $optKey => $optLabel)
                <option value="{{ $optKey }}" {{ $value == $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
            @endforeach
        </select>
    @elseif($type === 'file')
        <div class="input-group">
            <div class="input-group-prepend">
                <button type="button" class="input-group-text admin-file-manager" data-input="{{ $fieldKey }}">
                    <i class="fa fa-upload"></i>
                </button>
            </div>
            <input type="text" name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" id="{{ $fieldKey }}" value="{{ $value }}" class="form-control">
        </div>
    @elseif($type === 'map')
        @include('partials._location_picker', ['addressName' => $fieldKey, 'showAjaxSave' => false])
    @else
        <input type="text" name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" value="{{ $value }}" class="form-control">
    @endif
</div>

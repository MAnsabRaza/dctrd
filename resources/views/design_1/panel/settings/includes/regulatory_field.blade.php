@php $type = $field['type'] ?? 'text'; @endphp

<div class="form-group">
    <label class="form-group-label">{{ $field['label'] }} @if(!empty($field['required']))<span class="text-danger">*</span>@endif</label>

    @if($type === 'textarea')
        <textarea name="{{ $field['key'] }}" class="form-control" rows="3">{{ $value }}</textarea>
    @elseif($type === 'select')
        <select name="{{ $field['key'] }}" class="form-control select2">
            <option value="">Select</option>
            @foreach($field['options'] ?? [] as $optKey => $optLabel)
                <option value="{{ $optKey }}" {{ $value == $optKey ? 'selected' : '' }}>{{ $optLabel }}</option>
            @endforeach
        </select>
    @elseif($type === 'file')
        <div class="input-group">
            <div class="input-group-prepend">
                <button type="button" class="input-group-text admin-file-manager" data-input="{{ $field['key'] }}">
                    <i class="fa fa-upload"></i>
                </button>
            </div>
            <input type="text" name="{{ $field['key'] }}" id="{{ $field['key'] }}" value="{{ $value }}" class="form-control">
        </div>
    @elseif($type === 'map')
        {{-- reuse existing location picker partial --}}
        @include('partials._location_picker', ['addressName' => $field['key'], 'showAjaxSave' => false])
    @else
        <input type="text" name="{{ $field['key'] }}" value="{{ $value }}" class="form-control">
    @endif
</div>
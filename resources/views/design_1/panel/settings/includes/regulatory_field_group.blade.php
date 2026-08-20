@php
    $submission = $submission ?? null;
@endphp

@forelse($fields as $field)
    @php
        $fieldKey = 'field_' . $field->id;
        $fieldValue = data_get($submission->data ?? [], $fieldKey);
        $inputName = "regulatory_forms[{$formKey}][fields][{$fieldKey}]";
    @endphp
    <div class="form-group js-regulatory-field" data-required="{{ $field->required ? '1' : '0' }}">
        <label class="form-group-label">
            {{ $field->title }}
            @if($field->required)<span class="text-danger">*</span>@endif
        </label>

        @if($field->type === 'dropdown')
            <select name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" class="form-control select2">
                <option value="">Select</option>
                @foreach($field->options as $option)
                    <option value="{{ $option->id }}" {{ (string) $fieldValue === (string) $option->id ? 'selected' : '' }}>
                        {{ $option->title }}
                    </option>
                @endforeach
            </select>

     @elseif($field->type === 'radio')
    @foreach($field->options as $option)
        <div class="form-check">
            <input type="radio"
                   name="{{ $inputName }}"
                   data-field-key="{{ $fieldKey }}"
                   value="{{ $option->id }}"
                   class="form-check-input"
                   id="opt_{{ $option->id }}"
                   {{ (string) $fieldValue === (string) $option->id ? 'checked' : '' }}>
            <label class="form-check-label" for="opt_{{ $option->id }}">{{ $option->title }}</label>
        </div>
    @endforeach

     @elseif($field->type === 'checkbox')
    @php $selectedValues = (array) ($fieldValue ?? []); @endphp
    @foreach($field->options as $option)
        <div class="form-check">
            <input type="checkbox"
                   name="{{ $inputName }}[]"
                   data-field-key="{{ $fieldKey }}"
                   value="{{ $option->id }}"
                   class="form-check-input"
                   id="opt_{{ $option->id }}"
                   {{ in_array($option->id, $selectedValues) ? 'checked' : '' }}>
            <label class="form-check-label" for="opt_{{ $option->id }}">{{ $option->title }}</label>
        </div>
    @endforeach
     @elseif($field->type === 'toggle')
    <div class="form-check form-switch">
        <input type="checkbox"
               name="{{ $inputName }}"
               data-field-key="{{ $fieldKey }}"
               value="1"
               class="form-check-input"
               id="{{ $fieldKey }}"
               {{ !empty($fieldValue) ? 'checked' : '' }}>
        <label class="form-check-label" for="{{ $fieldKey }}"></label>
    </div>

        @elseif($field->type === 'upload')
            <div class="input-group">
                <div class="input-group-prepend">
                    <button type="button" class="input-group-text admin-file-manager" data-input="{{ $fieldKey }}" data-preview="holder">
                        <i class="fa fa-upload"></i>
                    </button>
                </div>
                <input type="text" name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" id="{{ $fieldKey }}" value="{{ $fieldValue }}" class="form-control">
                <div class="input-group-append">
                    <button type="button" class="input-group-text admin-file-view" data-input="{{ $fieldKey }}">
                        <i class="fa fa-eye"></i>
                    </button>
                </div>
            </div>

        @elseif($field->type === 'textarea')
            <textarea name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" class="form-control" rows="3">{{ $fieldValue }}</textarea>

        @elseif($field->type === 'date')
            <input type="text" name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" value="{{ $fieldValue }}" class="form-control js-datepicker">

        @elseif($field->type === 'number')
            <input type="number" name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" value="{{ $fieldValue }}" class="form-control">

        @else
            <input type="text" name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" value="{{ $fieldValue }}" class="form-control">
        @endif

        <div class="invalid-feedback js-field-error"></div>
    </div>
@empty
    <p class="text-gray-500">No fields have been added to this form yet.</p>
@endforelse
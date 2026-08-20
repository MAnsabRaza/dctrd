@php
    $submission = $submission ?? null;
@endphp

@forelse($fields as $field)
    @php
        $fieldKey = 'field_' . $field->id;
        $fieldValue = data_get($submission->data ?? [], $fieldKey);
        $inputName = "regulatory_forms[{$formKey}][fields][{$fieldKey}]";
    @endphp
    <div class="form-group">
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
        @elseif(in_array($field->type, ['checkbox', 'radio']))
            @php $selectedValues = (array) ($fieldValue ?? []); @endphp
            @foreach($field->options as $option)
                <div class="custom-control custom-{{ $field->type }}">
                    <input type="{{ $field->type }}"
                           name="{{ $inputName }}{{ $field->type === 'checkbox' ? '[]' : '' }}"
                           data-field-key="{{ $fieldKey }}"
                           value="{{ $option->id }}"
                           class="custom-control-input"
                           id="opt_{{ $option->id }}"
                           {{ in_array($option->id, $selectedValues) ? 'checked' : '' }}>
                    <label class="custom-control-label" for="opt_{{ $option->id }}">{{ $option->title }}</label>
                </div>
            @endforeach
        @else
            <input type="text" name="{{ $inputName }}" data-field-key="{{ $fieldKey }}" value="{{ $fieldValue }}" class="form-control">
        @endif
    </div>
@empty
    <p class="text-gray-500">Is form ke liye admin ne abhi tak koi field add nahi ki.</p>
@endforelse
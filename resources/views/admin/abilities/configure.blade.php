@extends('layouts.vendor')

@section('content')
<div class="container-fluid">
    <h3>Configure: {{ $ability->name }}</h3>

    <form action="{{ route('vendor.abilities.save', $ability) }}" method="POST">
        @csrf

        @foreach($ability->getConfigFields() as $field)
            <div class="form-group mb-3">
                <label>{{ $field['label'] }} @if($field['required'] ?? false)<span class="text-danger">*</span>@endif</label>

                @php $currentValue = $vendorAbility->config_json[$field['key']] ?? ''; @endphp

                @if($field['type'] === 'boolean')
                    <div>
                        <input type="checkbox" name="config[{{ $field['key'] }}]" value="1"
                               {{ $currentValue ? 'checked' : '' }}>
                    </div>
                @elseif($field['type'] === 'textarea')
                    <textarea name="config[{{ $field['key'] }}]" class="form-control"
                              {{ ($field['required'] ?? false) ? 'required' : '' }}>{{ $currentValue }}</textarea>
                @else
                    <input type="{{ $field['type'] === 'password' ? 'password' : 'text' }}"
                           name="config[{{ $field['key'] }}]"
                           class="form-control"
                           value="{{ $field['type'] === 'password' ? '' : $currentValue }}"
                           placeholder="{{ $field['type'] === 'password' && $currentValue ? '••••••••' : '' }}"
                           {{ ($field['required'] ?? false) ? 'required' : '' }}>
                @endif
            </div>
        @endforeach

        <button type="submit" class="btn btn-primary">Save Configuration</button>

        @if($vendorAbility->exists)
            <button type="button" id="test-conn-btn"
                    data-url="{{ route('vendor.vendor-abilities.test', $vendorAbility) }}"
                    class="btn btn-outline-secondary">Test Connection</button>
        @endif
    </form>
</div>

<script>
document.getElementById('test-conn-btn')?.addEventListener('click', function () {
    fetch(this.dataset.url, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
        },
    })
    .then(res => res.json())
    .then(data => alert(data.message));
});
</script>
@endsection
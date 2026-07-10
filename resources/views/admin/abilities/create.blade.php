@extends('layouts.admin')

@section('content')
<div class="container-fluid">
    <h3>+ New Ability</h3>

    <form action="{{ route('admin.abilities.store') }}" method="POST" id="ability-form">
        @csrf

        <div class="form-group mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>

        <div class="form-group mb-3">
            <label>Type</label>
            <select name="type" class="form-control" required>
                <option value="import">Import</option>
                <option value="export">Export</option>
                <option value="booking">Booking</option>
                <option value="dropshipping">Dropshipping</option>
            </select>
        </div>

        <div class="form-group mb-3">
            <label>Driver Class</label>
            <input type="text" name="driver_class" class="form-control"
                   placeholder="App\Services\Abilities\PerfexExportAbility" required>
        </div>

        <div class="form-group mb-3">
            <label>Description</label>
            <textarea name="description" class="form-control"></textarea>
        </div>

        <hr>
        <h5>Config Fields</h5>
        <div id="fields-wrapper"></div>
        <button type="button" id="add-field-btn" class="btn btn-sm btn-secondary mb-3">+ Add Field</button>

        <div>
            <button type="submit" class="btn btn-primary">Save Ability</button>
        </div>
    </form>
</div>

<template id="field-row-template">
    <div class="field-row row mb-2 align-items-center">
        <div class="col-3">
            <input type="text" name="fields[__INDEX__][key]" class="form-control" placeholder="Field Key (e.g. api_key)" required>
        </div>
        <div class="col-3">
            <input type="text" name="fields[__INDEX__][label]" class="form-control" placeholder="Label" required>
        </div>
        <div class="col-2">
            <select name="fields[__INDEX__][type]" class="form-control">
                <option value="text">Text</option>
                <option value="password">Password</option>
                <option value="boolean">Checkbox</option>
                <option value="select">Select</option>
                <option value="textarea">Textarea</option>
            </select>
        </div>
        <div class="col-2">
            <label class="mb-0">
                <input type="checkbox" name="fields[__INDEX__][required]" value="1"> Required
            </label>
        </div>
        <div class="col-2">
            <button type="button" class="btn btn-sm btn-danger remove-field-btn">Remove</button>
        </div>
    </div>
</template>

<script>
let fieldIndex = 0;
const wrapper = document.getElementById('fields-wrapper');
const template = document.getElementById('field-row-template');

function addFieldRow() {
    const html = template.innerHTML.replaceAll('__INDEX__', fieldIndex);
    const div = document.createElement('div');
    div.innerHTML = html;
    wrapper.appendChild(div.firstElementChild);
    fieldIndex++;
}

document.getElementById('add-field-btn').addEventListener('click', addFieldRow);

wrapper.addEventListener('click', function (e) {
    if (e.target.classList.contains('remove-field-btn')) {
        e.target.closest('.field-row').remove();
    }
});

// pehli field row default add ho jaye
addFieldRow();
</script>
@endsection
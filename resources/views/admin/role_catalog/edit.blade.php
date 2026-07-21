@extends('admin.layouts.app')

@section('content')
<section class="section">
    <div class="section-header">
        <h1>{{ $pageTitle }}</h1>
    </div>

    <div class="section-body">
        <div class="card">
            <div class="card-body">
                <form method="POST" action="{{ getAdminPanelUrl('/role-catalog/'.$role->id.'/update') }}">
                    @csrf

                    <div class="form-group">
                        <label class="input-label">Label</label>
                        <input type="text" name="label" value="{{ $role->label }}" class="form-control">
                    </div>

                    <div class="form-group d-flex align-items-center">
                        <label class="mr-2 mb-0">Active</label>
                        <div class="custom-control custom-switch">
                            <input type="checkbox" name="active" id="activeSwitch" class="custom-control-input" {{ $role->active ? 'checked' : '' }}>
                            <label class="custom-control-label" for="activeSwitch"></label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="input-label">Role Bundle — yeh role kin roles ko already cover karta hai?</label>
                        <p class="text-gray-500 text-small">
                            Agar user ke paas yeh role hai, to yahan check kiye gaye roles "Add Role" list mein uske liye offer nahi honge
                            (kyunki woh already cover ho rahe hain).
                        </p>
                        <div class="d-flex flex-wrap gap-2">
                            @foreach($allRoles as $otherRole)
                                <div class="custom-control custom-checkbox">
                                    <input type="checkbox" name="supersedes[]" value="{{ $otherRole->key }}"
                                           id="bundle_{{ $otherRole->id }}" class="custom-control-input"
                                           {{ in_array($otherRole->key, $role->supersedes ?? []) ? 'checked' : '' }}>
                                    <label class="custom-control-label" for="bundle_{{ $otherRole->id }}">
                                        [{{ ucfirst($otherRole->family) }}] {{ $otherRole->label }}
                                    </label>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <button type="submit" class="btn btn-success mt-3">Save Change</button>
                </form>
            </div>
        </div>
    </div>
</section>
@endsection
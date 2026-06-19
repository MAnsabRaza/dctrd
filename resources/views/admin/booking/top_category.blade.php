@extends('admin.layouts.app')

@section('content')
<div class="row">
    <div class="col-12">
        <div class="card">
            <div class="card-header">
                <h4>{{ $pageTitle }}</h4>
            </div>
            <div class="card-body">
                @include('admin.includes.alerts.success')
                @include('admin.includes.alerts.errors')

                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ (!isset($activeTab) or $activeTab == 'list') ? 'active' : '' }}" 
                                   id="list-tab" data-toggle="tab" href="#list" role="tab" aria-controls="list">
                                    <i class="fas fa-list"></i> {{ trans('admin/main.list') }}
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ (isset($activeTab) and $activeTab == 'new') ? 'active' : '' }}" 
                                   id="new-tab" data-toggle="tab" href="#new" role="tab" aria-controls="new">
                                    <i class="fas fa-plus"></i> {{ trans('admin/main.add_top_category') }}
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content">
                            <!-- List Tab -->
                            <div class="tab-pane fade {{ (!isset($activeTab) or $activeTab == 'list') ? 'show active' : '' }}" 
                                 id="list" role="tabpanel" aria-labelledby="list-tab">
                                <table class="table table-striped table-hover">
                                    <thead class="table-dark">
                                        <tr>
                                            <th>{{ trans('admin/main.image') }}</th>
                                            <th>{{ trans('admin/main.category') }}</th>
                                            <th>{{ trans('admin/main.actions') }}</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($items as $item)
                                            <tr>
                                                <td>
                                                    @if($item->image)
                                                        <img src="{{ asset('storage/' . $item->image) }}" alt="thumbnail" style="max-width: 50px; max-height: 50px;">
                                                    @else
                                                        <span class="badge badge-secondary">{{ trans('admin/main.no_image') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    @if($item->category)
                                                        <strong>{{ $item->category->title }}</strong>
                                                    @else
                                                        <span class="text-danger">{{ trans('admin/main.deleted') }}</span>
                                                    @endif
                                                </td>
                                                <td>
                                                    <a href="{{ getAdminPanelUrl() }}/booking/top-categories/{{ $item->id }}/edit" 
                                                       class="btn btn-sm btn-info" title="{{ trans('admin/main.edit') }}">
                                                        <i class="fas fa-edit"></i>
                                                    </a>
                                                    <a href="{{ getAdminPanelUrl() }}/booking/top-categories/{{ $item->id }}/delete" 
                                                       class="btn btn-sm btn-danger" 
                                                       onclick="return confirm('{{ trans('admin/main.are_you_sure') }}');"
                                                       title="{{ trans('admin/main.delete') }}">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </td>
                                            </tr>
                                        @empty
                                            <tr>
                                                <td colspan="3" class="text-center text-muted">
                                                    {{ trans('admin/main.no_result') }}
                                                </td>
                                            </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                                {{ $items->links() }}
                            </div>

                            <!-- New/Edit Tab -->
                            <div class="tab-pane fade {{ (isset($activeTab) and $activeTab == 'new') ? 'show active' : '' }}" 
                                 id="new" role="tabpanel" aria-labelledby="new-tab">
                                <form method="post" 
                                      action="@if(isset($item)){{ getAdminPanelUrl() }}/booking/top-categories/{{ $item->id }}@else{{ getAdminPanelUrl() }}/booking/top-categories@endif"
                                      enctype="multipart/form-data">
                                    @csrf
                                    @if(isset($item))
                                        @method('PUT')
                                    @endif

                                    <div class="form-group">
                                        <label for="category_id">{{ trans('admin/main.category') }} <span class="text-danger">*</span></label>
                                        <select name="category_id" id="category_id" class="form-control @error('category_id') is-invalid @enderror" required>
                                            <option value="">{{ trans('admin/main.select_category') }}</option>
                                            @foreach($categories as $id => $title)
                                                <option value="{{ $id }}" @if((isset($item) and $item->category_id == $id) or old('category_id') == $id) selected @endif>
                                                    {{ $title }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('category_id')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <label for="image">{{ trans('admin/main.thumbnail') }}</label>
                                        @if(isset($item) and $item->image)
                                            <div class="mb-2">
                                                <img src="{{ asset('storage/' . $item->image) }}" alt="thumbnail" style="max-width: 200px;">
                                            </div>
                                        @endif
                                        <input type="file" name="image" id="image" class="form-control @error('image') is-invalid @enderror" accept="image/*">
                                        <small class="form-text text-muted">{{ trans('admin/main.allowed_formats') }}: JPEG, PNG, JPG, GIF ({{ trans('admin/main.max_size') }}: 5MB)</small>
                                        @error('image')
                                            <span class="invalid-feedback">{{ $message }}</span>
                                        @enderror
                                    </div>

                                    <div class="form-group">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="fas fa-save"></i> {{ trans('admin/main.save_changes') }}
                                        </button>
                                        @if(isset($item))
                                            <a href="{{ getAdminPanelUrl() }}/booking/top-categories" class="btn btn-secondary">
                                                {{ trans('admin/main.cancel') }}
                                            </a>
                                        @endif
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

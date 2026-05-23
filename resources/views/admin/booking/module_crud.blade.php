@extends('admin.layouts.app')

@section('content')
    <section class="section">
        <div class="section-header">
            <h1>{{ $pageTitle }}</h1>
            <div class="section-header-breadcrumb">
                <div class="breadcrumb-item active">
                    <a href="{{ getAdminPanelUrl() }}">{{ trans('admin/main.dashboard') }}</a>
                </div>
                <div class="breadcrumb-item">{{ $pageTitle }}</div>
            </div>
        </div>

        <div class="section-body">
            <div class="row">
                <div class="col-12 col-lg-8">
                    <div class="card">
                        <div class="card-header">
                            <h5>{{ $pageTitle }}</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table custom-table font-14">
                                    <tr>
                                        @foreach($config['columns'] as $column)
                                            <th>{{ ucwords(str_replace('_', ' ', $column)) }}</th>
                                        @endforeach
                                        <th>{{ trans('admin/main.action') }}</th>
                                    </tr>

                                    @foreach($items as $item)
                                        <tr>
                                            @foreach($config['columns'] as $column)
                                                <td>
                                                    @php($value = data_get($item, $column))
                                                    @if(is_bool($value))
                                                        <span class="badge badge-{{ $value ? 'success' : 'danger' }}">{{ $value ? trans('admin/main.active') : trans('admin/main.inactive') }}</span>
                                                    @elseif(is_array($value))
                                                        <code>{{ json_encode($value) }}</code>
                                                    @else
                                                        {{ $value ?? '-' }}
                                                    @endif
                                                </td>
                                            @endforeach
                                            <td width="120">
                                                @can($config['permission'] . '_edit')
                                                    <a href="{{ getAdminPanelUrl("/booking/modules/{$resource}/{$item->id}/edit") }}" class="btn btn-sm btn-primary">{{ trans('admin/main.edit') }}</a>
                                                @endcan

                                                @can($config['permission'] . '_delete')
                                                    @include('admin.includes.delete_button', [
                                                        'url' => getAdminPanelUrl("/booking/modules/{$resource}/{$item->id}/delete"),
                                                        'btnClass' => 'btn btn-sm btn-danger',
                                                        'btnText' => trans('admin/main.delete'),
                                                    ])
                                                @endcan
                                            </td>
                                        </tr>
                                    @endforeach
                                </table>
                            </div>

                            {{ $items->links() }}
                        </div>
                    </div>
                </div>

                @php($formPermission = !empty($editItem) ? $config['permission'] . '_edit' : $config['permission'] . '_create')

                @can($formPermission)
                    <div class="col-12 col-lg-4">
                        <div class="card">
                            <div class="card-header">
                                <h5>{{ !empty($editItem) ? trans('admin/main.edit') : trans('admin/main.create') }}</h5>
                            </div>
                            <div class="card-body">
                                <form method="post" action="{{ !empty($editItem) ? getAdminPanelUrl("/booking/modules/{$resource}/{$editItem->id}/update") : getAdminPanelUrl("/booking/modules/{$resource}/store") }}">
                                    {{ csrf_field() }}

                                    @foreach($config['fields'] as $field)
                                        @php($value = old($field, !empty($editItem) ? data_get($editItem, $field) : null))

                                        <div class="form-group">
                                            <label>{{ ucwords(str_replace('_', ' ', $field)) }}</label>

                                            @if(in_array($field, $config['booleans'] ?? []))
                                                <select name="{{ $field }}" class="form-control">
                                                    <option value="1" {{ (string) $value === '1' ? 'selected' : '' }}>{{ trans('admin/main.active') }}</option>
                                                    <option value="0" {{ (string) $value === '0' ? 'selected' : '' }}>{{ trans('admin/main.inactive') }}</option>
                                                </select>
                                            @elseif(in_array($field, $config['json'] ?? []))
                                                <textarea name="{{ $field }}" rows="3" class="form-control">{{ is_array($value) ? json_encode($value) : $value }}</textarea>
                                            @elseif(str_contains($field, '_at'))
                                                <input type="datetime-local" name="{{ $field }}" value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d\TH:i') : '' }}" class="form-control">
                                            @elseif(str_contains($field, 'date'))
                                                <input type="date" name="{{ $field }}" value="{{ $value ? \Carbon\Carbon::parse($value)->format('Y-m-d') : '' }}" class="form-control">
                                            @elseif(str_contains($field, 'time'))
                                                <input type="time" name="{{ $field }}" value="{{ $value }}" class="form-control">
                                            @elseif(in_array($field, ['message', 'description']))
                                                <textarea name="{{ $field }}" rows="3" class="form-control">{{ $value }}</textarea>
                                            @else
                                                <input type="text" name="{{ $field }}" value="{{ $value }}" class="form-control">
                                            @endif
                                        </div>
                                    @endforeach

                                    <button type="submit" class="btn btn-primary">{{ trans('admin/main.save') }}</button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endcan
            </div>
        </div>
    </section>
@endsection

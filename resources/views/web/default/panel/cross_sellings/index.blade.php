@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <style>
        .group-even {
            background-color: #e9e9e9;
        }

        .group-odd {
            background-color: #ffffff;
        }
    </style>
@endpush

@section('content')
    <section>
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2 class="section-title">{{ trans('cross.Cross Selling Relations') }}</h2>
                <a href="{{ route('cross-sellings.create') }}" class="btn btn-primary btn-sm">{{ trans('cross.Add New') }}</a>
            </div>
            <div class="card-body">
                @php
                    $groupIndex = 0;
                @endphp

                <table class="table table-bordered text-center">
                    <thead>
                        <tr>
                            <th>{{ trans('cross.Source') }}</th>
                            <th>{{ trans('cross.Target (cross selling items)') }}</th>
                            <th>{{ trans('cross.Actions') }}</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($relations as $source => $items)
                            @php
                                $groupClass = $groupIndex % 2 == 0 ? 'group-even' : 'group-odd';
                                $target_type =
                                    $items[0]->target_type == 'App\Models\Webinar'
                                        ? trans('cross.Course')
                                        : trans('cross.' . class_basename($items[0]->target_type));
                            @endphp

                            <tr class="{{ $groupClass }}">
                                <td rowspan="{{ count($items) }}">
                                    {{ $source }}
                                </td>
                                <td>{{ optional($items[0]->target)->title ?? 'N/A' }} - ({{ $target_type }})</td>
                                <td>
                                    <form action="{{ route('cross-sellings.destroy', $items[0]) }}" method="POST">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn btn-danger btn-sm"
                                            onclick="return confirm('Are you sure?')">
                                            <svg width="25" height="25" viewBox="0 0 16 16"
                                                xmlns="http://www.w3.org/2000/svg" fill="#fff">
                                                <path fill-rule="evenodd" clip-rule="evenodd"
                                                    d="M10 3h3v1h-1v9l-1 1H4l-1-1V4H2V3h3V2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1zM9 2H6v1h3zM4 13h7V4H4zm2-8H5v7h1zm1 0h1v7H7zm2 0h1v7H9z" />
                                            </svg>
                                        </button>
                                    </form>
                                </td>
                            </tr>

                            @foreach ($items->slice(1) as $relation)
                                @php
                                    $target_type =
                                        $relation->target_type == 'App\Models\Webinar'
                                            ? trans('cross.Course')
                                            : trans('cross.' . class_basename($relation->target_type));
                                @endphp
                                <tr class="{{ $groupClass }}">
                                    <td>{{ optional($relation->target)->title ?? 'N/A' }} - ({{ $target_type }})</td>
                                    <td>
                                        <form action="{{ route('cross-sellings.destroy', $relation) }}" method="POST">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm"
                                                onclick="return confirm('Are you sure?')">
                                                <svg width="25" height="25" viewBox="0 0 16 16"
                                                    xmlns="http://www.w3.org/2000/svg" fill="#fff">
                                                    <path fill-rule="evenodd" clip-rule="evenodd"
                                                        d="M10 3h3v1h-1v9l-1 1H4l-1-1V4H2V3h3V2a1 1 0 0 1 1-1h3a1 1 0 0 1 1 1zM9 2H6v1h3zM4 13h7V4H4zm2-8H5v7h1zm1 0h1v7H7zm2 0h1v7H9z" />
                                                </svg>
                                            </button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach

                            @php $groupIndex++; @endphp
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
@endpush

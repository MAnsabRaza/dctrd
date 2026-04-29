@extends(getTemplate() . '.panel.layouts.panel_layout')

@push('styles_top')
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
@endpush

@section('content')
    <section>
        <div class="card">
            <div class="card-header d-flex align-items-center justify-content-between">
                <h2 class="section-title">{{ trans('cross.Create Cross Selling Relation') }}</h2>
            </div>
            <div class="card-body">
                <form method="POST" action="{{ route('cross-sellings.store') }}">
                    @csrf

                    <div class="mb-3">
                        <label>{{ trans('cross.Source Type') }}</label>
                        <select name="source_type" id="source_type" class="form-control" required>
                            <option value="">-- {{ trans('cross.Select Source Type') }} --</option>
                            <option value="App\Models\Webinar">{{ trans('cross.Course') }}</option>
                            <option value="App\Models\Product">{{ trans('cross.Product') }}</option>
                            <option value="App\Models\Blog">{{ trans('cross.Article') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>{{ trans('cross.Source Item') }}</label>
                        <select name="source_id" id="source_id" class="form-control" required></select>
                    </div>

                    <hr>

                    <div class="mb-3">
                        <label>{{ trans('cross.Target Type') }}</label>
                        <select name="target_type" id="target_type" class="form-control" required>
                            <option value="">-- {{ trans('cross.Select Target Type') }} --</option>
                            <option value="App\Models\Webinar">{{ trans('cross.Course') }}</option>
                            <option value="App\Models\Product">{{ trans('cross.Product') }}</option>
                            <option value="App\Models\Blog">{{ trans('cross.Article') }}</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label>{{ trans('cross.Target Item') }}</label>
                        <select name="target_id" id="target_id" class="form-control" required></select>
                    </div>

                    <button type="submit" class="btn btn-primary">{{ trans('cross.Save') }}</button>
                </form>
            </div>
        </div>
    </section>
@endsection

@push('scripts_bottom')
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

    <script>
        function initDynamicSelect(typeSelector, idSelector, source_id = null) {
            $(idSelector).select2({
                ajax: {
                    url: '{{ route('panel.cross-selling.search') }}',
                    data: function(params) {
                        return {
                            q: params.term,
                            type: $(typeSelector).val(),
                            source_id: source_id
                        };
                    },
                    processResults: function(data) {
                        return {
                            results: data.map(item => {
                                // Get translation title (fallback to English, or first available)
                                let title = item.translations?.find(t => t.locale === 'en')?.title ||
                                    item.translations?.[0]?.title ||
                                    'Untitled';

                                return {
                                    id: item.id,
                                    text: title
                                };
                            })
                        };
                    },
                    delay: 250
                },
                placeholder: 'Select an item',
                allowClear: true,
                width: '100%'
            });
        }


        $(document).ready(function() {
            $('#source_type').on('change', function() {
                var source_id = $('#source_id').val();
                $('#source_id').val(null).trigger('change');
                initDynamicSelect('#source_type', '#source_id', source_id);
            });

            $('#target_type').on('change', function() {
                var source_id = $('#source_id').val();
                $('#target_id').val(null).trigger('change');
                initDynamicSelect('#target_type', '#target_id', source_id);
            });
        });
    </script>
@endpush

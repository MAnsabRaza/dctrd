@extends('design_1.panel.layouts.panel')

@push('styles_top')
    <link rel="stylesheet" href="/assets/default/vendors/daterangepicker/daterangepicker.min.css">
@endpush

@section('content')

    @if(!empty($comments) and !$comments->isEmpty())
        <div class="bg-white pt-16 rounded-24">
            <div class="d-flex align-items-center justify-content-between pb-16 px-16 border-bottom-gray-100">
                <div class="">
                    <h3 class="font-16">{{ trans('panel.my_booking_comments') }}</h3>
                </div>
            </div>

            {{-- Filters --}}
            @include('design_1.panel.bookings.my_comments.filters')

            {{-- List Table --}}
            <div id="tableListContainer" class="table-responsive-lg" data-view-data-path="/panel/bookings/my-comments">
                <table class="table panel-table">
                    <thead>
                    <tr>
                        <th class="text-left">{{ trans('update.booking') }}</th>
                        <th class="text-center">{{ trans('panel.comment') }}</th>
                        <th class="text-center">{{ trans('public.status') }}</th>
                        <th class="text-center">{{ trans('public.date') }}</th>
                        <th class="text-right">{{ trans('update.controls') }}</th>
                    </tr>
                    </thead>
                    <tbody class="js-table-body-lists">
                    @foreach($comments as $commentRow)
                        @include('design_1.panel.bookings.my_comments.table_items', ['comment' => $commentRow])
                    @endforeach
                    </tbody>
                </table>

                {{-- Pagination --}}
                <div id="pagination" class="js-ajax-pagination" data-container-id="tableListContainer" data-container-items=".js-table-body-lists">
                    {!! $pagination !!}
                </div>
            </div>
        </div>
    @else
        @include('design_1.panel.includes.no-result',[
            'file_name' => 'store_product_comments.svg',
            'title' => trans('panel.my_comments_no_result'),
            'hint' =>  nl2br(trans('panel.my_comments_no_result_hint')),
            'extraClass' => 'mt-0',
        ])
    @endif

    {{-- Edit Comment Modal --}}
    <div class="modal fade" id="editCommentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">

                <form id="editCommentForm" method="POST" action="">
                    @csrf

                    <div class="modal-header">
                        <h5 class="modal-title">{{ trans('panel.edit_comment') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body">

                        <div class="form-group">
                            <label class="form-group-label">{{ trans('panel.comment') }}</label>
                            <textarea name="comment" id="editCommentText" class="form-control" rows="5" required></textarea>
                        </div>

                        <div class="form-group">
                            <label class="form-group-label">{{ trans('public.status') }}</label>
                            <select id="editCommentStatus" class="form-control select2" disabled>
                                <option value="active">{{ trans('public.published') }}</option>
                                <option value="pending">{{ trans('public.pending') }}</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-light" data-dismiss="modal">{{ trans('public.close') }}</button>
                        <button type="submit" class="btn btn-primary">{{ trans('public.save') }}</button>
                    </div>

                </form>

            </div>
        </div>
    </div>

@endsection

@push('scripts_bottom')
    <script>
        var commentLang = '{{ trans('panel.comment') }}';
        var replyToCommentLang = '{{ trans('panel.reply_to_the_comment') }}';
        var editCommentLang = '{{ trans('panel.edit_comment') }}';
        var saveLang = '{{ trans('public.save') }}';
        var closeLang = '{{ trans('public.close') }}';
        var failedLang = '{{ trans('quiz.failed') }}';
    </script>

    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

    <script src="/assets/design_1/js/panel/comments.min.js"></script>

    <script>
        $(document).on('click', '.js-edit-comment', function () {
            const commentId = $(this).data('comment-id');

            const description = $('#commentDescription' + commentId).val();
            const status = $('#commentStatus' + commentId).val();

            $('#editCommentText').val(description);
            $('#editCommentStatus').val(status).trigger('change');

            $('#editCommentForm').attr('action', '/panel/bookings/my-comments/' + commentId + '/update');

            $('#editCommentModal').modal('show');
        });
    </script>
@endpush
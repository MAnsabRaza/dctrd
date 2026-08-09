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

    {{-- Booking Comment — Dedicated Edit Modal --}}
    <div class="modal fade" id="editBookingCommentModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content rounded-16 border-0 shadow-sm">

                <form id="editBookingCommentForm">
                    @csrf

                    <div class="modal-header px-24 py-16 border-bottom-gray-100">
                        <h5 class="modal-title font-16 fw-bold mb-0">{{ trans('panel.edit_comment') }}</h5>
                        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>

                    <div class="modal-body px-24 py-20">

                        <div class="form-group mb-16">
                            <label class="form-group-label font-14 fw-semibold mb-8">{{ trans('panel.reply_to_the_comment') }}</label>
                            <textarea id="editBookingCommentText" class="form-control rounded-8" rows="5" required></textarea>
                        </div>

                        <div class="form-group mb-0">
                            <label class="form-group-label font-14 fw-semibold mb-8">{{ trans('public.status') }}</label>
                            <select id="editBookingCommentStatus" class="form-control select2 rounded-8">
                                <option value="active">{{ trans('public.published') }}</option>
                                <option value="pending">{{ trans('public.pending') }}</option>
                            </select>
                        </div>

                    </div>

                    <div class="modal-footer px-24 py-16 border-top-gray-100">
                        <button type="button" class="btn btn-light rounded-8" data-dismiss="modal">{{ trans('public.close') }}</button>
                        <button type="submit" class="btn btn-primary rounded-8">{{ trans('public.save') }}</button>
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
        var publishedLang = '{{ trans('public.published') }}';
        var pendingLang = '{{ trans('public.pending') }}';
    </script>

    <script src="/assets/default/vendors/moment.min.js"></script>
    <script src="/assets/default/vendors/daterangepicker/daterangepicker.min.js"></script>
    <script src="{{ getDesign1ScriptPath("get_view_data") }}"></script>

    <script src="/assets/design_1/js/panel/comments.min.js"></script>

    <script>
        $(document).on('click', '.js-edit-booking-comment', function () {
            const commentId = $(this).data('comment-id');

            $('#editBookingCommentForm').data('comment-id', commentId);
            $('#editBookingCommentText').val($('#commentDescription' + commentId).val());
            $('#editBookingCommentStatus').val($('#commentStatus' + commentId).val()).trigger('change');

           $('.modal-backdrop').remove();
$('body').removeClass('modal-open');
$('#editBookingCommentModal').modal('show');
        });

        $('#editBookingCommentForm').on('submit', function (e) {
            e.preventDefault();

            const commentId = $(this).data('comment-id');
            const comment = $('#editBookingCommentText').val();
            const status = $('#editBookingCommentStatus').val();
            const $submitBtn = $(this).find('button[type="submit"]');

            $submitBtn.prop('disabled', true);

            $.ajax({
                url: '/panel/bookings/my-comments/' + commentId + '/update',
                method: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    comment: comment,
                    status: status,
                },
                success: function () {
                    $('#commentDescription' + commentId).val(comment);
                    $('#commentStatus' + commentId).val(status);

                    const badgeHtml = (status === 'active')
                        ? '<span class="d-inline-flex-center px-8 py-6 rounded-8 bg-success-30 font-12 text-success">' + publishedLang + '</span>'
                        : '<span class="d-inline-flex-center px-8 py-6 rounded-8 bg-warning-30 font-12 text-warning">' + pendingLang + '</span>';

                    $('#statusBadge' + commentId).html(badgeHtml);

                    $('#editBookingCommentModal').modal('hide');
                },
                error: function () {
                    alert(failedLang);
                },
                complete: function () {
                    $submitBtn.prop('disabled', false);
                },
            });
        });
    </script>
@endpush
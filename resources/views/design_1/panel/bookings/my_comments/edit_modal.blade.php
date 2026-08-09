<div class="p-20">
    <div class="section-title">
        <h3 class="section-title__heading">{{ trans('panel.edit_comment') }}</h3>
    </div>

    <form action="{{ url('/panel/bookings/my-comments/' . $comment->id . '/update') }}" method="post" class="mt-20">
        @csrf

        <div class="form-group">
            <label class="form-group-label bg-white">{{ trans('panel.reply_to_the_comment') }}</label>
            <textarea name="comment" rows="6" class="form-control bg-white">{{ $comment->comment }}</textarea>
        </div>

        <div class="form-group mt-16">
            <label class="form-group-label bg-white">{{ trans('public.status') }}</label>
            <select name="status" class="form-control bg-white">
                <option value="pending" {{ $comment->status == 'pending' ? 'selected' : '' }}>
                    {{ trans('public.pending') }}
                </option>
                <option value="active" {{ $comment->status == 'active' ? 'selected' : '' }}>
                    {{ trans('public.published') }}
                </option>
            </select>
        </div>

        <div class="mt-32 d-flex align-items-center justify-content-end">
            <button type="button" class="btn btn-sm btn-primary js-save-form">{{ trans('public.save') }}</button>
            <button type="button" class="btn btn-sm btn-danger ml-8 close-swl">{{ trans('public.close') }}</button>
        </div>
    </form>
</div>
@if(session()->has('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="close" data-dismiss="alert" aria-label="{{ trans('admin/main.close') }}">
            <span aria-hidden="true">&times;</span>
        </button>
    </div>
@endif

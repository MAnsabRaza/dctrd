@extends("design_1.panel.layouts.app")

@section('content')
<div class="bg-white p-16 rounded-16 border-gray-200">
    <h3 class="font-16 font-weight-bold mb-16">{{ trans('update.my_roles') ?? 'My Roles' }}</h3>

    {{-- Current roles with status badges --}}
    <div class="d-flex flex-wrap gap-8 mb-24">
        @forelse($currentRoles as $userRole)
            <span class="badge-status px-3 py-2 rounded-12 d-flex align-items-center gap-2
                {{ $userRole->status === 'active' ? 'text-success bg-success-30' : 'text-warning bg-warning-30' }}">
                {{ $userRole->roleCatalog->label ?? '-' }}
                <small>({{ $userRole->status === 'active' ? 'Active' : 'Pending' }})</small>
            </span>
        @empty
            <div class="text-gray-500">{{ trans('update.no_roles_yet') ?? 'Abhi koi role nahi hai.' }}</div>
        @endforelse
    </div>

    <button type="button" class="btn btn-primary" id="addRoleBtn">
        + {{ trans('update.add_role') ?? 'Add Role' }}
    </button>
</div>

{{-- Confirmation + role select modal --}}
<div class="modal fade" id="addRoleModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">{{ trans('update.add_role') ?? 'Add Role' }}</h5>
                <button type="button" class="close" data-dismiss="modal">&times;</button>
            </div>
            <div class="modal-body">
                <p class="text-gray-500">Are you sure you want to add a new role?</p>

                <div class="form-group">
                    <label class="input-label">Select Role</label>
                    <select id="eligibleRoleSelect" class="form-control select2">
                        <option value="">— Select —</option>
                        @foreach($eligibleRoles as $role)
                            <option value="{{ $role->id }}">
                                [{{ ucfirst($role->family) }}] {{ $role->label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-primary" id="confirmAddRoleBtn">Yes, Add Role</button>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts_bottom')
<script>
(function ($) {
    $('#addRoleBtn').on('click', function () {
        $('#addRoleModal').modal('show');
    });

    $('#confirmAddRoleBtn').on('click', function () {
        var roleCatalogId = $('#eligibleRoleSelect').val();

        if (!roleCatalogId) {
            showToast('error', 'Error', 'Pehle role select karein.');
            return;
        }

        $.post('{{ route("panel.roles.request") }}', {
            _token: '{{ csrf_token() }}',
            role_catalog_id: roleCatalogId
        }, function (res) {
            showToast('success', '', res.msg);
            $('#addRoleModal').modal('hide');
            setTimeout(function () { window.location.reload(); }, 1000);
        }).fail(function (xhr) {
            var msg = xhr.responseJSON && xhr.responseJSON.msg ? xhr.responseJSON.msg : 'Something went wrong';
            showToast('error', 'Error', msg);
        });
    });
})(jQuery);
</script>
@endpush
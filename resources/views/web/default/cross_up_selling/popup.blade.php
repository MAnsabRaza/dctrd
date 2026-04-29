<div class="modal fade" id="crossSellingModal" tabindex="-1" role="dialog" aria-labelledby="crossSellingModalLabel"
    aria-hidden="true">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <x-cross-selling :products="$products" :title="$title" :slider="null" :display="null" :type="$type"/>
            </div>
        </div>
    </div>
</div>

@push('scripts_bottom')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            $('#crossSellingModal').modal('show');
        });
    </script>
@endpush

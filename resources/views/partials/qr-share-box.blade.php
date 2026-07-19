@if(!empty($item) && $item->qr_enabled && $item->qr_image_path)
    <div class="qr-share-box bg-white p-16 rounded-24 mt-24">
        <h3 class="font-16 mb-2">{{ trans('update.share_via_qr') ?? 'Share via QR' }}</h3>
        <div class="d-flex align-items-center" style="gap: 12px;">
            <img src="{{ $item->qr_image_url }}" alt="QR Code" width="90" height="90" style="border-radius:6px;">
            <div class="flex-grow-1">
                <input type="text" class="form-control form-control-sm mb-2"
                       value="{{ $item->short_url }}" readonly onclick="this.select()">
                <button type="button" class="btn btn-sm btn-outline-primary"
                        onclick="navigator.clipboard.writeText('{{ $item->short_url }}')">
                    {{ trans('update.copy_link') ?? 'Copy Link' }}
                </button>
            </div>
        </div>
    </div>
@endif
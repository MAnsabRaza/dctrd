@if(!empty($item) && $item->qr_enabled && $item->qr_image_path)
    <div class="qr-share-box bg-white p-16 rounded-16 mt-24 d-flex align-items-center" style="gap: 14px;">
        <img src="{{ $item->qr_image_url }}"
             alt="QR Code"
             width="64" height="64"
             style="border-radius: 8px; flex-shrink: 0; object-fit: cover;">

        <div class="flex-grow-1" style="min-width: 0;">
            <div class="text-gray-500 font-12 mb-1">Short URL</div>

            <a href="{{ $item->short_url }}" target="_blank"
               class="d-block text-truncate font-14 font-weight-bold text-primary"
               style="text-decoration: none;">
                {{ $item->short_url }}
            </a>

            <button type="button" class="btn btn-sm btn-outline-primary mt-2 px-3 py-1"
                    style="font-size: 12px;"
                    onclick="navigator.clipboard.writeText('{{ $item->short_url }}'); this.textContent='Copied!'; setTimeout(() => this.textContent='Copy Link', 1500);">
                Copy Link
            </button>
        </div>
    </div>
@endif
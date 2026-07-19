{{--
    Usage (booking form mein):
        @include('admin.partials.qr-toggle-section', [
            'item'          => $editBooking ?? null,
            'regenerateUrl' => !empty($editBooking) ? getAdminPanelUrl('/booking/'.$editBooking->id.'/qr/regenerate') : null,
        ])

    Baad mein Product/Course/Bundle admin forms mein bhi isi partial ko reuse karna,
    bas 'item' aur 'regenerateUrl' apne route ke mutabiq change karna.
--}}
@php
    $qrEnabled    = old('qr_enabled', !empty($item) ? (bool) $item->qr_enabled : false);
    $hasShortCode = !empty($item) && !empty($item->short_code);
@endphp

<div class="booking-section" id="section-qr-code">
    <h3 class="booking-section-title">QR Code & Short Link</h3>

    <div class="row">
        <div class="col-12 col-lg-6">
            <div class="form-group d-flex align-items-center">
                <div class="custom-control custom-switch">
                    <input type="hidden" name="qr_enabled" value="0">
                    <input type="checkbox" name="qr_enabled" id="qrEnabledSwitch"
                           value="1" class="custom-control-input"
                           {{ $qrEnabled ? 'checked' : '' }}>
                    <label class="custom-control-label" for="qrEnabledSwitch"></label>
                </div>
                <label for="qrEnabledSwitch" class="mb-0 ml-2" id="qrEnabledSwitchLabel">
                    {{ $qrEnabled
                        ? 'Enable and Show QR Code & Short URL Creation'
                        : 'Disable and Hide QR Code & Short URL Creation' }}
                </label>
            </div>

            @if($qrEnabled && $hasShortCode)
                <div class="mt-2" id="qrRegenerateBlock">
                    <div class="text-danger text-small mb-2">
                        BEWARE: You will not be able to see the old QR Code and Short URL!
                    </div>
                    <a href="{{ $regenerateUrl ?? '#' }}" class="btn btn-sm btn-primary">
                        Re-Creation
                    </a>
                </div>

                <div class="mt-3">
                    <img src="{{ $item->qr_image_url }}" alt="QR Code" width="120">
                    <div class="text-muted text-small mt-1">{{ $item->short_url }}</div>
                </div>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    var qrSwitch = document.getElementById('qrEnabledSwitch');
    var qrLabel  = document.getElementById('qrEnabledSwitchLabel');
    var regenBlock = document.getElementById('qrRegenerateBlock');

    if (qrSwitch && qrLabel) {
        qrSwitch.addEventListener('change', function () {
            qrLabel.textContent = this.checked
                ? 'Enable and Show QR Code & Short URL Creation'
                : 'Disable and Hide QR Code & Short URL Creation';

            // Agar user abhi checkbox ko uncheck kare, regenerate warning/button chhupa do
            // (kyunki save hone ke baad qr_enabled false ho jayega aur QR disable ho jayegi)
            if (regenBlock) {
                regenBlock.style.display = this.checked ? '' : 'none';
            }
        });
    }
})();
</script>
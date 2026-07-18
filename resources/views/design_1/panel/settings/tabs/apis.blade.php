@php
    $erpImportExport  = $erpImportExport  ?? null;
    $erpDropshipping  = $erpDropshipping  ?? null;
    $erpChecklistKeys = $erpChecklistKeys ?? \App\Models\ErpCredential::CHECKLIST_KEYS;

    $checklistLabels = [
        'dropship_price'           => 'Dropship Price',
        'stock_availability'       => 'Stock Availability',
        'product_approval_status'  => 'Product Approval Status',
        'product_images'           => 'Product Images',
        'shipping_rules'           => 'Shipping Rules',
        'feed_refresh_frequency'   => 'Feed Refresh Frequency',
        'tracking_order'           => 'Tracking Order',
        'tickets_complaints'       => 'Tickets/Complaints',
    ];
@endphp

<style>
    .erp-panel-block { border:1px solid #e5e9f0; border-radius:12px; padding:22px; background:#f8fafc; margin-bottom:20px; }
    .erp-panel-title { font-weight:700; font-size:15px; margin-bottom:14px; display:flex; align-items:center; justify-content:space-between; }
    .erp-checklist { display:grid; grid-template-columns: 1fr 1fr; gap:6px 16px; margin: 12px 0; }
    .erp-checklist label { font-size: 13.5px; display:flex; align-items:center; gap:8px; margin-bottom:0; }
    .erp-key-field { font-family: monospace; font-size: 12.5px; }
    .erp-panel-badge { font-size:12.5px; font-weight:600; padding:4px 12px; border-radius:999px; }
    .erp-badge-on  { background:#ecfdf3; color:#16a34a; }
    .erp-badge-off { background:#f1f5f9; color:#64748b; }
</style>

<div class="p-16">
    <div class="row">
        {{-- ================= IMPORT / EXPORT ================= --}}
        <div class="col-12 col-md-6">
            <div class="erp-panel-block" id="erp-block-import_export">
                <div class="erp-panel-title">
                    <span>API Credentials — Import / Export</span>
                    <span class="erp-panel-badge {{ $erpImportExport->is_active ? 'erp-badge-on' : 'erp-badge-off' }}">
                        {{ $erpImportExport->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="form-group">
                    <label>API Base URL</label>
                    <input type="text" class="form-control erp-base-url" value="{{ $erpImportExport->base_url }}">
                </div>

                <div class="form-group">
                    <label>API Key</label>
                    <input type="text" class="form-control erp-key-field" readonly
                           value="{{ $erpImportExport->api_key ?? '— not generated —' }}">
                </div>

                <div class="custom-control custom-switch mb-2">
                    <input type="checkbox" class="custom-control-input erp-export-ability" id="p_ie_export"
                           {{ $erpImportExport->export_ability_enabled ? 'checked' : '' }}>
                    <label class="custom-control-label" for="p_ie_export">Export Ability</label>
                </div>

                <div class="erp-checklist">
                    @foreach($erpChecklistKeys as $key)
                        <label>
                            <input type="checkbox" class="erp-checklist-item" data-key="{{ $key }}"
                                   {{ !empty($erpImportExport->checklist[$key]) ? 'checked' : '' }}>
                            {{ $checklistLabels[$key] ?? ucwords(str_replace('_',' ',$key)) }}
                        </label>
                    @endforeach
                </div>

                <div class="form-group">
                    <label>Rate Limiting (req/min)</label>
                    <input type="number" class="form-control erp-rate-limit" min="1" max="6000"
                           value="{{ $erpImportExport->rate_limit_per_minute ?? 60 }}">
                </div>

                <div class="d-flex align-items-center gap-8 mt-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="erpSave('import_export')">Save Changes</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="erpRegenerateKey('import_export')">Regenerate Key</button>
                    <div class="custom-control custom-switch ml-auto mb-0">
                        <input type="checkbox" class="custom-control-input" id="p_ie_subscribe"
                               onchange="erpToggleSubscription('import_export', this.checked)"
                               {{ $erpImportExport->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="p_ie_subscribe">Subscribe</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ================= DROPSHIPPERS ================= --}}
        <div class="col-12 col-md-6">
            <div class="erp-panel-block" id="erp-block-dropshipping">
                <div class="erp-panel-title">
                    <span>API Credentials — for DropShippers</span>
                    <span class="erp-panel-badge {{ $erpDropshipping->is_active ? 'erp-badge-on' : 'erp-badge-off' }}">
                        {{ $erpDropshipping->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <div class="form-group">
                    <label>API Base URL</label>
                    <input type="text" class="form-control erp-base-url" value="{{ $erpDropshipping->base_url }}">
                </div>

                <div class="form-group">
                    <label>API Key</label>
                    <input type="text" class="form-control erp-key-field" readonly
                           value="{{ $erpDropshipping->api_key ?? '— not generated —' }}">
                </div>

                <div class="custom-control custom-switch mb-2">
                    <input type="checkbox" class="custom-control-input erp-export-ability" id="p_ds_import"
                           {{ $erpDropshipping->import_dropshipping_enabled ? 'checked' : '' }}>
                    <label class="custom-control-label" for="p_ds_import">Import Dropshipping Enabled</label>
                </div>

                <div class="erp-checklist">
                    @foreach($erpChecklistKeys as $key)
                        <label>
                            <input type="checkbox" class="erp-checklist-item" data-key="{{ $key }}"
                                   {{ !empty($erpDropshipping->checklist[$key]) ? 'checked' : '' }}>
                            {{ $checklistLabels[$key] ?? ucwords(str_replace('_',' ',$key)) }}
                        </label>
                    @endforeach
                </div>

                <div class="form-group">
                    <label>Rate Limiting (req/min)</label>
                    <input type="number" class="form-control erp-rate-limit" min="1" max="6000"
                           value="{{ $erpDropshipping->rate_limit_per_minute ?? 60 }}">
                </div>

                <div class="d-flex align-items-center gap-8 mt-2">
                    <button type="button" class="btn btn-primary btn-sm" onclick="erpSave('dropshipping')">Save Changes</button>
                    <button type="button" class="btn btn-outline-secondary btn-sm" onclick="erpRegenerateKey('dropshipping')">Regenerate Key</button>
                    <div class="custom-control custom-switch ml-auto mb-0">
                        <input type="checkbox" class="custom-control-input" id="p_ds_subscribe"
                               onchange="erpToggleSubscription('dropshipping', this.checked)"
                               {{ $erpDropshipping->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="p_ds_subscribe">Subscribe</label>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function erpCsrf() {
        return document.querySelector('meta[name="csrf-token"]')?.content;
    }

    function erpPost(url, data) {
        return fetch(url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', 'X-CSRF-TOKEN': erpCsrf() },
            body: JSON.stringify(data || {}),
        }).then(async (res) => {
            const body = await res.json().catch(() => ({}));
            if (!res.ok) throw new Error(body.message || 'Request failed');
            return body;
        });
    }

    function erpCollect(type) {
        const block = document.getElementById('erp-block-' + type);
        const checklist = {};
        block.querySelectorAll('.erp-checklist-item').forEach((el) => { checklist[el.dataset.key] = el.checked; });

        const payload = {
            base_url: block.querySelector('.erp-base-url').value,
            rate_limit_per_minute: block.querySelector('.erp-rate-limit').value,
            checklist: checklist,
        };

        if (type === 'import_export') {
            payload.export_ability_enabled = block.querySelector('.erp-export-ability').checked;
        } else {
            payload.import_dropshipping_enabled = block.querySelector('.erp-export-ability').checked;
        }

        return payload;
    }

    function erpSave(type) {
        erpPost('{{ url("/panel/setting/erp") }}/' + type + '/save', erpCollect(type))
            .then(() => window.location.reload())
            .catch((err) => alert(err.message));
    }

    function erpRegenerateKey(type) {
        erpPost('{{ url("/panel/setting/erp") }}/' + type + '/regenerate-key', {})
            .then(() => window.location.reload())
            .catch((err) => alert(err.message));
    }

    function erpToggleSubscription(type, active) {
        erpPost('{{ url("/panel/setting/erp") }}/' + type + '/toggle-subscription', { active: active })
            .then(() => window.location.reload())
            .catch((err) => alert(err.message));
    }
</script>

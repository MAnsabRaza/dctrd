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
    .erp-block {
        border: 1px solid #e5e9f0;
        border-radius: 12px;
        padding: 24px;
        background: #f8fafc;
        margin-bottom: 20px;
    }
    .erp-block-title { font-weight: 700; font-size: 15px; margin-bottom: 14px; display:flex; align-items:center; justify-content:space-between; }
    .erp-key-row { display:flex; gap:8px; }
    .erp-key-row input { font-family: monospace; font-size: 12.5px; }
    .erp-checklist { display:grid; grid-template-columns: 1fr 1fr; gap:6px 16px; margin: 12px 0; }
    .erp-checklist label { font-size: 13.5px; display:flex; align-items:center; gap:8px; margin-bottom:0; }
</style>

<div class="tab-pane mt-3 fade {{ (request()->get('tab') == 'apis') ? 'active show' : '' }}"
     id="apis" role="tabpanel" aria-labelledby="apis-tab">

    <div class="row">
        {{-- ================= IMPORT / EXPORT ================= --}}
        <div class="col-12 col-lg-6">
            <div class="erp-block">
                <div class="erp-block-title">
                    <span>API Credentials — Import / Export</span>
                    <span class="badge {{ $erpImportExport->is_active ? 'badge-success' : 'badge-secondary' }}">
                        {{ $erpImportExport->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <form method="POST" action="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/erp/import_export/save">
                    @csrf
                    <div class="form-group">
                        <label>API Base URL</label>
                        <input type="text" name="base_url" class="form-control"
                               value="{{ $erpImportExport->base_url }}"
                               placeholder="https://erp.example.com">
                    </div>

                    <div class="form-group">
                        <label>API Key</label>
                        <div class="erp-key-row">
                            <input type="text" class="form-control" readonly
                                   value="{{ $erpImportExport->api_key ?? '— not generated —' }}">
                        </div>
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" name="is_active" value="1"
                               id="ie_active" {{ $erpImportExport->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ie_active">Status (subscribe to ERP access)</label>
                    </div>

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" name="export_ability_enabled" value="1"
                               id="ie_export" {{ $erpImportExport->export_ability_enabled ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ie_export">Export Ability</label>
                    </div>

                    <div class="erp-checklist">
                        @foreach($erpChecklistKeys as $key)
                            <label>
                                <input type="checkbox" name="checklist[{{ $key }}]" value="1"
                                       {{ !empty($erpImportExport->checklist[$key]) ? 'checked' : '' }}>
                                {{ $checklistLabels[$key] ?? ucwords(str_replace('_',' ',$key)) }}
                            </label>
                        @endforeach
                    </div>

                    <div class="form-group">
                        <label>Rate Limiting (requests / minute)</label>
                        <input type="number" name="rate_limit_per_minute" class="form-control" min="1" max="6000"
                               value="{{ $erpImportExport->rate_limit_per_minute ?? 60 }}">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">{{ trans('admin/main.submit') }}</button>
                </form>

                <form method="POST"
                      action="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/erp/import_export/regenerate-key"
                      class="d-inline mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm mt-2">Regenerate API Key</button>
                </form>
            </div>
        </div>

        {{-- ================= DROPSHIPPERS ================= --}}
        <div class="col-12 col-lg-6">
            <div class="erp-block">
                <div class="erp-block-title">
                    <span>API Credentials — for DropShippers</span>
                    <span class="badge {{ $erpDropshipping->is_active ? 'badge-success' : 'badge-secondary' }}">
                        {{ $erpDropshipping->is_active ? 'Active' : 'Inactive' }}
                    </span>
                </div>

                <form method="POST" action="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/erp/dropshipping/save">
                    @csrf
                    <div class="form-group">
                        <label>API Base URL</label>
                        <input type="text" name="base_url" class="form-control"
                               value="{{ $erpDropshipping->base_url }}"
                               placeholder="https://supplier-feed.example.com">
                    </div>

                    <div class="form-group">
                        <label>API Key</label>
                        <input type="text" class="form-control" readonly
                               value="{{ $erpDropshipping->api_key ?? '— not generated —' }}">
                    </div>

                    <div class="custom-control custom-switch mb-3">
                        <input type="checkbox" class="custom-control-input" name="is_active" value="1"
                               id="ds_active" {{ $erpDropshipping->is_active ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ds_active">Status (subscribe to ERP access)</label>
                    </div>

                    <div class="custom-control custom-switch mb-2">
                        <input type="checkbox" class="custom-control-input" name="import_dropshipping_enabled" value="1"
                               id="ds_import" {{ $erpDropshipping->import_dropshipping_enabled ? 'checked' : '' }}>
                        <label class="custom-control-label" for="ds_import">Import Dropshipping Enabled</label>
                    </div>

                    <div class="erp-checklist">
                        @foreach($erpChecklistKeys as $key)
                            <label>
                                <input type="checkbox" name="checklist[{{ $key }}]" value="1"
                                       {{ !empty($erpDropshipping->checklist[$key]) ? 'checked' : '' }}>
                                {{ $checklistLabels[$key] ?? ucwords(str_replace('_',' ',$key)) }}
                            </label>
                        @endforeach
                    </div>

                    <div class="form-group">
                        <label>Rate Limiting (requests / minute)</label>
                        <input type="number" name="rate_limit_per_minute" class="form-control" min="1" max="6000"
                               value="{{ $erpDropshipping->rate_limit_per_minute ?? 60 }}">
                    </div>

                    <button type="submit" class="btn btn-primary btn-sm">{{ trans('admin/main.submit') }}</button>
                </form>

                <form method="POST"
                      action="{{ getAdminPanelUrl() }}/users/{{ $user->id }}/erp/dropshipping/regenerate-key"
                      class="d-inline mt-2">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary btn-sm mt-2">Regenerate API Key</button>
                </form>
            </div>
        </div>
    </div>
</div>

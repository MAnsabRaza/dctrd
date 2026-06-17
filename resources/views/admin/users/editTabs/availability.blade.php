{{-- resources/views/admin/user/edittab/availability.blade.php --}}

@php
    use App\Models\OrgAvailabilityRule;
    use App\Models\OrgAvailabilityRange;

    $currentId  = $orgId ?? ($user->id ?? null);
    $rangeTypes = ['custom', 'daily', 'weekly', 'monthly', 'date_range'];

    if (!$currentId) {
        throw new \Exception('Availability partial requires orgId or user.id');
    }

    $rule = $rule ?? OrgAvailabilityRule::firstOrNew(
        ['org_id' => $currentId],
        [
            'availability_mode'                => 'available_by_default',
            'product_specific_takes_precedence' => false,
            'make_all_unavailable_by_default'  => false,
        ]
    );

    $ranges = $ranges ?? OrgAvailabilityRange::where('org_id', $currentId)->orderBy('id')->get();
    $assets = $assets ?? collect();
    $assetRanges = $assetRanges ?? collect();

    $saveRoute    = route('admin.users.availability.save', ['id' => $currentId]);
    $deleteRowUrl = url("admin/users/{$currentId}/availability/row/delete");
@endphp

@push('styles')
<style>
/* ── Availability Toggle Switch ────────────────────────────────── */
.availability-toggle {
    display: inline-block;
    width: 44px;
    height: 24px;
    border-radius: 12px;
    background: #ccc;
    position: relative;
    cursor: pointer;
    transition: background 0.25s;
    vertical-align: middle;
}
.availability-toggle::after {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 18px; height: 18px;
    border-radius: 50%;
    background: #fff;
    transition: left 0.25s;
    box-shadow: 0 1px 3px rgba(0,0,0,.25);
}
.availability-toggle.is-on {
    background: #3B82F6;
}
.availability-toggle.is-on::after {
    left: 23px;
}

/* ── Table & Layout Resets ────────────────────────────────────── */
.avail-table th {
    font-size: 0.85rem;
    font-weight: 600;
    color: #374151;
    background: #f9fafb;
}
.avail-table td {
    vertical-align: middle;
}
.btn-remove-range, .btn-remove-asset {
    line-height: 1;
    padding: 0.2rem 0.5rem;
    font-size: 1.1rem;
}

/* ── UI Sections (Strictly match provided image mockup) ───────── */
.avail-section {
    background: #fff;
    padding: 1.5rem 1.5rem;
    margin-bottom: 2rem;
    border-bottom: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    box-shadow: 0 1px 2px rgba(15, 23, 42, 0.04);
}
.avail-section:last-of-type {
    border-bottom: none;
}
.avail-section-title {
    font-size: 1.05rem;
    font-weight: 700;
    margin-bottom: 0.7rem;
    color: #0f172a;
}
.avail-section-desc {
    font-size: 0.9rem;
    color: #475569;
    margin-bottom: 1rem;
    line-height: 1.7;
}
.asset-usecases {
    font-size: 0.87rem;
    color: #475569;
    padding-left: 1.25rem;
    margin-bottom: 1rem;
    line-height: 1.7;
}
.avail-save-btn {
    min-width: 140px;
    font-size: 0.9rem;
}
#availToast {
    position: fixed;
    bottom: 1.5rem; right: 1.5rem;
    z-index: 9999;
    min-width: 280px;
    display: none;
}
</style>
@endpush

<div class="tab-pane mt-3 fade {{ (request()->get('tab') == 'availability') ? 'active show' : '' }}" id="availability" role="tabpanel" aria-labelledby="availability-tab">
    <div class="container-fluid py-3" style="max-width:1000px">

    {{-- SECTION 1 · Global Availability --}}
    <div class="avail-section">
        <h2 class="avail-section-title">Global Availability</h2>
        <div class="avail-section-desc">
            Define global availability rules here. These will reflect in all the bookable products and can be overridden at a product level.<br>
            There are two ways you can set the availability rules:<br>
            <ol class="mt-2 mb-0">
                <li>All dates are available by default. Create rules to set the time period when you are not available to take bookings.</li>
                <li class="font-weight-bold">OR</li>
                <li>Mark all the dates/blocks as unavailable and then set the time period when you are available for bookings.</li>
            </ol>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox" name="make_all_unavailable_by_default" id="chkMakeAllUnavailable" value="1" {{ $rule->make_all_unavailable_by_default ? 'checked' : '' }}>
            <label class="form-check-label text-muted" for="chkMakeAllUnavailable" style="font-size:0.875rem">
                Make all dates/blocks unavailable by default
            </label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="product_specific_takes_precedence" id="chkProductPrecedence" value="1" {{ $rule->product_specific_takes_precedence ? 'checked' : '' }}>
            <label class="form-check-label text-muted" for="chkProductPrecedence" style="font-size:0.875rem">
                Enable this to ensure product-specific availability settings take precedence. Global availability rules will only apply if no product-specific rule exists for a given time slot.
            </label>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered avail-table mb-0" id="globalRangeTable">
                <thead>
                    <tr>
                        <th style="width: 25%;">Range Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th class="text-center" style="width:120px">Bookable</th>
                        <th style="width:60px"></th>
                    </tr>
                </thead>
                <tbody id="globalRangeTableBody">
                    @foreach($ranges as $i => $range)
                        @php
                            $selType  = $range->range_type ?? 'custom';
                            $fromDate = $range->from_date ? \Carbon\Carbon::parse($range->from_date)->format('Y-m-d') : '';
                            $toDate   = $range->to_date ? \Carbon\Carbon::parse($range->to_date)->format('Y-m-d') : '';
                            $bookable = $range->bookable ?? true;
                        @endphp
                        <tr class="availability-range-row" data-id="{{ $range->id }}">
                            <td>
                                <select name="ranges[{{ $i }}][range_type]" class="form-control form-control-sm range-type-select">
                                    @foreach($rangeTypes as $rt)
                                        <option value="{{ $rt }}" @selected($selType === $rt)>
                                            {{ ucfirst(str_replace('_', ' ', $rt)) }}
                                        </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="date" name="ranges[{{ $i }}][from_date]" value="{{ $fromDate }}" class="form-control form-control-sm from-date-input" required>
                            </td>
                            <td>
                                <input type="date" name="ranges[{{ $i }}][to_date]" value="{{ $toDate }}" class="form-control form-control-sm to-date-input" required>
                            </td>
                            <td class="text-center">
                                <label class="availability-toggle-label mb-0">
                                    <input type="hidden" name="ranges[{{ $i }}][bookable]" value="0">
                                    <input type="checkbox" name="ranges[{{ $i }}][bookable]" value="1" class="bookable-checkbox d-none" @checked($bookable)>
                                    <span class="availability-toggle {{ $bookable ? 'is-on' : '' }}"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-clean text-danger btn-remove-range" data-id="{{ $range->id }}">&times;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-4">
            <button type="button" class="btn btn-sm btn-light-primary btn-outline-primary" id="btnAddGlobalRange">+ Add</button>
        </div>

        <div>
            <button type="button" class="btn btn-primary btn-sm avail-save-btn" id="globalSaveBtn">
                <span class="save-label">Save changes</span>
                <span class="save-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving…</span>
            </button>
        </div>
    </div>

    {{-- SECTION 2 · Asset --}}
    <div class="avail-section">
        <h2 class="avail-section-title">Asset</h2>
        <div class="avail-section-desc">
            Assets are global resources which can be attached either to a single product or to multiple products. Assets have a quantity and availability attached.<br>
            <strong class="d-block mt-2">Use assets in following cases:</strong>
        </div>
        
        <ul class="asset-usecases">
            <li>When Product A is booked, you need product B to be automatically booked for the same time.</li>
            <li>When you have a staff that handles multiple services. When service A is booked, other services from the same staff are unavailable to book for the same time.</li>
            <li>When you have multiple staff with varying availability or price.</li>
            <li>When you have multiple resource types with different quantities, for Eg: types of Kayaks to be chosen by the user to book.</li>
        </ul>
        <div class="avail-section-desc">
            Refer <a href="#" class="text-primary font-weight-bold">Documentation</a> for further details.
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered avail-table mb-0" id="assetTable">
                <thead>
                    <tr>
                        <th>Assets Name</th>
                        <th style="width: 250px;">Quantity</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody id="assetTableBody">
                    @foreach($assets ?? [] as $ai => $asset)
                        <tr class="asset-name-row" data-id="{{ $asset->id }}">
                            <td>
                                <input type="text" name="assets[{{ $ai }}][name]" value="{{ $asset->name }}" class="form-control form-control-sm asset-name-input" placeholder="Asset Name">
                            </td>
                            <td>
                                <input type="number" name="assets[{{ $ai }}][quantity]" value="{{ $asset->quantity ?? 1 }}" min="1" class="form-control form-control-sm">
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-clean text-danger btn-remove-asset" data-id="{{ $asset->id }}">&times;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div>
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddAsset">+ Add</button>
        </div>
    </div>

    {{-- SECTION 3 · Asset Availability --}}
    <div class="avail-section">
        <h2 class="avail-section-title">Asset Availability</h2>
        <div class="avail-section-desc">Define availability for each asset.</div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="make_all_assets_unavailable_by_default" id="chkMakeAllAssetsUnavailable" value="1" {{ ($rule->make_all_assets_unavailable_by_default ?? false) ? 'checked' : '' }}>
            <label class="form-check-label text-muted" for="chkMakeAllAssetsUnavailable" style="font-size:0.875rem">
                Make all Assets unavailable by default
            </label>
        </div>

        <div class="table-responsive mb-3">
            <table class="table table-bordered avail-table mb-0" id="assetAvailTable">
                <thead>
                    <tr>
                        <th style="width: 20%;">Asset</th>
                        <th style="width: 20%;">Range Type</th>
                        <th>From</th>
                        <th>To</th>
                        <th class="text-center" style="width:120px">Bookable</th>
                        <th style="width: 60px;"></th>
                    </tr>
                </thead>
                <tbody id="assetAvailTableBody">
                    @foreach($assetRanges ?? [] as $ari => $arange)
                        @php
                            $selType  = $arange->range_type ?? 'custom';
                            $fromDate = $arange->from_date ? \Carbon\Carbon::parse($arange->from_date)->format('Y-m-d') : '';
                            $toDate   = $arange->to_date ? \Carbon\Carbon::parse($arange->to_date)->format('Y-m-d') : '';
                            $bookable = $arange->bookable ?? true;
                            $assetId  = $arange->asset_id ?? '';
                        @endphp
                        <tr class="availability-range-row" data-id="{{ $arange->id }}">
                            <td>
                                <select name="asset_ranges[{{ $ari }}][asset_id]" class="form-control form-control-sm asset-id-select">
                                    <option value="">— select asset —</option>
                                    @foreach($assets ?? [] as $asset)
                                        <option value="{{ $asset->id }}" @selected($assetId == $asset->id)>{{ $asset->name }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="asset_ranges[{{ $ari }}][range_type]" class="form-control form-control-sm range-type-select">
                                    @foreach($rangeTypes as $rt)
                                        <option value="{{ $rt }}" @selected($selType === $rt)>{{ ucfirst(str_replace('_', ' ', $rt)) }}</option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <input type="date" name="asset_ranges[{{ $ari }}][from_date]" value="{{ $fromDate }}" class="form-control form-control-sm from-date-input" required>
                            </td>
                            <td>
                                <input type="date" name="asset_ranges[{{ $ari }}][to_date]" value="{{ $toDate }}" class="form-control form-control-sm to-date-input" required>
                            </td>
                            <td class="text-center">
                                <label class="availability-toggle-label mb-0">
                                    <input type="hidden" name="asset_ranges[{{ $ari }}][bookable]" value="0">
                                    <input type="checkbox" name="asset_ranges[{{ $ari }}][bookable]" value="1" class="bookable-checkbox d-none" @checked($bookable)>
                                    <span class="availability-toggle {{ $bookable ? 'is-on' : '' }}"></span>
                                </label>
                            </td>
                            <td class="text-center">
                                <button type="button" class="btn btn-sm btn-clean text-danger btn-remove-range" data-id="{{ $arange->id }}">&times;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-4">
            <button type="button" class="btn btn-sm btn-outline-primary" id="btnAddAssetAvail">+ Add</button>
        </div>

        <div>
            <button type="button" class="btn btn-primary btn-sm avail-save-btn" id="assetSaveBtn">
                <span class="save-label">Save changes</span>
                <span class="save-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving…</span>
            </button>
        </div>
    </div>
</div>

<div id="availToast" class="alert mb-0" role="alert"></div>

@push('scripts')
<script>
(function () {
    'use strict';

    const SAVE_URL      = @json($saveRoute);
    const DELETE_BASE   = @json($deleteRowUrl);
    const CSRF          = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
    const RANGE_TYPES   = @json($rangeTypes);

    function showToast(msg, isError = false) {
        const el = document.getElementById('availToast');
        el.className = 'alert mb-0 ' + (isError ? 'alert-danger' : 'alert-success');
        el.textContent = msg;
        el.style.display = 'block';
        setTimeout(() => { el.style.display = 'none'; }, 3500);
    }

    function setBusy(btnId, busy) {
        const btn = document.getElementById(btnId);
        if (!btn) return;
        btn.querySelector('.save-label').classList.toggle('d-none', busy);
        btn.querySelector('.save-spinner').classList.toggle('d-none', !busy);
        btn.disabled = busy;
    }

    function bindToggle(row) {
        const toggle   = row.querySelector('.availability-toggle');
        const checkbox = row.querySelector('.bookable-checkbox');
        if (!toggle || !checkbox) return;
        toggle.addEventListener('click', () => {
            checkbox.checked = !checkbox.checked;
            toggle.classList.toggle('is-on', checkbox.checked);
        });
    }

    document.querySelectorAll('.availability-range-row').forEach(bindToggle);

    function bindRemoveBtn(row) {
        const btn = row.querySelector('.btn-remove-range');
        if (!btn) return;
        btn.addEventListener('click', async () => {
            const rowId = btn.dataset.id;
            if (rowId) {
                try {
                    const res = await fetch(`${DELETE_BASE}/${rowId}`, {
                        method: 'POST',
                        headers: { 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' }
                    });
                    const data = await res.json();
                    if (!data.success) {
                        showToast(data.message ?? 'Error deleting row', true);
                        return;
                    }
                } catch (e) {
                    showToast('Error deleting row', true);
                    return;
                }
            }
            row.remove();
        });
    }

    document.querySelectorAll('.availability-range-row').forEach(bindRemoveBtn);

    let globalRowCounter = {{ $ranges->count() }};
    document.getElementById('btnAddGlobalRange').addEventListener('click', () => {
        const tbody = document.getElementById('globalRangeTableBody');
        const idx = globalRowCounter++;
        
        const rtOptions = RANGE_TYPES.map(rt =>
            `<option value="${rt}">${rt.charAt(0).toUpperCase() + rt.slice(1).replace('_',' ')}</option>`
        ).join('');

        const tr = document.createElement('tr');
        tr.className = 'availability-range-row';
        tr.innerHTML = `
            <td><select name="ranges[${idx}][range_type]" class="form-control form-control-sm range-type-select">${rtOptions}</select></td>
            <td><input type="date" name="ranges[${idx}][from_date]" class="form-control form-control-sm from-date-input" required></td>
            <td><input type="date" name="ranges[${idx}][to_date]" class="form-control form-control-sm to-date-input" required></td>
            <td class="text-center">
                <label class="availability-toggle-label mb-0">
                    <input type="hidden" name="ranges[${idx}][bookable]" value="0">
                    <input type="checkbox" name="ranges[${idx}][bookable]" value="1" class="bookable-checkbox d-none" checked>
                    <span class="availability-toggle is-on"></span>
                </label>
            </td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-clean text-danger btn-remove-range" data-id="">&times;</button></td>`;
        tbody.appendChild(tr);
        bindToggle(tr);
        bindRemoveBtn(tr);
    });

    let assetCounter = {{ count($assets ?? []) }};
    document.getElementById('btnAddAsset').addEventListener('click', () => {
        const tbody = document.getElementById('assetTableBody');
        const idx   = assetCounter++;
        const tr    = document.createElement('tr');
        tr.className = 'asset-name-row';
        tr.innerHTML = `
            <td><input type="text" name="assets[${idx}][name]" class="form-control form-control-sm asset-name-input" placeholder="Asset Name"></td>
            <td><input type="number" name="assets[${idx}][quantity]" value="1" min="1" class="form-control form-control-sm"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-clean text-danger btn-remove-asset" data-id="">&times;</button></td>`;
        tbody.appendChild(tr);
        tr.querySelector('.btn-remove-asset').addEventListener('click', () => tr.remove());
    });

    document.querySelectorAll('.asset-name-row').forEach(row => {
        row.querySelector('.btn-remove-asset')?.addEventListener('click', () => row.remove());
    });

    let assetAvailCounter = {{ count($assetRanges ?? []) }};
    document.getElementById('btnAddAssetAvail').addEventListener('click', () => {
        const tbody = document.getElementById('assetAvailTableBody');
        const idx   = assetAvailCounter++;
        const assetRows  = document.querySelectorAll('#assetTableBody .asset-name-row');
        let assetOptions = '<option value="">— select asset —</option>';
        
        assetRows.forEach(r => {
            const name = r.querySelector('.asset-name-input')?.value ?? '';
            const id   = r.dataset.id ?? '';
            if (name) assetOptions += `<option value="${id}">${name}</option>`;
        });

        const rtOptions = RANGE_TYPES.map(rt =>
            `<option value="${rt}">${rt.charAt(0).toUpperCase() + rt.slice(1).replace('_',' ')}</option>`
        ).join('');

        const tr = document.createElement('tr');
        tr.className = 'availability-range-row';
        tr.innerHTML = `
            <td><select name="asset_ranges[${idx}][asset_id]" class="form-control form-control-sm asset-id-select">${assetOptions}</select></td>
            <td><select name="asset_ranges[${idx}][range_type]" class="form-control form-control-sm range-type-select">${rtOptions}</select></td>
            <td><input type="date" name="asset_ranges[${idx}][from_date]" class="form-control form-control-sm from-date-input" required></td>
            <td><input type="date" name="asset_ranges[${idx}][to_date]" class="form-control form-control-sm to-date-input" required></td>
            <td class="text-center">
                <label class="availability-toggle-label mb-0">
                    <input type="hidden" name="asset_ranges[${idx}][bookable]" value="0">
                    <input type="checkbox" name="asset_ranges[${idx}][bookable]" value="1" class="bookable-checkbox d-none" checked>
                    <span class="availability-toggle is-on"></span>
                </label>
            </td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-clean text-danger btn-remove-range" data-id="">&times;</button></td>`;
        tbody.appendChild(tr);
        bindToggle(tr);
        bindRemoveBtn(tr);
    });

    function collectGlobalRanges() {
        return Array.from(document.querySelectorAll('#globalRangeTableBody .availability-range-row')).map(row => ({
            range_type : row.querySelector('.range-type-select')?.value ?? 'custom',
            from_date  : row.querySelector('.from-date-input')?.value ?? '',
            to_date    : row.querySelector('.to-date-input')?.value ?? '',
            bookable   : row.querySelector('.bookable-checkbox')?.checked ? 1 : 0,
        }));
    }

    async function sendPayload(payload, btnId) {
        setBusy(btnId, true);
        try {
            const res = await fetch(SAVE_URL, {
                method: 'POST',
                headers: { 'Content-Type' : 'application/json', 'X-CSRF-TOKEN' : CSRF, 'Accept' : 'application/json' },
                body: JSON.stringify(payload),
            });
            const data = await res.json();
            showToast(data.message ?? 'Saved successfully', !data.success);
        } catch (e) {
            showToast('Save failed', true);
        } finally {
            setBusy(btnId, false);
        }
    }

    document.getElementById('globalSaveBtn').addEventListener('click', () => {
        sendPayload({
            availability_mode: 'available_by_default',
            make_all_unavailable_by_default   : document.getElementById('chkMakeAllUnavailable')?.checked ? 1 : 0,
            product_specific_takes_precedence : document.getElementById('chkProductPrecedence')?.checked ? 1 : 0,
            ranges                            : collectGlobalRanges(),
        }, 'globalSaveBtn');
    });
})();
</script>
@endpush
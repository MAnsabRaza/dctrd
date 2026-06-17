{{-- resources/views/admin/user/edittab/availability.blade.php --}}
{{-- 
    Used by BOTH:
      - Panel:   GET /panel/setting/availability
      - Admin:   GET /admin/users/{id}/availability
--}}

@php
    // Dynamically check if we are in admin mode or look at controller variable
    $isAdmin      = $isAdmin ?? false;
    
    // Agar admin panel hai to $orgId use hoga, warna logged-in user ki ID
    $currentId    = $isAdmin ? ($orgId ?? null) : auth()->id(); 

    // Routes configuration based on panel type
    $saveRoute    = $isAdmin
        ? route('admin.users.availability.save', ['id' => $currentId])
        : route('panel.setting.availability.save');
        
    $deleteRowUrl = $isAdmin 
        ? url("admin/users/{$currentId}/availability/row/delete")
        : url("panel/setting/availability/row/delete"); 
        
    $addRowUrl    = $isAdmin
        ? route('admin.users.availability.addRow', ['id' => $currentId])
        : route('panel.setting.availability.addRow');

    $rangeTypes   = ['custom', 'daily', 'weekly', 'monthly', 'date_range'];
@endphp

@extends($isAdmin ? 'admin.layouts.app' : 'panel.layouts.app')

@section('title', trans('booking.availability_assets'))

@push('styles')
<style>
/* ── Availability Toggle ───────────────────────────────────────── */
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

/* ── Table ─────────────────────────────────────────────────────── */
.avail-table th {
    font-size: 0.82rem;
    font-weight: 600;
    color: #374151;
    background: #f9fafb;
    border-top: none;
}
.avail-table td {
    vertical-align: middle;
}
.btn-remove-range {
    line-height: 1;
    padding: 0.2rem 0.5rem;
    font-size: 1rem;
}

/* ── Section card ──────────────────────────────────────────────── */
.avail-section {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 0.5rem;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.5rem;
}
.avail-section-title {
    font-size: 1rem;
    font-weight: 700;
    margin-bottom: 0.3rem;
    color: #111827;
}
.avail-section-desc {
    font-size: 0.88rem;
    color: #374151;
    margin-bottom: 0.1rem;
    line-height: 1.6;
}
.avail-section-desc ol {
    margin-bottom: 0.25rem;
    padding-left: 1.2rem;
}

/* ── Bullet list for asset use-cases ──────────────────────────── */
.asset-usecases {
    font-size: 0.85rem;
    color: #374151;
    padding-left: 1.1rem;
    margin-bottom: 0.5rem;
    line-height: 1.7;
}

/* ── Save button ───────────────────────────────────────────────── */
.avail-save-btn {
    min-width: 130px;
    font-size: 0.875rem;
}

/* ── Toast ─────────────────────────────────────────────────────── */
#availToast {
    position: fixed;
    bottom: 1.5rem; right: 1.5rem;
    z-index: 9999;
    min-width: 260px;
    display: none;
}

/* ── Add row button ────────────────────────────────────────────── */
.btn-add-row {
    font-size: 0.85rem;
    color: #3B82F6;
    border-color: #3B82F6;
    padding: 0.25rem 0.85rem;
}
.btn-add-row:hover {
    background: #eff6ff;
    color: #1d4ed8;
}

/* ── Asset name input row ──────────────────────────────────────── */
.asset-name-input {
    border: none;
    border-bottom: 1px solid #d1d5db;
    border-radius: 0;
    background: transparent;
    padding-left: 0;
    font-size: 0.88rem;
}
.asset-name-input:focus {
    outline: none;
    box-shadow: none;
    border-bottom-color: #3B82F6;
}
</style>
@endsection

@section('content')

<div class="container-fluid py-4" style="max-width:960px">

    {{-- SECTION 1 · Global Availability --}}
    <div class="avail-section">
        <p class="avail-section-title">{{ trans('booking.global_availability') }}</p>

        <div class="avail-section-desc mb-2">
            {{ trans('booking.global_availability_desc') }}<br>
            {{ trans('booking.global_availability_desc2') }}
        </div>

        <div class="avail-section-desc mb-3">
            <ol style="margin-bottom:0">
                <li>{{ trans('booking.global_availability_rule_1') }}</li>
                <li>{{ trans('booking.global_availability_rule_2') }}</li>
            </ol>
        </div>

        <div class="form-check mb-2">
            <input class="form-check-input" type="checkbox"
                   name="make_all_unavailable_by_default"
                   id="chkMakeAllUnavailable"
                   value="1"
                   {{ $rule->make_all_unavailable_by_default ? 'checked' : '' }}>
            <label class="form-check-label" for="chkMakeAllUnavailable" style="font-size:0.875rem">
                {{ trans('booking.make_all_unavailable_by_default') }}
            </label>
        </div>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox"
                   name="product_specific_takes_precedence"
                   id="chkProductPrecedence"
                   value="1"
                   {{ $rule->product_specific_takes_precedence ? 'checked' : '' }}>
            <label class="form-check-label" for="chkProductPrecedence" style="font-size:0.875rem">
                {{ trans('booking.product_specific_takes_precedence') }}
            </label>
        </div>

        <div class="table-responsive mb-2">
            <table class="table table-bordered avail-table mb-0" id="globalRangeTable">
                <thead>
                    <tr>
                        <th style="min-width:160px">{{ trans('booking.range_type') }}</th>
                        <th style="min-width:150px">{{ trans('booking.from_date') }}</th>
                        <th style="min-width:150px">{{ trans('booking.to_date') }}</th>
                        <th class="text-center" style="width:110px">{{ trans('booking.bookable') }}</th>
                        <th class="text-center" style="width:70px"></th>
                    </tr>
                </thead>
                <tbody id="globalRangeTableBody">
                    @foreach($ranges as $i => $range)
                        @php
                            $selType  = $range->range_type ?? 'custom';
                            $fromDate = $range->from_date ? (\Carbon\Carbon::parse($range->from_date)->format('Y-m-d')) : '';
                            $toDate   = $range->to_date ? (\Carbon\Carbon::parse($range->to_date)->format('Y-m-d')) : '';
                            $bookable = $range->bookable ?? true;
                        @endphp
                        <tr class="availability-range-row" data-id="{{ $range->id ?? '' }}">
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
                                <button type="button" class="btn btn-sm btn-danger btn-remove-range" data-id="{{ $range->id ?? '' }}" title="{{ trans('booking.remove') }}">&times;</button>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mb-3">
            <button type="button" class="btn btn-outline-primary btn-sm btn-add-row" id="btnAddGlobalRange">
                + {{ trans('booking.add') }}
            </button>
        </div>

        <div>
            <button type="button" class="btn btn-primary btn-sm avail-save-btn" id="globalSaveBtn">
                <span class="save-label">{{ trans('booking.save_changes') }}</span>
                <span class="save-spinner d-none">
                    <span class="spinner-border spinner-border-sm me-1"></span>{{ trans('booking.saving') }}…
                </span>
            </button>
        </div>
    </div>

    {{-- SECTION 2 · Asset --}}
    <div class="avail-section">
        <p class="avail-section-title">{{ trans('booking.asset') }}</p>
        <p class="avail-section-desc mb-1">{{ trans('booking.asset_desc') }}</p>
        <p class="avail-section-desc mb-1">{{ trans('booking.asset_use_cases_label') }}</p>

        <ul class="asset-usecases">
            <li>{{ trans('booking.asset_use_case_1') }}</li>
            <li>{{ trans('booking.asset_use_case_2') }}</li>
            <li>{{ trans('booking.asset_use_case_3') }}</li>
            <li>{{ trans('booking.asset_use_case_4') }}</li>
        </ul>

        <table class="table table-bordered avail-table mb-2" id="assetTable">
            <thead>
                <tr>
                    <th>{{ trans('booking.assets_name') }}</th>
                    <th style="width:180px">{{ trans('booking.quantity') }}</th>
                    <th style="width:70px"></th>
                </tr>
            </thead>
            <tbody id="assetTableBody">
                @forelse($assets ?? [] as $ai => $asset)
                <tr class="asset-name-row" data-id="{{ $asset->id ?? '' }}">
                    <td>
                        <input type="text" name="assets[{{ $ai }}][name]" value="{{ $asset->name ?? '' }}" class="form-control form-control-sm asset-name-input" placeholder="{{ trans('booking.asset_name_placeholder') }}">
                    </td>
                    <td>
                        <input type="number" name="assets[{{ $ai }}][quantity]" value="{{ $asset->quantity ?? 1 }}" min="1" class="form-control form-control-sm">
                    </td>
                    <td class="text-center">
                        <button type="button" class="btn btn-sm btn-danger btn-remove-asset" data-id="{{ $asset->id ?? '' }}">&times;</button>
                    </td>
                </tr>
                @empty
                @endforelse
            </tbody>
        </table>

        <button type="button" class="btn btn-outline-primary btn-sm btn-add-row" id="btnAddAsset">+ {{ trans('booking.add') }}</button>
    </div>

    {{-- SECTION 3 · Asset Availability --}}
    <div class="avail-section">
        <p class="avail-section-title">{{ trans('booking.asset_availability') }}</p>
        <p class="avail-section-desc mb-2">{{ trans('booking.asset_availability_desc') }}</p>

        <div class="form-check mb-3">
            <input class="form-check-input" type="checkbox" name="make_all_assets_unavailable_by_default" id="chkMakeAllAssetsUnavailable" value="1" {{ ($rule->make_all_assets_unavailable_by_default ?? false) ? 'checked' : '' }}>
            <label class="form-check-label" for="chkMakeAllAssetsUnavailable" style="font-size:0.875rem">
                {{ trans('booking.make_all_assets_unavailable_by_default') }}
            </label>
        </div>

        <table class="table table-bordered avail-table mb-2" id="assetAvailTable">
            <thead>
                <tr>
                    <th style="min-width:140px">{{ trans('booking.asset') }}</th>
                    <th style="min-width:140px">{{ trans('booking.range_type') }}</th>
                    <th style="min-width:140px">{{ trans('booking.from_date') }}</th>
                    <th style="min-width:140px">{{ trans('booking.to_date') }}</th>
                    <th class="text-center" style="width:110px">{{ trans('booking.bookable') }}</th>
                    <th style="width:70px"></th>
                </tr>
            </thead>
            <tbody id="assetAvailTableBody">
                @forelse($assetRanges ?? [] as $ari => $arange)
                    @php
                        $selType  = $arange->range_type ?? 'custom';
                        $fromDate = $arange->from_date ? (\Carbon\Carbon::parse($arange->from_date)->format('Y-m-d')) : '';
                        $toDate   = $arange->to_date ? (\Carbon\Carbon::parse($arange->to_date)->format('Y-m-d')) : '';
                        $bookable = $arange->bookable ?? true;
                        $assetId  = $arange->asset_id ?? '';
                    @endphp
                    <tr class="availability-range-row" data-id="{{ $arange->id ?? '' }}">
                        <td>
                            <select name="asset_ranges[{{ $ari }}][asset_id]" class="form-control form-control-sm">
                                <option value="">— {{ trans('booking.select_asset') }} —</option>
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
                            <button type="button" class="btn btn-sm btn-danger btn-remove-range" data-id="{{ $arange->id ?? '' }}">&times;</button>
                        </td>
                    </tr>
                @empty
                @endforelse
            </tbody>
        </table>

        <button type="button" class="btn btn-outline-primary btn-sm btn-add-row" id="btnAddAssetAvail">+ {{ trans('booking.add') }}</button>
        <div class="mt-3">
            <button type="button" class="btn btn-primary btn-sm avail-save-btn" id="assetSaveBtn">
                <span class="save-label">{{ trans('booking.save_changes') }}</span>
                <span class="save-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>{{ trans('booking.saving') }}…</span>
            </button>
        </div>
    </div>
</div>

<div id="availToast" class="alert mb-0" role="alert"></div>
@endsection

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
                    const res  = await fetch(`${DELETE_BASE}/${rowId}`, {
                        method: 'DELETE',
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
            <td>
                <select name="ranges[${idx}][range_type]" class="form-control form-control-sm range-type-select">
                    ${rtOptions}
                </select>
            </td>
            <td><input type="date" name="ranges[${idx}][from_date]" class="form-control form-control-sm from-date-input" required></td>
            <td><input type="date" name="ranges[${idx}][to_date]" class="form-control form-control-sm to-date-input" required></td>
            <td class="text-center">
                <label class="availability-toggle-label mb-0">
                    <input type="hidden" name="ranges[${idx}][bookable]" value="0">
                    <input type="checkbox" name="ranges[${idx}][bookable]" value="1" class="bookable-checkbox d-none" checked>
                    <span class="availability-toggle is-on"></span>
                </label>
            </td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-range" data-id="">&times;</button></td>`;
        tbody.appendChild(tr);
        bindToggle(tr);
        bindRemoveBtn(tr);
    });

    let assetCounter = {{ isset($assets) ? count($assets) : 0 }};
    document.getElementById('btnAddAsset').addEventListener('click', () => {
        const tbody = document.getElementById('assetTableBody');
        const idx   = assetCounter++;
        const tr    = document.createElement('tr');
        tr.className = 'asset-name-row';
        tr.innerHTML = `
            <td><input type="text" name="assets[${idx}][name]" class="form-control form-control-sm asset-name-input" placeholder="{{ trans('booking.asset_name_placeholder') }}"></td>
            <td><input type="number" name="assets[${idx}][quantity]" value="1" min="1" class="form-control form-control-sm"></td>
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-asset" data-id="">&times;</button></td>`;
        tbody.appendChild(tr);
        tr.querySelector('.btn-remove-asset').addEventListener('click', () => tr.remove());
    });

    document.querySelectorAll('.asset-name-row').forEach(row => {
        row.querySelector('.btn-remove-asset')?.addEventListener('click', () => row.remove());
    });

    let assetAvailCounter = {{ isset($assetRanges) ? count($assetRanges) : 0 }};
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
            <td><select name="asset_ranges[${idx}][asset_id]" class="form-control form-control-sm">${assetOptions}</select></td>
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
            <td class="text-center"><button type="button" class="btn btn-sm btn-danger btn-remove-range" data-id="">&times;</button></td>`;
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

    document.getElementById('globalSaveBtn').addEventListener('click', async () => {
        setBusy('globalSaveBtn', true);
        const payload = {
            availability_mode: 'available_by_default',
            make_all_unavailable_by_default   : document.getElementById('chkMakeAllUnavailable')?.checked ? 1 : 0,
            product_specific_takes_precedence : document.getElementById('chkProductPrecedence')?.checked ? 1 : 0,
            ranges                            : collectGlobalRanges(),
        };
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
            setBusy('globalSaveBtn', false);
        }
    });
})();
</script>
@endsection
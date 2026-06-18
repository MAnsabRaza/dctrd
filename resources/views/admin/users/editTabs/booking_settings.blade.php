{{--
    resources/views/admin/users/editTabs/booking_settings.blade.php
    Included from admin/users/edit.blade.php as:
    @include('admin.users.editTabs.booking_settings')
    Controller must pass: $categoryTree, $saveUrl
--}}
@php
    $categoryTree = $categoryTree ?? [];
    $saveUrl      = $saveUrl      ?? '';
@endphp

@push('styles_bottom')
<style>
/* ── Shell ─────────────────────────────────────────────────────── */
.booking-settings-shell {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 1.25rem;
}
.booking-settings-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: .25rem;
}
.booking-settings-note {
    color: #64748b;
    font-size: .88rem;
    line-height: 1.6;
    margin-bottom: 1rem;
}

/* ── Sub-tabs ──────────────────────────────────────────────────── */
.booking-subtabs {
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1rem;
    display: flex;
    flex-wrap: wrap;
    gap: .25rem;
}
.booking-subtabs .nav-link {
    border: 1px solid transparent;
    border-bottom: 0;
    color: #334155;
    font-weight: 600;
    border-radius: .5rem .5rem 0 0;
    padding: .5rem .9rem;
    font-size: .88rem;
}
.booking-subtabs .nav-link.active {
    color: #0f172a;
    background: #fff;
    border-color: #e5e7eb;
    border-bottom-color: #fff;
}

/* ── Category grid ─────────────────────────────────────────────── */
.booking-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
    gap: 1rem;
}

/* ── Parent card ───────────────────────────────────────────────── */
.booking-group {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: .9rem 1rem 1rem;
    background: #fff;
}
.booking-group-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    margin-bottom: .65rem;
    padding-bottom: .55rem;
    border-bottom: 1px solid #f1f5f9;
}
.booking-group-title {
    font-size: .95rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

/* ── Child rows ────────────────────────────────────────────────── */
.booking-tree {
    display: flex;
    flex-direction: column;
    gap: .4rem;
}
.booking-node {
    display: flex;
    flex-direction: column;
    gap: .3rem;
}
.booking-node-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    padding: .2rem 0;
}
.booking-node-label {
    font-size: .88rem;
    font-weight: 500;
    color: #334155;
    margin: 0;
}

/* ── Grand-children indent ─────────────────────────────────────── */
.booking-children {
    margin-left: 1.2rem;
    padding-left: .75rem;
    border-left: 1px dashed #cbd5e1;
    display: flex;
    flex-direction: column;
    gap: .3rem;
}

/* ── Toggle ────────────────────────────────────────────────────── */
.booking-toggle {
    display: inline-block;
    width: 40px;
    height: 22px;
    border-radius: 999px;
    background: #cbd5e1;
    position: relative;
    cursor: pointer;
    transition: background .2s;
    flex-shrink: 0;
}
.booking-toggle::after {
    content: '';
    position: absolute;
    top: 3px; left: 3px;
    width: 16px; height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.2);
    transition: left .2s;
}
.booking-toggle.is-on {
    background: #2563eb;
}
.booking-toggle.is-on::after {
    left: 21px;
}
.booking-toggle.is-disabled {
    opacity: .4;
    cursor: not-allowed;
}

/* ── Empty / placeholder panes ─────────────────────────────────── */
.booking-empty {
    background: #f8fafc;
    border: 1px dashed #dbe4ee;
    border-radius: 10px;
    padding: 1rem;
    color: #64748b;
    font-size: .88rem;
}

/* ── Toast ─────────────────────────────────────────────────────── */
#bookingSettingsToast {
    position: fixed;
    right: 1.25rem;
    bottom: 1.25rem;
    z-index: 9999;
    display: none;
    min-width: 260px;
}
</style>
@endpush

{{-- ── TAB PANE ───────────────────────────────────────────────── --}}
<div class="tab-pane mt-3 fade {{ (request()->get('tab') == 'bookingSettings') ? 'active show' : '' }}"
     id="bookingSettings" role="tabpanel" aria-labelledby="bookingSettings-tab">

    <div class="booking-settings-shell">

        {{-- Header --}}
        <div class="booking-settings-title">Booking Settings</div>
        <div class="booking-settings-note">
            Manage which booking categories are visible for this organisation or instructor.<br>
            If a parent category is disabled, all its child categories are also disabled automatically.
        </div>

        {{-- Sub-tabs --}}
        <ul class="nav booking-subtabs" id="bookingSettingsTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" data-toggle="tab" href="#bsBackend">Backend Connection</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bsCross">Cross Selling</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bsUp">Up Selling</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bsCheckout">Check-out Options</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" data-toggle="tab" href="#bsApis">APIs</a>
            </li>
        </ul>

        <div class="tab-content">

            {{-- ── Backend Connection (categories) ────────────────── --}}
            <div class="tab-pane fade show active" id="bsBackend" role="tabpanel">

                @if(!empty($categoryTree))
                    <div class="booking-grid" id="bookingCategoryTree">
                        @foreach($categoryTree as $root)
                            @include('admin.users.editTabs.partials.booking_category_node', ['node' => $root])
                        @endforeach
                    </div>
                @else
                    <div class="booking-empty mt-2">
                        No booking categories found in the database.
                    </div>
                @endif

                <div class="mt-3">
                    <button type="button" class="btn btn-primary btn-sm" id="bookingSettingsSaveBtn"
                            style="min-width:120px">
                        <span class="save-label">Save changes</span>
                        <span class="save-spinner d-none">
                            <span class="spinner-border spinner-border-sm mr-1"></span>Saving…
                        </span>
                    </button>
                </div>
            </div>

            {{-- placeholder panes --}}
            <div class="tab-pane fade" id="bsCross">
                <div class="booking-empty mt-2">Cross Selling — coming soon.</div>
            </div>
            <div class="tab-pane fade" id="bsUp">
                <div class="booking-empty mt-2">Up Selling — coming soon.</div>
            </div>
            <div class="tab-pane fade" id="bsCheckout">
                <div class="booking-empty mt-2">Check-out options managed in the existing checkout settings.</div>
            </div>
            <div class="tab-pane fade" id="bsApis">
                <div class="booking-empty mt-2">API integrations — coming soon.</div>
            </div>

        </div>{{-- /tab-content --}}
    </div>{{-- /shell --}}
</div>{{-- /tab-pane --}}

<div id="bookingSettingsToast" class="alert mb-0" role="alert"></div>

@push('scripts_bottom')
<script>
(function () {
    'use strict';

    const SAVE_URL = @json($saveUrl);
    const CSRF     = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    // ── Toast ───────────────────────────────────────────────────
    function showToast(msg, isError) {
        const el = document.getElementById('bookingSettingsToast');
        if (!el) return;
        el.className = 'alert mb-0 ' + (isError ? 'alert-danger' : 'alert-success');
        el.textContent = msg;
        el.style.display = 'block';
        setTimeout(() => { el.style.display = 'none'; }, 3500);
    }

    // ── Save busy state ─────────────────────────────────────────
    function setBusy(busy) {
        const btn = document.getElementById('bookingSettingsSaveBtn');
        if (!btn) return;
        btn.disabled = busy;
        btn.querySelector('.save-label').classList.toggle('d-none', busy);
        btn.querySelector('.save-spinner').classList.toggle('d-none', !busy);
    }

    // ── Collect all nodes ────────────────────────────────────────
    function collectCategories() {
        return Array.from(
            document.querySelectorAll('#bookingCategoryTree .booking-node[data-category-id]')
        ).map(node => ({
            id:      Number(node.dataset.categoryId),
            enabled: node.querySelector('.booking-category-checkbox')?.checked ? 1 : 0,
        }));
    }

    // ── Save ─────────────────────────────────────────────────────
    async function saveSettings() {
        setBusy(true);
        try {
            const res  = await fetch(SAVE_URL, {
                method:  'POST',
                headers: {
                    'Content-Type':  'application/json',
                    'X-CSRF-TOKEN':  CSRF,
                    'Accept':        'application/json',
                },
                body: JSON.stringify({ categories: collectCategories() }),
            });
            const data = await res.json();
            showToast(data.message ?? 'Saved', !data.success);
        } catch (e) {
            showToast('Save failed. Please try again.', true);
        } finally {
            setBusy(false);
        }
    }

    // ── Disable / enable all descendants ─────────────────────────
    function setDescendants(node, parentIsOn) {
        // Direct child nodes inside .booking-tree or .booking-children
        node.querySelectorAll(':scope > .booking-tree > .booking-node,' +
                              ':scope > .booking-children > .booking-node').forEach(child => {
            const chk    = child.querySelector(':scope > .booking-node-row .booking-category-checkbox');
            const toggle = child.querySelector(':scope > .booking-node-row .booking-toggle');
            if (!chk || !toggle) return;

            if (!parentIsOn) {
                // Lock child OFF
                if (!chk.dataset.prevChecked) {
                    chk.dataset.prevChecked = chk.checked ? '1' : '0';
                }
                chk.checked      = false;
                chk.dataset.locked = '1';
                toggle.classList.remove('is-on');
                toggle.classList.add('is-disabled');
            } else {
                // Unlock child — restore previous state
                chk.dataset.locked = '0';
                toggle.classList.remove('is-disabled');
                const prev = chk.dataset.prevChecked;
                if (prev !== undefined) {
                    chk.checked = prev === '1';
                    delete chk.dataset.prevChecked;
                }
                toggle.classList.toggle('is-on', chk.checked);
            }

            // Recurse into this child's own children
            setDescendants(child, parentIsOn && chk.checked);
        });
    }

    function getParentNode(node) {
        const parent = node.parentElement?.closest('.booking-node[data-category-id]');
        return parent && parent !== node ? parent : null;
    }

    function setNodeOn(node) {
        const chk = node.querySelector(':scope > .booking-node-row .booking-category-checkbox,'+
                                      ':scope > .booking-group-head .booking-category-checkbox');
        const toggle = node.querySelector(':scope > .booking-node-row .booking-toggle,'+
                                          ':scope > .booking-group-head .booking-toggle');
        if (!chk || !toggle) return;

        chk.dataset.locked = '0';
        chk.checked = true;
        toggle.classList.add('is-on');
        toggle.classList.remove('is-disabled');
    }

    function enableAncestors(node) {
        const ancestors = [];
        let current = getParentNode(node);

        while (current) {
            ancestors.unshift(current);
            current = getParentNode(current);
        }

        ancestors.forEach(ancestor => {
            setNodeOn(ancestor);
            setDescendants(ancestor, true);
        });
    }

    // ── Bind a single node's toggle ──────────────────────────────
    function bindToggle(node) {
        const toggle = node.querySelector(':scope > .booking-node-row .booking-toggle,'+
                                         ':scope > .booking-group-head .booking-toggle');
        const chk    = node.querySelector(':scope > .booking-node-row .booking-category-checkbox,'+
                                          ':scope > .booking-group-head .booking-category-checkbox');
        if (!toggle || !chk) return;

        toggle.addEventListener('click', () => {
            if (chk.dataset.locked === '1') {
                chk.dataset.locked = '0';
                chk.checked = true;
                toggle.classList.add('is-on');
                toggle.classList.remove('is-disabled');
                enableAncestors(node);
                setDescendants(node, true);
                return;
            }   // parent is OFF, ignore clicks

            chk.checked = !chk.checked;
            toggle.classList.toggle('is-on', chk.checked);

            if (chk.checked) {
                enableAncestors(node);
            }

            // Cascade down to children
            setDescendants(node, chk.checked);
        });
    }

    // ── Bind all nodes ───────────────────────────────────────────
    document.querySelectorAll('#bookingCategoryTree .booking-node[data-category-id]')
            .forEach(bindToggle);

    // ── Save button ──────────────────────────────────────────────
    document.getElementById('bookingSettingsSaveBtn')
            ?.addEventListener('click', saveSettings);

})();
</script>
@endpush

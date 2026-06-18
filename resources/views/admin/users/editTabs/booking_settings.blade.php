@push('styles_bottom')
<style>
.booking-settings-card {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 1.25rem;
}

.booking-settings-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: 1rem;
}

.booking-settings-title {
    font-size: 1.05rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.booking-settings-note {
    margin-top: .35rem;
    color: #6b7280;
    font-size: .9rem;
    line-height: 1.55;
}

.booking-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(290px, 1fr));
    gap: 1rem;
}

.booking-node {
    background: #fff;
}

.booking-category-card {
    border: 1px solid #e5e7eb;
    border-radius: 12px;
    padding: .9rem 1rem 1rem;
}

.booking-category-head {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
    padding-bottom: .65rem;
    margin-bottom: .7rem;
    border-bottom: 1px solid #f1f5f9;
}

.booking-category-title {
    font-size: .95rem;
    font-weight: 700;
    color: #111827;
    margin: 0;
}

.booking-children {
    display: flex;
    flex-direction: column;
    gap: .45rem;
    margin-left: .25rem;
    padding-left: .95rem;
    border-left: 1px dashed #d1d5db;
}

.booking-child-node {
    padding-top: .15rem;
}

.booking-child-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}

.booking-child-label {
    font-size: .88rem;
    font-weight: 500;
    color: #374151;
    margin: 0;
}

.booking-toggle-wrap {
    display: inline-flex;
    align-items: center;
    flex-shrink: 0;
}

.booking-toggle {
    width: 40px;
    height: 22px;
    border: 0;
    border-radius: 999px;
    background: #cbd5e1;
    position: relative;
    cursor: pointer;
    padding: 0;
    transition: background .2s ease, opacity .2s ease;
    outline: none !important;
}

.booking-toggle::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 16px;
    height: 16px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, .2);
    transition: left .2s ease;
}

.booking-toggle.is-on {
    background: #2563eb;
}

.booking-toggle.is-on::after {
    left: 21px;
}

.booking-toggle.is-disabled {
    opacity: .45;
    cursor: not-allowed;
}

.booking-empty {
    background: #f8fafc;
    border: 1px dashed #dbe4ee;
    border-radius: 10px;
    padding: 1rem;
    color: #64748b;
    font-size: .9rem;
}

.booking-loading {
    background: #f8fafc;
    border: 1px dashed #dbe4ee;
    border-radius: 10px;
    padding: 1rem;
    color: #64748b;
    font-size: .9rem;
    text-align: center;
}

.booking-actions {
    margin-top: 1rem;
}

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

@php
    // This tab no longer depends on $categoryTree / $saveUrl being passed down
    // from the parent edit page. It fetches its own data via AJAX from the
    // BookingCategorySettingsController::index route, the same controller
    // that already powers the standalone admin.users.booking_settings.index route.
    $bookingSettingsUserId = $user->id ?? request()->route('id');
    $bookingSettingsIndexUrl = route('admin.users.booking_settings.index', ['id' => $bookingSettingsUserId]);
@endphp

<div class="tab-pane mt-3 fade {{ (request()->get('tab') == 'bookingSettings') ? 'active show' : '' }}"
     id="bookingSettings" role="tabpanel" aria-labelledby="bookingSettings-tab">
    <div class="booking-settings-card">
        <div class="booking-settings-header">
            <div>
                <h4 class="booking-settings-title">Booking Settings</h4>
                <div class="booking-settings-note">
                    Manage booking categories for this user. Parent categories control their children automatically.
                    If a parent category is disabled, every nested child category is disabled too.
                </div>
            </div>
        </div>

        <div id="bookingCategoryTreeWrapper">
            <div class="booking-loading" id="bookingSettingsLoading">Loading booking categories...</div>
            <div class="booking-grid d-none" id="bookingCategoryTree"></div>
            <div class="booking-empty d-none" id="bookingSettingsEmpty">
                No booking categories found.
            </div>
            <div class="booking-empty d-none" id="bookingSettingsError">
                Failed to load booking categories. Please refresh and try again.
            </div>
        </div>

        <div class="booking-actions">
            <button type="button" class="btn btn-primary btn-sm" id="bookingSettingsSaveBtn" style="min-width: 120px;" disabled>
                <span class="save-label">Save changes</span>
                <span class="save-spinner d-none">
                    <span class="spinner-border spinner-border-sm mr-1"></span>
                    Saving...
                </span>
            </button>
        </div>
    </div>
</div>

<div id="bookingSettingsToast" class="alert mb-0" role="alert"></div>

@push('scripts_bottom')
<script>
(function () {
    'use strict';

    const INDEX_URL = @json($bookingSettingsIndexUrl);
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content || '';

    let SAVE_URL = '';

    function escapeHtml(value) {
        const div = document.createElement('div');
        div.textContent = value ?? '';
        return div.innerHTML;
    }

    function showToast(message, isError) {
        const el = document.getElementById('bookingSettingsToast');
        if (!el) {
            return;
        }

        el.className = 'alert mb-0 ' + (isError ? 'alert-danger' : 'alert-success');
        el.textContent = message;
        el.style.display = 'block';

        window.clearTimeout(el._hideTimer);
        el._hideTimer = window.setTimeout(() => {
            el.style.display = 'none';
        }, 3500);
    }

    function setBusy(busy) {
        const btn = document.getElementById('bookingSettingsSaveBtn');
        if (!btn) {
            return;
        }

        btn.disabled = busy;
        btn.querySelector('.save-label')?.classList.toggle('d-none', busy);
        btn.querySelector('.save-spinner')?.classList.toggle('d-none', !busy);
    }

    function buildNodeHtml(node) {
        const enabled = !!node.enabled;
        const checked = enabled ? 'checked' : '';
        const onClass = enabled ? 'is-on' : '';
        const ariaPressed = enabled ? 'true' : 'false';
        const title = escapeHtml(node.title);

        let childrenHtml = '';
        if (Array.isArray(node.children) && node.children.length) {
            const childRows = node.children.map(buildNodeHtml).join('');
            childrenHtml = `<div class="booking-children">${childRows}</div>`;
        }

        return `
            <div class="booking-node booking-category-card" data-category-id="${node.id}">
                <div class="booking-category-head">
                    <div class="booking-category-title">${title}</div>
                    <div class="booking-toggle-wrap">
                        <input type="checkbox" class="booking-category-checkbox d-none" data-locked="0" ${checked}>
                        <button type="button" class="booking-toggle ${onClass}" aria-pressed="${ariaPressed}" aria-label="Toggle category"></button>
                    </div>
                </div>
                ${childrenHtml}
            </div>
        `;
    }

    function getCheckbox(node) {
        return node.querySelector('.booking-category-checkbox');
    }

    function getToggle(node) {
        return node.querySelector('.booking-toggle');
    }

    function getDirectChildren(node) {
        return Array.from(node.querySelectorAll(':scope > .booking-children > .booking-node'));
    }

    function setNodeState(node, enabled, locked) {
        const checkbox = getCheckbox(node);
        const toggle = getToggle(node);
        if (!checkbox || !toggle) {
            return;
        }

        checkbox.checked = !!enabled;
        checkbox.dataset.locked = locked ? '1' : '0';
        toggle.classList.toggle('is-on', !!enabled);
        toggle.classList.toggle('is-disabled', !!locked);
        toggle.setAttribute('aria-pressed', enabled ? 'true' : 'false');
        toggle.setAttribute('aria-disabled', locked ? 'true' : 'false');
    }

    function getParentNode(node) {
        const parent = node.parentElement?.closest('.booking-node[data-category-id]');
        return parent && parent !== node ? parent : null;
    }

    function syncDescendants(node, parentEnabled) {
        getDirectChildren(node).forEach((child) => {
            const checkbox = getCheckbox(child);
            if (!checkbox) {
                return;
            }

            if (!parentEnabled) {
                if (checkbox.dataset.prevChecked === undefined) {
                    checkbox.dataset.prevChecked = checkbox.checked ? '1' : '0';
                }

                setNodeState(child, false, true);
                syncDescendants(child, false);
                return;
            }

            const restore = checkbox.dataset.prevChecked !== undefined
                ? checkbox.dataset.prevChecked === '1'
                : checkbox.checked;

            delete checkbox.dataset.prevChecked;
            setNodeState(child, restore, false);
            syncDescendants(child, restore);
        });
    }

    function enableAncestors(node) {
        const ancestors = [];
        let current = getParentNode(node);

        while (current) {
            ancestors.unshift(current);
            current = getParentNode(current);
        }

        ancestors.forEach((ancestor) => {
            setNodeState(ancestor, true, false);
            syncDescendants(ancestor, true);
        });
    }

    function toggleNode(node) {
        const checkbox = getCheckbox(node);
        if (!checkbox) {
            return;
        }

        if (checkbox.dataset.locked === '1') {
            enableAncestors(node);
            setNodeState(node, true, false);
            syncDescendants(node, true);
            return;
        }

        const nextEnabled = !checkbox.checked;
        setNodeState(node, nextEnabled, false);

        if (nextEnabled) {
            enableAncestors(node);
        }

        syncDescendants(node, nextEnabled);
    }

    function collectCategories() {
        return Array.from(document.querySelectorAll('#bookingCategoryTree .booking-node[data-category-id]'))
            .map((node) => ({
                id: Number(node.dataset.categoryId),
                enabled: getCheckbox(node)?.checked ? 1 : 0,
            }));
    }

    function attachToggleHandlers() {
        document.querySelectorAll('#bookingCategoryTree .booking-node[data-category-id]').forEach((node) => {
            const toggle = getToggle(node);
            if (!toggle) {
                return;
            }

            toggle.addEventListener('click', (event) => {
                event.preventDefault();
                toggleNode(node);
            });
        });
    }

    async function loadBookingSettings() {
        const loadingEl = document.getElementById('bookingSettingsLoading');
        const treeEl = document.getElementById('bookingCategoryTree');
        const emptyEl = document.getElementById('bookingSettingsEmpty');
        const errorEl = document.getElementById('bookingSettingsError');

        try {
            const response = await fetch(INDEX_URL, {
                method: 'GET',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json',
                },
            });

            if (!response.ok) {
                throw new Error('Request failed with status ' + response.status);
            }

            const data = await response.json();

            SAVE_URL = data.saveUrl || '';

            loadingEl?.classList.add('d-none');

            if (!Array.isArray(data.categoryTree) || data.categoryTree.length === 0) {
                emptyEl?.classList.remove('d-none');
                return;
            }

            treeEl.innerHTML = data.categoryTree.map(buildNodeHtml).join('');
            treeEl.classList.remove('d-none');

            attachToggleHandlers();

            const saveBtn = document.getElementById('bookingSettingsSaveBtn');
            if (saveBtn) {
                saveBtn.disabled = false;
            }
        } catch (error) {
            loadingEl?.classList.add('d-none');
            errorEl?.classList.remove('d-none');
        }
    }

    async function saveSettings() {
        if (!SAVE_URL) {
            showToast('Booking categories are still loading. Please wait.', true);
            return;
        }

        setBusy(true);

        try {
            const response = await fetch(SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ categories: collectCategories() }),
            });

            const payload = await response.json().catch(() => ({}));

            if (!response.ok) {
                const firstError = payload?.errors ? Object.values(payload.errors).flat()[0] : null;
                throw new Error(firstError || payload.message || 'Save failed.');
            }

            showToast(payload.message || 'Saved successfully.', false);
        } catch (error) {
            showToast(error?.message || 'Save failed. Please try again.', true);
        } finally {
            setBusy(false);
        }
    }

    document.getElementById('bookingSettingsSaveBtn')?.addEventListener('click', saveSettings);

    loadBookingSettings();
})();
</script>
@endpush
@php
    $categoryTree = $categoryTree ?? [];
    $saveUrl = $saveUrl ?? '';
@endphp

@push('styles_bottom')
<style>
.booking-settings-shell {
    background: #fff;
    border: 1px solid #e5e7eb;
    border-radius: 14px;
    padding: 1.25rem;
    box-shadow: 0 1px 3px rgba(15, 23, 42, 0.06);
}

.booking-settings-title {
    font-size: 1.1rem;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: .35rem;
}

.booking-settings-note {
    color: #64748b;
    font-size: .9rem;
    line-height: 1.65;
}

.booking-subtabs {
    border-bottom: 1px solid #e5e7eb;
    margin-bottom: 1rem;
    gap: .35rem;
}

.booking-subtabs .nav-link {
    border: 1px solid transparent;
    border-bottom: 0;
    color: #334155;
    font-weight: 600;
    border-radius: .55rem .55rem 0 0;
    padding: .55rem .9rem;
}

.booking-subtabs .nav-link.active {
    color: #0f172a;
    background: #fff;
    border-color: #e5e7eb;
    border-bottom-color: #fff;
}

.booking-section {
    padding: .25rem 0 0;
}

.booking-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 1rem;
}

.booking-group {
    border: 1px solid #edf2f7;
    border-radius: 12px;
    padding: .95rem 1rem 1rem;
    background: linear-gradient(180deg, #ffffff 0%, #fbfdff 100%);
}

.booking-group-head {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: .75rem;
    margin-bottom: .75rem;
}

.booking-group-title {
    font-size: .98rem;
    font-weight: 700;
    color: #0f172a;
    margin: 0;
}

.booking-tree {
    display: flex;
    flex-direction: column;
    gap: .4rem;
}

.booking-node {
    display: flex;
    flex-direction: column;
    gap: .35rem;
}

.booking-node-row {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .75rem;
}

.booking-node-label {
    font-size: .92rem;
    font-weight: 600;
    color: #334155;
    margin: 0;
}

.booking-children {
    margin-left: 1.35rem;
    padding-left: .85rem;
    border-left: 1px dashed #dbe4ee;
    display: flex;
    flex-direction: column;
    gap: .35rem;
}

.booking-child-label {
    font-size: .88rem;
    font-weight: 500;
}

.booking-toggle {
    display: inline-block;
    width: 44px;
    height: 24px;
    border-radius: 999px;
    background: #cbd5e1;
    position: relative;
    cursor: pointer;
    transition: background .2s ease;
    flex: 0 0 auto;
}

.booking-toggle::after {
    content: '';
    position: absolute;
    top: 3px;
    left: 3px;
    width: 18px;
    height: 18px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 3px rgba(0,0,0,.22);
    transition: left .2s ease;
}

.booking-toggle.is-on {
    background: #0d6efd;
}

.booking-toggle.is-on::after {
    left: 23px;
}

.booking-toggle.is-disabled {
    opacity: .45;
    cursor: not-allowed;
}

.booking-actions {
    margin-top: 1rem;
}

.booking-save-btn {
    min-width: 132px;
}

.booking-helper {
    margin-top: .75rem;
    font-size: .88rem;
    color: #475569;
}

.booking-empty {
    background: #f8fafc;
    border: 1px dashed #dbe4ee;
    border-radius: 10px;
    padding: 1rem;
    color: #64748b;
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

<div class="tab-pane mt-3 fade {{ (request()->get('tab') == 'bookingSettings') ? 'active show' : '' }}" id="bookingSettings" role="tabpanel" aria-labelledby="bookingSettings-tab">
    <div class="booking-settings-shell">
        <div class="mb-3">
            <div class="booking-settings-title">Booking Settings</div>
            <div class="booking-settings-note">
                Manage which booking categories are visible for this organization or instructor.
                If we disable an option it wont appear at the Organization/Instructor.
            </div>
        </div>

        <ul class="nav nav-tabs booking-subtabs" id="bookingSettingsTabs" role="tablist">
            <li class="nav-item">
                <a class="nav-link active" id="bookingBackend-tab" data-toggle="tab" href="#bookingBackendConnection" role="tab" aria-controls="bookingBackendConnection" aria-selected="true">Backend Connection</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="bookingCrossSelling-tab" data-toggle="tab" href="#bookingCrossSelling" role="tab" aria-controls="bookingCrossSelling" aria-selected="false">Cross Selling</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="bookingUpSelling-tab" data-toggle="tab" href="#bookingUpSelling" role="tab" aria-controls="bookingUpSelling" aria-selected="false">Up Selling</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="bookingCheckout-tab" data-toggle="tab" href="#bookingCheckoutOptions" role="tab" aria-controls="bookingCheckoutOptions" aria-selected="false">Check-out Options</a>
            </li>
            <li class="nav-item">
                <a class="nav-link" id="bookingApis-tab" data-toggle="tab" href="#bookingApis" role="tab" aria-controls="bookingApis" aria-selected="false">APIs</a>
            </li>
        </ul>

        <div class="tab-content">
            <div class="tab-pane fade show active booking-section" id="bookingBackendConnection" role="tabpanel" aria-labelledby="bookingBackend-tab">
                @if(!empty($categoryTree))
                    <div class="booking-grid" id="bookingCategoryTree">
                        @foreach($categoryTree as $root)
                            @include('admin.users.editTabs.partials.booking_category_node', ['node' => $root])
                        @endforeach
                    </div>
                @else
                    <div class="booking-empty">
                        No booking categories were found in the database.
                    </div>
                @endif

                <div class="booking-helper">
                    Parent toggle OFF disables all its child categories. Child toggle changes do not affect the parent.
                </div>

                <div class="booking-actions">
                    <button type="button" class="btn btn-primary btn-sm booking-save-btn" id="bookingSettingsSaveBtn">
                        <span class="save-label">Save changes</span>
                        <span class="save-spinner d-none"><span class="spinner-border spinner-border-sm me-1"></span>Saving...</span>
                    </button>
                </div>
            </div>

            <div class="tab-pane fade booking-section" id="bookingCrossSelling" role="tabpanel" aria-labelledby="bookingCrossSelling-tab">
                <div class="booking-empty">Cross Selling settings can be connected here later.</div>
            </div>

            <div class="tab-pane fade booking-section" id="bookingUpSelling" role="tabpanel" aria-labelledby="bookingUpSelling-tab">
                <div class="booking-empty">Up Selling settings can be connected here later.</div>
            </div>

            <div class="tab-pane fade booking-section" id="bookingCheckoutOptions" role="tabpanel" aria-labelledby="bookingCheckout-tab">
                <div class="booking-empty">Check-out options are managed from the existing checkout settings flow.</div>
            </div>

            <div class="tab-pane fade booking-section" id="bookingApis" role="tabpanel" aria-labelledby="bookingApis-tab">
                <div class="booking-empty">API integrations can be added here later.</div>
            </div>
        </div>
    </div>
</div>

<div id="bookingSettingsToast" class="alert mb-0" role="alert"></div>

@push('scripts_bottom')
<script>
(function () {
    'use strict';

    const SAVE_URL = @json($saveUrl);
    const CSRF = document.querySelector('meta[name="csrf-token"]')?.content ?? '';

    function showToast(message, isError = false) {
        const toast = document.getElementById('bookingSettingsToast');
        if (!toast) return;

        toast.className = 'alert mb-0 ' + (isError ? 'alert-danger' : 'alert-success');
        toast.textContent = message;
        toast.style.display = 'block';

        setTimeout(() => {
            toast.style.display = 'none';
        }, 3500);
    }

    function setBusy(busy) {
        const btn = document.getElementById('bookingSettingsSaveBtn');
        if (!btn) return;

        btn.disabled = busy;
        btn.querySelector('.save-label')?.classList.toggle('d-none', busy);
        btn.querySelector('.save-spinner')?.classList.toggle('d-none', !busy);
    }

    function setDescendantsState(node, enabled) {
        const childrenWrap = Array.from(node.children).find(child => child.classList?.contains('booking-children'));
        if (!childrenWrap) return;

        Array.from(childrenWrap.children).forEach(childNode => {
            if (!childNode.classList?.contains('booking-node')) return;

            const checkbox = childNode.querySelector('.booking-category-checkbox');
            const toggle = childNode.querySelector('.booking-toggle');
            if (!checkbox || !toggle) return;

            if (!enabled) {
                checkbox.dataset.prevChecked = checkbox.checked ? '1' : '0';
                checkbox.checked = false;
                checkbox.disabled = true;
                toggle.classList.remove('is-on');
                toggle.classList.add('is-disabled');
                setDescendantsState(childNode, false);
                return;
            }

            checkbox.disabled = false;
            toggle.classList.remove('is-disabled');

            const prev = checkbox.dataset.prevChecked;
            if (typeof prev !== 'undefined') {
                checkbox.checked = prev === '1';
            }

            toggle.classList.toggle('is-on', checkbox.checked);
            setDescendantsState(childNode, checkbox.checked);
        });
    }

    function bindNode(node) {
        const checkbox = node.querySelector('.booking-category-checkbox');
        const toggle = node.querySelector('.booking-toggle');
        if (!checkbox || !toggle) return;

        const syncToggle = () => {
            toggle.classList.toggle('is-on', checkbox.checked);
        };

        toggle.addEventListener('click', () => {
            if (checkbox.disabled) return;

            checkbox.checked = !checkbox.checked;
            syncToggle();
            setDescendantsState(node, checkbox.checked);
        });

        checkbox.addEventListener('change', () => {
            syncToggle();
            setDescendantsState(node, checkbox.checked);
        });

        syncToggle();
        setDescendantsState(node, checkbox.checked);
    }

    document.querySelectorAll('.booking-node[data-category-id]').forEach(bindNode);

    function showOuterBookingSettingsTab() {
        var tabLink = document.getElementById('bookingSettings-tab');
        var tabPane = document.getElementById('bookingSettings');
        if (!tabLink || !tabPane) return;

        var tabContainer = tabPane.closest('.tab-content');
        if (!tabContainer) return;

        var nav = tabLink.closest('.nav');
        if (nav) {
            nav.querySelectorAll('.nav-link').forEach(function (link) {
                link.classList.remove('active');
            });
        }

        tabContainer.querySelectorAll(':scope > .tab-pane').forEach(function (pane) {
            pane.classList.remove('active', 'show');
        });

        tabLink.classList.add('active');
        tabPane.classList.add('active', 'show');
    }

    document.getElementById('bookingSettings-tab')?.addEventListener('click', function () {
        setTimeout(showOuterBookingSettingsTab, 0);
    });

    async function saveSettings() {
        const categories = Array.from(document.querySelectorAll('.booking-node[data-category-id]')).map(node => {
            const checkbox = node.querySelector('.booking-category-checkbox');
            return {
                id: Number(node.dataset.categoryId),
                enabled: checkbox?.checked ? 1 : 0,
            };
        });

        setBusy(true);

        try {
            const response = await fetch(SAVE_URL, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': CSRF,
                    'Accept': 'application/json',
                },
                body: JSON.stringify({ categories }),
            });

            const data = await response.json();
            showToast(data.message ?? 'Saved successfully', !data.success);
        } catch (error) {
            showToast('Save failed', true);
        } finally {
            setBusy(false);
        }
    }

    document.getElementById('bookingSettingsSaveBtn')?.addEventListener('click', saveSettings);
})();
</script>
@endpush

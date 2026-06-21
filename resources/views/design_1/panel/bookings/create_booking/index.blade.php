{{--
    resources/views/design_1/panel/booking/create_booking/index.blade.php

    Wizard shell: top icon-stepper + current step content + footer nav.
    Each step's HTML lives in ./steps/stepN_*.blade.php and is swapped in
    via AJAX (see script block at the bottom) so "Save as Draft" works
    on a partially-filled booking.
--}}
@extends('design_1.panel.layout')

@section('content')

<style>
.wiz-stepper {
    display: flex;
    align-items: center;
    justify-content: space-between;
    background: #fff;
    border-radius: 12px;
    padding: 18px 24px;
    margin-bottom: 20px;
    overflow-x: auto;
    gap: 4px;
}
.wiz-step {
    display: flex;
    align-items: center;
    gap: 10px;
    flex: 1;
    min-width: 0;
    cursor: pointer;
    opacity: .45;
    transition: opacity .15s;
}
.wiz-step.is-reachable { opacity: 1; }
.wiz-step.is-active .wiz-step-icon { background: #2563eb; color: #fff; }
.wiz-step.is-done .wiz-step-icon { background: #16a34a; color: #fff; }
.wiz-step-icon {
    width: 38px;
    height: 38px;
    border-radius: 50%;
    background: #eef1f5;
    color: #6b7280;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    flex-shrink: 0;
}
.wiz-step-label {
    font-size: 13px;
    font-weight: 600;
    color: #344054;
    white-space: nowrap;
}
.wiz-step-sub {
    font-size: 11px;
    color: #98a2b3;
    white-space: nowrap;
}
.wiz-divider {
    flex: 0 0 24px;
    height: 1px;
    background: #e4e7ec;
    margin: 0 4px;
}
.wiz-panel {
    background: #fff;
    border-radius: 12px;
    padding: 28px;
    min-height: 360px;
}
.wiz-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 20px;
    background: #fff;
    border-radius: 12px;
    padding: 16px 24px;
}
.wiz-footer-right { display: flex; gap: 10px; align-items: center; }
.wiz-loading-overlay {
    position: absolute;
    inset: 0;
    background: rgba(255,255,255,.7);
    display: none;
    align-items: center;
    justify-content: center;
    border-radius: 12px;
    z-index: 5;
}
.wiz-panel-wrap { position: relative; }
</style>

<div class="container-fluid" id="bookingWizardApp" data-booking-id="{{ $booking->id }}" data-current-step="{{ $currentStep }}" data-booking-type="{{ $booking->booking_type }}">

    <div class="wiz-stepper" id="wizStepper">
        @foreach($steps as $num => $step)
            <div class="wiz-step {{ $num == $currentStep ? 'is-active' : '' }} {{ $num <= $booking->wizard_step ? 'is-reachable' : '' }} {{ $num < $booking->wizard_step ? 'is-done' : '' }}"
                 data-step="{{ $num }}">
                <div class="wiz-step-icon">{{ $num < $booking->wizard_step ? '✓' : $num }}</div>
                <div>
                    <div class="wiz-step-label">{{ $step['label'] }}</div>
                    <div class="wiz-step-sub">Step {{ $num }} of {{ count($steps) }}</div>
                </div>
            </div>
            @if($num < count($steps))
                <div class="wiz-divider"></div>
            @endif
        @endforeach
    </div>

    <div class="wiz-panel-wrap">
        <div class="wiz-loading-overlay" id="wizLoading">
            <i class="fa fa-spinner fa-spin fa-2x text-primary"></i>
        </div>
        <div class="wiz-panel" id="wizPanel">
            @include('design_1.panel.booking.create_booking.' . $steps[$currentStep]['view'])
        </div>
    </div>

    <div class="wiz-footer">
        <button type="button" class="btn btn-outline-secondary" id="wizBackBtn" {{ $currentStep == 1 ? 'disabled' : '' }}>
            <i class="fa fa-arrow-left mr-1"></i> Back
        </button>
        <div class="wiz-footer-right">
            <button type="button" class="btn btn-light" id="wizSaveDraftBtn">Save as Draft</button>
            @if($currentStep < count($steps))
                <button type="button" class="btn btn-primary" id="wizNextBtn">Next <i class="fa fa-arrow-right ml-1"></i></button>
            @else
                <button type="button" class="btn btn-primary" id="wizSubmitBtn">Submit for Review</button>
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    const app = document.getElementById('bookingWizardApp');
    const bookingId = app.dataset.bookingId;
    const baseUrl = '{{ url('panel/bookings/wizard') }}';
    let currentStep = parseInt(app.dataset.currentStep, 10);

    function showLoading(show) {
        document.getElementById('wizLoading').style.display = show ? 'flex' : 'none';
    }

    function collectStepForm() {
        const form = document.querySelector('#wizPanel form, #wizPanel [data-wiz-form]');
        if (!form) return new FormData();
        return new FormData(form);
    }

    function gotoStep(step, opts = {}) {
        showLoading(true);
        fetch(`${baseUrl}/${bookingId}/step/${step}`, {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
        .then(r => r.text())
        .then(html => {
            document.getElementById('wizPanel').innerHTML = html;
            currentStep = step;
            updateStepperUI();
            window.dispatchEvent(new CustomEvent('wiz:step-loaded', { detail: { step } }));
        })
        .finally(() => showLoading(false));
    }

    function updateStepperUI() {
        document.querySelectorAll('.wiz-step').forEach(el => {
            const num = parseInt(el.dataset.step, 10);
            el.classList.toggle('is-active', num === currentStep);
        });
        document.getElementById('wizBackBtn').disabled = currentStep === 1;
    }

    function saveCurrentStep({ silent = false } = {}) {
        return new Promise((resolve, reject) => {
            const formData = collectStepForm();
            fetch(`${baseUrl}/${bookingId}/step/${currentStep}`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: formData
            })
            .then(r => r.json())
            .then(data => {
                if (!data.success && !silent) {
                    alert(data.message || 'Could not save this step.');
                    reject(data);
                    return;
                }
                resolve(data);
            })
            .catch(reject);
        });
    }

    document.getElementById('wizNextBtn')?.addEventListener('click', function () {
        showLoading(true);
        saveCurrentStep()
            .then(() => gotoStep(currentStep + 1))
            .catch(() => showLoading(false));
    });

    document.getElementById('wizBackBtn')?.addEventListener('click', function () {
        gotoStep(Math.max(1, currentStep - 1));
    });

    document.getElementById('wizSaveDraftBtn')?.addEventListener('click', function () {
        showLoading(true);
        saveCurrentStep({ silent: true })
            .then(() => { window.location.href = '{{ route('panel.bookings.index') }}'; })
            .finally(() => showLoading(false));
    });

    document.getElementById('wizSubmitBtn')?.addEventListener('click', function () {
        showLoading(true);
        saveCurrentStep()
            .then(() => fetch(`${baseUrl}/${bookingId}/submit`, {
                method: 'POST',
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                }
            }))
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = data.redirect;
                } else {
                    alert(data.message || 'Could not submit.');
                }
            })
            .finally(() => showLoading(false));
    });

    document.querySelectorAll('.wiz-step.is-reachable').forEach(el => {
        el.addEventListener('click', function () {
            const target = parseInt(this.dataset.step, 10);
            if (target === currentStep) return;
            // Save current step before navigating away, but don't block the jump on failure
            saveCurrentStep({ silent: true }).finally(() => gotoStep(target));
        });
    });
})();
</script>

@endsection

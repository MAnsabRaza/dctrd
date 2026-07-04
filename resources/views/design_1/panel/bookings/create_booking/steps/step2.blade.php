{{--
    Step 2 — Pricing & Availability
--}}
@php
    $existingPlans = $booking->ratePlans ?? collect();
    $subTemplate = $subTemplate ?? null;
    $tplFields = $subTemplate ? $subTemplate->relevantFields() : $config->fields();
    $requiredFields = $subTemplate ? $subTemplate->required() : $config->required();
    $fieldLabels = $subTemplate ? $subTemplate->fieldLabels() : $config->fieldLabels();
    $priceUnitLabel = $subTemplate ? $subTemplate->priceUnit() : $config->priceUnitLabel();
    $fieldLabel = fn (string $field, string $fallback) => $fieldLabels[$field] ?? $fallback;
    $isRequired = fn (string $field) => in_array($field, $requiredFields, true);
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-tags"></i></div>
    <div>
        <h6>Pricing &amp; Availability</h6>
        <p class="section-sub">Set the base price and seasonal rate overrides for {{ $subTemplate ? $subTemplate->label() : $config->label() }}.</p>
    </div>
</div>

<div class="panel-card">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-money"></i></div>
        <div><h6>Booking Cost</h6></div>
    </div>

    <div class="row">
        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>{{ $fieldLabel('price', 'Base Price') }} <span class="text-danger">*</span></label>
                <input name="price" type="number" step="0.01" min="0" class="form-control"
                       value="{{ old('price', $booking->price ?? '') }}" placeholder="0.00">
                <small class="text-muted">{{ $priceUnitLabel }}</small>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Discount Price</label>
                <input name="discount_price" type="number" step="0.01" min="0" class="form-control"
                       value="{{ old('discount_price', $booking->discount_price ?? '') }}" placeholder="0.00">
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Currency</label>
                <select name="currency" class="form-control">
                    @foreach(['USD','EUR','GBP','PKR','AED','SAR','INR'] as $cur)
                        <option value="{{ $cur }}" {{ old('currency', $booking->currency ?? 'USD') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Price Unit</label>
                <input name="price_unit" type="text" class="form-control"
                       value="{{ old('price_unit', $subTemplate ? $priceUnitLabel : ($booking->price_unit ?? $priceUnitLabel)) }}" placeholder="per night, per adult">
            </div>
        </div>

        @if(in_array('price_per', $tplFields))
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Price per Extra Person / Hour</label>
                    <input name="price_per" type="number" step="0.01" min="0" class="form-control"
                           value="{{ old('price_per', $booking->price_per ?? '') }}" placeholder="0.00">
                </div>
            </div>
        @endif

        @if(in_array('duration_minutes', $tplFields))
            <div class="col-12 col-md-4">
                <div class="form-group mb-0">
                    <label>{{ $fieldLabel('duration_minutes', 'Duration (minutes)') }} @if($isRequired('duration_minutes')) <span class="text-danger">*</span> @endif</label>
                    <input name="duration_minutes" type="number" min="0" class="form-control"
                           value="{{ old('duration_minutes', $booking->duration_minutes ?? '') }}" {{ $isRequired('duration_minutes') ? 'required' : '' }}>
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ── Capacity & Inventory ────────────────────────────────────────────
     Only relevant for templates that declare 'capacity' and/or 'inventory'
     in BookingTemplateConfig (events ticket count, beauty-spa group service,
     accommodation room capacity). Driving this off $tplFields means a future
     template added to the config picks this panel up automatically. --}}
@if(in_array('capacity', $tplFields) || in_array('inventory', $tplFields))
    <div class="panel-card">
        <div class="section-head mb-3">
            <div class="badge-icon"><i class="fa fa-users"></i></div>
            <div>
                <h6>Capacity &amp; Inventory</h6>
                <p class="section-sub">
                    @if($booking->booking_type === \App\Services\BookingTemplateConfig::EVENTS)
                        Total seats and how many tickets are currently available to sell.
                    @elseif($booking->booking_type === \App\Services\BookingTemplateConfig::ACCOMMODATION)
                        Maximum guests this room/unit can hold.
                    @else
                        Maximum people that can join this service at the same time.
                    @endif
                </p>
            </div>
        </div>
        <div class="row">
            @if(in_array('capacity', $tplFields))
                <div class="col-12 col-md-6">
                    <div class="form-group mb-0">
                        <label>{{ $fieldLabel('capacity', 'Capacity') }} @if($isRequired('capacity')) <span class="text-danger">*</span> @endif</label>
                        <input name="capacity" type="number" min="1" class="form-control"
                               value="{{ old('capacity', $booking->capacity ?? '') }}" placeholder="Leave empty for unlimited" {{ $isRequired('capacity') ? 'required' : '' }}>
                    </div>
                </div>
            @endif
            @if(in_array('inventory', $tplFields))
                <div class="col-12 col-md-6">
                    <div class="form-group mb-0">
                        <label>{{ $fieldLabel('inventory', 'Available Tickets / Seats') }} @if($isRequired('inventory')) <span class="text-danger">*</span> @endif</label>
                        <input name="inventory" type="number" min="0" class="form-control"
                               value="{{ old('inventory', $booking->inventory ?? '') }}" placeholder="Leave empty for unlimited" {{ $isRequired('inventory') ? 'required' : '' }}>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- ── Deposit ──────────────────────────────────────────────────────────
     Relevant for accommodation and automotive (both define deposit_enabled
     /deposit_amount/deposit_type as fields). --}}
@if(in_array('deposit_enabled', $tplFields))
    @php $depositEnabled = old('deposit_enabled', $booking->deposit_enabled ?? false); @endphp
    <div class="panel-card">
        <div class="booking-switch-row">
            <label class="booking-switch" for="depositSwitch">
                <input type="checkbox" id="depositSwitch" name="deposit_enabled"
                       {{ $depositEnabled ? 'checked' : '' }}
                       onchange="document.getElementById('depositFields').style.display = this.checked ? 'flex' : 'none'">
                <span class="booking-switch-slider"></span>
            </label>
            <label class="booking-switch-label mb-0" for="depositSwitch">
                Require a deposit
                <small>Charge a portion of the price upfront, refundable on return/checkout</small>
            </label>
        </div>
        <div id="depositFields" class="row mt-2" style="{{ $depositEnabled ? 'display:flex' : 'display:none' }}">
            <div class="col-12 col-md-6">
                <div class="form-group mb-0">
                    <label>Deposit Amount</label>
                    <input name="deposit_amount" type="number" step="0.01" min="0" class="form-control"
                           value="{{ old('deposit_amount', $booking->deposit_amount ?? '') }}">
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group mb-0">
                    <label>Deposit Type</label>
                    <select name="deposit_type" class="form-control">
                        <option value="fixed" {{ old('deposit_type', $booking->deposit_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="percentage" {{ old('deposit_type', $booking->deposit_type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage of Price</option>
                    </select>
                </div>
            </div>
        </div>
    </div>
@endif

<div class="panel-card mb-0">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-calendar"></i></div>
        <div>
            <h6>Seasonal Rates</h6>
            <p class="section-sub">Optional price overrides by date range</p>
        </div>
    </div>

    <div class="mini-table-wrap">
        <table class="mini-table" id="ratePlansTable">
            <thead>
                <tr><th>Name</th><th>From</th><th>To</th><th>Price</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($existingPlans as $i => $plan)
                    <tr>
                        <td><input type="text" class="form-control form-control-sm" name="rate_plans[{{ $i }}][name]" value="{{ $plan->name }}" placeholder="e.g. Summer"></td>
                        <td><input type="text" class="form-control form-control-sm" name="rate_plans[{{ $i }}][from]" value="{{ $plan->conditions['from'] ?? '' }}" placeholder="e.g. June"></td>
                        <td><input type="text" class="form-control form-control-sm" name="rate_plans[{{ $i }}][to]" value="{{ $plan->conditions['to'] ?? '' }}" placeholder="e.g. August"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="rate_plans[{{ $i }}][price]" value="{{ $plan->price }}" placeholder="0.00"></td>
                        <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-rate-row"><i class="fa fa-trash"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        @if($existingPlans->isEmpty())
            <div class="empty-state" id="ratePlansEmptyHint">
                <div class="badge-icon"><i class="fa fa-calendar"></i></div>
                <div class="empty-title">No seasonal rates yet</div>
                <div class="empty-sub">Add a rate to override pricing for a date range</div>
            </div>
        @endif
    </div>

    <button type="button" class="btn btn-sm btn-outline-primary" id="addRateRow">
        <i class="fa fa-plus mr-1"></i> Add rate
    </button>

    @if(($orgAvailabilityRule ?? null))
        <div class="alert alert-light border mt-3 mb-0">
            <strong>Organization Availability Mode:</strong> {{ ucfirst($orgAvailabilityRule->availability_mode) }}
        </div>
    @endif
</div>

<script>
(function () {
    let rateIndex = {{ $existingPlans->count() }};
    document.getElementById('addRateRow')?.addEventListener('click', function () {
        document.getElementById('ratePlansEmptyHint')?.remove();
        const tbody = document.querySelector('#ratePlansTable tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" class="form-control form-control-sm" name="rate_plans[${rateIndex}][name]" placeholder="e.g. Summer"></td>
            <td><input type="text" class="form-control form-control-sm" name="rate_plans[${rateIndex}][from]" placeholder="e.g. June"></td>
            <td><input type="text" class="form-control form-control-sm" name="rate_plans[${rateIndex}][to]" placeholder="e.g. August"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="rate_plans[${rateIndex}][price]" placeholder="0.00"></td>
            <td class="text-right"><button type="button" class="btn btn-sm btn-link text-danger remove-rate-row"><i class="fa fa-trash"></i></button></td>
        `;
        tbody.appendChild(row);
        rateIndex++;
    });
    document.querySelector('#ratePlansTable')?.addEventListener('click', function (e) {
        if (e.target.closest('.remove-rate-row')) e.target.closest('tr').remove();
    });
})();
</script>

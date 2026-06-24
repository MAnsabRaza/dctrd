{{--
    Step 2 — Pricing & Availability
--}}
@php
    $existingPlans = $booking->ratePlans ?? collect();
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-tags"></i></div>
    <div>
        <h6>Pricing &amp; Availability</h6>
        <p class="section-sub">Set the base price and seasonal rate overrides.</p>
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
                <label>Base Price</label>
                <input name="price" type="number" step="0.01" min="0" class="form-control"
                       value="{{ old('price', $booking->price ?? '') }}" placeholder="0.00">
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
                       value="{{ old('price_unit', $booking->price_unit ?? 'booking') }}" placeholder="per night, per adult">
            </div>
        </div>

        <div class="col-12 col-md-4">
            <div class="form-group mb-0">
                <label>Duration (minutes)</label>
                <input name="duration_minutes" type="number" min="0" class="form-control"
                       value="{{ old('duration_minutes', $booking->duration_minutes ?? '') }}">
            </div>
        </div>
    </div>
</div>

<div class="panel-card">
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
{{--
    Step 2 — Pricing & Availability
--}}
@php
    $existingPlans = $booking->ratePlans ?? collect();
@endphp

<h5 class="mb-1">Pricing &amp; Availability</h5>
<p class="text-muted mb-4">Set the base price and seasonal rate overrides.</p>

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
        <div class="form-group">
            <label>Duration (minutes)</label>
            <input name="duration_minutes" type="number" min="0" class="form-control"
                   value="{{ old('duration_minutes', $booking->duration_minutes ?? '') }}">
        </div>
    </div>

    <div class="col-12 mt-3">
        <label class="font-weight-bold mb-2 d-block">Seasonal Rates <small class="text-muted">(optional overrides by date range)</small></label>
        <table class="table table-sm" id="ratePlansTable">
            <thead>
                <tr><th>Name</th><th>From</th><th>To</th><th>Price</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($existingPlans as $i => $plan)
                    <tr>
                        <td><input type="text" class="form-control form-control-sm" name="rate_plans[{{ $i }}][name]" value="{{ $plan->name }}"></td>
                        <td><input type="text" class="form-control form-control-sm" name="rate_plans[{{ $i }}][from]" value="{{ $plan->conditions['from'] ?? '' }}" placeholder="e.g. June"></td>
                        <td><input type="text" class="form-control form-control-sm" name="rate_plans[{{ $i }}][to]" value="{{ $plan->conditions['to'] ?? '' }}" placeholder="e.g. August"></td>
                        <td><input type="number" step="0.01" class="form-control form-control-sm" name="rate_plans[{{ $i }}][price]" value="{{ $plan->price }}"></td>
                        <td><button type="button" class="btn btn-sm btn-link text-danger remove-rate-row"><i class="fa fa-trash"></i></button></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
        <button type="button" class="btn btn-sm btn-outline-primary" id="addRateRow">
            <i class="fa fa-plus mr-1"></i> Add rate
        </button>
    </div>

    @if(($orgAvailabilityRule ?? null))
        <div class="col-12 mt-4">
            <div class="alert alert-light border">
                <strong>Organization Availability Mode:</strong> {{ ucfirst($orgAvailabilityRule->availability_mode) }}
            </div>
        </div>
    @endif
</div>

<script>
(function () {
    let rateIndex = {{ $existingPlans->count() }};
    document.getElementById('addRateRow')?.addEventListener('click', function () {
        const tbody = document.querySelector('#ratePlansTable tbody');
        const row = document.createElement('tr');
        row.innerHTML = `
            <td><input type="text" class="form-control form-control-sm" name="rate_plans[${rateIndex}][name]" placeholder="e.g. Summer"></td>
            <td><input type="text" class="form-control form-control-sm" name="rate_plans[${rateIndex}][from]" placeholder="e.g. June"></td>
            <td><input type="text" class="form-control form-control-sm" name="rate_plans[${rateIndex}][to]" placeholder="e.g. August"></td>
            <td><input type="number" step="0.01" class="form-control form-control-sm" name="rate_plans[${rateIndex}][price]" placeholder="0.00"></td>
            <td><button type="button" class="btn btn-sm btn-link text-danger remove-rate-row"><i class="fa fa-trash"></i></button></td>
        `;
        tbody.appendChild(row);
        rateIndex++;
    });
    document.querySelector('#ratePlansTable')?.addEventListener('click', function (e) {
        if (e.target.closest('.remove-rate-row')) e.target.closest('tr').remove();
    });
})();
</script>

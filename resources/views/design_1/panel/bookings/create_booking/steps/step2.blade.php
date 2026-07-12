{{--
    Step 2 — Pricing & Availability

    FIX (Step 2 requirement — validation error messages):
    Pehle is step ke fields (price, capacity, inventory, duration_minutes,
    deposit_amount/type, rate_plans.*.name/price) pe koi @error() / is-invalid
    nahi tha. Isse jab required validation fail hoti thi (e.g. rate_plans.*.price
    required_with), form wapas aa jata tha lekin user ko pata nahi chalta tha
    exactly konsi field/row galat hai — sirf top-level generic error list
    (index.blade.php mein) dikhti thi. Ab har field apna khud ka inline error
    dikhata hai, jaisa admin form mein already ho raha hai.
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

{{-- FIX: general error summary for this step, same pattern as admin form --}}
@if ($errors->any())
    <div class="alert alert-danger" id="step2ErrorsAlert">
        <strong>{{ $errors->count() }} {{ $errors->count() == 1 ? 'error' : 'errors' }} found — please check the highlighted fields below:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="panel-card">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-money"></i></div>
        <div><h6>Booking Cost</h6></div>
    </div>

    <div class="row">
        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>{{ $fieldLabel('price', 'Base Price') }} <span class="text-danger">*</span></label>
                <input name="price" type="number" step="0.01" min="0"
                       class="form-control @error('price') is-invalid @enderror"
                       value="{{ old('price', $booking->price ?? '') }}" placeholder="0.00">
                <small class="text-muted">{{ $priceUnitLabel }}</small>
                @error('price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Discount Price</label>
                <input name="discount_price" type="number" step="0.01" min="0"
                       class="form-control @error('discount_price') is-invalid @enderror"
                       value="{{ old('discount_price', $booking->discount_price ?? '') }}" placeholder="0.00">
                @error('discount_price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Currency</label>
                <select name="currency" class="form-control @error('currency') is-invalid @enderror">
                    @foreach(['USD','EUR','GBP','PKR','AED','SAR','INR'] as $cur)
                        <option value="{{ $cur }}" {{ old('currency', $booking->currency ?? 'USD') === $cur ? 'selected' : '' }}>{{ $cur }}</option>
                    @endforeach
                </select>
                @error('currency')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        <div class="col-12 col-md-3">
            <div class="form-group">
                <label>Price Unit</label>
                <input name="price_unit" type="text"
                       class="form-control @error('price_unit') is-invalid @enderror"
                       value="{{ old('price_unit', $subTemplate ? $priceUnitLabel : ($booking->price_unit ?? $priceUnitLabel)) }}" placeholder="per night, per adult">
                @error('price_unit')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
            </div>
        </div>

        @if(in_array('price_per', $tplFields))
            <div class="col-12 col-md-4">
                <div class="form-group">
                    <label>Price per Extra Person / Hour</label>
                    <input name="price_per" type="number" step="0.01" min="0"
                           class="form-control @error('price_per') is-invalid @enderror"
                           value="{{ old('price_per', $booking->price_per ?? '') }}" placeholder="0.00">
                    @error('price_per')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        @endif

        @if(in_array('duration_minutes', $tplFields))
            <div class="col-12 col-md-4">
                <div class="form-group mb-0">
                    <label>{{ $fieldLabel('duration_minutes', 'Duration (minutes)') }} @if($isRequired('duration_minutes')) <span class="text-danger">*</span> @endif</label>
                    <input name="duration_minutes" type="number" min="0"
                           class="form-control @error('duration_minutes') is-invalid @enderror"
                           value="{{ old('duration_minutes', $booking->duration_minutes ?? '') }}" {{ $isRequired('duration_minutes') ? 'required' : '' }}>
                    @error('duration_minutes')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
        @endif
    </div>
</div>

{{-- ── Capacity & Inventory ──────────────────────────────────────────── --}}
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
                        <input name="capacity" type="number" min="1"
                               class="form-control @error('capacity') is-invalid @enderror"
                               value="{{ old('capacity', $booking->capacity ?? '') }}" placeholder="Leave empty for unlimited" {{ $isRequired('capacity') ? 'required' : '' }}>
                        @error('capacity')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            @endif
            @if(in_array('inventory', $tplFields))
                <div class="col-12 col-md-6">
                    <div class="form-group mb-0">
                        <label>{{ $fieldLabel('inventory', 'Available Tickets / Seats') }} @if($isRequired('inventory')) <span class="text-danger">*</span> @endif</label>
                        <input name="inventory" type="number" min="0"
                               class="form-control @error('inventory') is-invalid @enderror"
                               value="{{ old('inventory', $booking->inventory ?? '') }}" placeholder="Leave empty for unlimited" {{ $isRequired('inventory') ? 'required' : '' }}>
                        @error('inventory')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- ── Deposit ──────────────────────────────────────────────────────────── --}}
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
                    <input name="deposit_amount" type="number" step="0.01" min="0"
                           class="form-control @error('deposit_amount') is-invalid @enderror"
                           value="{{ old('deposit_amount', $booking->deposit_amount ?? '') }}">
                    @error('deposit_amount')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                </div>
            </div>
            <div class="col-12 col-md-6">
                <div class="form-group mb-0">
                    <label>Deposit Type</label>
                    <select name="deposit_type" class="form-control @error('deposit_type') is-invalid @enderror">
                        <option value="fixed" {{ old('deposit_type', $booking->deposit_type ?? '') === 'fixed' ? 'selected' : '' }}>Fixed Amount</option>
                        <option value="percentage" {{ old('deposit_type', $booking->deposit_type ?? '') === 'percentage' ? 'selected' : '' }}>Percentage of Price</option>
                    </select>
                    @error('deposit_type')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
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

    {{-- FIX: rate_plans ke liye bhi koi field-level error nahi tha.
         Laravel nested-array error keys "rate_plans.{i}.name" / "rate_plans.{i}.price"
         format mein aati hain — is index ($i) ke hisaab se hi @error() call
         karna zaroori hai taake sahi row pe sahi error dikhe. --}}
    <div class="mini-table-wrap">
        <table class="mini-table" id="ratePlansTable">
            <thead>
                <tr><th>Name</th><th>From</th><th>To</th><th>Price</th><th></th></tr>
            </thead>
            <tbody>
                @foreach($existingPlans as $i => $plan)
                    <tr>
                        <td>
                            <input type="text" class="form-control form-control-sm @error('rate_plans.'.$i.'.name') is-invalid @enderror"
                                   name="rate_plans[{{ $i }}][name]" value="{{ $plan->name }}" placeholder="e.g. Summer">
                            @error('rate_plans.'.$i.'.name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
                        <td><input type="text" class="form-control form-control-sm" name="rate_plans[{{ $i }}][from]" value="{{ $plan->conditions['from'] ?? '' }}" placeholder="e.g. June"></td>
                        <td><input type="text" class="form-control form-control-sm" name="rate_plans[{{ $i }}][to]" value="{{ $plan->conditions['to'] ?? '' }}" placeholder="e.g. August"></td>
                        <td>
                            <input type="number" step="0.01" class="form-control form-control-sm @error('rate_plans.'.$i.'.price') is-invalid @enderror"
                                   name="rate_plans[{{ $i }}][price]" value="{{ $plan->price }}" placeholder="0.00">
                            @error('rate_plans.'.$i.'.price')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </td>
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

    // FIX: agar server-side error is step ke kisi field mein hai to us field
    // tak auto-scroll karo, taake user ko fauran nazar aaye (khaaskar tab
    // jab wo pehle se scroll ho chuka ho ya field neeche ho).
    var firstInvalid = document.querySelector('.is-invalid');
    if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
})();
</script>
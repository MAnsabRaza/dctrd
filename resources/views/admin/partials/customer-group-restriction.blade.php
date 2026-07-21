@php
    $selectedGroups = old('allowed_customer_groups', $item->allowed_customer_groups ?? []);
    $customerGroups = [
        'individual' => 'Individual', 'student' => 'Student', 'store' => 'Store',
        'wholeseller' => 'Wholeseller', 'importer' => 'Importer', 'advertiser' => 'Advertiser',
        'promoter' => 'Promoter', 'travel_agency_ota' => 'Travel Agency/OTA', 'tour_operator_cust' => 'Tour Operator',
    ];
@endphp

<div class="booking-section" id="section-customer-group">
    <h3 class="booking-section-title">Customer Group Restriction</h3>
    <p class="text-muted text-small mb-3">Khali chhodo agar sab customer groups khareed sakein.</p>

    <select name="allowed_customer_groups[]" multiple data-plugin-selectTwo class="form-control">
        @foreach($customerGroups as $key => $label)
            <option value="{{ $key }}" {{ in_array($key, (array) $selectedGroups) ? 'selected' : '' }}>
                {{ $label }}
            </option>
        @endforeach
    </select>
</div>
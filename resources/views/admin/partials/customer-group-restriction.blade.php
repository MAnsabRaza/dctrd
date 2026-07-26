@php
    $selectedGroups = old('allowed_customer_groups', $item->allowed_customer_groups ?? []);
    $customerGroups = [
        'individual' => 'Individual', 'student' => 'Student', 'store' => 'Store',
        'wholeseller' => 'Wholeseller', 'importer' => 'Importer', 'advertiser' => 'Advertiser',
        'promoter' => 'Promoter', 'travel_agency_ota' => 'Travel Agency/OTA', 'tour_operator_cust' => 'Tour Operator',
    ];
@endphp

<div class="booking-section" id="section-customer-group">
    <div class="border rounded-12 p-16" id="customerGroupRestrictionBox">
        <div class="form-group mb-3">
            <label class="mb-1 d-block font-weight-bold">Customer Group Restriction</label>
            <p class="text-muted text-small mb-0">Khali chhodo agar sab customer groups khareed sakein.</p>
        </div>

        <div class="form-group mb-0">
            <select name="allowed_customer_groups[]" id="allowedCustomerGroups" multiple
                data-plugin-selectTwo class="form-control" style="width:100%">
                @foreach($customerGroups as $key => $label)
                    <option value="{{ $key }}" {{ in_array($key, (array) $selectedGroups) ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</div>

<script>
    $(document).ready(function () {
        $('#allowedCustomerGroups').selectTwo({
            width: '100%',
            placeholder: 'Select customer groups'
        });
    });
</script>
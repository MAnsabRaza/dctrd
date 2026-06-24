{{--
    Step 8 — Review & Submit
--}}
<div class="section-head">
    <div class="badge-icon"><i class="fa fa-paper-plane"></i></div>
    <div>
        <h6>Review &amp; Submit</h6>
        <p class="section-sub">A couple of final messages, then send this for review.</p>
    </div>
</div>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="panel-card h-100 mb-0">
            <div class="section-head mb-3">
                <div class="badge-icon"><i class="fa fa-comment"></i></div>
                <div><h6>Message to Buyer</h6></div>
            </div>
            <div class="form-group mb-0">
                <textarea name="checkout_message" rows="6" class="form-control"
                          placeholder="Shown to the customer after they complete checkout.">{{ old('checkout_message', $booking->checkout_message) }}</textarea>
            </div>
        </div>
    </div>
    <div class="col-12 col-md-6 mt-4 mt-md-0">
        <div class="panel-card h-100 mb-0">
            <div class="section-head mb-3">
                <div class="badge-icon"><i class="fa fa-sticky-note"></i></div>
                <div><h6>Message to Reviewer</h6></div>
            </div>
            <div class="form-group mb-0">
                <textarea name="reviewer_message" rows="6" class="form-control"
                          placeholder="Internal note for the team reviewing this listing.">{{ old('reviewer_message', $booking->reviewer_message) }}</textarea>
            </div>
        </div>
    </div>
</div>

<div class="panel-card mt-4">
    <div class="booking-switch-row">
        <label class="booking-switch" for="termsAccepted">
            <input type="checkbox" id="termsAccepted" name="terms_accepted"
                   class="@error('terms_accepted') is-invalid @enderror"
                   {{ old('terms_accepted', $booking->meta['terms_accepted'] ?? false) ? 'checked' : '' }}>
            <span class="booking-switch-slider"></span>
        </label>
        <label class="booking-switch-label mb-0" for="termsAccepted">
            I agree to the <a href="{{ url('/terms') }}" target="_blank">terms and conditions</a>
        </label>
    </div>
    @error('terms_accepted')<div class="text-danger small mt-1">{{ $message }}</div>@enderror
</div>

<div class="panel-card mb-0">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-lightbulb-o"></i></div>
        <div><h6>Tips &amp; Policies</h6></div>
    </div>
    <ul class="small text-muted mb-0 pl-3">
        <li class="mb-1">Make your title clear so users understand immediately.</li>
        <li class="mb-1">Use a high-quality cover image for professionalism.</li>
        <li class="mb-1">Check pricing and discounts carefully before publishing.</li>
        <li class="mb-1">Write clear, compelling descriptions highlighting key points.</li>
        <li class="mb-1">Add relevant keywords and categorize for easy discovery.</li>
        <li class="mb-1">Upload only original content you own or licensed.</li>
        <li class="mb-1">Clarify refund terms, service rules, and conditions upfront.</li>
        <li class="mb-1">List all fees, amenities, and known constraints.</li>
        <li class="mb-0">Be responsive to questions and feedback from buyers.</li>
    </ul>
</div>
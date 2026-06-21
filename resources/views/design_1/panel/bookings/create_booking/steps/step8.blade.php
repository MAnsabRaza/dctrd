{{--
    Step 8 — Review & Submit
--}}
<h5 class="mb-1">Review &amp; Submit</h5>
<p class="text-muted mb-4">A couple of final messages, then send this for review.</p>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>Message to Buyer</label>
            <textarea name="checkout_message" rows="6" class="form-control"
                      placeholder="Shown to the customer after they complete checkout.">{{ old('checkout_message', $booking->checkout_message) }}</textarea>
        </div>
    </div>
    <div class="col-12 col-md-6">
        <div class="form-group">
            <label>Message to Reviewer</label>
            <textarea name="reviewer_message" rows="6" class="form-control"
                      placeholder="Internal note for the team reviewing this listing.">{{ old('reviewer_message', $booking->reviewer_message) }}</textarea>
        </div>
    </div>

    <div class="col-12">
        <div class="form-check mt-2 mb-4">
            <input class="form-check-input @error('terms_accepted') is-invalid @enderror" type="checkbox" id="termsAccepted" name="terms_accepted"
                   {{ old('terms_accepted', $booking->terms_accepted) ? 'checked' : '' }}>
            <label class="form-check-label" for="termsAccepted">
                I agree to the <a href="{{ url('/terms') }}" target="_blank">terms and conditions</a>
            </label>
            @error('terms_accepted')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>
    </div>

    <div class="col-12">
        <div class="card bg-light border-0">
            <div class="card-body">
                <h6 class="font-weight-bold">Tips &amp; Policies</h6>
                <ul class="small text-muted mb-0">
                    <li>Make your title clear so users understand immediately.</li>
                    <li>Use a high-quality cover image for professionalism.</li>
                    <li>Check pricing and discounts carefully before publishing.</li>
                    <li>Write clear, compelling descriptions highlighting key points.</li>
                    <li>Add relevant keywords and categorize for easy discovery.</li>
                    <li>Upload only original content you own or licensed.</li>
                    <li>Clarify refund terms, service rules, and conditions upfront.</li>
                    <li>List all fees, amenities, and known constraints.</li>
                    <li>Be responsive to questions and feedback from buyers.</li>
                </ul>
            </div>
        </div>
    </div>
</div>

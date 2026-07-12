{{--
    Step 5 — Prerequisites & Related

    FIX (Step 2 requirement — validation error messages):
    Nothing here was strictly required, but related_booking_ids.* has an
    "exists:bookings,id" rule and prerequisite_text/related_booking_ids can
    still fail (e.g. stale/removed booking id in the checkbox list). Added
    the same error-summary + field-level pattern used elsewhere so this step
    is consistent with the rest of the wizard.
--}}
@php
    $relatedIds = old('related_booking_ids', $booking->meta['related_booking_ids'] ?? []);
    $allBookingsList = $allBookings ?? collect();
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-list"></i></div>
    <div>
        <h6>Prerequisites &amp; Related</h6>
        <p class="section-sub">Add anything customers should know beforehand or related listings to surface on the page.</p>
    </div>
</div>

{{-- FIX: general error summary for this step --}}
@if ($errors->any())
    <div class="alert alert-danger" id="step5ErrorsAlert">
        <strong>{{ $errors->count() }} {{ $errors->count() == 1 ? 'error' : 'errors' }} found — please check the highlighted fields below:</strong>
        <ul class="mb-0 mt-2">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row">
    <div class="col-12 col-md-6">
        <div class="panel-card h-100 mb-0">
            <div class="section-head mb-3">
                <div class="badge-icon"><i class="fa fa-shield"></i></div>
                <div>
                    <h6>Prerequisites</h6>
                    <p class="section-sub">Anything a customer needs to have or do before booking.</p>
                </div>
            </div>

            <div class="form-group mb-0">
                <textarea name="prerequisite_text" rows="8" class="form-control @error('prerequisite_text') is-invalid @enderror"
                          placeholder="e.g. Must be 18+, valid ID required, swimming ability required">{{ old('prerequisite_text', $booking->meta['prerequisite_text'] ?? '') }}</textarea>
                @error('prerequisite_text')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6 mt-4 mt-md-0">
        <div class="panel-card h-100 mb-0">
            <div class="section-head mb-3">
                <div class="badge-icon"><i class="fa fa-link"></i></div>
                <div>
                    <h6>Related Bookings</h6>
                    <p class="section-sub">Show related listings on this booking's page to help customers discover more.</p>
                </div>
            </div>

            @if($allBookingsList->isEmpty())
                <div class="empty-state">
                    <div class="badge-icon"><i class="fa fa-link"></i></div>
                    <div class="empty-title">No other bookings available yet</div>
                </div>
            @else
                <div class="form-group mb-0 @error('related_booking_ids') is-invalid @enderror" style="max-height:260px; overflow-y:auto;">
                    @foreach($allBookingsList as $related)
                        <div class="form-check py-1">
                            <input class="form-check-input @error('related_booking_ids.'.$loop->index) is-invalid @enderror"
                                   type="checkbox"
                                   name="related_booking_ids[]" value="{{ $related->id }}"
                                   id="related_{{ $related->id }}"
                                   {{ in_array($related->id, $relatedIds) ? 'checked' : '' }}>
                            <label class="form-check-label" for="related_{{ $related->id }}">{{ $related->title }}</label>
                        </div>
                    @endforeach
                </div>
                @error('related_booking_ids')
                    <div class="invalid-feedback d-block">{{ $message }}</div>
                @enderror
                @foreach($relatedIds as $idx => $relatedId)
                    @error('related_booking_ids.'.$idx)
                        <div class="text-danger small mt-1">{{ $message }}</div>
                    @enderror
                @endforeach
            @endif
        </div>
    </div>
</div>

<script>
(function () {
    var firstInvalid = document.querySelector('.is-invalid');
    if (firstInvalid) {
        firstInvalid.scrollIntoView({ behavior: 'smooth', block: 'center' });
    }
})();
</script>
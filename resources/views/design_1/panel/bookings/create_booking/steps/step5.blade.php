{{--
    Step 5 — Prerequisites & Related
--}}
@php
    $relatedIds = old('related_booking_ids', $booking->meta['related_booking_ids'] ?? []);
    $allBookingsList = $allBookings ?? collect();
@endphp

<h5 class="mb-1">Prerequisites &amp; Related</h5>
<p class="text-muted mb-4">Add anything customers should know beforehand or related listings to surface on the page.</p>

<div class="row">
    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="font-weight-bold">Prerequisites</h6>
                <p class="text-muted small">Anything a customer needs to have or do before booking.</p>
                <div class="form-group">
                    <textarea name="prerequisite_text" rows="6" class="form-control"
                              placeholder="e.g. Must be 18+, valid ID required, swimming ability required">{{ old('prerequisite_text', $booking->meta['prerequisite_text'] ?? '') }}</textarea>
                </div>
            </div>
        </div>
    </div>

    <div class="col-12 col-md-6">
        <div class="card h-100">
            <div class="card-body">
                <h6 class="font-weight-bold">Related Bookings</h6>
                <p class="text-muted small">Show related listings on this booking's page to help customers discover more.</p>

                @if($allBookingsList->isEmpty())
                    <div class="text-center text-muted py-4">No other bookings available yet.</div>
                @else
                    <div class="form-group" style="max-height:260px; overflow-y:auto;">
                        @foreach($allBookingsList as $related)
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox"
                                       name="related_booking_ids[]" value="{{ $related->id }}"
                                       id="related_{{ $related->id }}"
                                       {{ in_array($related->id, $relatedIds) ? 'checked' : '' }}>
                                <label class="form-check-label" for="related_{{ $related->id }}">{{ $related->title }}</label>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

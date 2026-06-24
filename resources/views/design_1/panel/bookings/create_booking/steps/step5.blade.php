{{--
    Step 5 — Prerequisites & Related
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
                <textarea name="prerequisite_text" rows="8" class="form-control"
                          placeholder="e.g. Must be 18+, valid ID required, swimming ability required">{{ old('prerequisite_text', $booking->meta['prerequisite_text'] ?? '') }}</textarea>
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
                <div class="form-group mb-0" style="max-height:260px; overflow-y:auto;">
                    @foreach($allBookingsList as $related)
                        <div class="form-check py-1">
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
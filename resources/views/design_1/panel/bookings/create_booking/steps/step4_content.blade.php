{{--
    Step 4 of 8 — Content
    Maps to Image 6: "Sections" list, each a labeled block of content.
    Stored in booking.meta.content_sections (no dedicated table requested).
--}}
@php
    $sections = old('content_sections', $booking->meta['content_sections'] ?? []);
@endphp

<form data-wiz-form id="stepContentForm">
    <div class="d-flex justify-content-between align-items-center mb-1">
        <h5 class="mb-0">Content</h5>
        <button type="button" class="btn btn-sm btn-primary" id="addSectionBtn">
            <i class="fa fa-plus mr-1"></i> New Section
        </button>
    </div>
    <p class="text-muted mb-4">Organize the booking's content into sections (e.g. what's included, itinerary, house rules).</p>

    <div id="sectionsList">
        @forelse($sections as $i => $section)
            <div class="card mb-2 section-card">
                <div class="card-body py-2">
                    <div class="form-row align-items-start">
                        <div class="col-12 col-md-4">
                            <input type="text" class="form-control form-control-sm mb-1"
                                   name="content_sections[{{ $i }}][title]"
                                   placeholder="Section title"
                                   value="{{ $section['title'] ?? '' }}">
                        </div>
                        <div class="col-12 col-md-7">
                            <textarea class="form-control form-control-sm"
                                      name="content_sections[{{ $i }}][body]"
                                      rows="2" placeholder="Section content">{{ $section['body'] ?? '' }}</textarea>
                        </div>
                        <div class="col-12 col-md-1 text-right">
                            <button type="button" class="btn btn-sm btn-link text-danger remove-section"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4" id="emptySectionsHint">
                No sections yet. Click "New Section" to add your first one.
            </div>
        @endforelse
    </div>
</form>

<script>
(function () {
    let sectionIndex = {{ count($sections) }};

    document.getElementById('addSectionBtn')?.addEventListener('click', function () {
        document.getElementById('emptySectionsHint')?.remove();

        const wrap = document.createElement('div');
        wrap.className = 'card mb-2 section-card';
        wrap.innerHTML = `
            <div class="card-body py-2">
                <div class="form-row align-items-start">
                    <div class="col-12 col-md-4">
                        <input type="text" class="form-control form-control-sm mb-1" name="content_sections[${sectionIndex}][title]" placeholder="Section title">
                    </div>
                    <div class="col-12 col-md-7">
                        <textarea class="form-control form-control-sm" name="content_sections[${sectionIndex}][body]" rows="2" placeholder="Section content"></textarea>
                    </div>
                    <div class="col-12 col-md-1 text-right">
                        <button type="button" class="btn btn-sm btn-link text-danger remove-section"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>`;
        document.getElementById('sectionsList').appendChild(wrap);
        sectionIndex++;
    });

    document.getElementById('sectionsList')?.addEventListener('click', function (e) {
        if (e.target.closest('.remove-section')) {
            e.target.closest('.section-card').remove();
        }
    });
})();
</script>

{{--
    Step 4 — Content
--}}
@php
    $sections = old('content_sections', $booking->meta['content_sections'] ?? []);
@endphp

<div class="section-head">
    <div class="badge-icon"><i class="fa fa-align-left"></i></div>
    <div>
        <h6>Content</h6>
        <p class="section-sub">Organize the booking's content into sections (e.g. what's included, itinerary, house rules).</p>
    </div>
    <div class="section-head-actions">
        <button type="button" class="btn btn-sm btn-primary" id="addSectionBtn">
            <i class="fa fa-plus mr-1"></i> New Section
        </button>
    </div>
</div>

<div class="panel-card">
    <div id="sectionsList">
        @forelse($sections as $i => $section)
            <div class="card mb-2 section-card border">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start">
                        <div class="badge-icon mr-3 mt-1"><i class="fa fa-list-ul"></i></div>
                        <div class="flex-grow-1">
                            <div class="form-row">
                                <div class="col-12 col-md-4">
                                    <input type="text" class="form-control form-control-sm mb-2"
                                           name="content_sections[{{ $i }}][title]"
                                           placeholder="Section title"
                                           value="{{ $section['title'] ?? '' }}">
                                </div>
                                <div class="col-12 col-md-8">
                                    <textarea class="form-control form-control-sm"
                                              name="content_sections[{{ $i }}][body]"
                                              rows="2" placeholder="Section content">{{ $section['body'] ?? '' }}</textarea>
                                </div>
                            </div>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger remove-section ml-2"><i class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" id="emptySectionsHint">
                <div class="badge-icon"><i class="fa fa-align-left"></i></div>
                <div class="empty-title">No sections yet</div>
                <div class="empty-sub">Click "New Section" to add your first one.</div>
            </div>
        @endforelse
    </div>
</div>

<script>
(function () {
    let sectionIndex = {{ count($sections) }};
    document.getElementById('addSectionBtn')?.addEventListener('click', function () {
        document.getElementById('emptySectionsHint')?.remove();
        const wrap = document.createElement('div');
        wrap.className = 'card mb-2 section-card border';
        wrap.innerHTML = `
            <div class="card-body py-3">
                <div class="d-flex align-items-start">
                    <div class="badge-icon mr-3 mt-1"><i class="fa fa-list-ul"></i></div>
                    <div class="flex-grow-1">
                        <div class="form-row">
                            <div class="col-12 col-md-4">
                                <input type="text" class="form-control form-control-sm mb-2" name="content_sections[${sectionIndex}][title]" placeholder="Section title">
                            </div>
                            <div class="col-12 col-md-8">
                                <textarea class="form-control form-control-sm" name="content_sections[${sectionIndex}][body]" rows="2" placeholder="Section content"></textarea>
                            </div>
                        </div>
                    </div>
                    <button type="button" class="btn btn-sm btn-link text-danger remove-section ml-2"><i class="fa fa-trash"></i></button>
                </div>
            </div>`;
        document.getElementById('sectionsList').appendChild(wrap);
        sectionIndex++;
    });
    document.getElementById('sectionsList')?.addEventListener('click', function (e) {
        if (e.target.closest('.remove-section')) e.target.closest('.section-card').remove();
    });
})();
</script>
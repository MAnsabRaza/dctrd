{{--
Step 6 — FAQ
--}}
<div class="section-head">
    <div class="badge-icon"><i class="fa fa-question-circle"></i></div>
    <div>
        <h6>Frequently Asked Questions</h6>
        <p class="section-sub">Answer common questions upfront to reduce back-and-forth with customers.</p>
    </div>
</div>

<div class="panel-card">
    <div id="faqList">
        @forelse($booking->faqs ?? [] as $faq)
            <div class="card mb-2 faq-card border" data-id="{{ $faq->id }}">
                <div class="card-body py-3">
                    <div class="d-flex align-items-start">
                        <div class="badge-icon mr-3 mt-1"><i class="fa fa-question"></i></div>
                        <div class="flex-grow-1">
                            <strong class="d-block small mb-1">{{ $faq->title }}</strong>
                            <span class="text-muted small">{{ $faq->answer }}</span>
                        </div>
                        <button type="button" class="btn btn-sm btn-link text-danger remove-faq ml-2"><i
                                class="fa fa-trash"></i></button>
                    </div>
                </div>
            </div>
        @empty
            <div class="empty-state" id="emptyFaqHint">
                <div class="badge-icon"><i class="fa fa-question-circle"></i></div>
                <div class="empty-title">No FAQs yet</div>
            </div>
        @endforelse
    </div>
</div>

<div class="panel-card mb-0">
    <div class="section-head mb-3">
        <div class="badge-icon"><i class="fa fa-plus"></i></div>
        <div>
            <h6>Add a question</h6>
        </div>
    </div>
    <div class="form-group">
        <label>Question</label>
        <input type="text" class="form-control" id="newFaqQuestion" placeholder="e.g. What's the cancellation policy?">
    </div>
    <div class="form-group">
        <label>Answer</label>
        <textarea class="form-control" id="newFaqAnswer" rows="2" placeholder="Answer"></textarea>
    </div>
    <button type="button" class="btn btn-primary" id="addFaqBtn">
        <i class="fa fa-plus mr-1"></i> Add FAQ
    </button>
</div>

<script>
    (function () {
        @if(!empty($booking->id))
            const bookingId = {{ $booking->id }};
            const csrf = '{{ csrf_token() }}';

            document.getElementById('addFaqBtn')?.addEventListener('click', function () {
                const title = document.getElementById('newFaqQuestion').value.trim();
                const answer = document.getElementById('newFaqAnswer').value.trim();
                if (!title) return;

                fetch(`/panel/bookings/${bookingId}/faqs`, {
                    method: 'POST',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
                    body: JSON.stringify({ title, answer })
                })
                    .then(r => r.json())
                    .then(data => {
                        if (!data.success) return;
                        document.getElementById('emptyFaqHint')?.remove();
                        const faq = data.faq;
                        const card = document.createElement('div');
                        card.className = 'card mb-2 faq-card border';
                        card.dataset.id = faq.id;
                        card.innerHTML = `
                    <div class="card-body py-3">
                        <div class="d-flex align-items-start">
                            <div class="badge-icon mr-3 mt-1"><i class="fa fa-question"></i></div>
                            <div class="flex-grow-1">
                                <strong class="d-block small mb-1">${faq.question}</strong>
                                <span class="text-muted small">${faq.answer ?? ''}</span>
                            </div>
                            <button type="button" class="btn btn-sm btn-link text-danger remove-faq ml-2"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>`;
                        document.getElementById('faqList').appendChild(card);
                        document.getElementById('newFaqQuestion').value = '';
                        document.getElementById('newFaqAnswer').value = '';
                    });
            });

            document.getElementById('faqList')?.addEventListener('click', function (e) {
                const btn = e.target.closest('.remove-faq');
                if (!btn) return;
                const card = btn.closest('.faq-card');
                fetch(`/panel/bookings/faqs/${card.dataset.id}`, {
                    method: 'DELETE',
                    headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
                })
                    .then(r => r.json())
                    .then(data => { if (data.success) card.remove(); });
            });
        @endif
})();
</script>
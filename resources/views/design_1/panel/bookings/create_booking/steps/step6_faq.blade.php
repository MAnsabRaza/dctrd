{{--
    Step 6 of 8 — FAQ
--}}
<div id="stepFaqWrapper">
    <h5 class="mb-1">Frequently Asked Questions</h5>
    <p class="text-muted mb-4">Answer common questions upfront to reduce back-and-forth with customers.</p>

    <div id="faqList">
        @forelse($booking->faqs ?? [] as $faq)
            <div class="card mb-2 faq-card" data-id="{{ $faq->id }}">
                <div class="card-body py-2">
                    <div class="form-row">
                        <div class="col-12 col-md-5">
                            <strong class="d-block small">{{ $faq->question }}</strong>
                        </div>
                        <div class="col-12 col-md-6">
                            <span class="text-muted small">{{ $faq->answer }}</span>
                        </div>
                        <div class="col-12 col-md-1 text-right">
                            <button type="button" class="btn btn-sm btn-link text-danger remove-faq"><i class="fa fa-trash"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="text-center text-muted py-4" id="emptyFaqHint">
                No FAQs yet.
            </div>
        @endforelse
    </div>

    <div class="card mt-3">
        <div class="card-body">
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
    </div>
</div>

<script>
(function () {
    const bookingId = document.getElementById('bookingWizardApp')?.dataset.bookingId;
    const baseUrl = '{{ url('panel/bookings/wizard') }}';
    const csrf = '{{ csrf_token() }}';

    document.getElementById('addFaqBtn')?.addEventListener('click', function () {
        const question = document.getElementById('newFaqQuestion').value.trim();
        const answer = document.getElementById('newFaqAnswer').value.trim();
        if (!question) return;

        fetch(`${baseUrl}/${bookingId}/faqs`, {
            method: 'POST',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf, 'Content-Type': 'application/json' },
            body: JSON.stringify({ question, answer })
        })
        .then(r => r.json())
        .then(data => {
            if (!data.success) return;
            document.getElementById('emptyFaqHint')?.remove();
            const faq = data.faq;
            const card = document.createElement('div');
            card.className = 'card mb-2 faq-card';
            card.dataset.id = faq.id;
            card.innerHTML = `
                <div class="card-body py-2">
                    <div class="form-row">
                        <div class="col-12 col-md-5"><strong class="d-block small">${faq.question}</strong></div>
                        <div class="col-12 col-md-6"><span class="text-muted small">${faq.answer ?? ''}</span></div>
                        <div class="col-12 col-md-1 text-right">
                            <button type="button" class="btn btn-sm btn-link text-danger remove-faq"><i class="fa fa-trash"></i></button>
                        </div>
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
        fetch(`${baseUrl}/faqs/${card.dataset.id}`, {
            method: 'DELETE',
            headers: { 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': csrf }
        })
        .then(r => r.json())
        .then(data => { if (data.success) card.remove(); });
    });
})();
</script>

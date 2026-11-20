{{-- Newsletter Subscription Modal --}}
<div class="modal fade" id="newsletterModal" tabindex="-1" aria-labelledby="newsletterModalLabel" aria-hidden="true"
    data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content pro-news-popup">
            <div class="modal-body text-center position-relative">
                <button type="button" class="btn-close position-absolute top-0 end-0 m-2 js-news-dismiss"
                    data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                <h2 class="h5 mb-2" id="newsletterModalLabel">{{ __('Get 10% off your first order') }}</h2>
                <p class="text-muted small mb-3">{{ __('Subscribe for deals, new arrivals & seller updates.') }}</p>
                <form id="proNewsPopupForm" class="d-flex flex-column gap-2">
                    @csrf
                    <input type="email" name="email" required class="form-control rounded-pill"
                        placeholder="{{ __('Your email') }}" autocomplete="email" aria-label="{{ __('Email') }}">
                    <button type="submit" class="btn btn-primary rounded-pill">{{ __('Subscribe') }}</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    (function () {
        @if(!config('holiday.enabled'))
        var newsEl = document.getElementById('newsletterModal');
        if (newsEl) {
            newsEl.addEventListener('hidden.bs.modal', function () {
                localStorage.setItem('zm_news_popup', '1');
            });
        }
        if (!localStorage.getItem('zm_news_popup') && window.bootstrap && newsEl) {
            setTimeout(function () {
                new bootstrap.Modal(newsEl).show();
            }, 20000);
        }
        document.getElementById('proNewsPopupForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            localStorage.setItem('zm_news_popup', '1');
            bootstrap.Modal.getInstance(newsEl)?.hide();
        });
        @endif
    })();
</script>

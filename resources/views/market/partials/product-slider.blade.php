{{-- Expects: $products (iterable), $sliderId (string unique id) --}}
<div class="cb-product-slider">
    <div class="cb-product-slider__viewport">
        <button type="button" class="cb-slider-btn cb-slider-btn--prev" aria-controls="{{ $sliderId }}" aria-label="{{ __('Previous') }}">
            <i class="bi bi-chevron-left fs-5" aria-hidden="true"></i>
        </button>
        <div class="cb-scroll-row cb-product-slider__track" id="{{ $sliderId }}" tabindex="0" data-slider-track>
            @foreach($products as $product)
                @include('market.partials.product-card', ['product' => $product])
            @endforeach
        </div>
        <button type="button" class="cb-slider-btn cb-slider-btn--next" aria-controls="{{ $sliderId }}" aria-label="{{ __('Next') }}">
            <i class="bi bi-chevron-right fs-5" aria-hidden="true"></i>
        </button>
    </div>
</div>

@once
@push('scripts')
<script>
(function () {
    function gapPx(el) {
        var st = window.getComputedStyle(el);
        var g = parseFloat(st.gap || st.columnGap);
        return isNaN(g) ? 16 : g;
    }
    function step(track) {
        var card = track.querySelector('.zm-pro-card, .basic-product, .cb-pcard');
        if (!card) return Math.max(200, track.clientWidth * 0.82);
        return card.getBoundingClientRect().width + gapPx(track);
    }
    function updateArrows(track, prev, next) {
        var max = Math.max(0, track.scrollWidth - track.clientWidth - 1);
        prev.disabled = track.scrollLeft <= 2;
        next.disabled = track.scrollLeft >= max - 2;
        prev.classList.toggle('is-disabled', prev.disabled);
        next.classList.toggle('is-disabled', next.disabled);
    }
    function bind(wrap) {
        var track = wrap.querySelector('[data-slider-track]');
        var prev = wrap.querySelector('.cb-slider-btn--prev');
        var next = wrap.querySelector('.cb-slider-btn--next');
        if (!track || !prev || !next) return;

        prev.addEventListener('click', function () {
            track.scrollBy({ left: -step(track), behavior: 'smooth' });
        });
        next.addEventListener('click', function () {
            track.scrollBy({ left: step(track), behavior: 'smooth' });
        });
        track.addEventListener('scroll', function () {
            updateArrows(track, prev, next);
        }, { passive: true });
        window.addEventListener('resize', function () {
            updateArrows(track, prev, next);
        });
        track.addEventListener('keydown', function (e) {
            if (e.key === 'ArrowLeft') {
                e.preventDefault();
                track.scrollBy({ left: -step(track), behavior: 'smooth' });
            } else if (e.key === 'ArrowRight') {
                e.preventDefault();
                track.scrollBy({ left: step(track), behavior: 'smooth' });
            }
        });
        updateArrows(track, prev, next);
    }
    function init() {
        document.querySelectorAll('.cb-product-slider').forEach(bind);
    }
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }
})();
</script>
@endpush
@endonce

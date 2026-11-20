{{-- Storefront Page Preloader --}}
<div id="mkPagePreloader" class="mk-page-preloader" role="alert" aria-live="polite" aria-busy="true"
    aria-label="{{ __('Loading') }}">
    <div class="mk-page-preloader__inner">
        <div class="mk-page-preloader__plate">
            <span class="mk-page-preloader__ring" aria-hidden="true"></span>
        </div>
    </div>
</div>

<script>
    (function () {
        var el = document.getElementById('mkPagePreloader');
        if (!el) {
            document.documentElement.classList.remove('mk-wait');
            return;
        }
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        var done = false;
        function finish() {
            if (done) return;
            done = true;
            el.classList.add('is-done');
            el.setAttribute('aria-busy', 'false');
            el.setAttribute('aria-hidden', 'true');
            document.documentElement.classList.remove('mk-wait');
            setTimeout(function () {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, reduced ? 0 : 280);
        }
        if (document.readyState === 'complete') {
            finish();
        } else if (document.readyState === 'interactive') {
            window.setTimeout(finish, reduced ? 0 : 120);
        } else {
            document.addEventListener('DOMContentLoaded', function () {
                window.setTimeout(finish, reduced ? 0 : 120);
            });
        }
        window.setTimeout(finish, 1600);
    })();
</script>

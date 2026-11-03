{{-- Global Storefront UI Scripts (Header scroll, Mega top, Account dropdown, Back to top, Reveals, Image hover) --}}
<script>
    (function () {
        var header = document.querySelector('.cb-header');
        function setMegaTop() {
            var header = document.querySelector('.cb-header--myntra');
            var el = header || document.querySelector('.mk-myntra-bar');
            if (!el) return;
            /* Pull mega up slightly so it overlaps the header seam — avoids hover breaking in the gap */
            var overlapPx = 10;
            var topPx = Math.max(0, el.getBoundingClientRect().bottom - overlapPx);
            document.documentElement.style.setProperty('--mk-mega-top', topPx + 'px');
        }
        var proScrollTop = document.getElementById('proScrollTop');
        function onScroll() {
            var y = window.scrollY;
            if (header) {
                header.classList.toggle('cb-header--scrolled', y > 28);
            }
            setMegaTop();
            if (proScrollTop) {
                var showTop = y > 320;
                proScrollTop.classList.toggle('is-visible', showTop);
                document.body.classList.toggle('has-scroll-top', showTop);
            }
        }
        onScroll();
        setMegaTop();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', setMegaTop, { passive: true });

        var accountDrop = document.querySelector('.mk-myntra-action--dropdown');
        if (accountDrop) {
            var accountMenu = accountDrop.querySelector('.dropdown-menu');
            var accountBtn = accountDrop.querySelector('[data-bs-toggle="dropdown"]');
            var accountHide = null;
            function accountOpen() {
                if (accountHide) {
                    clearTimeout(accountHide);
                    accountHide = null;
                }
                if (accountMenu) accountMenu.classList.add('show');
                accountDrop.classList.add('show');
                if (accountBtn) accountBtn.setAttribute('aria-expanded', 'true');
            }
            function accountClose() {
                accountHide = setTimeout(function () {
                    if (accountMenu) accountMenu.classList.remove('show');
                    accountDrop.classList.remove('show');
                    if (accountBtn) accountBtn.setAttribute('aria-expanded', 'false');
                }, 160);
            }
            accountDrop.addEventListener('mouseenter', accountOpen);
            accountDrop.addEventListener('mouseleave', accountClose);
            if (accountMenu) {
                accountMenu.addEventListener('mouseenter', accountOpen);
                accountMenu.addEventListener('mouseleave', accountClose);
            }
        }

        if (proScrollTop) {
            proScrollTop.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        var reveals = document.querySelectorAll('.cb-reveal');
        if (reveals.length && 'IntersectionObserver' in window && !window.matchMedia('(prefers-reduced-motion: reduce)').matches) {
            var io = new IntersectionObserver(function (entries) {
                entries.forEach(function (e) {
                    if (!e.isIntersecting) return;
                    e.target.classList.add('cb-reveal--visible');
                    io.unobserve(e.target);
                });
            }, { rootMargin: '0px 0px -5% 0px', threshold: 0.07 });
            reveals.forEach(function (el) { io.observe(el); });
        } else {
            reveals.forEach(function (el) { el.classList.add('cb-reveal--visible'); });
        }

        document.addEventListener('pointerover', function (e) {
            var card = e.target && e.target.closest ? e.target.closest('.zm-pro-card') : null;
            if (!card) return;
            var secImg = card.querySelector('.zm-pro-card__img--secondary');
            if (secImg && secImg.complete && secImg.naturalWidth > 0) {
                secImg.classList.add('is-loaded');
            }
            var hoverImg = card.querySelector('img[data-hover-src]');
            if (!hoverImg) return;
            var hoverSrc = hoverImg.getAttribute('data-hover-src');
            if (hoverSrc && hoverImg.getAttribute('src') !== hoverSrc) {
                hoverImg.setAttribute('src', hoverSrc);
                hoverImg.addEventListener('load', function () { hoverImg.classList.add('is-loaded'); }, { once: true });
            }
        }, true);
    })();
</script>

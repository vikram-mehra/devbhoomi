{{-- Mobile Bottom Sticky Navigation --}}
<nav class="pro-mobile-nav d-lg-none" id="proMobileNav" aria-label="{{ __('Bottom navigation') }}">
    <a href="{{ route('market.home') }}" class="{{ request()->routeIs('market.home') ? 'active' : '' }}">
        <i class="bi bi-house-door" aria-hidden="true"></i>
        <span class="pro-mobile-nav__label">{{ __('Home') }}</span>
    </a>
    <a href="{{ route('shop.search') }}" data-mk-open-search>
        <i class="bi bi-search" aria-hidden="true"></i>
        <span class="pro-mobile-nav__label">{{ __('Search') }}</span>
    </a>
    <span class="pro-mobile-nav__wrap">
        <button type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer"
            class="text-center border-0 bg-transparent p-0 d-flex flex-column align-items-center"
            style="color:inherit;">
            <span class="position-relative d-inline-flex">
                <i class="bi bi-bag" aria-hidden="true"></i>
                <span class="pro-mobile-nav__badge" @if(($layoutCartCount ?? 0) === 0) style="display:none;"
                @endif>{{ $layoutCartCount > 9 ? '9+' : $layoutCartCount }}</span>
            </span>
            <span class="pro-mobile-nav__label">{{ __('Cart') }}</span>
        </button>
    </span>
    @auth
        <a href="{{ route('account.dashboard') }}"
            class="{{ request()->routeIs('account.*', 'orders.*') ? 'active' : '' }}">
            <i class="bi bi-person" aria-hidden="true"></i>
            <span class="pro-mobile-nav__label">{{ __('Profile') }}</span>
        </a>
    @else
        <a href="{{ route('login') }}">
            <i class="bi bi-person" aria-hidden="true"></i>
            <span class="pro-mobile-nav__label">{{ __('Profile') }}</span>
        </a>
    @endauth
</nav>

<script>
    (function () {
        var nav = document.getElementById('proMobileNav');
        if (!nav) return;
        var mq = window.matchMedia('(max-width: 991.98px)');
        function pinMobileNav() {
            if (!mq.matches) {
                document.documentElement.style.removeProperty('--pro-mobile-nav-offset');
                return;
            }
            var vv = window.visualViewport;
            if (!vv) {
                document.documentElement.style.setProperty('--pro-mobile-nav-offset', '0px');
                return;
            }
            var activeEl = document.activeElement;
            var isInputFocused = activeEl && (
                activeEl.tagName === 'INPUT' ||
                activeEl.tagName === 'TEXTAREA' ||
                activeEl.tagName === 'SELECT' ||
                activeEl.hasAttribute('contenteditable')
            );
            var diff = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
            if (isInputFocused && diff > 100) {
                document.documentElement.style.setProperty('--pro-mobile-nav-offset', diff + 'px');
                nav.classList.add('pro-mobile-nav--keyboard');
            } else {
                document.documentElement.style.setProperty('--pro-mobile-nav-offset', '0px');
                nav.classList.remove('pro-mobile-nav--keyboard');
            }
        }
        if (window.visualViewport) {
            window.visualViewport.addEventListener('resize', pinMobileNav);
            window.visualViewport.addEventListener('scroll', pinMobileNav);
        }
        window.addEventListener('resize', pinMobileNav);
        window.addEventListener('orientationchange', pinMobileNav);
        if (typeof mq.addEventListener === 'function') {
            mq.addEventListener('change', pinMobileNav);
        } else if (typeof mq.addListener === 'function') {
            mq.addListener(pinMobileNav);
        }
        pinMobileNav();
    })();
</script>

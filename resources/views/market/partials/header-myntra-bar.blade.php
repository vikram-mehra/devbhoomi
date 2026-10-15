@php
    $brandName = config('app.name', 'Devbhoomi Naturals');
    $currentMenuSlug = request()->routeIs('shop.products')
        ? 'our-products'
        : (request()->routeIs('shop.menu') ? request()->route('slug') : null);
@endphp
<div class="mk-myntra-bar">
    <button class="cb-icon-btn mk-myntra-hamburger d-lg-none flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#marketNav" aria-controls="marketNav" aria-label="{{ __('Menu') }}">
        <i class="bi bi-list" aria-hidden="true"></i>
    </button>

    <a href="{{ route('market.home') }}" class="mk-myntra-brand flex-shrink-0 text-decoration-none" aria-label="{{ $brandName }}">
        @if(!empty($siteLogoUrl))
            <img src="{{ \App\Support\OptimizedImage::url($siteLogoUrl, 480) }}" alt="{{ $brandName }}" class="mk-myntra-brand__logo" width="200" height="54" decoding="async" fetchpriority="low">
        @else
            <span class="mk-myntra-brand__text font-anc-serif">{{ $brandName }}</span>
        @endif
    </a>

    <button type="button" class="mk-myntra-search-toggle d-lg-none" data-mk-open-search aria-label="{{ __('Search') }}">
        <i class="bi bi-search" aria-hidden="true"></i>
    </button>

    <nav class="mk-myntra-nav d-none d-lg-flex align-items-stretch flex-shrink-0" aria-label="{{ __('Primary navigation') }}">
        @include('market.partials.menu-nav-myntra')
    </nav>

    <div class="mk-myntra-search-wrap mk-myntra-search--compact" data-suggest-url="{{ route('shop.search.suggest') }}">
        <form action="{{ route('shop.search') }}" method="get" role="search" class="mk-myntra-search">
            <label class="visually-hidden" for="mkMyntraSearchInput">{{ __('Search') }}</label>
            <span class="mk-myntra-search__icon" aria-hidden="true"><i class="bi bi-search"></i></span>
            <input type="search" id="mkMyntraSearchInput" name="q" value="{{ request('q') }}" class="mk-myntra-search__input" placeholder="{{ __('Search for products, brands and more') }}" autocomplete="off" aria-autocomplete="list" aria-controls="mkSearchSuggest" aria-expanded="false">
            <button type="submit" class="visually-hidden">{{ __('Search') }}</button>
        </form>
        <div id="mkSearchSuggest" class="mk-search-suggest" hidden role="listbox" aria-label="{{ __('Search suggestions') }}"></div>
    </div>

    <div class="mk-myntra-actions flex-shrink-0 d-flex align-items-center gap-2 gap-md-3">
        @guest
            <a href="{{ route('login') }}" class="mk-myntra-action text-decoration-none text-dark">
                <i class="bi bi-person mk-myntra-action__icon" aria-hidden="true"></i>
                <span class="mk-myntra-action__label">{{ __('Profile') }}</span>
            </a>
        @else
            @php $headerUser = auth()->user(); @endphp
            <div class="dropdown mk-myntra-action mk-myntra-action--dropdown">
                <button class="mk-myntra-action__btn dropdown-toggle border-0 bg-transparent p-0 text-dark" type="button" data-bs-toggle="dropdown" data-bs-display="static" aria-expanded="false" aria-label="{{ __('Account menu') }}">
                    <i class="bi bi-person mk-myntra-action__icon" aria-hidden="true"></i>
                    <span class="mk-myntra-action__label">{{ __('Profile') }}</span>
                </button>
                <ul class="dropdown-menu shadow border-0 pro-header-account-menu">
                    @if($headerUser->role === 'admin')
                        <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('admin.*')) active @endif" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2" aria-hidden="true"></i>{{ __('Admin') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                    @elseif($headerUser->role === 'seo')
                        <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('admin.*')) active @endif" href="{{ route('admin.seo.index') }}"><i class="bi bi-search" aria-hidden="true"></i>{{ __('SEO workspace') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                    @elseif($headerUser->role === 'vendor')
                        <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('vendor.*')) active @endif" href="{{ route('vendor.dashboard') }}"><i class="bi bi-shop" aria-hidden="true"></i>{{ __('Seller hub') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                    <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('account.dashboard')) active @endif" href="{{ route('account.dashboard') }}"><i class="bi bi-house-door" aria-hidden="true"></i>{{ __('Dashboard') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('account.details')) active @endif" href="{{ route('account.details') }}"><i class="bi bi-person-vcard" aria-hidden="true"></i>{{ __('Account details') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('orders.index', 'orders.show')) active @endif" href="{{ route('orders.index') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i>{{ __('My orders') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('orders.track', 'orders.track.lookup')) active @endif" href="{{ route('orders.track') }}"><i class="bi bi-truck" aria-hidden="true"></i>{{ __('Track order') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('account.refunds')) active @endif" href="{{ route('account.refunds') }}"><i class="bi bi-currency-dollar" aria-hidden="true"></i>{{ __('Refund history') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link @if(request()->routeIs('account.addresses.*')) active @endif" href="{{ route('account.addresses.index') }}"><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ __('Address book') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li class="pro-header-account-menu__logout-item">
                        <form action="{{ route('logout') }}" method="post">
                            @csrf
                            <button type="submit" class="pro-header-account-menu__logout">
                                <i class="bi bi-box-arrow-right" aria-hidden="true"></i>{{ __('Logout') }}
                            </button>
                        </form>
                    </li>
                </ul>
            </div>
        @endguest

        @php $wishlistCount = (int) ($layoutWishlistCount ?? 0); @endphp
        <a href="{{ auth()->check() ? route('wishlist.index') : route('login') }}" class="mk-myntra-action text-decoration-none text-dark" title="{{ __('Wishlist') }}">
            <span class="mk-myntra-action__iconwrap">
                <i class="bi bi-heart mk-myntra-action__icon" aria-hidden="true"></i>
                <span class="mk-myntra-wish-badge" @if($wishlistCount === 0) style="display:none;" @endif>{{ $wishlistCount > 99 ? '99+' : $wishlistCount }}</span>
            </span>
            <span class="mk-myntra-action__label">{{ __('Wishlist') }}</span>
        </a>

        <button type="button" class="mk-myntra-action mk-myntra-action--bag border-0 bg-transparent p-0 text-dark" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" aria-controls="cartDrawer" title="{{ __('Cart') }}">
            <span class="mk-myntra-action__iconwrap">
                <i class="bi bi-bag mk-myntra-action__icon" aria-hidden="true"></i>
                <span class="mk-myntra-bag-badge" @if(($layoutCartCount ?? 0) === 0) style="display:none;" @endif>{{ $layoutCartCount > 99 ? '99+' : $layoutCartCount }}</span>
            </span>
            <span class="mk-myntra-action__label">{{ __('Bag') }}</span>
        </button>
    </div>
</div>

<div id="mkSearchScreen" class="mk-search-screen" hidden>
    <div class="mk-search-screen__backdrop" data-mk-close-search></div>
    <div class="mk-search-screen__panel" role="dialog" aria-modal="true" aria-labelledby="mkSearchScreenTitle">
        <div class="mk-search-screen__top">
            <h2 id="mkSearchScreenTitle" class="mk-search-screen__title font-anc-serif">{{ __('Search') }}</h2>
            <button type="button" class="mk-search-screen__close" data-mk-close-search aria-label="{{ __('Close') }}">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>
        <form action="{{ route('shop.search') }}" method="get" role="search" class="mk-search-screen__form">
            <label class="visually-hidden" for="mkMobileSearchInput">{{ __('Search') }}</label>
            <input type="search" id="mkMobileSearchInput" name="q" class="mk-search-screen__input" placeholder="{{ __('Search') }}" autocomplete="off" aria-autocomplete="list" aria-controls="mkMobileSearchSuggest" aria-expanded="false">
            <button type="submit" class="visually-hidden">{{ __('Search') }}</button>
        </form>
        <div id="mkMobileSearchSuggest" class="mk-search-suggest mk-search-suggest--sheet" hidden role="listbox" aria-label="{{ __('Search suggestions') }}"></div>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var wrap = document.querySelector('.mk-myntra-search-wrap[data-suggest-url]');
    var suggestUrl = wrap ? wrap.getAttribute('data-suggest-url') : @json(route('shop.search.suggest'));
    var desktopInput = document.getElementById('mkMyntraSearchInput');
    var desktopPanel = document.getElementById('mkSearchSuggest');
    var mobileInput = document.getElementById('mkMobileSearchInput');
    var mobilePanel = document.getElementById('mkMobileSearchSuggest');
    var screenEl = document.getElementById('mkSearchScreen');
    var recentKey = 'dn_recent_searches';
    var labels = {
        viewAll: @json(__('View all results')),
        empty: @json(__('No matches — press Enter to search')),
        recent: @json(__('Recent searches')),
        noRecent: @json(__('No recent searches')),
        off: @json(__('off')),
        remove: @json(__('Remove')),
    };
    var debounceMs = 260;
    var minLen = 2;
    var debounceTimer = null;
    var abortCtrl = null;
    var activePanel = desktopPanel;
    var activeInput = desktopInput;
    var searchLockY = 0;

    function isMobileSearch() {
        return window.matchMedia('(max-width: 991.98px)').matches;
    }

    function loadRecents() {
        try {
            var raw = localStorage.getItem(recentKey);
            var list = raw ? JSON.parse(raw) : [];
            return Array.isArray(list) ? list.filter(function (q) { return typeof q === 'string' && q.trim(); }).slice(0, 8) : [];
        } catch (e) {
            return [];
        }
    }

    function saveRecents(list) {
        try { localStorage.setItem(recentKey, JSON.stringify(list.slice(0, 8))); } catch (e) {}
    }

    function rememberQuery(q) {
        q = (q || '').trim();
        if (q.length < 2) return;
        var list = loadRecents().filter(function (item) {
            return item.toLowerCase() !== q.toLowerCase();
        });
        list.unshift(q);
        saveRecents(list);
    }

    function removeRecent(q) {
        saveRecents(loadRecents().filter(function (item) { return item !== q; }));
    }

    function hidePanel(panel, input) {
        if (!panel) return;
        panel.hidden = true;
        panel.innerHTML = '';
        if (input) input.setAttribute('aria-expanded', 'false');
    }

    function showPanel(panel, input) {
        if (!panel) return;
        panel.hidden = false;
        if (input) input.setAttribute('aria-expanded', 'true');
    }

    function productRow(row, input) {
        var a = document.createElement('a');
        a.className = 'mk-search-suggest__product';
        a.href = row.url;
        a.setAttribute('role', 'option');
        a.addEventListener('click', function () { rememberQuery(input && input.value); });

        var img = document.createElement('img');
        img.className = 'mk-search-suggest__thumb';
        img.src = row.image || '';
        img.alt = '';
        img.width = 52;
        img.height = 52;
        img.loading = 'lazy';

        var meta = document.createElement('div');
        meta.className = 'mk-search-suggest__meta';

        var name = document.createElement('div');
        name.className = 'mk-search-suggest__name';
        name.textContent = row.name || '';

        var price = document.createElement('div');
        price.className = 'mk-search-suggest__price';
        var now = document.createElement('span');
        now.className = 'mk-search-suggest__now';
        now.textContent = row.price_text || '';
        price.appendChild(now);
        if (row.compare_text && Number(row.percent_off) > 0) {
            var was = document.createElement('span');
            was.className = 'mk-search-suggest__compare';
            was.textContent = row.compare_text;
            var off = document.createElement('span');
            off.className = 'mk-search-suggest__off';
            off.textContent = row.percent_off + '% ' + labels.off;
            price.appendChild(was);
            price.appendChild(off);
        }
        meta.appendChild(name);
        meta.appendChild(price);
        a.appendChild(img);
        a.appendChild(meta);
        return a;
    }

    function renderProducts(panel, input, data) {
        if (!panel) return;
        panel.innerHTML = '';
        var frag = document.createDocumentFragment();
        var items = data.products || [];
        if (items.length) {
            var list = document.createElement('div');
            list.className = 'mk-search-suggest__list';
            items.forEach(function (row) { list.appendChild(productRow(row, input)); });
            frag.appendChild(list);
        } else {
            var empty = document.createElement('p');
            empty.className = 'mk-search-suggest__empty';
            empty.textContent = labels.empty;
            frag.appendChild(empty);
        }
        if (data.view_all_url) {
            var foot = document.createElement('div');
            foot.className = 'mk-search-suggest__footer';
            var va = document.createElement('a');
            va.href = data.view_all_url;
            va.textContent = labels.viewAll;
            va.addEventListener('click', function () { rememberQuery(input && input.value); });
            foot.appendChild(va);
            frag.appendChild(foot);
        }
        panel.appendChild(frag);
        showPanel(panel, input);
    }

    function renderRecents(panel, input) {
        if (!panel) return;
        var items = loadRecents();
        panel.innerHTML = '';
        if (!items.length) {
            if (panel === mobilePanel) {
                var empty = document.createElement('p');
                empty.className = 'mk-search-suggest__empty';
                empty.textContent = labels.noRecent;
                panel.appendChild(empty);
                showPanel(panel, input);
            } else {
                hidePanel(panel, input);
            }
            return;
        }
        var head = document.createElement('div');
        head.className = 'mk-search-suggest__grouphead';
        head.textContent = labels.recent;
        panel.appendChild(head);
        var list = document.createElement('div');
        list.className = 'mk-search-suggest__list';
        items.forEach(function (q) {
            var row = document.createElement('div');
            row.className = 'mk-search-suggest__recent';
            var btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'mk-search-suggest__recent-q';
            var icon = document.createElement('i');
            icon.className = 'bi bi-clock-history';
            icon.setAttribute('aria-hidden', 'true');
            var text = document.createElement('span');
            text.textContent = q;
            btn.appendChild(icon);
            btn.appendChild(text);
            btn.addEventListener('click', function () {
                if (input) {
                    input.value = q;
                    input.focus();
                }
                rememberQuery(q);
                fetchSuggest(q, panel, input);
            });
            var del = document.createElement('button');
            del.type = 'button';
            del.className = 'mk-search-suggest__recent-del';
            del.setAttribute('aria-label', labels.remove);
            var x = document.createElement('i');
            x.className = 'bi bi-x';
            x.setAttribute('aria-hidden', 'true');
            del.appendChild(x);
            del.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                removeRecent(q);
                renderRecents(panel, input);
            });
            row.appendChild(btn);
            row.appendChild(del);
            list.appendChild(row);
        });
        panel.appendChild(list);
        showPanel(panel, input);
    }

    function fetchSuggest(q, panel, input) {
        if (!suggestUrl) return;
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();
        var ctrl = abortCtrl;
        var sep = suggestUrl.indexOf('?') >= 0 ? '&' : '?';
        fetch(suggestUrl + sep + 'q=' + encodeURIComponent(q), {
            signal: ctrl.signal,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(new Error('bad status')); })
            .then(function (data) { renderProducts(panel, input, data); })
            .catch(function () {
                if (ctrl.signal.aborted) return;
                hidePanel(panel, input);
            });
    }

    function onQueryChange(input, panel) {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            var q = (input.value || '').trim();
            if (q.length < minLen) {
                renderRecents(panel, input);
                return;
            }
            fetchSuggest(q, panel, input);
        }, debounceMs);
    }

    function bindField(input, panel) {
        if (!input || !panel) return;
        input.addEventListener('input', function () { onQueryChange(input, panel); });
        input.addEventListener('focus', function () {
            activeInput = input;
            activePanel = panel;
            var q = (input.value || '').trim();
            if (q.length >= minLen) fetchSuggest(q, panel, input);
            else renderRecents(panel, input);
        });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                if (screenEl && !screenEl.hidden) closeSearchScreen();
                else hidePanel(panel, input);
            }
        });
        var form = input.closest('form');
        if (form) {
            form.addEventListener('submit', function () {
                rememberQuery(input.value);
                hidePanel(panel, input);
            });
        }
    }

    function mountSearchScreen() {
        if (screenEl && screenEl.parentElement !== document.body) {
            document.body.appendChild(screenEl);
        }
    }

    function positionSearchScreen() {
        if (!screenEl) return;
        var header = document.querySelector('.cb-header--myntra');
        var top = 56;
        if (header) {
            var rect = header.getBoundingClientRect();
            top = Math.max(0, Math.round(rect.bottom));
        }
        screenEl.style.setProperty('--mk-search-screen-top', top + 'px');
    }

    function lockPageScroll() {
        searchLockY = window.scrollY || window.pageYOffset || 0;
        document.body.classList.add('is-search-open');
        document.body.style.top = '-' + searchLockY + 'px';
    }

    function unlockPageScroll() {
        document.body.classList.remove('is-search-open');
        document.body.style.top = '';
        window.scrollTo(0, searchLockY);
    }

    function openSearchScreen(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        if (!screenEl || !isMobileSearch()) return;
        if (screenEl.classList.contains('is-open')) return;
        mountSearchScreen();
        positionSearchScreen();
        lockPageScroll();
        screenEl.hidden = false;
        screenEl.classList.add('is-open');
        if (mobileInput) {
            if (!mobileInput.value) renderRecents(mobilePanel, mobileInput);
            window.setTimeout(function () {
                try { mobileInput.focus(); } catch (err) {}
            }, 50);
        }
    }

    function closeSearchScreen() {
        if (!screenEl || !screenEl.classList.contains('is-open')) return;
        screenEl.hidden = true;
        screenEl.classList.remove('is-open');
        unlockPageScroll();
        hidePanel(mobilePanel, mobileInput);
    }

    mountSearchScreen();
    bindField(desktopInput, desktopPanel);
    bindField(mobileInput, mobilePanel);

    document.addEventListener('click', function (e) {
        if (e.target.closest('[data-mk-open-search]')) {
            openSearchScreen(e);
            return;
        }
        if (e.target.closest('[data-mk-close-search]')) {
            closeSearchScreen();
            return;
        }
        if (desktopInput && desktopPanel && wrap && !wrap.contains(e.target)) {
            hidePanel(desktopPanel, desktopInput);
        }
    });

    window.addEventListener('resize', function () {
        if (!isMobileSearch()) closeSearchScreen();
        else if (screenEl && screenEl.classList.contains('is-open')) positionSearchScreen();
    });
})();
</script>
@endpush

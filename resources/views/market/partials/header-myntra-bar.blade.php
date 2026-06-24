@php
    $brandName = config('app.name', 'Devbhoomi Naturals');
    $currentMenuSlug = request()->routeIs('shop.menu') ? request()->route('slug') : null;
@endphp
<div class="mk-myntra-bar">
    <button class="cb-icon-btn mk-myntra-hamburger d-lg-none flex-shrink-0" type="button" data-bs-toggle="offcanvas" data-bs-target="#marketNav" aria-controls="marketNav" aria-label="{{ __('Menu') }}">
        <i class="bi bi-list fs-5" aria-hidden="true"></i>
    </button>

    <a href="{{ route('market.home') }}" class="mk-myntra-brand flex-shrink-0 text-decoration-none" aria-label="{{ $brandName }}">
        @if(!empty($siteLogoUrl))
            <img src="{{ $siteLogoUrl }}" alt="{{ $brandName }}" class="mk-myntra-brand__logo" width="150" height="40" decoding="async">
        @else
            <span class="mk-myntra-brand__text font-anc-serif">{{ $brandName }}</span>
        @endif
    </a>

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
                <button class="mk-myntra-action__btn dropdown-toggle border-0 bg-transparent p-0 text-dark" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="{{ __('Account menu') }}">
                    <i class="bi bi-person mk-myntra-action__icon" aria-hidden="true"></i>
                    <span class="mk-myntra-action__label">{{ __('Profile') }}</span>
                </button>
                <ul class="dropdown-menu dropdown-menu-end shadow border-0 pro-header-account-menu" style="min-width: 14rem;">
                    @if($headerUser->role === 'admin')
                        <li><a class="dropdown-item pro-header-account-menu__link" href="{{ route('admin.dashboard') }}"><i class="bi bi-speedometer2" aria-hidden="true"></i>{{ __('Admin') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                    @elseif($headerUser->role === 'vendor')
                        <li><a class="dropdown-item pro-header-account-menu__link" href="{{ route('vendor.dashboard') }}"><i class="bi bi-shop" aria-hidden="true"></i>{{ __('Seller hub') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                    @endif
                    <li><a class="dropdown-item pro-header-account-menu__link" href="{{ route('account.dashboard') }}"><i class="bi bi-house-door" aria-hidden="true"></i>{{ __('Dashboard') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link" href="{{ route('account.details') }}"><i class="bi bi-person-vcard" aria-hidden="true"></i>{{ __('Account details') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link" href="{{ route('orders.index') }}"><i class="bi bi-file-earmark-text" aria-hidden="true"></i>{{ __('My orders') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link" href="{{ route('account.refunds') }}"><i class="bi bi-currency-dollar" aria-hidden="true"></i>{{ __('Refund history') }}</a></li>
                    <li><a class="dropdown-item pro-header-account-menu__link" href="{{ route('account.addresses.index') }}"><i class="bi bi-geo-alt" aria-hidden="true"></i>{{ __('Address book') }}</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <form action="{{ route('logout') }}" method="post" class="px-3 py-1">@csrf<button type="submit" class="btn btn-link p-0 text-danger text-decoration-underline w-100 text-start">{{ __('Logout') }}</button></form>
                    </li>
                </ul>
            </div>
        @endguest

        @auth
            <a href="{{ route('wishlist.index') }}" class="mk-myntra-action text-decoration-none text-dark">
                <i class="bi bi-heart mk-myntra-action__icon" aria-hidden="true"></i>
                <span class="mk-myntra-action__label">{{ __('Wishlist') }}</span>
            </a>
        @else
            <a href="{{ route('login') }}" class="mk-myntra-action text-decoration-none text-dark">
                <i class="bi bi-heart mk-myntra-action__icon" aria-hidden="true"></i>
                <span class="mk-myntra-action__label">{{ __('Wishlist') }}</span>
            </a>
        @endauth

        <button type="button" class="mk-myntra-action mk-myntra-action--bag border-0 bg-transparent p-0 text-dark" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" aria-controls="cartDrawer" title="{{ __('Cart') }}">
            <span class="mk-myntra-action__iconwrap">
                <i class="bi bi-bag mk-myntra-action__icon" aria-hidden="true"></i>
                <span class="mk-myntra-bag-badge" @if(($layoutCartCount ?? 0) === 0) style="display:none;" @endif>{{ $layoutCartCount > 99 ? '99+' : $layoutCartCount }}</span>
            </span>
            <span class="mk-myntra-action__label">{{ __('Bag') }}</span>
        </button>
    </div>
</div>

@push('scripts')
<script>
(function () {
    var wrap = document.querySelector('.mk-myntra-search-wrap[data-suggest-url]');
    if (!wrap) return;
    var suggestUrl = wrap.getAttribute('data-suggest-url');
    var input = document.getElementById('mkMyntraSearchInput');
    var panel = document.getElementById('mkSearchSuggest');
    if (!input || !panel || !suggestUrl) return;

    var labels = {
        viewAll: @json(__('View all results')),
        empty: @json(__('No matches — press Enter to search')),
    };

    var debounceMs = 260;
    var minLen = 2;
    var debounceTimer = null;
    var abortCtrl = null;

    function hidePanel() {
        panel.hidden = true;
        panel.innerHTML = '';
        input.setAttribute('aria-expanded', 'false');
    }

    function render(data) {
        panel.innerHTML = '';
        var frag = document.createDocumentFragment();
        var items = data.suggestions || [];
        var hasItems = items.length > 0;

        if (hasItems) {
            var head = document.createElement('div');
            head.className = 'mk-search-suggest__grouphead';
            head.textContent = data.group_title || 'All Others';
            frag.appendChild(head);
            var list = document.createElement('div');
            list.className = 'mk-search-suggest__list';
            items.forEach(function (row) {
                var a = document.createElement('a');
                a.className = 'mk-search-suggest__row';
                a.href = row.url;
                a.setAttribute('role', 'option');
                a.textContent = row.text;
                list.appendChild(a);
            });
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
            foot.appendChild(va);
            frag.appendChild(foot);
        }

        panel.appendChild(frag);
        panel.hidden = false;
        input.setAttribute('aria-expanded', 'true');
    }

    function fetchSuggest(q) {
        if (abortCtrl) abortCtrl.abort();
        abortCtrl = new AbortController();
        var sep = suggestUrl.indexOf('?') >= 0 ? '&' : '?';
        var u = suggestUrl + sep + 'q=' + encodeURIComponent(q);
        fetch(u, {
            signal: abortCtrl.signal,
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
            credentials: 'same-origin',
        })
            .then(function (r) { return r.ok ? r.json() : Promise.reject(new Error('bad status')); })
            .then(render)
            .catch(function () {
                if (abortCtrl && abortCtrl.signal.aborted) return;
                hidePanel();
            });
    }

    function scheduleFetch() {
        clearTimeout(debounceTimer);
        debounceTimer = setTimeout(function () {
            var q = input.value.trim();
            if (q.length < minLen) {
                hidePanel();
                return;
            }
            fetchSuggest(q);
        }, debounceMs);
    }

    input.addEventListener('input', scheduleFetch);
    input.addEventListener('focus', function () {
        if (input.value.trim().length >= minLen) scheduleFetch();
    });
    input.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            hidePanel();
            input.blur();
        }
    });
    document.addEventListener('click', function (e) {
        if (!wrap.contains(e.target)) hidePanel();
    });
    wrap.querySelector('form')?.addEventListener('submit', function () {
        hidePanel();
    });
})();
</script>
@endpush

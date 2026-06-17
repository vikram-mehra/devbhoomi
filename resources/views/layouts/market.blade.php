<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2d5a3d">
    @include('partials.favicon')
    @include('partials.seo-head')
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@300;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link href="{{ asset('css/market.css') }}?v=12" rel="stylesheet">
    <link rel="preload" href="https://fonts.googleapis.com/css2?family=Poppins:ital,wght@0,300;0,400;0,500;0,600;0,700&display=swap" as="style">
    <link href="{{ asset('css/market-pro.css') }}?v=91" rel="stylesheet">
    @stack('head')
    @php $seoService = app(\App\Services\SeoService::class); @endphp
    <script type="application/ld+json">
    {!! json_encode($seoService->websiteSchema(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($seoService->organizationSchema(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($seoService->localBusinessSchema(), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    @php $faqSchema = request()->routeIs('market.home') ? $seoService->faqSchemaFromJson($seoService->global('faq_schema_json')) : null; @endphp
    @if($faqSchema)
    <script type="application/ld+json">
    {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
    </script>
    @endif
    @stack('schema')
</head>
@php $gaId = app(\App\Services\SeoService::class)->global('google_analytics_id'); @endphp
@if(filled($gaId))
<!-- Google tag (gtag.js) -->
<script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());
  gtag('config', '{{ $gaId }}');
</script>
@if(session('ga_events'))
<script>
  @foreach(session('ga_events') as $event)
    gtag('event', '{{ $event['name'] }}', {!! json_encode($event['data'], JSON_UNESCAPED_SLASHES) !!});
  @endforeach
</script>
@endif
@endif
<body class="cb-body cb-market-pro">
    <div id="mkPagePreloader" class="mk-page-preloader" role="alert" aria-live="polite" aria-busy="true" aria-label="{{ __('Loading') }}">
        <div class="mk-page-preloader__inner">
            <!-- <span class="mk-page-preloader__dot" aria-hidden="true"></span> -->
            <div class="mk-page-preloader__plate">
                <span class="mk-page-preloader__ring" aria-hidden="true"></span>
            </div>
        </div>
    </div>
    {{-- 1. Announcement bar --}}
    <div class="pro-announcement">
        <div class="cb-container">
            <div class="pro-announcement__marquee">
                <span><i class="bi bi-tag-fill me-1" aria-hidden="true"></i>{{ __('Extra 10% off on prepaid orders.') }}</span>
                <span><i class="bi bi-telephone me-1" aria-hidden="true"></i>{{ __('Support') }}: <a href="tel:+91 9217732670">+91 9217732670</a></span>
            </div>
        </div>
    </div>

    {{-- 2. Header (Myntra-style: mark + category strip + grey search + profile / wishlist / bag) --}}
    <header class="cb-header cb-header--myntra">
        <div class="cb-container mk-header-wrap">
            @include('market.partials.header-myntra-bar')

            <div class="mk-header-mega mk-header-mega--legacy d-none" aria-hidden="true">
                <nav class="cb-nav-main" aria-label="{{ __('Primary') }}">
                    @include('market.partials.header-menu')
                </nav>
            </div>
        </div>
    </header>

    <div class="offcanvas offcanvas-start pro-nav-offcanvas" tabindex="-1" id="marketNav" aria-labelledby="marketNavLabel">
        <div class="offcanvas-header pro-mnav-header">
            <a href="{{ route('market.home') }}" class="pro-mnav-brand text-decoration-none" id="marketNavLabel">
                @if(!empty($siteLogoUrl))
                    <img src="{{ $siteLogoUrl }}" alt="{{ config('app.name') }}" class="pro-mnav-brand__logo" width="130" height="36" decoding="async">
                @else
                    <span class="pro-mnav-brand__name font-anc-serif">{{ config('app.name') }}</span>
                @endif
                <!-- <span class="pro-mnav-brand__tag">{{ __('Come, relive style') }}</span> -->
            </a>
            <button type="button" class="btn-close pro-mnav-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body pro-mnav-body p-0">
            <nav class="pro-mnav" aria-label="{{ __('Mobile') }}">
                @include('market.partials.header-menu-mobile')

                <div class="pro-mnav-divider" role="presentation"></div>
                <a class="pro-mnav-rowlink" href="{{ route('shop.search') }}">{{ __('Search') }}</a>
                <a class="pro-mnav-rowlink" href="{{ route('shop.search', ['sort' => 'newest']) }}">{{ __('New arrivals') }}</a>

                <div class="pro-mnav-divider pro-mnav-divider--loose" role="presentation"></div>
                <span class="pro-mnav-section-label">{{ __('Account') }}</span>

                @guest
                    <a class="pro-mnav-rowlink" href="{{ route('login') }}">{{ __('Sign in') }}</a>
                    <a class="pro-mnav-rowlink" href="{{ route('register') }}">{{ __('Create account') }}</a>
                @else
                    @php $navUser = auth()->user(); @endphp
                    <div class="pro-mnav-profile px-3 py-3">
                        <div class="pro-mnav-profile__icon" aria-hidden="true"><i class="bi bi-person-circle"></i></div>
                        <div class="pro-mnav-profile__meta min-w-0">
                            <div class="pro-mnav-profile__name text-truncate">{{ $navUser->name }}</div>
                            <div class="pro-mnav-profile__email text-truncate">{{ $navUser->email }}</div>
                        </div>
                    </div>
                    @if($navUser->role === 'admin')
                        <a class="pro-mnav-rowlink" href="{{ route('admin.dashboard') }}">{{ __('Admin') }}</a>
                    @elseif($navUser->role === 'vendor')
                        <a class="pro-mnav-rowlink" href="{{ route('vendor.dashboard') }}">{{ __('Seller hub') }}</a>
                    @endif
                    <a class="pro-mnav-rowlink" href="{{ route('cart.index') }}">{{ __('Cart') }}</a>
                    <a class="pro-mnav-rowlink" href="{{ route('wishlist.index') }}">{{ __('Wishlist') }}</a>
                    <a class="pro-mnav-rowlink" href="{{ route('account.dashboard') }}">{{ __('Dashboard') }}</a>
                    <a class="pro-mnav-rowlink" href="{{ route('account.details') }}">{{ __('Account details') }}</a>
                    <a class="pro-mnav-rowlink" href="{{ route('orders.index') }}">{{ __('My orders') }}</a>
                    <a class="pro-mnav-rowlink" href="{{ route('account.refunds') }}">{{ __('Refund history') }}</a>
                    <a class="pro-mnav-rowlink" href="{{ route('account.addresses.index') }}">{{ __('Address book') }}</a>
                    <form action="{{ route('logout') }}" method="post" class="pro-mnav-logout px-3 py-2">@csrf<button type="submit" class="btn btn-link text-danger p-0 text-start w-100">{{ __('Logout') }}</button></form>
                @endguest
            </nav>
        </div>
    </div>

    {{-- Cart sidebar --}}
    <div class="offcanvas offcanvas-end pro-cart-drawer" tabindex="-1" id="cartDrawer" aria-labelledby="cartDrawerLabel">
        <div class="offcanvas-header border-bottom">
            <h2 class="h5 offcanvas-title" id="cartDrawerLabel">{{ __('Your cart') }}</h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            @forelse($layoutCartItems ?? [] as $citem)
                @php
                    $p = $citem->variant->product;
                    $im = $p->images->first();
                    $cartThumb = \App\Models\Product::publicImageUrl($im?->path) ?? $p->namedPlaceholderUrl();
                @endphp
                <div class="pro-cart-item">
                    <img src="{{ $cartThumb }}" alt="{{ $p->name }}" title="{{ $p->name }}" width="128" height="128" loading="lazy" decoding="async">
                    <div class="flex-grow-1 min-w-0">
                        <a href="{{ route('product.show', $p) }}" class="fw-semibold text-decoration-none text-dark text-truncate d-block">{{ $p->name }}</a>
                        <div class="small text-muted">{{ __('Qty') }}: {{ $citem->qty }} × ₹{{ number_format($citem->variant->effectivePrice(), 0) }}</div>
                    </div>
                </div>
            @empty
                <p class="text-muted">{{ __('Your cart is empty.') }}</p>
            @endforelse
            <div class="mt-auto pt-3 border-top">
                @if(($layoutCartCount ?? 0) > 0)
                    <div class="d-flex justify-content-between fw-bold mb-3">
                        <span>{{ __('Subtotal') }}</span>
                        <span>₹{{ number_format($layoutCartSubtotal ?? 0, 0) }}</span>
                    </div>
                @endif
                <a href="{{ route('cart.index') }}" class="btn btn-primary w-100 rounded-pill mb-2">{{ __('View cart') }}</a>
                @auth
                    <a href="{{ route('checkout.index') }}" class="btn btn-outline-primary w-100 rounded-pill">{{ __('Checkout') }}</a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-outline-primary w-100 rounded-pill">{{ __('Login to checkout') }}</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Quick view modal --}}
    <div class="modal fade" id="quickViewModal" tabindex="-1" aria-labelledby="quickViewModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
            <div class="modal-content pro-modal">
                <div class="modal-header border-0 pb-0">
                    <h2 class="modal-title h5" id="quickViewModalLabel">{{ __('Quick view') }}</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <img src="" alt="" class="w-100 rounded-3 bg-light js-qv-img" width="400" height="480" style="object-fit:cover;aspect-ratio:4/5;">
                        </div>
                        <div class="col-md-7">
                            <p class="small text-primary fw-semibold mb-1 js-qv-brand"></p>
                            <h3 class="h5 js-qv-name"></h3>
                            <div class="fs-5 fw-bold mb-3 js-qv-price"></div>
                            <p class="text-muted small">{{ __('Select options on the product page for all variants.') }}</p>
                            <a href="#" class="btn btn-primary rounded-pill js-qv-link">{{ __('View full details') }}</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Newsletter popup --}}
    <div class="modal fade" id="newsletterModal" tabindex="-1" aria-labelledby="newsletterModalLabel" aria-hidden="true" data-bs-backdrop="static">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content pro-news-popup">
                <div class="modal-body text-center position-relative">
                    <button type="button" class="btn-close position-absolute top-0 end-0 m-2 js-news-dismiss" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
                    <h2 class="h5 mb-2" id="newsletterModalLabel">{{ __('Get 10% off your first order') }}</h2>
                    <p class="text-muted small mb-3">{{ __('Subscribe for deals, new arrivals & seller updates.') }}</p>
                    <form id="proNewsPopupForm" class="d-flex flex-column gap-2">
                        @csrf
                        <input type="email" name="email" required class="form-control rounded-pill" placeholder="{{ __('Your email') }}" autocomplete="email" aria-label="{{ __('Email') }}">
                        <button type="submit" class="btn btn-primary rounded-pill">{{ __('Subscribe') }}</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <main class="cb-main" id="main-content">
        @php $isMarketHome = request()->routeIs('market.home'); @endphp
        @if($isMarketHome)
            @yield('content')
        @else
            @stack('breadcrumb')
            <div class="cb-container py-3 pb-5">
                @yield('content')
            </div>
        @endif
    </main>

    @if(session('mk_cart_toast'))
        <div id="mkCartToast" class="mk-cart-toast" role="status" aria-live="polite" aria-atomic="true">
            <div class="mk-cart-toast__row">
                <span class="mk-cart-toast__msg">{{ __('Item added to cart') }}</span>
                <a href="{{ route('cart.index') }}" class="mk-cart-toast__action">{{ __('Go to cart') }}</a>
            </div>
            <button type="button" class="mk-cart-toast__close" aria-label="{{ __('Close') }}">&times;</button>
        </div>
        <script>
        (function () {
            var toast = document.getElementById('mkCartToast');
            if (!toast) return;
            if (document.querySelector('.pro-sticky-buy')) {
                toast.classList.add('mk-cart-toast--above-sticky');
            }
            requestAnimationFrame(function () {
                toast.classList.add('is-visible');
            });
            var hideTimer = setTimeout(function () { toast.classList.remove('is-visible'); }, 5200);
            function removeToast() {
                clearTimeout(hideTimer);
                toast.classList.remove('is-visible');
                setTimeout(function () { toast.remove(); }, 320);
            }
            toast.querySelector('.mk-cart-toast__close')?.addEventListener('click', removeToast);
        })();
        </script>
    @endif

    @php $footerBrand = config('app.name', 'Alluringstyle'); @endphp
    <footer class="cb-footer pro-footer-mk">
        <div class="cb-container position-relative">
            <button type="button" class="pro-footer-mk__scroll-top" id="proScrollTop" aria-label="{{ __('Back to top') }}" title="{{ __('Back to top') }}">
                <i class="bi bi-chevron-up" aria-hidden="true"></i>
            </button>
            <div class="row g-4 g-xl-5 pro-footer-mk__main py-2 align-items-start">
                <div class="col-12 col-md-6 col-xl-3">
                    <a href="{{ route('market.home') }}" class="pro-footer-mk__brand mb-3 text-decoration-none d-inline-block">
                        @if(!empty($siteLogoUrl))
                            <!-- <img src="{{ $siteLogoUrl }}" alt="{{ $footerBrand }}" class="pro-footer-mk__logo-img" width="160" height="44" decoding="async"> -->
                        @else
                            <span class="pro-footer-mk__logo-text font-anc-serif">
                                <span class="pro-footer-mk__logo-main">{{ $footerBrand }}</span>
                            </span>
                        @endif
                    </a>
                    <p class="pro-footer-mk__desc small mb-4">{{ __('Discover the latest trends and enjoy seamless shopping with our exclusive collections.') }}</p>
                    <ul class="list-unstyled pro-footer-mk__contact mb-0">
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                            <span>{{ config('app.name') }}, {{ __('Ranikhet, Uttarakhand') }}, {{ __('India') }}</span>
                        </li>
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-telephone" aria-hidden="true"></i>
                            <a href="tel:+91 9217732670">{{ __('Call Us') }}:  +91 9217732670 </a>
                        </li>
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <a href="mailto:support@devbhoominaturals.com">{{ __('Email Us') }}: support@devbhoominaturals.com</a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Menu') }}</h3>
                    @include('market.partials.menu-footer-links')
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Useful links') }}</h3>
                    <a href="{{ route('market.home') }}">{{ __('Home') }}</a>
                    <a href="{{ route('shop.search') }}">{{ __('Collections') }}</a>
                    <a href="{{ route('pages.about') }}">{{ __('About us') }}</a>
                    <a href="{{ route('blog.index') }}">{{ __('Blogs') }}</a>
                    <a href="{{ route('offers.index') }}">{{ __('Offers') }}</a>
                    <a href="{{ route('shop.search') }}">{{ __('Search') }}</a>
                    <!-- <a href="{{ route('vendor.register') }}" class="pro-footer-mk__sell">{{ __('Sell with us') }}</a> -->
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Help center') }}</h3>
                    @auth
                        <a href="{{ route('account.dashboard') }}">{{ __('My account') }}</a>
                        <a href="{{ route('orders.index') }}">{{ __('My orders') }}</a>
                        <a href="{{ route('orders.index') }}">{{ __('Track order') }}</a>
                        <a href="{{ route('wishlist.index') }}">{{ __('Wishlist') }}</a>
                    @else
                        <a href="{{ route('login') }}">{{ __('My account') }}</a>
                        <a href="{{ route('login') }}">{{ __('My orders') }}</a>
                        <a href="{{ route('login') }}">{{ __('Track order') }}</a>
                        <a href="{{ route('login') }}">{{ __('Wishlist') }}</a>
                    @endauth
                    <a href="{{ route('legal.terms') }}">{{ __('Terms & conditions') }}</a>
                    <a href="{{ route('legal.privacy') }}">{{ __('Privacy policy') }}</a>
                    <a href="{{ route('legal.refund') }}">{{ __('Refund policy') }}</a>
                    <a href="{{ route('legal.shipping') }}">{{ __('Shipping policy') }}</a>
                    <a href="{{ route('pages.contact') }}">{{ __('Contact us') }}</a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Follow us') }}</h3>
                    <p class="pro-footer-mk__news-text small mb-3">{{ __('Never miss anything from store by signing up to our newsletter.') }}</p>
                    <form id="cbFooterNewsForm" class="pro-footer-mk__news-form d-flex flex-column gap-2 mb-3">
                        @csrf
                        <input type="email" name="email" required class="form-control pro-footer-mk__news-input" placeholder="{{ __('Enter email address') }}" autocomplete="email" aria-label="{{ __('Email') }}">
                        <button type="submit" class="btn pro-footer-mk__subscribe w-100">{{ __('Subscribe') }}</button>
                    </form>
                    <p id="cbFooterNewsThanks" class="small text-success mb-0" hidden>{{ __('Thanks — you are subscribed.') }}</p>
                    <div class="pro-footer-mk__social d-flex flex-wrap gap-2">
                        <a href="https://www.facebook.com/share/1D7KtFBEGi/?mibextid=wwXIfr" class="pro-footer-mk__social-btn" aria-label="Facebook"><i class="bi bi-facebook" aria-hidden="true"></i></a>
                        <a href="#" class="pro-footer-mk__social-btn" aria-label="Twitter"><i class="bi bi-twitter-x" aria-hidden="true"></i></a>
                        <a href="https://www.instagram.com/dev_bhoominaturals?igsh=MXJwZXYzeDQwamNjMw%3D%3D" class="pro-footer-mk__social-btn" aria-label="Instagram"><i class="bi bi-instagram" aria-hidden="true"></i></a>
                        <a href="#" class="pro-footer-mk__social-btn" aria-label="Pinterest"><i class="bi bi-pinterest" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
            <div class="pro-footer-mk__bar cb-footer-bottom">
                <div class="d-flex flex-column flex-md-row align-items-center justify-content-md-between gap-3 py-4">
                    <p class="pro-footer-mk__copy small mb-0 text-center text-md-start">&copy; {{ date('Y') }} {{ config('app.name') }}. {{ __('All rights reserved.') }}</p>
                    <!-- <div class="pro-footer-mk__payments d-flex flex-wrap align-items-center justify-content-center gap-2" aria-label="{{ __('Payment methods') }}">
                        <span class="pro-footer-mk__pay">Razorpay</span>
                    </div> -->
                </div>
            </div>
        </div>
    </footer>

    <div id="cbCookie" class="cb-cookie" hidden>
        <span>{{ __('We use cookies to improve your experience.') }} <a href="{{ route('legal.privacy') }}" class="text-decoration-underline">{{ __('Privacy') }}</a></span>
        <button type="button" class="cb-btn-gold btn-sm" id="cbCookieOk">{{ __('Accept') }}</button>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
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
                proScrollTop.classList.toggle('is-visible', y > 320);
            }
        }
        onScroll();
        setMegaTop();
        window.addEventListener('scroll', onScroll, { passive: true });
        window.addEventListener('resize', setMegaTop, { passive: true });

        if (proScrollTop) {
            proScrollTop.addEventListener('click', function () {
                window.scrollTo({ top: 0, behavior: 'smooth' });
            });
        }

        document.querySelectorAll('.js-quick-view').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var m = document.getElementById('quickViewModal');
                if (!m) return;
                m.querySelector('.js-qv-img').src = btn.getAttribute('data-qv-img') || '';
                m.querySelector('.js-qv-img').alt = btn.getAttribute('data-qv-name') || '';
                m.querySelector('.js-qv-brand').textContent = btn.getAttribute('data-qv-brand') || '';
                m.querySelector('.js-qv-name').textContent = btn.getAttribute('data-qv-name') || '';
                var price = btn.getAttribute('data-qv-price');
                var cmp = btn.getAttribute('data-qv-compare');
                var el = m.querySelector('.js-qv-price');
                el.innerHTML = '';
                if (price) {
                    el.appendChild(document.createTextNode('₹' + Number(price).toLocaleString()));
                    if (cmp) {
                        var d = document.createElement('del');
                        d.className = 'text-muted fs-6 fw-normal ms-2';
                        d.textContent = '₹' + Number(cmp).toLocaleString();
                        el.appendChild(d);
                    }
                }
                var link = m.querySelector('.js-qv-link');
                link.href = btn.getAttribute('data-qv-url') || '#';
            });
        });

        var newsEl = document.getElementById('newsletterModal');
        if (newsEl) {
            newsEl.addEventListener('hidden.bs.modal', function () {
                localStorage.setItem('zm_news_popup', '1');
            });
        }
        if (!localStorage.getItem('zm_news_popup') && window.bootstrap && newsEl) {
            setTimeout(function () {
                new bootstrap.Modal(newsEl).show();
            }, 4500);
        }
        document.getElementById('proNewsPopupForm')?.addEventListener('submit', function (e) {
            e.preventDefault();
            localStorage.setItem('zm_news_popup', '1');
            bootstrap.Modal.getInstance(newsEl)?.hide();
        });

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
    })();
    </script>
    <script>
    (function () {
        var bar = document.getElementById('cbCookie');
        if (!bar) return;
        if (!localStorage.getItem('cb_cookie_ok')) bar.removeAttribute('hidden');
        document.getElementById('cbCookieOk')?.addEventListener('click', function () {
            localStorage.setItem('cb_cookie_ok', '1');
            bar.setAttribute('hidden', '');
        });
    })();
    </script>
    <script>
    document.getElementById('cbFooterNewsForm')?.addEventListener('submit', function (e) {
        e.preventDefault();
        var btn = this.querySelector('button[type="submit"]');
        if (btn) { btn.disabled = true; }
        document.getElementById('cbFooterNewsThanks')?.removeAttribute('hidden');
    });
    </script>
    <script>
    (function () {
        var el = document.getElementById('mkPagePreloader');
        if (!el) return;
        var reduced = window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches;
        function finish() {
            el.classList.add('is-done');
            el.setAttribute('aria-busy', 'false');
            el.setAttribute('aria-hidden', 'true');
            setTimeout(function () {
                if (el.parentNode) el.parentNode.removeChild(el);
            }, reduced ? 0 : 520);
        }
        if (document.readyState === 'complete') {
            finish();
        } else {
            window.addEventListener('load', finish);
        }
    })();
    </script>
    @include('partials.flash-toasts', ['includeValidationErrors' => true])
    @stack('body_end')
    @stack('scripts')

    <nav class="pro-mobile-nav d-lg-none" id="proMobileNav" aria-label="{{ __('Bottom navigation') }}">
        <a href="{{ route('market.home') }}" class="{{ request()->routeIs('market.home') ? 'active' : '' }}"><i class="bi bi-house-door" aria-hidden="true"></i>{{ __('Home') }}</a>
        <a href="{{ route('shop.search') }}"><i class="bi bi-search" aria-hidden="true"></i>{{ __('Search') }}</a>
        <span class="pro-mobile-nav__wrap position-relative d-inline-flex flex-column align-items-center">
            <button type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer" class="text-center border-0 bg-transparent p-0 d-flex flex-column align-items-center" style="color:inherit;">
                <i class="bi bi-bag" aria-hidden="true"></i><span class="mt-1">{{ __('Cart') }}</span>
            </button>
            @if(($layoutCartCount ?? 0) > 0)
                <span class="pro-mobile-nav__badge">{{ $layoutCartCount > 9 ? '9+' : $layoutCartCount }}</span>
            @endif
        </span>
        @auth
            <a href="{{ route('account.dashboard') }}" class="{{ request()->routeIs('account.*', 'orders.*') ? 'active' : '' }}"><i class="bi bi-person" aria-hidden="true"></i>{{ __('Account') }}</a>
        @else
            <a href="{{ route('login') }}"><i class="bi bi-person" aria-hidden="true"></i>{{ __('Account') }}</a>
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
            var offset = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
            document.documentElement.style.setProperty('--pro-mobile-nav-offset', offset + 'px');
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
</body>
</html>

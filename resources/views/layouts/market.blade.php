<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en-IN' : str_replace('_', '-', app()->getLocale()) }}" class="mk-wait">

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
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@500;600;700&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Assistant:wght@500;600;700&display=swap" rel="stylesheet"></noscript>
    <link href="{{ asset('css/market-critical.css') }}?v=7" rel="stylesheet">
    <style>
        html.mk-wait { overflow: hidden; }
        #mkPagePreloader {
            position: fixed; inset: 0; z-index: 2147483646;
            display: flex; align-items: center; justify-content: center;
            background: #ffffff;
            transition: opacity .28s ease, visibility .28s ease;
        }
        #mkPagePreloader.is-done { opacity: 0; visibility: hidden; pointer-events: none; }
        #mkPagePreloader .mk-page-preloader__plate {
            width: 78px; height: 78px; border-radius: 50%; background: #fff;
            box-shadow: 0 8px 32px rgba(0,0,0,.07), 0 2px 10px rgba(0,0,0,.05);
            display: flex; align-items: center; justify-content: center;
        }
        #mkPagePreloader .mk-page-preloader__ring {
            width: 42px; height: 42px; border-radius: 50%;
            border: 3px solid rgba(45,90,61,.14);
            border-top-color: #2d5a3d;
            border-right-color: #2d5a3d;
            animation: mk-page-preloader-spin .78s linear infinite;
        }
        @keyframes mk-page-preloader-spin { to { transform: rotate(360deg); } }
    </style>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="{{ asset('css/market.css') }}?v=13" rel="stylesheet">
    <link href="{{ asset('css/market-pro.css') }}?v=172" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" media="print" onload="this.media='all'">
    <noscript>
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    </noscript>
    @stack('head')
    @php $seoService = app(\App\Services\SeoService::class); @endphp
    <script type="application/ld+json">
    {!! json_encode($seoService->websiteSchema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($seoService->organizationSchema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    <script type="application/ld+json">
    {!! json_encode($seoService->localBusinessSchema(), JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
    </script>
    @php $faqSchema = request()->routeIs('market.home') ? $seoService->faqSchemaFromJson($seoService->global('faq_schema_json')) : null; @endphp
    @if($faqSchema)
        <script type="application/ld+json">
                    {!! json_encode($faqSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
                    </script>
    @endif
    @stack('schema')
</head>
@php
    $gaId = config('services.google.analytics_id') ?: $seoService->global('google_analytics_id');
    $isLocal = app()->environment('local');
@endphp
@if(filled($gaId) && !$isLocal)
    <!-- Google tag (gtag.js) -->
    <script async src="https://www.googletagmanager.com/gtag/js?id={{ $gaId }}"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag() { dataLayer.push(arguments); }
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
@else
    <script>
        window.gtag = window.gtag || function() {
            console.log('[GA4 Event]', arguments[0], arguments[1]);
        };
    </script>
@endif

<body class="cb-body cb-market-pro">
    <div id="mkPagePreloader" class="mk-page-preloader" role="alert" aria-live="polite" aria-busy="true"
        aria-label="{{ __('Loading') }}">
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
                <span><i class="bi bi-tag-fill me-1"
                        aria-hidden="true"></i>{{ __('Extra 10% off on prepaid orders.') }}</span>
                <span><i class="bi bi-telephone me-1" aria-hidden="true"></i>{{ __('Support') }}: <a
                        href="tel:+91 9217732670">+91 9217732670</a></span>
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

    <div class="offcanvas offcanvas-start pro-nav-offcanvas" tabindex="-1" id="marketNav"
        aria-labelledby="marketNavLabel">
        <div class="offcanvas-header pro-mnav-header">
            <a href="{{ route('market.home') }}" class="pro-mnav-brand text-decoration-none" id="marketNavLabel">
                @if(!empty($siteLogoUrl))
                    <img src="{{ \App\Support\OptimizedImage::url($siteLogoUrl, 420) }}" alt="{{ config('app.name') }}" class="pro-mnav-brand__logo" width="180"
                        height="50" decoding="async">
                @else
                    <span class="pro-mnav-brand__name font-anc-serif">{{ config('app.name') }}</span>
                @endif
                <!-- <span class="pro-mnav-brand__tag">{{ __('Come, relive style') }}</span> -->
            </a>
            <button type="button" class="btn-close pro-mnav-close" data-bs-dismiss="offcanvas"
                aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body pro-mnav-body p-0">
            <nav class="pro-mnav" aria-label="{{ __('Mobile') }}">
                @include('market.partials.header-menu-mobile')

                <div class="pro-mnav-divider" role="presentation"></div>
                <a class="pro-mnav-rowlink" href="{{ route('shop.search') }}">{{ __('Search') }}</a>
                <a class="pro-mnav-rowlink"
                    href="{{ route('shop.search', ['sort' => 'newest']) }}">{{ __('New arrivals') }}</a>

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
                    <form action="{{ route('logout') }}" method="post" class="pro-mnav-logout">@csrf
                        <button type="submit" class="pro-mnav-logout__btn">
                            <i class="bi bi-box-arrow-right" aria-hidden="true"></i>{{ __('Logout') }}
                        </button>
                    </form>
                @endguest
            </nav>
        </div>
    </div>

    <div class="offcanvas offcanvas-end pro-cart-drawer" tabindex="-1" id="cartDrawer"
        aria-labelledby="cartDrawerLabel">
        <div class="offcanvas-header border-bottom">
            <h2 class="h5 offcanvas-title fw-bold text-dark d-flex align-items-center gap-2" id="cartDrawerLabel">
                <span>{{ __('Your cart') }}</span>
                <span class="badge bg-primary rounded-pill fs-7 js-drawer-count-badge" @if(($layoutCartCount ?? 0) === 0)
                style="display:none;" @endif>{{ $layoutCartCount }}</span>
            </h2>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column">
            <div class="flex-grow-1 overflow-y-auto pe-1" id="drawerCartItemsList">
                @include('market.partials.cart-drawer-items')
            </div>

            <div class="mt-auto pt-3 border-top" id="drawerCartSummaryBlock" @if(($layoutCartCount ?? 0) === 0)
            style="display:none;" @endif>
                <div class="d-flex justify-content-between text-muted mb-2 small">
                    <span>{{ __('Subtotal') }}</span>
                    <span id="drawerCartSubtotal">₹{{ number_format($layoutCartSubtotal ?? 0, 2) }}</span>
                </div>
                <div class="d-flex justify-content-between text-muted mb-2 small">
                    <span>{{ __('Shipping Charge') }}</span>
                    <span id="drawerCartShipping" class="text-success fw-semibold">
                        @if(($layoutCartShipping ?? 0) <= 0)
                            {{ __('FREE') }}
                        @else
                            ₹{{ number_format($layoutCartShipping ?? 0, 2) }}
                        @endif
                    </span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between fw-bold mb-3">
                    <span>{{ __('Total') }}</span>
                    <span class="text-primary"
                        id="drawerCartTotal">₹{{ number_format($layoutCartTotal ?? 0, 2) }}</span>
                </div>

                @auth
                    <a href="{{ route('checkout.index') }}"
                        class="btn btn-primary w-100 rounded-pill py-2 fw-semibold mb-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                        {{ __('Proceed to checkout') }}
                        <i class="bi bi-arrow-right-short fs-5 transition-arrow"></i>
                    </a>
                @else
                    <a href="{{ route('login') }}"
                        class="btn btn-primary w-100 rounded-pill py-2 fw-semibold mb-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                        {{ __('Login to checkout') }}
                        <i class="bi bi-arrow-right-short fs-5 transition-arrow"></i>
                    </a>
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
                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                        aria-label="{{ __('Close') }}"></button>
                </div>
                <div class="modal-body pt-2">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <div class="qv-gallery js-qv-gallery qv-gallery--single">
                                <div class="qv-gallery__track js-qv-track" tabindex="0" aria-label="{{ __('Product images') }}">
                                    <div class="qv-gallery__slide is-active">
                                        <img src="" alt="" class="qv-gallery__img js-qv-img" width="400" height="480">
                                    </div>
                                </div>
                                <div class="qv-gallery__dots js-qv-dots" role="tablist" aria-label="{{ __('Image navigation') }}"></div>
                            </div>
                        </div>
                        <div class="col-md-7">
                            <p class="small text-primary fw-semibold mb-1 js-qv-brand"></p>
                            <h3 class="h5 js-qv-name"></h3>
                            <div class="fs-5 fw-bold mb-2 js-qv-price"></div>
                            <p class="js-qv-description small text-muted" hidden></p>
                            
                            <!-- Dynamic Variant Selectors -->
                            <div class="js-qv-variants-container mb-3 d-none">
                                <label class="form-label small fw-bold text-muted uppercase tracking-wider mb-2 js-qv-variants-label">{{ __('Select Option') }}</label>
                                <div class="d-flex flex-wrap gap-2 js-qv-variants-list"></div>
                            </div>
                            
                            <!-- Add to Cart Form inside Quick View -->
                            <div class="js-qv-cart-action mb-4">
                                <form action="{{ route('cart.add') }}" method="post" class="js-ajax-add-to-cart zm-pro-add-form d-flex gap-2">
                                    @csrf
                                    <input type="hidden" name="product_variant_id" class="js-qv-variant-id-input" value="">
                                    <div class="d-flex align-items-center border rounded-pill bg-light px-2 js-qv-qty-wrap" style="height: 38px; width: 110px;">
                                        <button type="button" class="btn btn-sm p-0 border-0 text-primary js-qv-qty-minus" style="font-size: 1.1rem; width: 25px; line-height: 1;"><i class="bi bi-dash"></i></button>
                                        <input type="number" name="qty" class="form-control form-control-sm text-center border-0 bg-transparent fw-semibold js-qv-qty-input" value="1" min="1" max="99" readonly style="box-shadow:none; padding:0; width:40px;">
                                        <button type="button" class="btn btn-sm p-0 border-0 text-primary js-qv-qty-plus" style="font-size: 1.1rem; width: 25px; line-height: 1;"><i class="bi bi-plus"></i></button>
                                    </div>
                                    <button type="submit" class="btn btn-primary rounded-pill px-4 js-qv-add-to-cart-btn">{{ __('Add to cart') }}</button>
                                    <button type="submit" name="buy_now" value="1" class="btn btn-outline-primary rounded-pill px-4 js-qv-buy-now-btn">{{ __('Buy now') }}</button>
                                </form>
                            </div>

                            <a href="#"
                                class="btn btn-link text-decoration-none p-0 small js-qv-link">{{ __('View full details') }} &gt;</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Newsletter popup --}}
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

    <main class="cb-main" id="main-content">
        @php $isMarketHome = request()->routeIs('market.home'); @endphp
        @if($isMarketHome)
            @yield('content')
        @else
            @stack('breadcrumb')
            <div class="cb-container pb-5">
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
            <button type="button" class="pro-footer-mk__scroll-top" id="proScrollTop"
                aria-label="{{ __('Back to top') }}" title="{{ __('Back to top') }}">
                <i class="bi bi-chevron-up" aria-hidden="true"></i>
            </button>
            <div class="row g-4 g-xl-5 pro-footer-mk__main py-2 align-items-start">
                <div class="col-12 col-md-6 col-xl-3">
                    <a href="{{ route('market.home') }}"
                        class="pro-footer-mk__brand mb-3 text-decoration-none d-inline-block">
                        <span class="pro-footer-mk__logo-text font-anc-serif">
                            <span class="pro-footer-mk__logo-main">{{ $footerBrand }}</span>
                        </span>
                    </a>
                    <p class="pro-footer-mk__desc small mb-4">
                        {{ __('Discover the latest trends and enjoy seamless shopping with our exclusive collections.') }}
                    </p>
                    <ul class="list-unstyled pro-footer-mk__contact mb-0">
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-geo-alt" aria-hidden="true"></i>
                            <span>{{ config('app.name') }}, {{ __('Ranikhet, Uttarakhand') }}, {{ __('India') }}</span>
                        </li>
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-telephone" aria-hidden="true"></i>
                            <a href="tel:+91 9217732670">{{ __('Call Us') }}: +91 9217732670 </a>
                        </li>
                        <li class="pro-footer-mk__contact-item">
                            <i class="bi bi-envelope" aria-hidden="true"></i>
                            <a href="mailto:support@devbhoominaturals.com">{{ __('Email Us') }}:
                                support@devbhoominaturals.com</a>
                        </li>
                    </ul>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Our Products') }}</h3>
                    @include('market.partials.menu-footer-links')
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Useful links') }}</h3>
                    <a href="{{ route('market.home') }}">{{ __('Home') }}</a>
                    <a href="{{ route('pages.about') }}">{{ __('About us') }}</a>
                    <a href="{{ route('blog.index') }}">{{ __('Blogs') }}</a>
                    <a href="{{ route('offers.index') }}">{{ __('Offers') }}</a>
                    <a href="{{ route('shop.search') }}">{{ __('Search') }}</a>
                </div>
                <div class="col-6 col-lg-4 col-xl-2">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Help center') }}</h3>
                    @auth
                        <a href="{{ route('account.dashboard') }}">{{ __('My account') }}</a>
                    @else
                        <a href="{{ route('login') }}">{{ __('My account') }}</a>
                    @endauth
                    <a href="{{ route('orders.track') }}">{{ __('Track order') }}</a>
                    <a href="{{ route('pages.contact') }}">{{ __('Contact us') }}</a>
                    <a href="{{ route('legal.terms') }}">{{ __('Terms & conditions') }}</a>
                    <a href="{{ route('legal.privacy') }}">{{ __('Privacy policy') }}</a>
                    <a href="{{ route('legal.refund') }}">{{ __('Refund policy') }}</a>
                    <a href="{{ route('legal.shipping') }}">{{ __('Shipping policy') }}</a>
                </div>
                <div class="col-12 col-md-6 col-xl-3">
                    <h3 class="cb-footer-heading pro-footer-mk__heading">{{ __('Follow us') }}</h3>
                    <p class="pro-footer-mk__news-text small mb-3">
                        {{ __('Never miss anything from store by signing up to our newsletter.') }}
                    </p>
                    <form id="cbFooterNewsForm" class="pro-footer-mk__news-form d-flex flex-column gap-2 mb-3">
                        @csrf
                        <input type="email" name="email" required class="form-control pro-footer-mk__news-input"
                            placeholder="{{ __('Enter email address') }}" autocomplete="email"
                            aria-label="{{ __('Email') }}">
                        <button type="submit" class="btn pro-footer-mk__subscribe w-100">{{ __('Subscribe') }}</button>
                    </form>
                    <p id="cbFooterNewsThanks" class="small text-success mb-0" hidden>
                        {{ __('Thanks — you are subscribed.') }}
                    </p>
                    <div class="pro-footer-mk__social d-flex flex-wrap gap-2">
                        <a href="https://www.facebook.com/share/1D7KtFBEGi/?mibextid=wwXIfr"
                            class="pro-footer-mk__social-btn" aria-label="Facebook"><i class="bi bi-facebook"
                                aria-hidden="true"></i></a>
                        <a href="#" class="pro-footer-mk__social-btn" aria-label="Twitter"><i class="bi bi-twitter-x"
                                aria-hidden="true"></i></a>
                        <a href="https://www.instagram.com/dev_bhoominaturals?igsh=MXJwZXYzeDQwamNjMw%3D%3D"
                            class="pro-footer-mk__social-btn" aria-label="Instagram"><i class="bi bi-instagram"
                                aria-hidden="true"></i></a>
                        <a href="https://wa.me/919217732670?text=Hi"
                            class="pro-footer-mk__social-btn pro-footer-mk__social-btn--whatsapp" aria-label="WhatsApp"
                            target="_blank" rel="noopener"><i class="bi bi-whatsapp" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
        </div>
        <div class="pro-footer-mk__bar cb-footer-bottom">
            <div class="cb-container">
                <div class="pro-footer-mk__bar-inner">
                    <p class="pro-footer-mk__copy small mb-0">&copy; {{ date('Y') }}
                        {{ config('app.name') }}. {{ __('All rights reserved.') }}
                    </p>
                    <div class="pro-footer-mk__payments" aria-label="{{ __('Payment methods') }}">
                        <img src="{{ asset('images/payments/visa.svg') }}" alt="Visa" width="48" height="30">
                        <img src="{{ asset('images/payments/mastercard.svg') }}" alt="Mastercard" width="48" height="30">
                        <img src="{{ asset('images/payments/rupay.svg') }}" alt="RuPay" width="52" height="30">
                        <img src="{{ asset('images/payments/upi.svg') }}" alt="UPI" width="42" height="30">
                        <img src="{{ asset('images/payments/razorpay.svg') }}" alt="Razorpay" width="68" height="30">
                    </div>
                    <p class="pro-footer-mk__credit small mb-0">
                        {{ __('Developed by') }}
                        <a href="https://quezent.com/" target="_blank" rel="noopener noreferrer">Quezent Technologies</a>
                    </p>
                </div>
            </div>
        </div>
    </footer>

    <div id="cbCookie" class="cb-cookie" hidden>
        <span>{{ __('We use cookies to improve your experience.') }} <a href="{{ route('legal.privacy') }}"
                class="text-decoration-underline">{{ __('Privacy') }}</a></span>
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

            var QV_SLIDE_MS = 3500;
            var qvAutoTimer = null;

            function qvStopAuto() {
                if (qvAutoTimer) {
                    clearInterval(qvAutoTimer);
                    qvAutoTimer = null;
                }
            }

            function qvStartAuto(m) {
                qvStopAuto();
                if (!m) return;
                var gallery = m.querySelector('.js-qv-gallery');
                var slides = m.querySelectorAll('.qv-gallery__slide');
                if (!gallery || slides.length < 2) return;
                if (window.matchMedia && window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;
                qvAutoTimer = setInterval(function () {
                    if (!m.classList.contains('show')) {
                        qvStopAuto();
                        return;
                    }
                    var total = m.querySelectorAll('.qv-gallery__slide').length;
                    if (total < 2) {
                        qvStopAuto();
                        return;
                    }
                    var next = ((gallery._qvIndex || 0) + 1) % total;
                    qvGoSlide(m, next);
                }, QV_SLIDE_MS);
            }

            var qvModal = document.getElementById('quickViewModal');
            if (qvModal) {
                qvModal.addEventListener('shown.bs.modal', function () {
                    var slides = qvModal.querySelectorAll('.qv-gallery__slide');
                    var active = qvModal.querySelector('.qv-gallery__slide.is-active');
                    var idx = Array.prototype.indexOf.call(slides, active);
                    qvGoSlide(qvModal, idx >= 0 ? idx : 0, true);
                    qvStartAuto(qvModal);
                });
                qvModal.addEventListener('hidden.bs.modal', qvStopAuto);
            }

            function qvUniqueUrls(list) {
                var out = [];
                var seen = {};
                (list || []).forEach(function (u) {
                    u = String(u || '').trim();
                    if (!u || seen[u]) return;
                    seen[u] = 1;
                    out.push(u);
                });
                return out;
            }

            function qvFileKey(u) {
                return String(u || '').split('?')[0].split('/').pop().toLowerCase();
            }

            function qvFindSlideIndex(urls, url) {
                if (!url) return -1;
                var exact = urls.indexOf(url);
                if (exact >= 0) return exact;
                var key = qvFileKey(url);
                if (!key) return -1;
                return urls.findIndex(function (u) {
                    var other = qvFileKey(u);
                    return other === key || other.indexOf(key.replace(/\.[^.]+$/, '')) !== -1 || key.indexOf(other.replace(/\.[^.]+$/, '')) !== -1;
                });
            }

            function qvGoSlide(m, index, instant) {
                var gallery = m.querySelector('.js-qv-gallery');
                var track = m.querySelector('.js-qv-track');
                var slides = m.querySelectorAll('.qv-gallery__slide');
                if (!gallery || !track || !slides[index]) return;
                var width = gallery.clientWidth || slides[0].offsetWidth || 0;
                track.style.transition = instant ? 'none' : '';
                track.style.transform = 'translate3d(-' + (index * width) + 'px, 0, 0)';
                if (instant) {
                    track.offsetHeight;
                    track.style.transition = '';
                }
                m.querySelectorAll('.qv-gallery__dot').forEach(function (dot, idx) {
                    dot.classList.toggle('is-active', idx === index);
                });
                slides.forEach(function (slide, idx) {
                    slide.classList.toggle('is-active', idx === index);
                });
                gallery._qvIndex = index;
            }

            function qvRenderGallery(m, urls, alt, activeUrl) {
                var gallery = m.querySelector('.js-qv-gallery');
                var track = m.querySelector('.js-qv-track');
                var dots = m.querySelector('.js-qv-dots');
                if (!gallery || !track || !dots) return;
                urls = qvUniqueUrls(urls);
                if (!urls.length) urls = [''];
                gallery._qvUrls = urls;
                track.innerHTML = '';
                dots.innerHTML = '';
                urls.forEach(function (src, i) {
                    var slide = document.createElement('div');
                    slide.className = 'qv-gallery__slide' + (i === 0 ? ' is-active' : '');
                    var img = document.createElement('img');
                    img.src = src;
                    img.alt = alt || '';
                    img.className = 'qv-gallery__img' + (i === 0 ? ' js-qv-img' : '');
                    img.width = 400;
                    img.height = 480;
                    img.decoding = 'async';
                    slide.appendChild(img);
                    track.appendChild(slide);

                    var dot = document.createElement('button');
                    dot.type = 'button';
                    dot.className = 'qv-gallery__dot' + (i === 0 ? ' is-active' : '');
                    dot.setAttribute('aria-label', 'Image ' + (i + 1));
                    dot.setAttribute('role', 'tab');
                    dot.addEventListener('click', function () {
                        qvGoSlide(m, i);
                        qvStartAuto(m);
                    });
                    dots.appendChild(dot);
                });
                gallery.classList.toggle('qv-gallery--single', urls.length < 2);
                var start = qvFindSlideIndex(urls, activeUrl);
                qvGoSlide(m, start >= 0 ? start : 0, true);
                qvStartAuto(m);
            }

            function qvShowImage(m, url) {
                var gallery = m.querySelector('.js-qv-gallery');
                var urls = (gallery && gallery._qvUrls) || [];
                var idx = qvFindSlideIndex(urls, url);
                if (idx >= 0) {
                    qvGoSlide(m, idx);
                    qvStartAuto(m);
                    return;
                }
                var img = m.querySelector('.qv-gallery__slide.is-active .qv-gallery__img') || m.querySelector('.js-qv-img');
                if (img && url) img.src = url;
            }

            document.querySelectorAll('.js-quick-view').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    var m = document.getElementById('quickViewModal');
                    if (!m) return;
                    
                    // Reset quantity
                    var qtyInput = m.querySelector('.js-qv-qty-input');
                    if (qtyInput) qtyInput.value = 1;

                    var qvName = btn.getAttribute('data-qv-name') || '';
                    var qvImages = [];
                    try {
                        qvImages = JSON.parse(btn.getAttribute('data-qv-images') || '[]');
                    } catch (e) {
                        qvImages = [];
                    }
                    if (!Array.isArray(qvImages) || !qvImages.length) {
                        qvImages = [btn.getAttribute('data-qv-img') || ''];
                    }
                    qvRenderGallery(m, qvImages, qvName, btn.getAttribute('data-qv-img') || '');
                    m.querySelector('.js-qv-brand').textContent = btn.getAttribute('data-qv-brand') || '';
                    m.querySelector('.js-qv-name').textContent = qvName;

                    var descEl = m.querySelector('.js-qv-description');
                    if (descEl) {
                        var desc = (btn.getAttribute('data-qv-desc') || '').trim();
                        descEl.textContent = desc;
                        descEl.hidden = !desc;
                    }
                    
                    var price = btn.getAttribute('data-qv-price');
                    var cmp = btn.getAttribute('data-qv-compare');
                    var el = m.querySelector('.js-qv-price');
                    
                    function updatePriceDisplay(p, c) {
                        el.innerHTML = '';
                        if (p !== null && p !== undefined && p !== '') {
                            el.appendChild(document.createTextNode('₹' + Number(p).toLocaleString()));
                            if (c) {
                                var d = document.createElement('del');
                                d.className = 'text-muted fs-6 fw-normal ms-2';
                                d.textContent = '₹' + Number(c).toLocaleString();
                                el.appendChild(d);
                            }
                        }
                    }
                    updatePriceDisplay(price, cmp);

                    var link = m.querySelector('.js-qv-link');
                    link.href = btn.getAttribute('data-qv-url') || '#';
                    
                    var variantsDataStr = btn.getAttribute('data-qv-variants');
                    var labelName = btn.getAttribute('data-qv-label') || 'Option';
                    var variantsContainer = m.querySelector('.js-qv-variants-container');
                    var variantsList = m.querySelector('.js-qv-variants-list');
                    var labelEl = m.querySelector('.js-qv-variants-label');
                    var varIdInput = m.querySelector('.js-qv-variant-id-input');
                    
                    var btnAdd = m.querySelector('.js-qv-add-to-cart-btn');
                    var btnBuy = m.querySelector('.js-qv-buy-now-btn');
                    var qtyWrap = m.querySelector('.js-qv-qty-wrap');

                    function qvIsBuyable(v) {
                        if (!v) return false;
                        var stock = parseInt(v.stock, 10);
                        if (isNaN(stock)) stock = v.buyable ? 1 : 0;
                        return !!v.buyable && stock > 0;
                    }

                    function applyQvStockUi(buyable, stock) {
                        var max = buyable ? Math.max(1, parseInt(stock, 10) || 1) : 1;
                        if (qtyInput) {
                            qtyInput.setAttribute('max', String(max));
                            var cur = parseInt(qtyInput.value, 10) || 1;
                            if (cur < 1) cur = 1;
                            if (cur > max) cur = max;
                            qtyInput.value = cur;
                        }
                        if (qtyWrap) qtyWrap.classList.toggle('d-none', !buyable);
                        if (btnAdd) {
                            btnAdd.disabled = !buyable;
                            btnAdd.textContent = buyable ? 'Add to cart' : 'Out of Stock';
                        }
                        if (btnBuy) btnBuy.classList.toggle('d-none', !buyable);
                    }

                    function selectQvVariant(v, pill) {
                        variantsList.querySelectorAll('button').forEach(function(b) {
                            b.classList.remove('active', 'btn-primary');
                            b.classList.add('btn-outline-secondary');
                        });
                        if (pill) {
                            pill.classList.add('active', 'btn-primary');
                            pill.classList.remove('btn-outline-secondary');
                        }
                        varIdInput.value = v.id;
                        updatePriceDisplay(v.effectivePrice, v.unitPrice > v.effectivePrice ? v.unitPrice : null);
                        qvShowImage(m, v.image || btn.getAttribute('data-qv-img') || '');
                        applyQvStockUi(qvIsBuyable(v), v.stock);
                    }
                    
                    if (variantsDataStr && variantsDataStr !== '[]' && variantsDataStr !== 'null') {
                        var variants = JSON.parse(variantsDataStr);
                        variantsContainer.classList.remove('d-none');
                        labelEl.textContent = 'Select ' + labelName;
                        variantsList.innerHTML = '';
                        
                        variants.forEach(function(v) {
                            var pill = document.createElement('button');
                            pill.type = 'button';
                            pill.className = 'btn btn-outline-secondary btn-sm rounded-pill px-3 py-1';
                            pill.textContent = v.size || v.color;
                            var buyable = qvIsBuyable(v);
                            if (!buyable) {
                                pill.disabled = true;
                                pill.classList.add('is-disabled');
                                pill.setAttribute('aria-disabled', 'true');
                                pill.title = 'Out of stock';
                            }

                            pill.addEventListener('click', function() {
                                if (pill.disabled) return;
                                selectQvVariant(v, pill);
                            });
                            
                            variantsList.appendChild(pill);
                        });
                        
                        var defaultSelected = variants.find(qvIsBuyable) || variants[0];
                        if (defaultSelected) {
                            var pills = variantsList.querySelectorAll('button');
                            var defIdx = variants.indexOf(defaultSelected);
                            selectQvVariant(defaultSelected, pills[defIdx] || null);
                        }
                    } else {
                        variantsContainer.classList.add('d-none');
                        varIdInput.value = btn.getAttribute('data-qv-variant') || '';
                        
                        var isBuyable = btn.closest('.js-cart-add-container')?.getAttribute('data-buyable') !== '0';
                        applyQvStockUi(!!isBuyable, isBuyable ? 99 : 0);
                    }
                    if (window.bootstrap && bootstrap.Modal) {
                        bootstrap.Modal.getOrCreateInstance(m).show();
                    }
                });
            });

            // Quick view quantity selectors
            var qvMinus = document.querySelector('.js-qv-qty-minus');
            var qvPlus = document.querySelector('.js-qv-qty-plus');
            var qvQtyInput = document.querySelector('.js-qv-qty-input');
            if (qvMinus && qvPlus && qvQtyInput) {
                qvMinus.addEventListener('click', function() {
                    var val = parseInt(qvQtyInput.value, 10) || 1;
                    if (val > 1) {
                        qvQtyInput.value = val - 1;
                    }
                });
                qvPlus.addEventListener('click', function() {
                    var val = parseInt(qvQtyInput.value, 10) || 1;
                    var max = parseInt(qvQtyInput.getAttribute('max'), 10) || 99;
                    if (val < max) {
                        qvQtyInput.value = val + 1;
                    }
                });
            }

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
                var hoverImg = card.querySelector('img[data-hover-src]');
                if (!hoverImg) return;
                var hoverSrc = hoverImg.getAttribute('data-hover-src');
                if (hoverSrc && hoverImg.getAttribute('src') !== hoverSrc) {
                    hoverImg.setAttribute('src', hoverSrc);
                }
            }, true);
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
    @include('partials.flash-toasts', ['includeValidationErrors' => true])
    @stack('body_end')
    @stack('scripts')

    <nav class="pro-mobile-nav d-lg-none" id="proMobileNav" aria-label="{{ __('Bottom navigation') }}">
        <a href="{{ route('market.home') }}" class="{{ request()->routeIs('market.home') ? 'active' : '' }}"><i
                class="bi bi-house-door" aria-hidden="true"></i>{{ __('Home') }}</a>
        <a href="{{ route('shop.search') }}"><i class="bi bi-search" aria-hidden="true"></i>{{ __('Search') }}</a>
        <span class="pro-mobile-nav__wrap">
            <button type="button" data-bs-toggle="offcanvas" data-bs-target="#cartDrawer"
                class="text-center border-0 bg-transparent p-0 d-flex flex-column align-items-center"
                style="color:inherit;">
                <span class="position-relative d-inline-flex">
                    <i class="bi bi-bag" aria-hidden="true"></i>
                    <span class="pro-mobile-nav__badge" @if(($layoutCartCount ?? 0) === 0) style="display:none;"
                    @endif>{{ $layoutCartCount > 9 ? '9+' : $layoutCartCount }}</span>
                </span>
                <span class="mt-1">{{ __('Cart') }}</span>
            </button>
        </span>
        @auth
            <a href="{{ route('account.dashboard') }}"
                class="{{ request()->routeIs('account.*', 'orders.*') ? 'active' : '' }}"><i class="bi bi-person"
                    aria-hidden="true"></i>{{ __('Account') }}</a>
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
                var activeEl = document.activeElement;
                var isInputFocused = activeEl && (
                    activeEl.tagName === 'INPUT' ||
                    activeEl.tagName === 'TEXTAREA' ||
                    activeEl.tagName === 'SELECT' ||
                    activeEl.hasAttribute('contenteditable')
                );
                var diff = Math.max(0, window.innerHeight - vv.height - vv.offsetTop);
                // Only shift if an input is focused and the keyboard is actually visible (diff > 100px)
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
    <script>
        (function () {
            var csrf = document.querySelector('meta[name="csrf-token"]');
            if (!csrf) return;

            var csrfToken = csrf.getAttribute('content');

            window.globalCartMap = @json(($layoutCartItems ?? collect())->mapWithKeys(fn($item) => [$item->product_variant_id => ['id' => $item->id, 'qty' => $item->qty]]));

            function formatMoney(n) {
                return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
            }

            function formatMoneyInt(n) {
                return '₹' + Math.round(n).toLocaleString('en-IN');
            }

            function updateCartBadges(count) {
                var mobileBadges = document.querySelectorAll('.pro-mobile-nav__badge');
                var desktopBadges = document.querySelectorAll('.mk-myntra-bag-badge');
                var drawerBadges = document.querySelectorAll('.js-drawer-count-badge');

                mobileBadges.forEach(function (badge) {
                    if (count > 0) {
                        badge.textContent = count > 9 ? '9+' : count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });

                desktopBadges.forEach(function (badge) {
                    if (count > 0) {
                        badge.textContent = count > 99 ? '99+' : count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });

                drawerBadges.forEach(function (badge) {
                    if (count > 0) {
                        badge.textContent = count;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });
            }

            function updateWishlistBadges(count) {
                var n = parseInt(count, 10) || 0;
                document.querySelectorAll('.mk-myntra-wish-badge').forEach(function (badge) {
                    if (n > 0) {
                        badge.textContent = n > 99 ? '99+' : n;
                        badge.style.display = '';
                    } else {
                        badge.style.display = 'none';
                    }
                });
            }

            function applyWishlistState(productId, wishlisted) {
                document.querySelectorAll('.js-ajax-wishlist').forEach(function (form) {
                    var idInput = form.querySelector('input[name="product_id"]');
                    if (!idInput || String(idInput.value) !== String(productId)) return;
                    var btn = form.querySelector('button[type="submit"]');
                    if (!btn) return;
                    btn.classList.toggle('is-wishlisted', !!wishlisted);
                    btn.setAttribute('aria-pressed', wishlisted ? 'true' : 'false');
                    btn.title = wishlisted ? 'Remove from wishlist' : 'Wishlist';
                    var icon = btn.querySelector('.bi');
                    if (icon) {
                        icon.classList.toggle('bi-heart-fill', !!wishlisted);
                        icon.classList.toggle('bi-heart', !wishlisted);
                    }
                    var label = btn.querySelector('.js-wish-label');
                    if (label) {
                        label.textContent = wishlisted ? 'Wishlisted' : 'Wishlist';
                    }
                });
            }

            function updateDrawerTotals(data, totalQty) {
                var subtotalEl = document.getElementById('drawerCartSubtotal');
                var shippingEl = document.getElementById('drawerCartShipping');
                var totalEl = document.getElementById('drawerCartTotal');

                if (subtotalEl) subtotalEl.textContent = formatMoney(data.subtotal);
                if (shippingEl) {
                    if (data.is_free_shipping) {
                        shippingEl.textContent = 'FREE';
                        shippingEl.className = 'text-success fw-semibold';
                    } else {
                        shippingEl.textContent = formatMoney(data.shipping_charge);
                        shippingEl.className = 'text-muted';
                    }
                }
                if (totalEl) totalEl.textContent = formatMoney(data.total);

                // Calculate count dynamically from UI quantities if not provided
                if (typeof totalQty === 'undefined') {
                    totalQty = 0;
                    document.querySelectorAll('.js-drawer-qty-val').forEach(function (el) {
                        totalQty += parseInt(el.textContent, 10) || 0;
                    });
                }

                updateCartBadges(totalQty);

                var summaryBlock = document.getElementById('drawerCartSummaryBlock');
                var emptyState = document.getElementById('drawerCartEmptyState');

                if (totalQty === 0) {
                    if (summaryBlock) summaryBlock.style.display = 'none';
                    if (emptyState) {
                        emptyState.style.display = 'block';
                    } else {
                        var itemsList = document.getElementById('drawerCartItemsList');
                        if (itemsList) {
                            itemsList.innerHTML = '<div class="text-center py-5" id="drawerCartEmptyState">' +
                                '<i class="bi bi-bag-x text-muted mb-2" style="font-size: 3rem; opacity: 0.4; display: block;"></i>' +
                                '<p class="text-muted small mb-0">Your cart is empty.</p>' +
                                '</div>';
                        }
                    }
                } else {
                    if (summaryBlock) summaryBlock.style.display = '';
                    if (emptyState) emptyState.style.display = 'none';
                }
            }

            window.msgOut = @json(__('Out of stock'));

            window.syncCartCTAContainers = function (cartMap) {
                document.querySelectorAll('.js-cart-add-container').forEach(function (container) {
                    var variantId = parseInt(container.getAttribute('data-variant-id'), 10);
                    if (!variantId) return;

                    var isHoverIcon = container.classList.contains('d-inline-block'); // Hover icon wrapper in card
                    var isPdp = container.getAttribute('data-pdp') === '1';
                    var isSticky = container.getAttribute('data-sticky') === '1';
                    var buyable = container.getAttribute('data-buyable') !== '0';
                    var item = cartMap[variantId];

                    // Remove existing selector if any
                    var oldSelector = container.querySelector('.js-qty-pill-selector');
                    if (oldSelector) oldSelector.remove();

                    var oldOos = container.querySelector('.js-pdp-oos-pill');
                    if (oldOos) oldOos.remove();

                    var form = container.querySelector('form');
                    var pdpCtas = container.querySelector('.js-default-pdp-ctas');
                    var pdpQtyCol = document.getElementById('pdpQtyCol');

                    // If not buyable (out of stock)
                    if (!buyable) {
                        if (form) form.classList.add('d-none');
                        if (pdpCtas) pdpCtas.classList.add('d-none');
                        if (isPdp && pdpQtyCol) pdpQtyCol.classList.add('d-none');

                        // Show "Out of stock" button inside the container
                        var oosHtml = '';
                        if (isPdp) {
                            oosHtml = '<div class="js-pdp-oos-pill pro-pdp-oos-cta">' +
                                '<button type="button" class="zm-btn btn-secondary" disabled style="min-height: 48px; background-color: #6c757d; color: #fff; border-color: #6c757d; padding: 0.85rem 1.85rem; font-size: 0.8125rem; font-weight: 600; text-transform: uppercase;">' + window.msgOut + '</button>' +
                                '</div>';
                        } else if (isSticky) {
                            oosHtml = '<div class="js-pdp-oos-pill pro-sticky-oos">' +
                                '<button type="button" class="btn btn-secondary rounded-pill" disabled>' + window.msgOut + '</button>' +
                                '</div>';
                        } else {
                            oosHtml = '<div class="js-pdp-oos-pill w-100">' +
                                '<button type="button" class="btn btn-secondary w-100" disabled>' + window.msgOut + '</button>' +
                                '</div>';
                        }
                        container.insertAdjacentHTML('beforeend', oosHtml);
                        return;
                    }

                    if (item && (isPdp || isSticky)) {
                        if (form) form.classList.add('d-none');
                        if (pdpCtas) pdpCtas.classList.add('d-none');
                        if (isPdp && pdpQtyCol) pdpQtyCol.classList.add('d-none');

                        var html = '';
                        if (isHoverIcon) {
                            html = '<div class="js-qty-pill-selector d-inline-flex align-items-center bg-light border rounded-pill px-1" style="height: 38px;" data-variant-id="' + variantId + '" data-item-id="' + item.id + '">' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 0.95rem; width: 24px; line-height: 1;"><i class="bi bi-dash"></i></button>' +
                                '<span class="fw-semibold px-1 js-selector-qty-val" style="font-size: 0.85rem; min-width: 16px; text-align: center;">' + item.qty + '</span>' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 0.95rem; width: 24px; line-height: 1;"><i class="bi bi-plus"></i></button>' +
                                '</div>';
                        } else if (isPdp) {
                            html = '<div class="js-qty-pill-selector d-flex gap-2" data-variant-id="' + variantId + '" data-item-id="' + item.id + '">' +
                                '<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2" style="height: 48px; min-width: 130px;">' +
                                '<button type="button" class="btn btn-lg p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 1.3rem; width: 35px; height: 100%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-dash"></i></button>' +
                                '<span class="fw-bold px-2 js-selector-qty-val" style="font-size: 1.1rem; min-width: 30px; text-align: center;">' + item.qty + '</span>' +
                                '<button type="button" class="btn btn-lg p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 1.3rem; width: 35px; height: 100%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-plus"></i></button>' +
                                '</div>' +
                                '</div>';
                        } else if (isSticky) {
                            html = '<div class="js-qty-pill-selector d-flex gap-2 align-items-center" data-variant-id="' + variantId + '" data-item-id="' + item.id + '">' +
                                '<div class="d-inline-flex align-items-center bg-light border rounded-pill px-2" style="height: 38px; min-width: 100px;">' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 1.1rem; width: 30px; height: 100%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-dash"></i></button>' +
                                '<span class="fw-bold px-2 js-selector-qty-val" style="font-size: 0.95rem; min-width: 20px; text-align: center;">' + item.qty + '</span>' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 1.1rem; width: 30px; height: 100%; display: flex; align-items: center; justify-content: center;"><i class="bi bi-plus"></i></button>' +
                                '</div>' +
                                '</div>';
                        } else {
                            html = '<div class="js-qty-pill-selector d-flex gap-2 w-100" data-variant-id="' + variantId + '" data-item-id="' + item.id + '">' +
                                '<div class="d-flex align-items-center justify-content-between border rounded-pill bg-light px-2" style="height: 38px; width: 100%;">' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 1.1rem; line-height: 1; height: 100%; display: flex; align-items: center; justify-content: center; width: 30px;"><i class="bi bi-dash"></i></button>' +
                                '<span class="fw-semibold js-selector-qty-val" style="font-size: 0.95rem; min-width: 24px; text-align: center;">' + item.qty + '</span>' +
                                '<button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 1.1rem; line-height: 1; height: 100%; display: flex; align-items: center; justify-content: center; width: 30px;"><i class="bi bi-plus"></i></button>' +
                                '</div>' +
                                '</div>';
                        }

                        container.insertAdjacentHTML('beforeend', html);
                    } else {
                        if (form) form.classList.remove('d-none');
                        if (pdpCtas) pdpCtas.classList.remove('d-none');
                        if (isPdp && pdpQtyCol) pdpQtyCol.classList.remove('d-none');
                    }
                });
            };

            function patchDrawerCart(itemId, qty, card) {
                var url = '/cart/' + itemId;
                return fetch(url, {
                    method: 'PATCH',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({ qty: qty })
                })
                    .then(function (r) {
                        if (!r.ok) throw new Error('Failed to update quantity');
                        return r.json();
                    })
                    .then(function (data) {
                        window.globalCartMap = {};
                        var totalQty = 0;
                        if (data.items) {
                            data.items.forEach(function (it) {
                                window.globalCartMap[it.product_variant_id] = { id: it.id, qty: parseInt(it.qty, 10) || 0 };
                                totalQty += parseInt(it.qty, 10) || 0;
                            });
                        }
                        window.syncCartCTAContainers(window.globalCartMap);

                        // Trigger Google Analytics events from AJAX response if defined
                        if (data.ga_events && Array.isArray(data.ga_events) && typeof gtag === 'function') {
                            data.ga_events.forEach(function (event) {
                                gtag('event', event.name, event.data);
                            });
                        }

                        // If updated from outside the drawer (e.g. PDP or product card listing), replace the HTML
                        var isFromDrawer = card && card.classList.contains('js-drawer-item-card');
                        if (!isFromDrawer) {
                            var itemsList = document.getElementById('drawerCartItemsList');
                            if (itemsList && data.html) {
                                itemsList.innerHTML = data.html;
                            }
                        }

                        updateDrawerTotals(data.totals, totalQty);

                        var mainCard = document.querySelector('.js-cart-item-card[data-item-id="' + itemId + '"]');
                        if (mainCard) {
                            var mainInput = mainCard.querySelector('.js-cart-qty-input');
                            if (mainInput) {
                                mainInput.value = qty;
                                var unitPriceMain = parseFloat(mainCard.querySelector('.js-item-unit').getAttribute('data-unit-price')) || 0;
                                var mainItemTotalEl = mainCard.querySelector('.js-item-total');
                                if (mainItemTotalEl) mainItemTotalEl.textContent = '₹' + Math.round(unitPriceMain * qty).toLocaleString('en-IN');
                                var subtotalEl = document.getElementById('cartSubtotal');
                                var shippingEl = document.getElementById('cartShipping');
                                var totalEl = document.getElementById('cartTotal');
                                if (subtotalEl) subtotalEl.textContent = formatMoney(data.totals.subtotal);
                                if (shippingEl) shippingEl.textContent = data.totals.is_free_shipping ? 'FREE' : formatMoney(data.totals.shipping_charge);
                                if (totalEl) totalEl.textContent = formatMoney(data.totals.total);
                                var minusBtn = mainCard.querySelector('.js-qty-minus');
                                if (minusBtn) {
                                    minusBtn.style.visibility = qty < 2 ? 'hidden' : 'visible';
                                }
                            }
                        }
                    })
                    .catch(function (err) {
                        console.error(err);
                    });
            }

            function deleteDrawerCart(itemId, card) {
                if (card) {
                    card.classList.add('js-drawer-item-fadeout');
                }
                var url = '/cart/' + itemId;
                return fetch(url, {
                    method: 'DELETE',
                    headers: {
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                    .then(function (r) {
                        if (!r.ok) {
                            if (card) card.classList.remove('js-drawer-item-fadeout');
                            throw new Error('Failed to delete item');
                        }
                        return r.json();
                    })
                    .then(function (data) {
                        if (card) {
                            card.remove();
                        }
                        window.globalCartMap = {};
                        var totalQty = 0;
                        if (data.items) {
                            data.items.forEach(function (it) {
                                window.globalCartMap[it.product_variant_id] = { id: it.id, qty: parseInt(it.qty, 10) || 0 };
                                totalQty += parseInt(it.qty, 10) || 0;
                            });
                        }
                        window.syncCartCTAContainers(window.globalCartMap);

                        // If deleted from outside the drawer, replace the HTML
                        var isFromDrawer = card && card.classList.contains('js-drawer-item-card');
                        if (!isFromDrawer) {
                            var itemsList = document.getElementById('drawerCartItemsList');
                            if (itemsList && data.html) {
                                itemsList.innerHTML = data.html;
                            }
                        }

                        updateDrawerTotals(data.totals, totalQty);

                        var mainCard = document.querySelector('.js-cart-item-card[data-item-id="' + itemId + '"]');
                        if (mainCard) {
                            window.location.reload();
                        }
                    })
                    .catch(function (err) {
                        console.error(err);
                    });
            }

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form.classList.contains('js-ajax-add-to-cart')) return;

                if (e.submitter && e.submitter.getAttribute('name') === 'buy_now') {
                    return;
                }

                e.preventDefault();

                var variantInput = form.querySelector('[name="product_variant_id"]');
                var qtyInput = form.querySelector('[name="qty"]');
                if (!variantInput) return;

                var variantId = parseInt(variantInput.value, 10);
                var qty = parseInt(qtyInput ? qtyInput.value : 1, 10) || 1;

                var submitBtn = e.submitter || form.querySelector('[type="submit"]');
                if (submitBtn) submitBtn.disabled = true;

                fetch(form.getAttribute('action') || '/cart', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_variant_id: variantId,
                        qty: qty
                    })
                })
                    .then(function (r) {
                        if (submitBtn) submitBtn.disabled = false;
                        if (!r.ok) return r.json().then(function (err) { throw new Error(err.error || 'Failed to add to cart'); });
                        return r.json();
                    })
                    .then(function (data) {
                        window.globalCartMap = {};
                        var totalQty = 0;
                        if (data.items) {
                            data.items.forEach(function (it) {
                                window.globalCartMap[it.product_variant_id] = { id: it.id, qty: parseInt(it.qty, 10) || 0 };
                                totalQty += parseInt(it.qty, 10) || 0;
                            });
                        }
                        window.syncCartCTAContainers(window.globalCartMap);

                        var itemsList = document.getElementById('drawerCartItemsList');
                        if (itemsList && data.html) {
                            itemsList.innerHTML = data.html;
                        }

                        updateDrawerTotals(data.totals, totalQty);

                        // Trigger Google Analytics events from AJAX response if defined
                        if (data.ga_events && Array.isArray(data.ga_events) && typeof gtag === 'function') {
                            data.ga_events.forEach(function (event) {
                                gtag('event', event.name, event.data);
                            });
                        }

                        // Close quickViewModal if open
                        var quickViewEl = document.getElementById('quickViewModal');
                        if (quickViewEl && window.bootstrap) {
                            var modal = bootstrap.Modal.getInstance(quickViewEl);
                            if (modal) {
                                modal.hide();
                            }
                        }

                        var drawerEl = document.getElementById('cartDrawer');
                        if (drawerEl && window.bootstrap) {
                            var offcanvas = bootstrap.Offcanvas.getInstance(drawerEl) || new bootstrap.Offcanvas(drawerEl);
                            offcanvas.show();
                        }
                    })
                    .catch(function (err) {
                        alert(err.message || 'Error adding to cart');
                    });
            });

            document.addEventListener('submit', function (e) {
                var form = e.target;
                if (!form.classList.contains('js-ajax-wishlist')) return;
                e.preventDefault();

                var idInput = form.querySelector('input[name="product_id"]');
                var btn = form.querySelector('button[type="submit"]');
                if (!idInput) return;
                if (form.getAttribute('data-busy') === '1') return;
                form.setAttribute('data-busy', '1');
                if (btn) btn.disabled = true;

                fetch(form.getAttribute('action') || '/wishlist', {
                    method: 'POST',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken,
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ product_id: parseInt(idInput.value, 10) })
                })
                    .then(function (r) {
                        if (r.status === 401 || r.status === 419) {
                            window.location.href = '{{ route('login') }}';
                            return Promise.reject();
                        }
                        if (!r.ok) {
                            return r.json().catch(function () { return {}; }).then(function (err) {
                                throw new Error((err && (err.message || err.error)) || 'Could not update wishlist');
                            });
                        }
                        return r.json();
                    })
                    .then(function (data) {
                        if (!data) return;
                        applyWishlistState(data.product_id || idInput.value, !!data.wishlisted);
                        if (typeof data.count !== 'undefined') {
                            updateWishlistBadges(data.count);
                        }
                        if (data.message && typeof window.zmShowFlashToast === 'function') {
                            window.zmShowFlashToast(data.message, 'success');
                        }
                    })
                    .catch(function (err) {
                        if (err && err.message && typeof window.zmShowFlashToast === 'function') {
                            window.zmShowFlashToast(err.message, 'danger');
                        } else if (err && err.message) {
                            alert(err.message);
                        }
                    })
                    .finally(function () {
                        form.removeAttribute('data-busy');
                        if (btn) btn.disabled = false;
                    });
            });

            document.addEventListener('click', function (e) {
                var target = e.target;

                var cardOpt = target.closest('.js-card-variant-pill');
                if (cardOpt) {
                    if (cardOpt.disabled || cardOpt.getAttribute('aria-disabled') === 'true') return;
                    var card = cardOpt.closest('.zm-pro-card');
                    if (!card) return;
                    card.querySelectorAll('.js-card-variant-pill').forEach(function (btn) {
                        btn.classList.toggle('is-active', btn === cardOpt);
                    });
                    var optId = cardOpt.getAttribute('data-id') || '';
                    var optPrice = cardOpt.getAttribute('data-price');
                    var optCompare = cardOpt.getAttribute('data-compare');
                    var optBuyable = cardOpt.getAttribute('data-buyable') === '1';
                    var optImage = cardOpt.getAttribute('data-image') || '';
                    var container = card.querySelector('.js-cart-add-container');
                    if (container) {
                        container.setAttribute('data-variant-id', optId);
                        container.setAttribute('data-buyable', optBuyable ? '1' : '0');
                        var variantInput = container.querySelector('input[name="product_variant_id"]');
                        if (variantInput) variantInput.value = optId;
                    }
                    var nowEl = card.querySelector('.js-card-price-now');
                    var cmpEl = card.querySelector('.js-card-price-compare');
                    var offEl = card.querySelector('.js-card-price-off');
                    if (nowEl && optPrice !== null && optPrice !== '') {
                        nowEl.textContent = '₹' + Math.round(Number(optPrice)).toLocaleString('en-IN');
                    }
                    if (cmpEl) {
                        if (optCompare) {
                            cmpEl.textContent = '₹' + Math.round(Number(optCompare)).toLocaleString('en-IN');
                            cmpEl.hidden = false;
                        } else {
                            cmpEl.hidden = true;
                        }
                    }
                    if (offEl) {
                        if (optCompare && Number(optCompare) > Number(optPrice)) {
                            offEl.textContent = Math.round(100 - (Number(optPrice) / Number(optCompare)) * 100) + '% Off';
                            offEl.hidden = false;
                        } else {
                            offEl.hidden = true;
                        }
                    }
                    if (optImage) {
                        var cardImg = card.querySelector('.zm-pro-card__img--primary');
                        if (cardImg) cardImg.src = optImage;
                    }
                    if (typeof window.syncCartCTAContainers === 'function') {
                        window.syncCartCTAContainers(window.globalCartMap || {});
                    }
                    return;
                }

                var buyNowBtn = target.closest('.js-pdp-direct-buynow');
                if (buyNowBtn) {
                    window.location.href = '/checkout';
                    return;
                }

                var plusBtn = target.closest('.js-selector-qty-plus');
                if (plusBtn) {
                    var selector = plusBtn.closest('.js-qty-pill-selector');
                    var itemId = selector.getAttribute('data-item-id');
                    var valEl = selector.querySelector('.js-selector-qty-val');
                    if (valEl) {
                        var qty = parseInt(valEl.textContent, 10) || 1;
                        if (qty < 99) {
                            qty++;
                            valEl.textContent = qty;
                            patchDrawerCart(itemId, qty, selector);
                        }
                    }
                    return;
                }

                var minusBtn = target.closest('.js-selector-qty-minus');
                if (minusBtn) {
                    var selector = minusBtn.closest('.js-qty-pill-selector');
                    var itemId = selector.getAttribute('data-item-id');
                    var valEl = selector.querySelector('.js-selector-qty-val');
                    if (valEl) {
                        var qty = parseInt(valEl.textContent, 10) || 1;
                        if (qty > 1) {
                            qty--;
                            valEl.textContent = qty;
                            patchDrawerCart(itemId, qty, selector);
                        } else {
                            deleteDrawerCart(itemId, selector);
                        }
                    }
                    return;
                }
            });

            var drawerList = document.getElementById('drawerCartItemsList');
            if (drawerList) {
                drawerList.addEventListener('click', function (e) {
                    var target = e.target;

                    var plusBtn = target.closest('.js-drawer-qty-plus');
                    if (plusBtn) {
                        var card = plusBtn.closest('.js-drawer-item-card');
                        var valEl = card.querySelector('.js-drawer-qty-val');
                        var minusBtn = card.querySelector('.js-drawer-qty-minus');
                        if (valEl) {
                            var qty = parseInt(valEl.textContent, 10) || 1;
                            if (qty < 99) {
                                qty++;
                                valEl.textContent = qty;
                                if (minusBtn) minusBtn.style.visibility = 'visible';

                                var unitPrice = parseFloat(card.getAttribute('data-unit-price')) || 0;
                                var totalEl = card.querySelector('.js-drawer-item-total');
                                if (totalEl) {
                                    totalEl.textContent = '₹' + Math.round(unitPrice * qty).toLocaleString('en-IN');
                                }
                                var compareEl = card.querySelector('.js-drawer-item-compare');
                                if (compareEl) {
                                    var compareUnit = parseFloat(compareEl.getAttribute('data-compare-unit')) || 0;
                                    compareEl.textContent = '₹' + Math.round(compareUnit * qty).toLocaleString('en-IN');
                                }

                                patchDrawerCart(card.getAttribute('data-item-id'), qty, card);
                            }
                        }
                        return;
                    }

                    var minusBtn = target.closest('.js-drawer-qty-minus');
                    if (minusBtn) {
                        var card = minusBtn.closest('.js-drawer-item-card');
                        var valEl = card.querySelector('.js-drawer-qty-val');
                        if (valEl) {
                            var qty = parseInt(valEl.textContent, 10) || 1;
                            if (qty > 1) {
                                qty--;
                                valEl.textContent = qty;
                                if (qty < 2 && minusBtn) minusBtn.style.visibility = 'hidden';

                                var unitPrice = parseFloat(card.getAttribute('data-unit-price')) || 0;
                                var totalEl = card.querySelector('.js-drawer-item-total');
                                if (totalEl) {
                                    totalEl.textContent = '₹' + Math.round(unitPrice * qty).toLocaleString('en-IN');
                                }
                                var compareEl = card.querySelector('.js-drawer-item-compare');
                                if (compareEl) {
                                    var compareUnit = parseFloat(compareEl.getAttribute('data-compare-unit')) || 0;
                                    compareEl.textContent = '₹' + Math.round(compareUnit * qty).toLocaleString('en-IN');
                                }

                                patchDrawerCart(card.getAttribute('data-item-id'), qty, card);
                            }
                        }
                        return;
                    }

                    var removeBtn = target.closest('.js-drawer-remove-btn');
                    if (removeBtn) {
                        var card = removeBtn.closest('.js-drawer-item-card');
                        deleteDrawerCart(card.getAttribute('data-item-id'), card);
                        return;
                    }
                });
            }

            window.syncCartCTAContainers(window.globalCartMap);
        })();
    </script>

    <!-- Floating WhatsApp chatbot FAB -->
    <a href="https://wa.me/919217732670?text=Hi" class="pro-whatsapp-fab" aria-label="Chat on WhatsApp" target="_blank"
        rel="noopener">
        <i class="bi bi-whatsapp" aria-hidden="true"></i>
    </a>
</body>

</html>
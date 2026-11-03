<!DOCTYPE html>
<html lang="{{ app()->getLocale() === 'en' ? 'en-IN' : str_replace('_', '-', app()->getLocale()) }}" class="mk-wait">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#2d5a3d">
    @include('partials.favicon')
    @include('partials.seo-head')
    <link rel="dns-prefetch" href="https://www.googletagmanager.com">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Assistant:wght@500;600;700&family=Great+Vibes&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet" media="print" onload="this.media='all'">
    <noscript><link href="https://fonts.googleapis.com/css2?family=Assistant:wght@500;600;700&family=Great+Vibes&family=Playfair+Display:ital,wght@0,600;0,700;1,600&display=swap" rel="stylesheet"></noscript>
    <link href="{{ asset('css/market-critical.css') }}?v=32" rel="stylesheet">
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
    <link href="{{ asset('css/market.css') }}?v=15" rel="stylesheet">
    <link href="{{ asset('css/market-pro.css') }}?v=280" rel="stylesheet">
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
    @php $faqSchema = request()->routeIs('market.home') ? $seoService->faqSchemaFromItems($seoService->homepageFaqItems()) : null; @endphp
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
    <script>
        (function () {
            if (!window.matchMedia('(max-width: 991.98px), (pointer: coarse)').matches) return;
            function stopZoom(e) { e.preventDefault(); }
            document.addEventListener('gesturestart', stopZoom, { passive: false });
            document.addEventListener('gesturechange', stopZoom, { passive: false });
            document.addEventListener('gestureend', stopZoom, { passive: false });
            var lastTouch = 0;
            document.addEventListener('touchend', function (e) {
                var now = Date.now();
                if (now - lastTouch <= 350) e.preventDefault();
                lastTouch = now;
            }, { passive: false });
        })();
    </script>

    {{-- Preloader --}}
    @include('market.partials.preloader')

    {{-- 1. Announcement bar --}}
    @include('market.partials.announcement-bar')

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

    {{-- Mobile Navigation Drawer --}}
    @include('market.partials.header-mobile-drawer')

    {{-- Cart Drawer --}}
    @include('market.partials.cart-drawer')

    {{-- Quick view modal --}}
    @include('market.partials.quick-view-modal')

    {{-- Holiday / Diwali Notice Popup Modal --}}
    @include('market.partials.holiday-modal')

    {{-- Newsletter popup --}}
    @include('market.partials.newsletter-modal')

    {{-- Main Content --}}
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

    {{-- Footer --}}
    @include('market.partials.footer-v2')
    <div class="dn-ft-mobile-spacer d-lg-none" aria-hidden="true"></div>

    {{-- Back to top button --}}
    <button type="button" class="pro-footer-mk__scroll-top" id="proScrollTop"
        aria-label="{{ __('Back to top') }}" title="{{ __('Back to top') }}">
        <i class="bi bi-chevron-up" aria-hidden="true"></i>
    </button>

    {{-- Cookie Consent Banner --}}
    @include('market.partials.cookie-banner')

    {{-- Mobile Bottom Navigation --}}
    @include('market.partials.mobile-nav')

    {{-- Floating WhatsApp chatbot FAB --}}
    @include('market.partials.whatsapp-fab')

    {{-- Vendor and Custom Scripts --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js" defer></script>
    @include('market.partials.scripts-global-ui')
    @include('market.partials.scripts-quick-view')
    @include('market.partials.scripts-cart-ajax')

    @include('partials.flash-toasts', ['includeValidationErrors' => true])
    @stack('body_end')
    @stack('scripts')
</body>

</html>

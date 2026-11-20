@extends('layouts.market')

@php
    $homeSeo = app(\App\Services\SeoService::class);
@endphp
@section('title', $homeSeo->global('default_title'))
@section('meta_description', $homeSeo->global('default_description'))
@section('meta_keywords', $homeSeo->global('default_keywords'))
@section('canonical', route('market.home'))

@if($banners->isNotEmpty())
@push('head')
@php
    $firstBanner = $banners->first();
    $heroMobile = \App\Support\OptimizedImage::url($firstBanner->resolvedMobileImageUrl(), 768);
    $heroDesktop = \App\Support\OptimizedImage::url($firstBanner->imageUrl(), 1400);
@endphp
<link rel="preload" as="image" href="{{ $heroMobile }}" media="(max-width: 767.98px)" fetchpriority="high">
<link rel="preload" as="image" href="{{ $heroDesktop }}" media="(min-width: 768px)" fetchpriority="high">
@endpush
@endif

@section('content')
    @php
        $newTab = ($newProducts ?? collect())->isNotEmpty() ? $newProducts : $trending->take(8);
        $featTab = $featured->filter(fn ($product) => (bool) $product->is_featured);
        $bestTab = $trending->take(8);
        $fallbackHeroImg = 'https://picsum.photos/seed/prohero/1920/700';
        // Homepage SEO H1 — one per page. Banner images are title-less, so this is the page heading.
        $homeH1 = __('Pure Organic Himalayan Products from Uttarakhand');
    @endphp
    <h1 class="visually-hidden">{{ $homeH1 }}</h1>

    {{-- Hero: full-bleed background image + overlay copy --}}
    @php $heroShopUrl = \App\Models\MenuItem::ourProductsUrl(); @endphp
    @if($banners->isNotEmpty())
        <section class="pro-hero p-0">
            <div id="proHeroSlider" class="carousel slide" data-bs-ride="false" data-bs-interval="8000" data-bs-touch="true">
                @if($banners->count() > 1)
                    <div class="carousel-indicators pro-hero__dots">
                        @foreach($banners as $i => $b)
                            <button type="button"
                                data-bs-target="#proHeroSlider"
                                data-bs-slide-to="{{ $i }}"
                                @class(['active' => $i === 0])
                                @if($i === 0) aria-current="true" @endif
                                aria-label="{{ __('Slide :n', ['n' => $i + 1]) }}"></button>
                        @endforeach
                    </div>
                @endif
                <div class="carousel-inner">
                    @foreach($banners as $i => $b)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            <div class="mk-hero-full mk-hero-full--photo">
                                <a href="{{ $b->link ? $b->resolvedLink() : $heroShopUrl }}" class="mk-hero-full__hit" aria-label="{{ $b->title ?: __('Our Products') }}"></a>
                                <picture>
                                    @php
                                        $slideMobile = \App\Support\OptimizedImage::url($b->resolvedMobileImageUrl(), 768);
                                        $slideDesktop = \App\Support\OptimizedImage::url($b->imageUrl(), 1400);
                                    @endphp
                                    @if($i === 0)
                                        <source media="(max-width: 767.98px)" srcset="{{ $slideMobile }}" width="768" height="576">
                                        <img
                                            src="{{ $slideDesktop }}"
                                            alt="{{ $b->title }}"
                                            class="mk-hero-full__img"
                                            width="1400"
                                            height="509"
                                            sizes="100vw"
                                            fetchpriority="high"
                                            decoding="async"
                                        >
                                    @else
                                        <source media="(max-width: 767.98px)" data-srcset="{{ $slideMobile }}" width="768" height="576">
                                        <img
                                            src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                            data-src="{{ $slideDesktop }}"
                                            alt="{{ $b->title }}"
                                            class="mk-hero-full__img"
                                            width="1400"
                                            height="509"
                                            sizes="100vw"
                                            loading="lazy"
                                            fetchpriority="low"
                                            decoding="async"
                                        >
                                    @endif
                                </picture>
                                @php
                                    $heroHasCopy = filled($b->eyebrow) || filled($b->title) || filled($b->subtitle) || filled($b->button_label) || filled($b->secondary_button_label);
                                @endphp
                                @if($heroHasCopy)
                                <div class="mk-hero-full__overlay">
                                    <div class="cb-container">
                                        <div class="mk-hero-copy">
                                            @if (filled($b->eyebrow))
                                                <span class="pro-hero__eyebrow">{{ $b->eyebrow }}</span>
                                            @endif
                                            @if (filled($b->title))
                                                <h2 class="pro-hero__title">{{ $b->title }}</h2>
                                            @endif
                                            @if (filled($b->subtitle))
                                                <p class="pro-hero__text">{{ $b->subtitle }}</p>
                                            @endif
                                            @if (filled($b->button_label) || filled($b->secondary_button_label))
                                            <div class="pro-hero__actions">
                                                @if (filled($b->button_label))
                                                    <a href="{{ $b->link ?: route('shop.search') }}" class="pro-btn-white">{{ $b->button_label }}</a>
                                                @endif
                                                @if (filled($b->secondary_button_label))
                                                    <a href="{{ $b->secondary_link ?: route('vendor.register') }}" class="pro-btn-outline-light">{{ $b->secondary_button_label }}</a>
                                                @endif
                                            </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                @if($banners->count() > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#proHeroSlider" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">{{ __('Previous') }}</span></button>
                    <button class="carousel-control-next" type="button" data-bs-target="#proHeroSlider" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">{{ __('Next') }}</span></button>
                @endif
            </div>
        </section>
    @else
        <section class="pro-hero p-0">
            <div class="mk-hero-full mk-hero-full--photo">
                <a href="{{ $heroShopUrl }}" class="mk-hero-full__hit" aria-label="{{ __('Our Products') }}"></a>
                <img src="{{ $fallbackHeroImg }}" alt="" class="mk-hero-full__img" width="1920" height="700" fetchpriority="high" decoding="async">
                <div class="mk-hero-full__overlay">
                    <div class="cb-container">
                        <div class="mk-hero-copy">
                            <span class="pro-hero__eyebrow">{{ __('Marketplace') }}</span>
                            <h2 class="pro-hero__title">{{ __('Everything you love, from stores you trust') }}</h2>
                            <p class="pro-hero__text">{{ __('Fashion, lifestyle & more — compare, save, and checkout in minutes.') }}</p>
                            <div class="pro-hero__actions">
                                <a href="{{ route('shop.search') }}" class="pro-btn-white">{{ __('Shop now') }}</a>
                                <a href="{{ route('shop.search', ['sort' => 'newest']) }}" class="pro-btn-outline-light">{{ __('New arrivals') }}</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif

    <div class="mk-home-bands">
        {{-- Trending tabs: New · Featured · Best selling --}}
        @include('market.partials.home.trending-now')

        {{-- Suggested for you --}}
        {{-- @include('market.partials.home.suggested-for-you') --}}

        {{-- Top 12 products (4 per row on desktop) --}}
        @include('market.partials.home.top-products')

        @include('market.partials.home-promo-tiles')
        @include('market.partials.home-why-us')

        @push('head')
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" media="print" onload="this.media='all'">
            <noscript><link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"></noscript>
        @endpush
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
            <script>
            (function () {
                var hero = document.getElementById('proHeroSlider');
                if (hero) {
                    function hydrateHeroSlide(item) {
                        if (!item) return;
                        item.querySelectorAll('source[data-srcset]').forEach(function (sourceEl) {
                            if (!sourceEl.getAttribute('srcset')) {
                                sourceEl.setAttribute('srcset', sourceEl.getAttribute('data-srcset'));
                            }
                        });
                        item.querySelectorAll('img[data-src]').forEach(function (img) {
                            var realSrc = img.getAttribute('data-src');
                            if (realSrc && img.getAttribute('src') !== realSrc) {
                                img.setAttribute('src', realSrc);
                            }
                        });
                    }
                    hero.addEventListener('slide.bs.carousel', function (e) {
                        hydrateHeroSlide(e.relatedTarget);
                    });
                    function heroCarousel() {
                        if (!window.bootstrap || !bootstrap.Carousel) return null;
                        return bootstrap.Carousel.getOrCreateInstance(hero, {
                            interval: 8000,
                            pause: false,
                            wrap: true,
                            touch: true
                        });
                    }
                    hero.querySelectorAll('.pro-hero__dots [data-bs-slide-to]').forEach(function (dot) {
                        dot.addEventListener('click', function () {
                            var carousel = heroCarousel();
                            if (carousel) carousel.cycle();
                        });
                    });
                    window.setTimeout(function () {
                        var next = hero.querySelector('.carousel-item.active')?.nextElementSibling
                            || hero.querySelector('.carousel-item:not(.active)');
                        hydrateHeroSlide(next);
                        var carousel = heroCarousel();
                        if (carousel) carousel.cycle();
                    }, 8000);
                }

                if (typeof Swiper === 'undefined') return;

                function swiperOptions(el) {
                    return {
                        slidesPerView: 'auto',
                        spaceBetween: 12,
                        watchOverflow: true,
                        observer: true,
                        observeParents: true,
                        observeSlideChildren: true,
                        watchSlidesProgress: true,
                        resizeObserver: true,
                        centeredSlides: false,
                        roundLengths: false,
                        navigation: {
                            nextEl: el.querySelector('.swiper-button-next'),
                            prevEl: el.querySelector('.swiper-button-prev'),
                        },
                        /* Mobile: 1 card + peek of next (CSS width). 620+: 2/3/4 columns. */
                        breakpoints: {
                            620: { slidesPerView: 'auto', spaceBetween: 16 },
                            768: { slidesPerView: 'auto', spaceBetween: 16 },
                            992: { slidesPerView: 'auto', spaceBetween: 16 },
                        },
                    };
                }

                function mountProProductSwiper(el) {
                    if (!el || el.querySelectorAll('.swiper-slide').length === 0) return;
                    if (el.dataset.swiperReady === '1') {
                        if (el.swiper) {
                            requestAnimationFrame(function () {
                                el.swiper.update();
                                if (el.swiper.navigation && el.swiper.navigation.update) el.swiper.navigation.update();
                                el.swiper.slideTo(0, 0);
                            });
                        }
                        return;
                    }
                    el.dataset.swiperReady = '1';
                    new Swiper(el, swiperOptions(el));
                }

                function mountTrendSwiper(pane) {
                    if (!pane) return;
                    mountProProductSwiper(pane.querySelector('.pro-product-swiper'));
                }
                window.mountTrendSwiper = mountTrendSwiper;

                function mountTestimonialSwiper() {
                    var el = document.getElementById('proTestimonialSwiper');
                    if (!el || el.querySelectorAll('.swiper-slide').length === 0) return;
                    if (el.dataset.swiperReady === '1') return;
                    el.dataset.swiperReady = '1';
                    var wrap = el.closest('.pro-testi-swiper-wrap') || el;
                    var reduceMotion = window.matchMedia('(prefers-reduced-motion: reduce)').matches;
                    new Swiper(el, {
                        slidesPerView: 1,
                        spaceBetween: 16,
                        loop: true,
                        loopAdditionalSlides: 2,
                        speed: 650,
                        watchOverflow: true,
                        autoplay: reduceMotion ? false : {
                            delay: 4200,
                            disableOnInteraction: false,
                            pauseOnMouseEnter: true,
                        },
                        pagination: {
                            el: el.querySelector('.swiper-pagination'),
                            clickable: true,
                        },
                        navigation: {
                            nextEl: wrap.querySelector('.swiper-button-next'),
                            prevEl: wrap.querySelector('.swiper-button-prev'),
                        },
                        breakpoints: {
                            768: { slidesPerView: 2, spaceBetween: 20 },
                            992: { slidesPerView: 3, spaceBetween: 24 },
                        },
                    });
                }

                function onReady() {
                    mountTrendSwiper(document.getElementById('pane-new'));
                    mountTestimonialSwiper();
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', onReady);
                } else {
                    onReady();
                }
            })();
            </script>
            <script>
            (function () {
                var MIN_MS = 550;
                var hideTimer = 0;
                var shownAt = 0;
                var pendingPaneEl = null;

                function paneFromBtn(btn) {
                    var sel = btn && btn.getAttribute('data-bs-target');
                    return sel ? document.querySelector(sel) : null;
                }

                function setPending(pane) {
                    if (!pane || pane.classList.contains('is-pending')) {
                        if (pane) {
                            shownAt = Date.now();
                            pendingPaneEl = pane;
                        }
                        return;
                    }
                    document.querySelectorAll('.pro-trend-tab-content .tab-pane.is-pending').forEach(function (el) {
                        if (el !== pane) el.classList.remove('is-pending');
                    });
                    pane.classList.add('is-pending');
                    shownAt = Date.now();
                    pendingPaneEl = pane;
                    if (hideTimer) {
                        window.clearTimeout(hideTimer);
                        hideTimer = 0;
                    }
                }

                function finishPane(pane) {
                    if (!pane) return;
                    var wait = Math.max(0, MIN_MS - (Date.now() - shownAt));
                    if (hideTimer) window.clearTimeout(hideTimer);
                    hideTimer = window.setTimeout(function () {
                        pane.classList.remove('is-pending');
                        if (pendingPaneEl === pane) pendingPaneEl = null;
                        requestAnimationFrame(function () {
                            if (typeof window.mountTrendSwiper === 'function') {
                                window.mountTrendSwiper(pane);
                            }
                            if (typeof window.bindProductCardSkeletons === 'function') {
                                window.bindProductCardSkeletons(pane);
                            }
                        });
                    }, wait);
                }

                function revealWhenReady(pane) {
                    if (!pane) return;
                    if (typeof window.bindProductCardSkeletons === 'function') {
                        window.bindProductCardSkeletons(pane);
                    }
                    var cards = pane.querySelectorAll('.swiper-slide .zm-pro-card.is-loading');
                    var waitCards = Array.prototype.slice.call(cards, 0, 4);
                    if (!pane.querySelector('.pro-product-swiper') || waitCards.length === 0) {
                        finishPane(pane);
                        return;
                    }
                    var left = waitCards.length;
                    var done = false;
                    function oneDone() {
                        if (done) return;
                        left -= 1;
                        if (left <= 0) {
                            done = true;
                            finishPane(pane);
                        }
                    }
                    waitCards.forEach(function (card) {
                        var img = card.querySelector('.zm-pro-card__img--primary');
                        if (!img || (img.complete && img.naturalWidth > 0)) {
                            oneDone();
                            return;
                        }
                        img.addEventListener('load', oneDone, { once: true });
                        img.addEventListener('error', oneDone, { once: true });
                    });
                    window.setTimeout(function () {
                        if (done) return;
                        done = true;
                        finishPane(pane);
                    }, 4000);
                }

                function bindTrendTabs() {
                    document.querySelectorAll('#proTrendTabs [data-bs-toggle="pill"]').forEach(function (btn) {
                        btn.addEventListener('click', function () {
                            if (btn.classList.contains('active')) return;
                            setPending(paneFromBtn(btn));
                        });
                        btn.addEventListener('show.bs.tab', function () {
                            setPending(paneFromBtn(btn));
                        });
                        btn.addEventListener('shown.bs.tab', function () {
                            revealWhenReady(paneFromBtn(btn));
                        });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', bindTrendTabs);
                } else {
                    bindTrendTabs();
                }
            })();
            </script>
        @endpush

        {{-- Testimonials (autoplay slider) --}}
        @include('market.partials.home.testimonials')

        {{-- Blog posts --}}
        @include('market.partials.home.blog-posts')

        @include('market.partials.home-faq')
    </div>
@endsection

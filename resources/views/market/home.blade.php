@extends('layouts.market')

@section('title', 'Devbhoomi Naturals | Pure Organic Himalayan Products')
@section('meta_description', 'Shop pure organic Himalayan products — millets, pahadi pulses, spices & grains direct from Uttarakhand farmers. Free delivery above ₹499.')
@section('meta_keywords', 'organic food, Himalayan products, Uttarakhand, millets, pahadi pulses, natural spices')
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
    @endphp

    {{-- Hero: full-bleed background image + overlay copy --}}
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
                                <picture>
                                    @php
                                        $slideMobile = \App\Support\OptimizedImage::url($b->resolvedMobileImageUrl(), 768);
                                        $slideDesktop = \App\Support\OptimizedImage::url($b->imageUrl(), 1400);
                                    @endphp
                                    @if($i === 0)
                                        <source media="(max-width: 767.98px)" srcset="{{ $slideMobile }}">
                                        <img
                                            src="{{ $slideDesktop }}"
                                            alt="{{ $b->title }}"
                                            class="mk-hero-full__img"
                                            width="1400"
                                            height="510"
                                            sizes="100vw"
                                            fetchpriority="high"
                                        >
                                    @else
                                        <source media="(max-width: 767.98px)" data-srcset="{{ $slideMobile }}">
                                        <img
                                            src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7"
                                            data-src="{{ $slideDesktop }}"
                                            alt="{{ $b->title }}"
                                            class="mk-hero-full__img"
                                            width="1400"
                                            height="510"
                                            sizes="100vw"
                                            loading="lazy"
                                            fetchpriority="low"
                                        >
                                    @endif
                                </picture>
                                <div class="mk-hero-full__overlay">
                                    <div class="cb-container">
                                        <div class="mk-hero-copy">
                                            <span class="pro-hero__eyebrow">{{ $b->eyebrow ?: __('New season') }}</span>
                                            @if($i === 0)
                                            <h1 class="pro-hero__title">{{ $b->title }}</h1>
                                            @else
                                            <h2 class="pro-hero__title">{{ $b->title }}</h2>
                                            @endif
                                            <p class="pro-hero__text">{{ $b->subtitle ?: __('Curated picks from verified sellers — easy returns & secure checkout.') }}</p>
                                            <div class="pro-hero__actions">
                                                <a href="{{ $b->link ?: route('shop.search') }}" class="pro-btn-white">{{ $b->button_label ?: __('Shop now') }}</a>
                                                <a href="{{ $b->secondary_link ?: route('vendor.register') }}" class="pro-btn-outline-light">{{ $b->secondary_button_label ?: __('Sell with us') }}</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <button class="carousel-control-prev" type="button" data-bs-target="#proHeroSlider" data-bs-slide="prev"><span class="carousel-control-prev-icon" aria-hidden="true"></span><span class="visually-hidden">{{ __('Previous') }}</span></button>
                <button class="carousel-control-next" type="button" data-bs-target="#proHeroSlider" data-bs-slide="next"><span class="carousel-control-next-icon" aria-hidden="true"></span><span class="visually-hidden">{{ __('Next') }}</span></button>
            </div>
        </section>
    @else
        <section class="pro-hero p-0">
            <div class="mk-hero-full mk-hero-full--photo">
                <img src="{{ $fallbackHeroImg }}" alt="" class="mk-hero-full__img" width="1920" height="700" fetchpriority="high" decoding="async">
                <div class="mk-hero-full__overlay">
                    <div class="cb-container">
                        <div class="mk-hero-copy">
                            <span class="pro-hero__eyebrow">{{ __('Marketplace') }}</span>
                            <h1 class="pro-hero__title">{{ __('Everything you love, from stores you trust') }}</h1>
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
        {{-- Trending tabs: New · Featured · Best selling (Swiper slider) --}}
        <section class="mk-home-band mk-section" aria-labelledby="trend-heading">
            <div class="cb-container">
            <div class="pro-section-head">
                <p class="pro-section-head__eyebrow">{{ __('Trending now') }}</p>
                <h2 class="pro-section-head__title" id="trend-heading">{{ __("Products you'll love") }}</h2>
            </div>
            <div class="pro-tabs-trend-wrap">
            <ul class="nav nav-pills pro-tabs pro-tabs--trend" id="proTrendTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-new" data-bs-toggle="pill" data-bs-target="#pane-new" type="button" role="tab">
                        <i class="bi bi-stars" aria-hidden="true"></i>{{ __('New') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-feat" data-bs-toggle="pill" data-bs-target="#pane-feat" type="button" role="tab">
                        <i class="bi bi-award" aria-hidden="true"></i>{{ __('Featured') }}
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-best" data-bs-toggle="pill" data-bs-target="#pane-best" type="button" role="tab">
                        <i class="bi bi-graph-up-arrow" aria-hidden="true"></i>{{ __('Best selling') }}
                    </button>
                </li>
            </ul>
            </div>
            <div class="tab-content pro-trend-tab-content">
                <div class="tab-pane fade show active" id="pane-new" role="tabpanel" aria-labelledby="tab-new">
                    @if($newTab->isNotEmpty())
                        <div class="swiper pro-product-swiper">
                            <div class="swiper-wrapper">
                                @foreach($newTab as $product)
                                    <div class="swiper-slide">
                                        <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $product])</div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                            <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
                        </div>
                    @else
                        <p class="text-center text-muted py-4 mb-0">{{ __('No products in this tab yet.') }}</p>
                    @endif
                </div>
                <div class="tab-pane fade" id="pane-feat" role="tabpanel" aria-labelledby="tab-feat">
                    @if($featTab->isNotEmpty())
                        <div class="swiper pro-product-swiper">
                            <div class="swiper-wrapper">
                                @foreach($featTab as $product)
                                    <div class="swiper-slide">
                                        <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $product])</div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                            <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
                        </div>
                    @else
                        <p class="text-center text-muted py-4 mb-0">{{ __('No products in this tab yet.') }}</p>
                    @endif
                </div>
                <div class="tab-pane fade" id="pane-best" role="tabpanel" aria-labelledby="tab-best">
                    @if($bestTab->isNotEmpty())
                        <div class="swiper pro-product-swiper">
                            <div class="swiper-wrapper">
                                @foreach($bestTab as $product)
                                    <div class="swiper-slide">
                                        <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $product])</div>
                                    </div>
                                @endforeach
                            </div>
                            <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                            <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
                        </div>
                    @else
                        <p class="text-center text-muted py-4 mb-0">{{ __('No products in this tab yet.') }}</p>
                    @endif
                </div>
            </div>
        </div>
        </section>
        
        @if(isset($suggestedForYou) && $suggestedForYou->isNotEmpty())
        <section class="mk-home-band mk-section cb-reveal" aria-labelledby="suggested-heading">
            <div class="cb-container">
            <div class="pro-section-head">
                <p class="pro-section-head__eyebrow">{{ __('Based on your browsing') }}</p>
                <h2 class="pro-section-head__title" id="suggested-heading">{{ __('Suggested for you') }}</h2>
                <p class="pro-section-head__sub">{{ __('Products you recently viewed') }}</p>
            </div>
            <div class="swiper pro-product-swiper" id="proSuggestedForYouSwiper">
                <div class="swiper-wrapper">
                    @foreach($suggestedForYou as $product)
                        <div class="swiper-slide">
                            <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $product])</div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
            </div>
            </div>
        </section>
        @endif

        {{-- Featured products --}}
        <section class="mk-home-band mk-section cb-reveal" aria-labelledby="feat-heading">
            <div class="cb-container">
            <div class="pro-section-head">
                <p class="pro-section-head__eyebrow">{{ __('Handpicked') }}</p>
                <h2 class="pro-section-head__title" id="feat-heading">{{ __('Featured products') }}</h2>
                <p class="pro-section-head__sub">{{ __('Hover for quick view, wishlist & cart — second image on hover.') }}</p>
            </div>
            @php $feat = $featTab; @endphp
            @if($feat->isNotEmpty())
                <div class="swiper pro-product-swiper" id="proFeaturedProductSwiper">
                    <div class="swiper-wrapper">
                        @foreach($feat as $product)
                            <div class="swiper-slide">
                                <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $product])</div>
                            </div>
                        @endforeach
                    </div>
                    <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                    <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
                </div>
            @else
                <p class="text-center text-muted">{{ __('Products will show here once listed.') }}</p>
            @endif
            </div>
        </section>

        @include('market.partials.home-promo-tiles')
        
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
                        slidesPerView: 1.5,
                        spaceBetween: 10,
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
                        /* Below 620px: 1 full card + half of the next. 620+: CSS 2/3/4 columns. */
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
                    mountProProductSwiper(document.getElementById('proSuggestedForYouSwiper'));
                    mountProProductSwiper(document.getElementById('proFeaturedProductSwiper'));
                    mountTrendSwiper(document.getElementById('pane-new'));
                    mountTestimonialSwiper();
                    document.querySelectorAll('#proTrendTabs [data-bs-toggle="pill"]').forEach(function (btn) {
                        btn.addEventListener('shown.bs.tab', function () {
                            var sel = btn.getAttribute('data-bs-target');
                            if (!sel) return;
                            requestAnimationFrame(function () {
                                mountTrendSwiper(document.querySelector(sel));
                            });
                        });
                    });
                }

                if (document.readyState === 'loading') {
                    document.addEventListener('DOMContentLoaded', onReady);
                } else {
                    onReady();
                }
            })();
            </script>
        @endpush

    {{-- Testimonials (autoplay slider) --}}
    @php
        $homeTestimonials = [
            [__('The mandua flour tastes like home in the hills — fresh, earthy, and packed with care.'), 'Priya S.', 'Mumbai', 5],
            [__('Love comparing sellers in one place. The pahadi pulses arrived sealed and genuinely organic.'), 'Arjun K.', 'Bengaluru', 5],
            [__('Support helped with a return quickly. I trust this store for weekly Himalayan staples.'), 'Neha R.', 'Delhi', 4],
            [__('Spices are fragrant, not dusty. You can tell they came straight from Uttarakhand farms.'), 'Kavita M.', 'Dehradun', 5],
            [__('Fast delivery and genuine products. The millets have become a regular in our kitchen.'), 'Rohan P.', 'Pune', 5],
            [__('Finally organic grains I can trust for my family — clean packing and honest flavour.'), 'Meera T.', 'Jaipur', 5],
            [__('Ordered red rice and gahat dal together — both arrived fresh and well packed.'), 'Sanjay D.', 'Chandigarh', 5],
            [__('A clean, honest store for pahadi groceries. I keep coming back for the millets.'), 'Ananya L.', 'Noida', 5],
        ];
    @endphp
    <section class="pro-testi-section mk-home-band mk-home-band--ownbg mk-section cb-reveal" aria-labelledby="testi-heading">
        <div class="pro-testi-section__deco" aria-hidden="true">
            <span class="pro-testi-section__blob pro-testi-section__blob--a"></span>
            <span class="pro-testi-section__blob pro-testi-section__blob--b"></span>
            <span class="pro-testi-section__blob pro-testi-section__blob--c"></span>
            <span class="pro-testi-section__ring"></span>
        </div>
        <div class="cb-container position-relative">
            <div class="pro-section-head">
                <p class="pro-section-head__eyebrow">{{ __('Reviews') }}</p>
                <h2 class="pro-section-head__title" id="testi-heading">{{ __('What buyers say') }}</h2>
                <p class="pro-section-head__sub">{{ __('Real notes from customers who shop Himalayan staples with us.') }}</p>
            </div>
            <div class="pro-testi-swiper-wrap">
                <div class="swiper pro-testi-swiper" id="proTestimonialSwiper">
                    <div class="swiper-wrapper">
                        @foreach($homeTestimonials as $t)
                            @php $rating = min(5, max(0, (int) $t[3])); @endphp
                            <div class="swiper-slide">
                                <article class="pro-testi-card">
                                    <i class="bi bi-quote pro-testi-card__mark" aria-hidden="true"></i>
                                    <div class="pro-testi-card__stars" aria-label="{{ $rating }} {{ __('out of 5 stars') }}">
                                        @for($i = 1; $i <= 5; $i++)
                                            <i class="bi {{ $i <= $rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                        @endfor
                                    </div>
                                    <p class="pro-testi-card__quote">&ldquo;{{ $t[0] }}&rdquo;</p>
                                    <div class="pro-testi-card__author">{{ $t[1] }}</div>
                                    <div class="small text-muted">{{ $t[2] }}</div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                    <div class="swiper-pagination pro-testi-swiper__dots"></div>
                </div>
                <button type="button" class="swiper-button-prev pro-testi-swiper__nav" aria-label="{{ __('Previous testimonials') }}"></button>
                <button type="button" class="swiper-button-next pro-testi-swiper__nav" aria-label="{{ __('Next testimonials') }}"></button>
            </div>
        </div>
    </section>

    @if(isset($blogPosts) && $blogPosts->isNotEmpty())
    <section class="mk-home-band mk-section cb-reveal" aria-labelledby="blog-heading">
        <div class="cb-container">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                <div>
                    <p class="pro-section-head__eyebrow mb-1">{{ __('Editorial') }}</p>
                    <h2 class="h4 mb-0" id="blog-heading">{{ __('From the blog') }}</h2>
                </div>
                <a href="{{ route('blog.index') }}" class="pro-blog-viewall">{{ __('View all') }}<i class="bi bi-arrow-right" aria-hidden="true"></i></a>
            </div>
            <div class="row g-3 pro-blog-list">
                @foreach($blogPosts as $post)
                    <div class="col-6 col-md-3">
                        <article class="pro-blog-card">
                            <a href="{{ route('blog.show', $post) }}" class="pro-blog-card__media">
                                <img src="{{ \App\Support\OptimizedImage::url($post->imageUrl(), 640) }}" class="pro-blog-card__img" alt="{{ $post->title }}" title="{{ $post->title }}" loading="lazy" width="640" height="400" decoding="async">
                            </a>
                            <div class="pro-blog-card__body">
                                <div class="pro-blog-card__date">{{ strtoupper(($post->published_at ?? $post->created_at)->format('M Y')) }}</div>
                                <a href="{{ route('blog.show', $post) }}" class="pro-blog-card__title d-inline-block">{{ $post->title }}</a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @include('market.partials.home-faq')
    </div>
@endsection

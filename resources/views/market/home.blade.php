@extends('layouts.market')

@section('title', 'Devbhoomi Naturals | Pure Organic Himalayan Products')
@section('meta_description', 'Shop pure organic Himalayan products — millets, pahadi pulses, spices & grains direct from Uttarakhand farmers. Free delivery above ₹499.')
@section('meta_keywords', 'organic food, Himalayan products, Uttarakhand, millets, pahadi pulses, natural spices')

@if($banners->isNotEmpty())
@push('head')
<link rel="preload" as="image" href="{{ $banners->first()->imageUrl() }}" fetchpriority="high">
@endpush
@endif

@section('content')
    @php
        $newTab = ($newProducts ?? collect())->isNotEmpty() ? $newProducts : $trending->take(8);
        $featTab = $featured->isNotEmpty() ? $featured->take(8) : $trending->take(8);
        $bestTab = $trending->take(8);
        $fallbackHeroImg = 'https://picsum.photos/seed/prohero/1920/700';
    @endphp

    {{-- Hero: full-bleed background image + overlay copy --}}
    @if($banners->isNotEmpty())
        <section class="pro-hero p-0 cb-reveal">
            <div id="proHeroSlider" class="carousel slide" data-bs-ride="carousel" data-bs-interval="5500">
                <div class="carousel-inner">
                    @foreach($banners as $i => $b)
                        <div class="carousel-item {{ $i === 0 ? 'active' : '' }}">
                            <div class="mk-hero-full" style="background-image: url('{{ e($b->imageUrl()) }}');">
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
        <section class="pro-hero p-0 cb-reveal">
            <div class="mk-hero-full" style="background-image: url('{{ $fallbackHeroImg }}');">
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

    @include('market.partials.home-promo-tiles')

    <div class="cb-container">
        @if(isset($suggestedForYou) && $suggestedForYou->isNotEmpty())
        <section class="mk-section cb-reveal" aria-labelledby="suggested-heading">
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
        </section>
        @endif

        {{-- Featured products --}}
        <section class="mk-section cb-reveal" aria-labelledby="feat-heading">
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
        </section>


        {{-- Trending tabs: New Â· Featured Â· Best selling (Swiper slider) --}}
        <section class="mk-section cb-reveal" aria-labelledby="trend-heading">
            <div class="pro-section-head">
                <p class="pro-section-head__eyebrow">{{ __('Trending now') }}</p>
                <h2 class="pro-section-head__title" id="trend-heading">{{ __("Products you'll love") }}</h2>
            </div>
            <ul class="nav nav-pills pro-tabs pro-tabs--rounded justify-content-center gap-2 mb-4 flex-wrap" id="proTrendTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-new" data-bs-toggle="pill" data-bs-target="#pane-new" type="button" role="tab">{{ __('New') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-feat" data-bs-toggle="pill" data-bs-target="#pane-feat" type="button" role="tab">{{ __('Featured') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-best" data-bs-toggle="pill" data-bs-target="#pane-best" type="button" role="tab">{{ __('Best selling') }}</button>
                </li>
            </ul>
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
        </section>

        @push('head')
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
        @endpush
        @push('scripts')
            <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
            <script>
            (function () {
                if (typeof Swiper === 'undefined') return;

                function swiperOptions(el) {
                    return {
                        slidesPerView: 1,
                        spaceBetween: 12,
                        watchOverflow: true,
                        observer: true,
                        observeParents: true,
                        observeSlideChildren: true,
                        watchSlidesProgress: true,
                        resizeObserver: true,
                        centeredSlides: false,
                        roundLengths: true,
                        navigation: {
                            nextEl: el.querySelector('.swiper-button-next'),
                            prevEl: el.querySelector('.swiper-button-prev'),
                        },
                        /* slidesPerView 'auto' + CSS (100cqi) = exact 2/3/4 columns, no cut-off 5th peek */
                        breakpoints: {
                            576: { slidesPerView: 'auto', spaceBetween: 16 },
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

                function onReady() {
                    mountProProductSwiper(document.getElementById('proSuggestedForYouSwiper'));
                    mountProProductSwiper(document.getElementById('proFeaturedProductSwiper'));
                    mountTrendSwiper(document.getElementById('pane-new'));
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

    </div>

        {{-- Testimonials (full-bleed creative background) --}}
        <section class="pro-testi-section mk-section cb-reveal" aria-labelledby="testi-heading">
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
                </div>
                <div class="row g-3 g-lg-4">
                    @foreach([
                        [__('Fast delivery and genuine products.'), 'Priya S.', 'Mumbai', 5],
                        [__('Love comparing sellers in one place.'), 'Arjun K.', 'Bengaluru', 5],
                        [__('Support helped with a return quickly.'), 'Neha R.', 'Delhi', 4],
                    ] as $t)
                        @php $rating = min(5, max(0, (int) $t[3])); @endphp
                        <div class="col-md-4">
                            <div class="pro-testi-card">
                                <div class="pro-testi-card__stars" aria-hidden="true">
                                    @for($i = 1; $i <= 5; $i++)
                                        <i class="bi {{ $i <= $rating ? 'bi-star-fill' : 'bi-star' }}"></i>
                                    @endfor
                                </div>
                                <p class="pro-testi-card__quote">"{{ $t[0] }}"</p>
                                <div class="pro-testi-card__author">{{ $t[1] }}</div>
                                <div class="small text-muted">{{ $t[2] }}</div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        @include('market.partials.home-faq')

    <div class="cb-container">

        {{-- Blog preview (dynamic) --}}
        @if(isset($blogPosts) && $blogPosts->isNotEmpty())
        <section class="mk-section cb-reveal" aria-labelledby="blog-heading">
            <div class="d-flex flex-wrap justify-content-between align-items-end gap-2 mb-3">
                <div>
                    <p class="pro-section-head__eyebrow mb-1">{{ __('Editorial') }}</p>
                    <h2 class="h4 mb-0" id="blog-heading">{{ __('From the blog') }}</h2>
                </div>
                <a href="{{ route('blog.index') }}" class="btn btn-outline-primary btn-sm" style="border-radius:8px;">{{ __('View all') }}</a>
            </div>
            <div class="row g-3">
                @foreach($blogPosts as $post)
                    <div class="col-md-4">
                        <article class="pro-blog-card">
                            <a href="{{ route('blog.show', $post) }}" class="d-block">
                                <img src="{{ $post->imageUrl() }}" class="pro-blog-card__img" alt="{{ $post->title }}" title="{{ $post->title }}" loading="lazy" width="640" height="400" decoding="async">
                            </a>
                            <div class="pro-blog-card__body">
                                <div class="pro-blog-card__date">{{ strtoupper(($post->published_at ?? $post->created_at)->format('M Y')) }}</div>
                                <a href="{{ route('blog.show', $post) }}" class="pro-blog-card__title d-inline-block">{{ $post->title }}</a>
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>
        </section>
        @endif
    </div>
@endsection

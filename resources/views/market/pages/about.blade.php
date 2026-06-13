@extends('layouts.market')

@section('title', ($page->meta_title ?: $page->hero_title).' | Devbhoomi Naturals')
@section('meta_description', $page->meta_description ?: Str::limit(strip_tags($page->hero_subtitle), 155))
@if(filled($page->meta_keywords))
@section('meta_keywords', $page->meta_keywords)
@endif
@section('canonical', $page->canonical_url ?: route('pages.about'))
@if(filled($page->og_image))
@section('og_image', $page->og_image)
@endif

@push('head')
    <link href="{{ asset('css/pages-static.css') }}?v=1" rel="stylesheet">
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => '',
        'items' => [['label' => $page->hero_title ?: __('About us')]],
    ])
@endpush

@section('content')
    <div class="pro-static-page pro-about-page">
        <section class="pro-about-hero">
            <div class="pro-about-hero__glow" aria-hidden="true"></div>
            <div class="cb-container">
                <div class="row align-items-center g-4 g-lg-5">
                    <div class="col-lg-6 order-lg-2">
                        <div class="pro-about-hero__media ratio ratio-4x3">
                            <img src="{{ $page->heroImageUrl() }}" alt="{{ $page->hero_title }}" class="object-fit-cover w-100 h-100" loading="eager" width="800" height="600">
                        </div>
                    </div>
                    <div class="col-lg-6 order-lg-1">
                        @if($page->hero_eyebrow)
                            <p class="pro-about-hero__eyebrow">{{ $page->hero_eyebrow }}</p>
                        @endif
                        <h1 class="pro-about-hero__title">{{ $page->hero_title }}</h1>
                        @if($page->hero_subtitle)
                            <p class="pro-about-hero__lead">{{ $page->hero_subtitle }}</p>
                        @endif
                        <a href="{{ route('shop.search') }}" class="btn pro-about-hero__cta">{{ __('Shop our collection') }} <i class="bi bi-arrow-right" aria-hidden="true"></i></a>
                    </div>
                </div>
            </div>
        </section>

        @if($page->highlights->isNotEmpty())
            <section class="pro-about-stats">
                <div class="cb-container">
                    <div class="row g-3 g-md-4">
                        @foreach($page->highlights as $stat)
                            <div class="col-6 col-lg-3">
                                <article class="pro-about-stat">
                                    <span class="pro-about-stat__icon"><i class="bi {{ $stat->icon }}" aria-hidden="true"></i></span>
                                    <p class="pro-about-stat__value">{{ $stat->value }}</p>
                                    <p class="pro-about-stat__label">{{ $stat->label }}</p>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        @if($page->story_heading || $page->story_body)
            <section class="pro-about-story">
                <div class="cb-container">
                    <div class="row align-items-center g-4 g-lg-5">
                        <div class="col-lg-5">
                            <div class="pro-about-story__media ratio ratio-1x1">
                                <img src="{{ $page->storyImageUrl() }}" alt="{{ $page->story_heading }}" class="object-fit-cover w-100 h-100" loading="lazy" width="600" height="600">
                            </div>
                        </div>
                        <div class="col-lg-7">
                            @if($page->story_heading)
                                <h2 class="pro-about-story__title">{{ $page->story_heading }}</h2>
                            @endif
                            @if($page->story_body)
                                <div class="pro-about-story__body">{!! nl2br(e($page->story_body)) !!}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @if($page->mission_title || $page->vision_title)
            <section class="pro-about-mv">
                <div class="cb-container">
                    <div class="row g-4">
                        @if($page->mission_title)
                            <div class="col-md-6">
                                <article class="pro-about-mv__card">
                                    <span class="pro-about-mv__badge"><i class="bi bi-compass" aria-hidden="true"></i></span>
                                    <h3 class="pro-about-mv__title">{{ $page->mission_title }}</h3>
                                    @if($page->mission_body)
                                        <p class="pro-about-mv__text">{{ $page->mission_body }}</p>
                                    @endif
                                </article>
                            </div>
                        @endif
                        @if($page->vision_title)
                            <div class="col-md-6">
                                <article class="pro-about-mv__card pro-about-mv__card--alt">
                                    <span class="pro-about-mv__badge"><i class="bi bi-binoculars" aria-hidden="true"></i></span>
                                    <h3 class="pro-about-mv__title">{{ $page->vision_title }}</h3>
                                    @if($page->vision_body)
                                        <p class="pro-about-mv__text">{{ $page->vision_body }}</p>
                                    @endif
                                </article>
                            </div>
                        @endif
                    </div>
                </div>
            </section>
        @endif

        @if($page->galleryItems->isNotEmpty())
            <section class="pro-about-gallery">
                <div class="cb-container">
                    @if($page->gallery_heading)
                        <h2 class="pro-about-gallery__title text-center">{{ $page->gallery_heading }}</h2>
                    @endif
                    <div class="row g-3 pro-about-gallery__grid">
                        @foreach($page->galleryItems as $item)
                            <div class="col-6 col-md-4 col-lg-3">
                                <figure class="pro-about-gallery__item">
                                    <img src="{{ $item->imageUrl() }}" alt="{{ $item->caption ?: config('app.name') }}" class="w-100" loading="lazy">
                                    @if($item->caption)
                                        <figcaption>{{ $item->caption }}</figcaption>
                                    @endif
                                </figure>
                            </div>
                        @endforeach
                    </div>
                </div>
            </section>
        @endif

        <section class="pro-about-cta-band">
            <div class="cb-container text-center">
                <h2 class="pro-about-cta-band__title">{{ __('Ready to taste the Himalayas?') }}</h2>
                <p class="pro-about-cta-band__text">{{ __('Explore farm-fresh products sourced with care from Uttarakhand.') }}</p>
                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('shop.search') }}" class="btn pro-about-hero__cta">{{ __('Browse products') }}</a>
                    <a href="{{ route('pages.contact') }}" class="btn btn-outline-light">{{ __('Contact us') }}</a>
                </div>
            </div>
        </section>
    </div>
@endsection

@extends('layouts.market')

@php
    $showcaseImages = collect($showcase['images'])->map(
        fn ($image) => \Illuminate\Support\Str::startsWith($image, ['http://', 'https://'])
            ? $image
            : asset(ltrim($image, '/'))
    )->values()->all();
@endphp

@section('title', $showcase['meta_title'])
@section('meta_description', $showcase['meta_description'])
@section('meta_keywords', $showcase['keywords'])
@section('canonical', url()->current())
@section('og_image', $showcaseImages[0])
@section('og_type', 'article')

@push('head')
<style>
    .showcase-article .pro-blog-detail__content h2 { color: #1a2e22; margin-top: 2.5rem; }
    .showcase-article .pro-blog-detail__content h2:first-child { margin-top: 0; }
    .showcase-article .pro-blog-detail__content h3 { color: #2d5a3d; margin-top: 1.75rem; }
    .showcase-article .pro-blog-detail__hero-media,
    .showcase-article__image,
    .showcase-article__gallery img { background: linear-gradient(135deg, #f8f6ef, #eef3ed); }
    .showcase-article .pro-blog-detail__hero-media { line-height: 0; }
    .showcase-article .pro-blog-detail__hero-img { display: block; width: 100%; height: auto; object-fit: contain; object-position: center; vertical-align: bottom; }
    .showcase-article__image { margin: 2rem 0; overflow: hidden; border-radius: 1rem; line-height: 0; }
    .showcase-article__image img { display: block; width: 100%; height: auto; object-fit: contain; object-position: center; vertical-align: bottom; }
    .showcase-article__content blockquote { border-left-color: #2d5a3d; }
    .showcase-article__facts { display: grid; grid-template-columns: repeat(auto-fit, minmax(150px, 1fr)); gap: .75rem; margin-top: 2rem; }
    .showcase-article__facts div { padding: 1rem; border-radius: .75rem; background: #f5f7f3; }
    .showcase-article__facts span { display: block; color: #5f6d62; font-size: .82rem; }
    .showcase-article__facts strong { display: block; margin-top: .25rem; color: #1a2e22; }
    .showcase-article__gallery { display: grid; grid-template-columns: repeat(2, 1fr); gap: 1rem; margin: 1.5rem auto 0; max-width: 860px; }
    .showcase-article__gallery img { display: block; width: 100%; height: auto; align-self: start; border-radius: .85rem; object-fit: contain; object-position: center; vertical-align: bottom; }
    @media (max-width: 575.98px) { .showcase-article__gallery { grid-template-columns: 1fr; } }
</style>
@endpush

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $showcase['title'],
    'description' => $showcase['meta_description'],
    'image' => $showcaseImages,
    'author' => ['@type' => 'Organization', 'name' => config('seo.organization.name', config('app.name'))],
    'publisher' => ['@type' => 'Organization', 'name' => config('seo.organization.name', config('app.name'))],
    'mainEntityOfPage' => url()->current(),
    'inLanguage' => 'en-IN',
], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => $showcase['title'],
        'heroClass' => 'pro-page-hero--blog',
        'items' => [
            ['label' => __('Food stories'), 'url' => route('blog.index')],
            ['label' => \Illuminate\Support\Str::limit($showcase['title'], 48)],
        ],
    ])
@endpush

@section('content')
    <article class="pro-blog-article pro-blog-detail showcase-article">
        <header class="pro-blog-detail__hero">
            <div class="pro-blog-detail__hero-glow" aria-hidden="true"></div>
            <div class="pro-blog-detail__hero-media">
                <img
                    src="{{ $showcaseImages[0] }}"
                    class="pro-blog-detail__hero-img"
                    alt="{{ $showcase['title'] }}"
                    loading="eager"
                    fetchpriority="high"
                >
            </div>
            <div class="pro-blog-detail__title-card">
                <p class="pro-blog-detail__eyebrow">{{ $showcase['eyebrow'] }}</p>
                <h1 class="pro-blog-detail__title">{{ $showcase['title'] }}</h1>
            </div>
        </header>

        <p class="pro-blog-detail__excerpt">{{ $showcase['summary'] }}</p>

        <div class="pro-blog-detail__paper">
            <div class="pro-blog-detail__content text-secondary lh-lg pro-blog-detail__content--rich">
                @if(!empty($showcase['article_sections']))
                    @foreach($showcase['article_sections'] as $section)
                        <h2>{{ $section['heading'] }}</h2>
                        @foreach($section['paragraphs'] as $paragraph)
                            <p>{{ $paragraph }}</p>
                        @endforeach
                    @endforeach
                @else
                    <h2>{{ __('The story behind this mountain staple') }}</h2>
                    @foreach($showcase['story'] as $paragraph)
                        <p>{{ $paragraph }}</p>
                    @endforeach
                @endif

                <figure class="showcase-article__image">
                    <img                     src="{{ $showcaseImages[1] }}" alt="{{ $showcase['title'] }} served with seasonal food" loading="lazy">
                </figure>

                <h2>{{ __('How to cook '.$showcase['title']) }}</h2>
                <p>{{ __('Follow these simple steps for a comforting, everyday preparation.') }}</p>
                <h3>{{ __('Ingredients') }}</h3>
                <ul>
                    @foreach($showcase['ingredients'] as $ingredient)
                        <li>{{ $ingredient }}</li>
                    @endforeach
                </ul>
                <h3>{{ __('Method') }}</h3>
                <ol>
                    @foreach($showcase['method'] as $step)
                        <li>{{ $step }}</li>
                    @endforeach
                </ol>
                <blockquote>
                    <strong>{{ __('Traditional dish:') }}</strong> {{ $showcase['serving'] }}
                </blockquote>

                <div class="showcase-article__facts">
                    @foreach($showcase['highlights'] as $highlight)
                        <div>
                            <span>{{ $highlight['label'] }}</span>
                            <strong>{{ $highlight['value'] }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="showcase-article__gallery" aria-label="{{ __('More story images') }}">
            @foreach(array_slice($showcaseImages, 2) as $image)
                <img src="{{ $image }}" alt="{{ $showcase['title'] }}" loading="lazy">
            @endforeach
        </div>
    </article>
@endsection

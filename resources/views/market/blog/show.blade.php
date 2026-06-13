@extends('layouts.market')

@section('title', ($post->meta_title ?: $post->title).' — Devbhoomi Blog')
@section('meta_description')
    {{ $post->meta_description ?: ($post->excerpt ?: Str::limit(strip_tags($post->body), 155)) }}
@endsection
@if(filled($post->meta_keywords))
@section('meta_keywords', $post->meta_keywords)
@endif
@section('canonical', $post->canonical_url ?: url()->current())
@section('og_image', $post->og_image ?: $post->imageUrl())
@section('og_type', 'article')

@php
    $published = $post->published_at ?? $post->created_at;
    $plainBody = strip_tags($post->body);
    $wordCount = max(0, str_word_count($plainBody));
    $readMinutes = max(1, (int) ceil($wordCount / 200));
@endphp

@push('schema')
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'Article',
    'headline' => $post->title,
    'description' => Str::limit(strip_tags($post->excerpt ?: $post->body), 160),
    'image' => [$post->imageUrl()],
    'datePublished' => $published->toIso8601String(),
    'dateModified' => optional($post->updated_at)->toIso8601String(),
    'author' => [
        '@type' => 'Organization',
        'name' => config('seo.organization.name', config('app.name')),
    ],
    'publisher' => [
        '@type' => 'Organization',
        'name' => config('seo.organization.name', config('app.name')),
        'logo' => [
            '@type' => 'ImageObject',
            'url' => app(\App\Services\SeoService::class)->absoluteUrl(config('seo.organization.logo')),
        ],
    ],
    'mainEntityOfPage' => url()->current(),
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => $post->title,
        'items' => [
            ['label' => __('Blog'), 'url' => route('blog.index')],
            ['label' => Str::limit($post->title, 48)],
        ],
    ])
@endpush

@section('content')
    <article class="pro-blog-article pro-blog-detail">
        <header class="pro-blog-detail__hero">
            <div class="pro-blog-detail__hero-glow" aria-hidden="true"></div>
            <div class="pro-blog-detail__hero-media ratio ratio-16x9">
                <img
                    src="{{ $post->imageUrl() }}"
                    class="object-fit-cover w-100 h-100"
                    alt="{{ $post->title }}"
                    loading="eager"
                    width="1200"
                    height="675"
                >
            </div>
            <div class="pro-blog-detail__title-card">
                <p class="pro-blog-detail__eyebrow">{{ __('Editorial') }}</p>
                <h1 class="pro-blog-detail__title">{{ $post->title }}</h1>
                <div class="pro-blog-detail__meta">
                    <span class="pro-blog-detail__meta-item">
                        <i class="bi bi-calendar3" aria-hidden="true"></i>
                        <time datetime="{{ $published->toIso8601String() }}">{{ $published->format('M j, Y') }}</time>
                    </span>
                    <span class="pro-blog-detail__meta-dot" aria-hidden="true"></span>
                    <span class="pro-blog-detail__meta-item">
                        <i class="bi bi-book" aria-hidden="true"></i>
                        {{ $readMinutes }} {{ __('min read') }}
                    </span>
                </div>
            </div>
        </header>

        @if($post->excerpt)
            <p class="pro-blog-detail__excerpt">{{ $post->excerpt }}</p>
        @endif

        <div class="pro-blog-detail__paper">
            <div class="pro-blog-detail__content text-secondary lh-lg pro-blog-detail__content--rich">
                @php
                    $bodyHtml = trim((string) $post->body);
                    $isRichHtml = $bodyHtml !== '' && $bodyHtml !== strip_tags($bodyHtml);
                @endphp
                @if($isRichHtml)
                    {!! $bodyHtml !!}
                @else
                    {!! nl2br(e($bodyHtml)) !!}
                @endif
            </div>
        </div>

        <footer class="pro-blog-detail__end">
            @if(($relatedPosts ?? collect())->isNotEmpty())
                <section class="pro-blog-related mb-4" aria-labelledby="proBlogRelatedHeading">
                    <h2 id="proBlogRelatedHeading" class="h5 fw-bold mb-3">{{ __('Related articles') }}</h2>
                    <div class="row g-3">
                        @foreach($relatedPosts as $related)
                            <div class="col-md-4">
                                <article class="pro-blog-card h-100">
                                    <a href="{{ route('blog.show', $related) }}" class="d-block">
                                        <img src="{{ $related->imageUrl() }}" class="pro-blog-card__img" alt="{{ $related->title }}" title="{{ $related->title }}" loading="lazy" width="640" height="400" decoding="async">
                                    </a>
                                    <div class="pro-blog-card__body p-3">
                                        <a href="{{ route('blog.show', $related) }}" class="pro-blog-card__title d-block text-decoration-none">{{ $related->title }}</a>
                                    </div>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
            <a href="{{ route('blog.index') }}" class="pro-blog-detail__back">
                <i class="bi bi-arrow-left" aria-hidden="true"></i>
                <span>{{ __('All posts') }}</span>
            </a>
        </footer>
    </article>
@endsection

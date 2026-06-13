@extends('layouts.market')

@section('title', __('Organic Food Blog').' | Devbhoomi Naturals')
@section('meta_description')
    {{ __('Tips on pure Himalayan spices, organic millets, traditional ingredients and healthy living from Devbhoomi Naturals, Uttarakhand.') }}
@endsection
@section('canonical', route('blog.index'))

@push('pagination_head')
    @include('market.partials.pagination-head', ['paginator' => $posts])
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('From the blog'),
        'items' => [['label' => __('Blog')]],
    ])
@endpush

@section('content')
    <div class="py-2 py-md-3">
        <header class="mb-4">
            <p class="pro-section-head__eyebrow mb-1">{{ __('Editorial') }}</p>
        </header>

        @if($posts->isEmpty())
            <p class="text-muted">{{ __('No articles yet. Check back soon.') }}</p>
        @else
            <div class="row g-3">
                @foreach($posts as $post)
                    <div class="col-md-6 col-lg-4">
                        <article class="pro-blog-card h-100">
                            <a href="{{ route('blog.show', $post) }}" class="d-block">
                                <img src="{{ $post->imageUrl() }}" class="pro-blog-card__img" alt="{{ $post->title }}" title="{{ $post->title }}" loading="lazy" width="640" height="400" decoding="async">
                            </a>
                            <div class="pro-blog-card__body">
                                <div class="pro-blog-card__date">{{ strtoupper(($post->published_at ?? $post->created_at)->format('M Y')) }}</div>
                                <a href="{{ route('blog.show', $post) }}" class="pro-blog-card__title d-inline-block">{{ $post->title }}</a>
                                @if($post->excerpt)
                                    <p class="small text-muted mt-2 mb-0">{{ Str::limit($post->excerpt, 120) }}</p>
                                @endif
                            </div>
                        </article>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $posts->links() }}
            </div>
        @endif
    </div>
@endsection

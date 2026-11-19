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

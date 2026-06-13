@extends('layouts.market')

@section('title', __('Sitemap').' | '.config('seo.organization.name', config('app.name')))
@section('meta_description', __('Browse all pages, categories, products, blog posts, and seller shops on Devbhoomi Naturals.'))
@section('canonical', route('pages.sitemap'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Sitemap'),
        'items' => [['label' => __('Sitemap')]],
    ])
@endpush

@section('content')
    <div class="pro-page-pad-mobile py-3">
        <p class="text-muted mb-4">{{ __('Quick links to every section of our store.') }}</p>

        <div class="row g-4">
            <div class="col-md-6 col-lg-4">
                <div class="zm-card p-3 h-100">
                    <h2 class="h6 fw-bold mb-3">{{ __('Main pages') }}</h2>
                    <ul class="list-unstyled mb-0 small">
                        @foreach($staticLinks as $link)
                            <li class="mb-2"><a href="{{ $link['url'] }}">{{ $link['label'] }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="zm-card p-3 h-100">
                    <h2 class="h6 fw-bold mb-3">{{ __('Categories') }}</h2>
                    <ul class="list-unstyled mb-0 small">
                        @forelse($categories as $cat)
                            <li class="mb-2"><a href="{{ route('shop.menu', $cat->slug) }}">{{ $cat->title }}</a></li>
                        @empty
                            <li class="text-muted">{{ __('No categories yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="col-md-6 col-lg-4">
                <div class="zm-card p-3 h-100">
                    <h2 class="h6 fw-bold mb-3">{{ __('Blog') }}</h2>
                    <ul class="list-unstyled mb-0 small">
                        @forelse($posts as $post)
                            <li class="mb-2"><a href="{{ route('blog.show', $post->slug) }}">{{ $post->title }}</a></li>
                        @empty
                            <li class="text-muted">{{ __('No posts yet.') }}</li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="zm-card p-3 h-100">
                    <h2 class="h6 fw-bold mb-3">{{ __('Products') }} ({{ $products->count() }})</h2>
                    <ul class="list-unstyled mb-0 small columns-2">
                        @foreach($products as $product)
                            <li class="mb-2"><a href="{{ route('product.show', $product->slug) }}">{{ $product->name }}</a></li>
                        @endforeach
                    </ul>
                </div>
            </div>

            @if($vendors->isNotEmpty())
                <div class="col-lg-6">
                    <div class="zm-card p-3 h-100">
                        <h2 class="h6 fw-bold mb-3">{{ __('Seller shops') }}</h2>
                        <ul class="list-unstyled mb-0 small">
                            @foreach($vendors as $vendor)
                                <li class="mb-2"><a href="{{ route('vendor.shop', $vendor->slug) }}">{{ $vendor->shop_name }}</a></li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif
        </div>
    </div>
@endsection

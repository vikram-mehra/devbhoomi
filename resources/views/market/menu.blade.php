@extends('layouts.market')

@section('title', ($menuItem->meta_title ?: $menuItem->title.' — Organic '.$menuItem->title))
@section('meta_description', $menuItem->meta_description ?: 'Buy '.$menuItem->title.' online — pure Himalayan organic products from Devbhoomi Naturals, Uttarakhand. Fast delivery across India.')
@if(filled($menuItem->meta_keywords))
@section('meta_keywords', $menuItem->meta_keywords)
@endif
@section('canonical', $menuItem->canonical_url ?: app(\App\Services\SeoService::class)->canonicalForListing(route('shop.menu', $menuItem->slug)))
@if(filled($menuItem->og_image))
@section('og_image', $menuItem->og_image)
@endif

@push('pagination_head')
    @include('market.partials.pagination-head', ['paginator' => $products])
@endpush

@push('schema')
@php
    $itemListSchema = app(\App\Services\SeoService::class)->itemListSchema(
        $menuItem->title,
        $products->map(fn ($p) => route('product.show', $p->slug))->all()
    );
@endphp
<script type="application/ld+json">
{!! json_encode([
    '@context' => 'https://schema.org',
    '@type' => 'CollectionPage',
    'name' => $menuItem->title,
    'description' => Str::limit(strip_tags($menuItem->meta_description ?: 'Shop '.$menuItem->title), 160),
    'url' => route('shop.menu', $menuItem->slug),
], JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@if($itemListSchema)
<script type="application/ld+json">
{!! json_encode($itemListSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => $menuItem->title,
        'items' => [['label' => $menuItem->title]],
    ])
@endpush

@section('content')
    <div class="row g-4">
        <aside class="col-lg-3">
            @include('market.partials.shop-filters', [
                'formAction' => route('shop.menu', $menuItem->slug),
                'facets' => $facets,
                'hiddenFields' => array_filter([
                    'q' => request('q'),
                    'sort' => request('sort', 'popular'),
                ], fn ($v) => $v !== null && $v !== ''),
            ])
        </aside>
        <div class="col-lg-9">
            <form method="get" action="{{ route('shop.menu', $menuItem->slug) }}" class="mk-shop-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                @foreach(request()->except(['sort', 'page']) as $k => $v)
                    @if(is_array($v))
                        @foreach($v as $item)
                            @if($item !== null && $item !== '')
                                <input type="hidden" name="{{ $k }}[]" value="{{ $item }}">
                            @endif
                        @endforeach
                    @else
                        @if($v !== null && $v !== '')
                            <input type="hidden" name="{{ $k }}" value="{{ $v }}">
                        @endif
                    @endif
                @endforeach
                @php $ptotal = $products->total(); @endphp
                <span class="small text-muted">{{ $ptotal }} {{ $ptotal === 1 ? __('product') : __('products') }}</span>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <label class="small text-muted mb-0" for="mkMenuSort">{{ __('Sort by') }}</label>
                    <select name="sort" id="mkMenuSort" class="form-select form-select-sm rounded-3" style="width: auto; min-width: 10rem;" onchange="this.form.submit()">
                        <option value="popular" @if(request('sort', 'popular') === 'popular') selected @endif>{{ __('Popularity') }}</option>
                        <option value="price_asc" @if(request('sort') === 'price_asc') selected @endif>{{ __('Price ↑') }}</option>
                        <option value="price_desc" @if(request('sort') === 'price_desc') selected @endif>{{ __('Price ↓') }}</option>
                        <option value="newest" @if(request('sort') === 'newest') selected @endif>{{ __('Newest') }}</option>
                    </select>
                </div>
            </form>

            <div class="zm-grid-products zm-grid-products--shop">
                @forelse($products as $product)
                    @include('market.partials.product-card', ['product' => $product, 'listing' => true])
                @empty
                    <p class="text-muted mb-0">{{ __('No products found.') }}</p>
                @endforelse
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        </div>
    </div>
@endsection

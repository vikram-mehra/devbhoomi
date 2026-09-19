@extends('layouts.market')

@section('title', request('q') ? __('Search').': '.request('q') : __('Shop Organic Products'))
@section('meta_description', request('q')
    ? __('Search results for :query — organic Himalayan products from Devbhoomi Naturals.', ['query' => request('q')])
    : 'Browse our full collection of organic millets, pahadi pulses, spices and Himalayan food products from Uttarakhand.')
@section('canonical', app(\App\Services\SeoService::class)->canonicalForListing(route('shop.search')))

@push('pagination_head')
    @include('market.partials.pagination-head', ['paginator' => $products])
@endpush

@push('schema')
@php
    $searchListSchema = app(\App\Services\SeoService::class)->itemListSchema(
        request('q') ? __('Search results for :query', ['query' => request('q')]) : __('Shop Organic Products'),
        $products->map(fn ($p) => route('product.show', $p->slug))->all()
    );
@endphp
@if($searchListSchema)
<script type="application/ld+json">
{!! json_encode($searchListSchema, JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endif
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Search products'),
        'items' => [['label' => __('Search products')]],
    ])
@endpush

@section('content')
    <div class="row g-4 pro-listing-page">
        <aside class="col-lg-3 mk-shop-filter-col" id="mkShopFilterCol">
            @include('market.partials.shop-filters', [
                'formAction' => route('shop.search'),
                'facets' => $facets,
                'hiddenFields' => array_filter([
                    'q' => request('q'),
                    'sort' => request('sort', 'popular'),
                    'category' => request('category'),
                ], fn ($v) => $v !== null && $v !== ''),
            ])
        </aside>
        <div class="col-lg-9">
            <form method="get" action="{{ route('shop.search') }}" class="mk-shop-toolbar d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
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
                @php $rtotal = $products->total(); @endphp
                <span class="small text-muted mk-shop-toolbar__count">{{ $rtotal }} {{ $rtotal === 1 ? __('result') : __('results') }}</span>
                <div class="mk-shop-toolbar__actions d-flex align-items-center gap-2 ms-auto">
                    <button type="button" class="mk-shop-filter-toggle d-lg-none" id="mkShopFilterOpen" aria-expanded="false" aria-controls="mkShopFilterPanel">
                        <i class="bi bi-sliders" aria-hidden="true"></i>{{ __('Filters') }}
                    </button>
                    <div class="mk-shop-toolbar__sort d-flex align-items-center gap-2">
                        <label class="small text-muted mb-0" for="mkSearchSort">{{ __('Sort by') }}</label>
                        <select name="sort" id="mkSearchSort" class="form-select form-select-sm rounded-3" style="width: auto; min-width: 8rem;" onchange="this.form.submit()">
                            <option value="popular" @if(request('sort', 'popular') === 'popular') selected @endif>{{ __('Popularity') }}</option>
                            <option value="price_asc" @if(request('sort') === 'price_asc') selected @endif>{{ __('Price ↑') }}</option>
                            <option value="price_desc" @if(request('sort') === 'price_desc') selected @endif>{{ __('Price ↓') }}</option>
                            <option value="newest" @if(request('sort') === 'newest') selected @endif>{{ __('Newest') }}</option>
                        </select>
                    </div>
                </div>
            </form>

            @if($products->isEmpty())
                @include('market.partials.empty-state', [
                    'icon' => 'bi-search',
                    'title' => __('No products found'),
                    'text' => request('q')
                        ? __('We could not find any products matching “:query”. Try a different keyword or browse the shop.', ['query' => request('q')])
                        : __('No products match these filters. Try changing your search, category, or price range.'),
                    'cta' => __('Continue shopping'),
                    'ctaUrl' => route('shop.search'),
                ])
            @else
                <div class="zm-grid-products zm-grid-products--shop">
                    @foreach($products as $product)
                        @include('market.partials.product-card', ['product' => $product, 'listing' => true])
                    @endforeach
                </div>
                {{ $products->links('market.partials.pagination') }}
            @endif
        </div>
    </div>
@endsection

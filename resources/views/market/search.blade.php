@extends('layouts.market')

@section('title', request('q') ? __('Search').': '.request('q') : __('Shop Organic Products'))
@section('meta_description', request('q')
    ? __('Search results for :query — organic Himalayan products from Devbhoomi Naturals.', ['query' => request('q')])
    : 'Browse our full collection of organic millets, pahadi pulses, spices and Himalayan food products from Uttarakhand.')
@section('canonical', app(\App\Services\SeoService::class)->canonicalForListing(route('shop.search')))

@push('pagination_head')
    @include('market.partials.pagination-head', ['paginator' => $products])
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Search products'),
        'items' => [['label' => __('Search products')]],
    ])
@endpush

@section('content')
    <div class="row g-4">
        <aside class="col-lg-3">
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
                <span class="small text-muted">{{ $rtotal }} {{ $rtotal === 1 ? __('result') : __('results') }}</span>
                <div class="d-flex align-items-center gap-2 ms-auto">
                    <label class="small text-muted mb-0" for="mkSearchSort">{{ __('Sort by') }}</label>
                    <select name="sort" id="mkSearchSort" class="form-select form-select-sm rounded-3" style="width: auto; min-width: 10rem;" onchange="this.form.submit()">
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
                    <p class="text-muted mb-0">{{ __('No matches.') }}</p>
                @endforelse
            </div>
            <div class="mt-4">{{ $products->links() }}</div>
        </div>
    </div>
@endsection

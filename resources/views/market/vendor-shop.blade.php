@extends('layouts.market')

@section('title', $vendor->meta_title ?: $vendor->shop_name.' | Devbhoomi Seller')
@section('meta_description', Str::limit($vendor->meta_description ?: strip_tags($vendor->description ?: 'Shop organic products from '.$vendor->shop_name.' on Devbhoomi Naturals.'), 160))
@if(filled($vendor->meta_keywords))
@section('meta_keywords', $vendor->meta_keywords)
@endif
@section('canonical', $vendor->canonical_url ?: app(\App\Services\SeoService::class)->canonicalForListing(route('vendor.shop', $vendor->slug)))

@push('pagination_head')
    @include('market.partials.pagination-head', ['paginator' => $products])
@endpush
@section('og_type', 'website')

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => $vendor->shop_name,
        'items' => [['label' => $vendor->shop_name]],
    ])
@endpush

@section('content')
    <div class="zm-card p-4 mb-4 d-flex flex-wrap gap-3 align-items-center justify-content-between">
        <div>
            <p class="h3 mb-1 fw-bold" role="doc-subtitle">{{ $vendor->shop_name }}</p>
            <div class="text-muted">{{ $vendor->city }}, {{ $vendor->state }} · ★ {{ number_format($vendor->rating_avg, 1) }} ({{ $vendor->rating_count }})</div>
            <p class="mb-0 mt-2">{{ $vendor->description }}</p>
        </div>
        @auth
            <a href="{{ route('chat.show', $vendor) }}" class="zm-btn zm-btn-primary"><i class="bi bi-chat-dots"></i> Message</a>
        @endauth
    </div>
    <div class="zm-grid-products">
        @forelse($products as $product)
            @include('market.partials.product-card', ['product' => $product])
        @empty
            <p>No products yet.</p>
        @endforelse
    </div>
    {{ $products->links() }}
@endsection

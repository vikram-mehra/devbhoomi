@extends('layouts.market')

@section('title', $product->name.' | Product Showcase')
@section('meta_description', \Illuminate\Support\Str::limit(strip_tags($product->short_description ?: $product->description ?: $product->name), 160))
@section('robots', 'noindex, nofollow')

@push('breadcrumb')
    @php
        $crumbs = [];
        if ($product->menuItem) {
            $crumbs[] = ['label' => $product->menuItem->title, 'url' => $product->menuItem->resolvedUrl()];
        }
        $crumbs[] = ['label' => $product->name];
    @endphp
    @include('market.partials.breadcrumbs', [
        'title' => $product->name,
        'items' => $crumbs,
    ])
@endpush

@push('head')
<style>
    .showcase-page { --showcase-green: var(--pro-primary, #2d5a3d); --showcase-ink: var(--pro-ink, #1a2e22); --showcase-muted: var(--pro-muted, #5f6d62); color: var(--showcase-ink); padding: 2rem 0 4rem; }
    .showcase-wrap { max-width: 1180px; margin: 0 auto; padding: 0 1rem; }
    .showcase-page h1, .showcase-page h2, .showcase-page h3 { font-family: var(--font-display, var(--font-ui, "Assistant", sans-serif)); }
    .showcase-eyebrow { color: var(--showcase-green); font-size: .75rem; font-weight: 700; letter-spacing: .14em; text-transform: uppercase; }
    .showcase-hero { display: grid; grid-template-columns: minmax(0, 1.05fr) minmax(300px, .95fr); gap: clamp(2rem, 5vw, 5rem); align-items: flex-start; padding: 1.5rem 0 4rem; }
    .showcase-gallery { min-width: 0; }
    .showcase-copy { width: 100%; }
    .showcase-main { width: 100%; background: #f5f7f3; border-radius: 1.25rem; min-height: 360px; aspect-ratio: 1 / 1; overflow: hidden; }
    .showcase-main img { display: block; width: 100%; height: 100%; object-fit: cover; object-position: center; }
    .showcase-thumbs { display: flex; flex-wrap: wrap; gap: .75rem; margin-top: .9rem; }
    .showcase-thumb { width: 72px; height: 72px; border: 1px solid #dfe6df; border-radius: .75rem; padding: .25rem; background: #fff; cursor: pointer; }
    .showcase-thumb:hover, .showcase-thumb:focus-visible { border-color: var(--showcase-green); outline: 2px solid rgba(45,90,61,.16); }
    .showcase-thumb img { width: 100%; height: 100%; object-fit: contain; }
    .showcase-title { font-size: clamp(2rem, 4vw, 3.5rem); font-weight: 700; line-height: 1.12; margin: .7rem 0 1rem; }
    .showcase-summary { color: var(--showcase-muted); font-size: .875em; line-height: 1.8; }
    .showcase-price { display: flex; flex-wrap: wrap; align-items: baseline; gap: .7rem; margin: 1.5rem 0; }
    .showcase-price-current { color: var(--showcase-green); font-size: 1.8rem; font-weight: 700; }
    .showcase-price-old { color: #89928b; text-decoration: line-through; }
    .showcase-discount { color: #2b7047; background: #e8f3eb; border-radius: 999px; padding: .25rem .7rem; font-size: .8rem; font-weight: 700; }
    .showcase-variants { display: flex; flex-wrap: wrap; gap: .65rem; margin-top: 1rem; }
    .showcase-variant { border: 1px solid #e1e7e1; border-radius: .7rem; padding: .65rem .8rem; background: white; }
    .showcase-variant-name { display: block; font-weight: 700; }
    .showcase-variant-meta { display: block; margin-top: .2rem; color: var(--showcase-muted); font-size: .85rem; }
    .showcase-section { border-top: 1px solid #e7ebe7; padding: 3rem 0; }
    .showcase-section h2 { font-size: clamp(1.7rem, 3vw, 2.4rem); font-weight: 700; margin: 0 0 1.25rem; }
    .showcase-description { max-width: 850px; color: var(--showcase-muted); line-height: 1.8; overflow-wrap: anywhere; }
    .showcase-description p { margin: 0 0 1rem; }
    .showcase-description ul, .showcase-description ol { margin: .75rem 0 1.25rem; padding-left: 1.75rem; }
    .showcase-description li { margin: 0 0 .55rem; padding-left: .2rem; }
    .showcase-description li:last-child { margin-bottom: 0; }
    .showcase-highlights { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; margin-top: 1.5rem; }
    .showcase-highlight { background: #f5f7f3; border-radius: .85rem; padding: 1rem 1.1rem; }
    .showcase-highlight span { display: block; color: var(--showcase-muted); font-size: .82rem; }
    .showcase-highlight strong { display: block; margin-top: .3rem; }
    .showcase-specs { width: 100%; max-width: 760px; border-collapse: collapse; }
    .showcase-specs th, .showcase-specs td { border-bottom: 1px solid #e7ebe7; padding: .9rem .5rem; text-align: left; vertical-align: top; }
    .showcase-specs th { width: 35%; color: var(--showcase-muted); font-weight: 600; }
    .showcase-products { display: grid; grid-template-columns: repeat(4, minmax(0, 1fr)); grid-auto-rows: 1fr; gap: 1rem; }
    .showcase-card { display: flex; flex-direction: column; overflow: hidden; height: 100%; border: 1px solid #e8ece8; border-radius: .9rem; background: #fff; }
    .showcase-card-image { position: relative; display: block; width: 100%; aspect-ratio: 3 / 4; background: #f5f7f3; overflow: hidden; }
    .showcase-card-image-backdrop { position: absolute; inset: 0; width: 100%; height: 100%; object-fit: cover; filter: blur(14px); opacity: .4; transform: scale(1.08); }
    .showcase-card-image-main { position: relative; display: block; width: 100%; height: 100%; object-fit: contain; object-position: center; }
    .showcase-card-body { display: flex; flex: 1; flex-direction: column; align-items: flex-start; padding: 1rem; }
    .showcase-card h3 { font-size: 1rem; line-height: 1.4; margin: 0 0 .45rem; }
    .showcase-card p { color: var(--showcase-muted); font-size: .9rem; line-height: 1.55; min-height: 2.8em; margin: 0 0 .8rem; }
    .showcase-card-body > a { margin-top: auto; color: var(--showcase-green); font-weight: 700; text-decoration: none; }
    .showcase-card-image + .showcase-card-body > a { padding-top: .2rem; }
    .showcase-card-body > a:hover { text-decoration: underline; }
    .showcase-empty { background: #f5f7f3; border-radius: .9rem; padding: 1.5rem; color: var(--showcase-muted); }
    @media (max-width: 767.98px) {
        .showcase-hero { grid-template-columns: 1fr; gap: 1.75rem; padding-top: .5rem; }
        .showcase-main { min-height: 260px; }
        .showcase-products { grid-template-columns: repeat(2, minmax(0, 1fr)); }
        .showcase-section { padding: 2.25rem 0; }
    }
    @media (max-width: 420px) {
        .showcase-products { gap: .65rem; }
        .showcase-card-body { padding: .8rem; }
        .showcase-card p { font-size: .82rem; }
    }
</style>
@endpush

@section('content')
    @php
        $summary = trim(strip_tags((string) ($product->short_description ?: '')));
        $description = trim((string) ($product->description ?: ''));
        $descriptionIsRich = $description !== '' && $description !== strip_tags($description);
        $price = $product->effectivePrice();
        $comparePrice = (float) ($product->compare_price ?? 0);
        $showComparePrice = $comparePrice > $price;
        $discountPercent = $showComparePrice ? (int) round((($comparePrice - $price) / $comparePrice) * 100) : 0;
        $specifications = array_filter([
            __('Brand') => $product->brand,
            __('Category') => $product->menuItem?->title,
            __('SKU') => $product->sku,
            __('Barcode') => $product->barcode,
            __('Weight') => $product->formattedWeightKg(),
            __('HSN code') => $product->hsn,
            __('GST') => filled($product->gst) ? $product->gst.'%' : null,
        ], fn ($value) => filled($value));
    @endphp

    <main class="showcase-page">
        <div class="showcase-wrap">
            <section class="showcase-hero" aria-labelledby="showcase-title">
                <div class="showcase-gallery">
                    <div class="showcase-main">
                        <img id="showcaseMainImage" src="{{ $galleryImages->first() }}" alt="{{ $product->name }}" fetchpriority="high">
                    </div>
                    @if($galleryImages->count() > 1)
                        <div class="showcase-thumbs" aria-label="{{ __('Product images') }}">
                            @foreach($galleryImages as $image)
                                <button type="button" class="showcase-thumb" data-showcase-image="{{ $image }}" aria-label="{{ __('Show another image for :product', ['product' => $product->name]) }}">
                                    <img src="{{ $image }}" alt="" loading="lazy">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="showcase-copy">
                    <span class="showcase-eyebrow">{{ __('Product showcase') }}</span>
                    <h1 id="showcase-title" class="showcase-title">{{ $product->name }}</h1>
                    @if($summary !== '')
                        <p class="showcase-summary">{!! nl2br(e($summary)) !!}</p>
                    @endif

                    <div class="showcase-price" aria-label="{{ __('Price') }}">
                        <span class="showcase-price-current">₹{{ number_format($price, 2) }}</span>
                        @if($showComparePrice)
                            <span class="showcase-price-old">₹{{ number_format($comparePrice, 2) }}</span>
                            <span class="showcase-discount">{{ $discountPercent }}% {{ __('off') }}</span>
                        @endif
                    </div>

                    @if($product->variants->isNotEmpty())
                        <h2 class="h6 fw-bold mt-4 mb-0">{{ $product->variant_label ?: __('Available options') }}</h2>
                        <div class="showcase-variants">
                            @foreach($product->variants as $variant)
                                <div class="showcase-variant">
                                    <span class="showcase-variant-name">{{ $variant->label() }}</span>
                                    <span class="showcase-variant-meta">
                                        ₹{{ number_format($variant->effectivePrice(), 2) }}
                                        ·
                                        {{ $variant->isBuyable() ? __('In stock') : __('Out of stock') }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </section>

            @if($description !== '')
                <section class="showcase-section" aria-labelledby="showcase-description-title">
                    <h2 id="showcase-description-title">{{ __('About this product') }}</h2>
                    <div class="showcase-description">
                        @if($descriptionIsRich)
                            {!! $description !!}
                        @else
                            {!! nl2br(e($description)) !!}
                        @endif
                    </div>
                </section>
            @endif

            @if($specifications !== [])
                <section class="showcase-section" aria-labelledby="showcase-specifications-title">
                    <h2 id="showcase-specifications-title">{{ __('Highlights & specifications') }}</h2>
                    <div class="showcase-highlights">
                        @if(filled($product->brand))
                            <div class="showcase-highlight"><span>{{ __('Brand') }}</span><strong>{{ $product->brand }}</strong></div>
                        @endif
                        @if($product->formattedWeightKg())
                            <div class="showcase-highlight"><span>{{ __('Product weight') }}</span><strong>{{ $product->formattedWeightKg() }}</strong></div>
                        @endif
                        @if((int) $product->rating_count > 0)
                            <div class="showcase-highlight"><span>{{ __('Customer rating') }}</span><strong>{{ number_format((float) $product->rating_avg, 1) }} / 5 · {{ $product->rating_count }}</strong></div>
                        @endif
                    </div>
                    <table class="showcase-specs mt-4">
                        <tbody>
                            @foreach($specifications as $label => $value)
                                <tr><th scope="row">{{ $label }}</th><td>{{ $value }}</td></tr>
                            @endforeach
                        </tbody>
                    </table>
                </section>
            @endif

            <section class="showcase-section" aria-labelledby="showcase-other-products-title">
                <span class="showcase-eyebrow">{{ __('Explore the collection') }}</span>
                <h2 id="showcase-other-products-title">{{ __('Other Products') }}</h2>
                @if($otherProducts->isNotEmpty())
                    <div class="showcase-products">
                        @foreach($otherProducts as $otherProduct)
                            @php
                                $cardImage = $otherProduct->cardImageUrls()[0] ?? $otherProduct->namedPlaceholderUrl();
                                $cardDescription = trim(strip_tags((string) ($otherProduct->short_description ?: $otherProduct->description ?: '')));
                            @endphp
                            <article class="showcase-card">
                                <a class="showcase-card-image" href="{{ route('showcase.show', $otherProduct->slug) }}" tabindex="-1" aria-hidden="true">
                                    <img class="showcase-card-image-backdrop" src="{{ $cardImage }}" alt="" aria-hidden="true" loading="lazy">
                                    <img class="showcase-card-image-main" src="{{ $cardImage }}" alt="{{ $otherProduct->name }}" loading="lazy">
                                </a>
                                <div class="showcase-card-body">
                                    <h3>{{ $otherProduct->name }}</h3>
                                    <p>{{ \Illuminate\Support\Str::limit($cardDescription ?: __('Discover more about this product.'), 105) }}</p>
                                    <a href="{{ route('showcase.show', $otherProduct->slug) }}">{{ __('View Details') }}</a>
                                </div>
                            </article>
                        @endforeach
                    </div>
                    @if($otherProducts->hasPages())
                        <div class="mt-4">{{ $otherProducts->links() }}</div>
                    @endif
                @else
                    <p class="showcase-empty">{{ __('There are no other products to show yet.') }}</p>
                @endif
            </section>
        </div>
    </main>
@endsection

@push('scripts')
<script>
    document.addEventListener('click', function (event) {
        var button = event.target.closest('[data-showcase-image]');
        if (!button) return;
        var image = document.getElementById('showcaseMainImage');
        if (image) image.src = button.getAttribute('data-showcase-image');
    });
</script>
@endpush

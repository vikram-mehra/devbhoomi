@extends('layouts.market')

@section('title', $product->meta_title ?: $product->name.' | Buy Online')
@section('meta_description', Str::limit($product->meta_description ?? strip_tags($product->short_description ?? $product->description), 160))
@section('canonical', $product->canonical_url ?: url()->current())
@if(filled($product->og_image))
@section('og_image', $product->og_image)
@elseif($product->images->isNotEmpty())
@section('og_image', \App\Models\Product::publicImageUrl($product->images->first()->path))
@endif
@section('og_type', 'product')

@if(filled($product->meta_keywords))
@push('head')
<meta name="keywords" content="{{ $product->meta_keywords }}">
<meta property="product:price:amount" content="{{ number_format($product->effectivePrice(), 2, '.', '') }}">
<meta property="product:price:currency" content="INR">
<meta property="product:availability" content="{{ $product->variants->contains(fn ($v) => $v->isBuyable()) ? 'in stock' : 'out of stock' }}">
@endpush
@else
@push('head')
<meta property="product:price:amount" content="{{ number_format($product->effectivePrice(), 2, '.', '') }}">
<meta property="product:price:currency" content="INR">
<meta property="product:availability" content="{{ $product->variants->contains(fn ($v) => $v->isBuyable()) ? 'in stock' : 'out of stock' }}">
@endpush
@endif

@push('head')
<style>
    .pdp-review-star {
        font-size: 1.5rem;
        cursor: pointer;
        transition: transform 0.15s ease-in-out, color 0.15s ease-in-out;
    }
    .pdp-review-star:hover {
        transform: scale(1.25);
    }
</style>
@endpush

@push('schema')
@php
    $schemaImages = $product->images->map(fn ($im) => \App\Models\Product::publicImageUrl($im->path))->filter()->values()->all();
    if (count($schemaImages) === 0) {
        $schemaImages = [$product->namedPlaceholderUrl(false)];
    }
    $hasStock = $product->variants->contains(fn ($v) => $v->isBuyable());
    $brandName = filled($product->brand) ? $product->brand : ($product->vendor->shop_name ?? config('seo.organization.name'));
@endphp
<script type="application/ld+json">
{!! json_encode(array_filter([
    '@context' => 'https://schema.org',
    '@type' => 'Product',
    'name' => $product->name,
    'image' => $schemaImages,
    'description' => Str::limit(strip_tags(trim(($product->short_description ?? '').' '.($product->description ?? ''))), 300),
    'sku' => $product->sku,
    'brand' => ['@type' => 'Brand', 'name' => $brandName],
    'category' => $product->menuItem?->title,
    'offers' => [
        '@type' => 'Offer',
        'priceCurrency' => 'INR',
        'price' => $product->effectivePrice(),
        'availability' => $hasStock ? 'https://schema.org/InStock' : 'https://schema.org/OutOfStock',
        'url' => url()->current(),
        'seller' => [
            '@type' => 'Organization',
            'name' => $product->vendor->shop_name ?? config('seo.organization.name'),
        ],
    ],
    'aggregateRating' => $product->rating_count ? [
        '@type' => 'AggregateRating',
        'ratingValue' => (float) $product->rating_avg,
        'reviewCount' => (int) $product->rating_count,
    ] : null,
]), JSON_UNESCAPED_SLASHES|JSON_UNESCAPED_UNICODE) !!}
</script>
@endpush

@push('breadcrumb')
    @php
        $crumbs = [];
        if ($product->menuItem) {
            $crumbs[] = ['label' => $product->menuItem->title, 'url' => route('shop.menu', $product->menuItem->slug)];
        }
        $crumbs[] = ['label' => $product->name];
    @endphp
    @include('market.partials.breadcrumbs', [
        'title' => $product->name,
        'items' => $crumbs,
    ])
@endpush

@section('content')
    @php
        $galleryUrls = collect();
        foreach ($product->images as $im) {
            $u = \App\Models\Product::publicImageUrl($im->path);
            if ($u) {
                $galleryUrls->push($u);
            }
        }
        if ($galleryUrls->isEmpty()) {
            $galleryUrls->push($product->namedPlaceholderUrl(false));
        }
        $pdpMain = $galleryUrls->first();
        $sizeRank = ['XXS' => 0, 'XS' => 1, 'S' => 2, 'M' => 3, 'L' => 4, 'XL' => 5, 'XXL' => 6, '3XL' => 7, '4XL' => 8];
        $activeVariants = $product->variants->values();
        $colorValues = $activeVariants->pluck('color')->filter(fn ($c) => filled($c))->unique()->values();
        $pdpColorFirst = $colorValues->isNotEmpty();
        $sizedVariants = $activeVariants
            ->filter(fn ($v) => filled($v->size))
            ->unique('size')
            ->sortBy(function ($v) use ($sizeRank) {
                $u = strtoupper(trim((string) $v->size));

                return [$sizeRank[$u] ?? 100, $v->size];
            })
            ->values();
        $useSizePills = $activeVariants->isNotEmpty()
            && $activeVariants->every(fn ($v) => filled($v->size));
        $isSareeCategory = $product->menuItem?->isColorOnlyMenu() ?? false;
        /** Saree: never show size row on PDP (even if legacy variants still have size values). */
        $showSizeOnPdp = $useSizePills && ! $isSareeCategory;
        $defaultVariant = $activeVariants->first(fn ($v) => $v->isBuyable()) ?? $activeVariants->first();
        $variantPayload = $activeVariants->map(fn ($v) => [
            'id' => $v->id,
            'color' => (string) ($v->color ?? ''),
            'size' => (string) ($v->size ?? ''),
            'stock' => (int) $v->stock_qty,
            'effectivePrice' => $v->effectivePrice(),
            'unitPrice' => $v->unitPrice(),
            'image' => $v->variantImageUrl(),
            'buyable' => $v->isBuyable(),
        ])->values()->all();
    @endphp
    <div class="row g-4 pro-page-pad-mobile">
        <div class="col-md-5">
            <div class="zm-card p-2 p-md-3">
                <div class="pro-pdp-gallery pro-pdp-gallery--hero">
                    <div class="pro-pdp-gallery__zoom-wrap">
                        <img id="mainImg" src="{{ $pdpMain }}" class="pro-pdp-gallery__main w-100" alt="{{ $product->name }}" title="{{ $product->name }}" loading="eager" fetchpriority="high" width="800" height="1000" decoding="async">
                    </div>
                    @if($galleryUrls->count() > 1)
                        <div class="pro-pdp-gallery__grid pro-pdp-gallery__grid--below-hero" role="list">
                            @foreach($galleryUrls as $url)
                                <button type="button"
                                    class="pro-pdp-gallery__cell {{ $loop->first ? 'is-active' : '' }}"
                                    data-img="{{ $url }}"
                                    aria-label="{{ __('View image :num', ['num' => $loop->iteration]) }}"
                                    role="listitem">
                                    <img src="{{ $url }}" alt="{{ $product->name }} — {{ __('Image :num', ['num' => $loop->iteration]) }}" title="{{ $product->name }}" loading="lazy" width="120" height="150" decoding="async">
                                </button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-7">
            <div class="small text-muted">
                @if($product->menuItem)
                    <a href="{{ route('shop.menu', $product->menuItem->slug) }}">{{ $product->menuItem->title }}</a>
                    <span class="mx-1">·</span>
                @endif
                <a href="{{ route('vendor.shop', $product->vendor->slug) }}">{{ $product->vendor->shop_name }}</a>
            </div>
            <p class="h3 fw-bold mb-2">{{ $product->name }}</p>
            <div class="mb-2"><i class="bi bi-star-fill text-warning" aria-hidden="true"></i> {{ number_format($product->rating_avg, 1) }} <span class="text-muted small">({{ $product->rating_count }} reviews)</span></div>
            @php $fp = $defaultVariant ? $defaultVariant->effectivePrice() : $product->effectivePrice(); @endphp
            <div class="mb-3"><span class="zm-price fs-4" id="pdpPriceNow">{!! '&#8377;' !!}{{ number_format($fp, 0) }}</span>
                <span class="zm-price-was @if(!($product->compare_price && (float) $product->compare_price > $fp)) d-none @endif" id="pdpPriceWas">@if($product->compare_price && (float) $product->compare_price > $fp){!! '&#8377;' !!}{{ number_format($product->compare_price, 0) }}@endif</span>
            </div>
            @if($weightLabel = $product->formattedWeightKg())
                <p class="small mb-2 pro-pdp-weight">
                    <span class="text-muted">{{ __('Weight') }}:</span>
                    <span class="fw-semibold text-dark">{{ $weightLabel }}</span>
                </p>
            @endif
            <p class="small text-muted mb-2 d-none" id="pdpStockMsg" role="status"></p>
            @if(filled($product->short_description))
                <div class="pro-pdp-short-block mb-3">
                    <h2 class="pro-pdp-short-block__title h6 fw-bold text-dark mb-2">{{ __('Product description') }}</h2>
                    <div class="pro-pdp-short-block__text text-muted small lh-lg">
                        {!! nl2br(e($product->short_description)) !!}
                    </div>
                </div>
            @endif


            <form action="{{ route('cart.add') }}" method="post" class="row g-2 align-items-end mb-3" id="productAddForm">
                @csrf
                @if($defaultVariant)
                    <input type="hidden" name="product_variant_id" id="mainVariantId" value="{{ $defaultVariant->id }}">
                @endif
                @if($pdpColorFirst)
                    <div class="col-12 mb-2">
                        <div class="pro-pdp-size-head">
                            <span class="pro-pdp-size-head__title">{{ __('Select color') }}</span>
                        </div>
                        <div class="pro-pdp-sizes pro-pdp-sizes--colors" role="group" aria-label="{{ __('Color') }}" id="pdpColorGroup">
                            @foreach($colorValues as $c)
                                @php
                                    $repVar = $activeVariants->first(fn ($v) => $v->color === $c);
                                    $swatch = $repVar?->variantImageUrl() ?? $pdpMain;
                                @endphp
                                <button type="button"
                                    class="pro-pdp-color-pill {{ $loop->first ? 'is-active' : '' }}"
                                    data-color="{{ $c }}"
                                    aria-pressed="{{ $loop->first ? 'true' : 'false' }}"
                                    title="{{ $c }}">
                                    <span class="pro-pdp-color-pill__swatch" style="background-image:url('{{ $swatch }}');"></span>
                                    <span class="pro-pdp-color-pill__label">{{ $c }}</span>
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($showSizeOnPdp)
                    <div class="col-12 mb-1">
                        <div class="pro-pdp-size-head">
                            <span class="pro-pdp-size-head__title">{{ __('Select size') }}</span>
                            <a href="#" class="pro-pdp-size-chart" onclick="return false;">{{ __('Size chart') }} &gt;</a>
                        </div>
                        <div class="pro-pdp-sizes" role="group" aria-label="{{ __('Size') }}" id="pdpSizeGroup">
                            @foreach($sizedVariants as $v)
                                @php
                                    $showPill = ! $pdpColorFirst || ! filled($v->color) || ($defaultVariant && $v->color === $defaultVariant->color);
                                    $pillBuyable = $v->isBuyable();
                                @endphp
                                <button type="button"
                                    class="pro-pdp-size-pill {{ ! $pillBuyable ? 'is-disabled' : '' }} @if($pdpColorFirst && ! $showPill) d-none @endif"
                                    data-variant-id="{{ $v->id }}"
                                    data-color="{{ $v->color ?? '' }}"
                                    data-size="{{ $v->size ?? '' }}"
                                    @if(! $pillBuyable) disabled @endif>{{ $v->size }}</button>
                            @endforeach
                        </div>
                    </div>
                @endif
                <div class="col-md-3 col-lg-2">
                    <label class="form-label">Qty</label>
                    <input type="number" name="qty" id="pdpQtyInput" value="1" min="1" class="form-control">
                </div>
                <div class="col-12 col-md-auto d-flex flex-wrap gap-2">
                    <button class="zm-btn zm-btn-primary pro-pdp-add-cart" type="submit" id="pdpAddCartBtn">{{ __('Add to cart') }}</button>
                    <button class="zm-btn zm-btn-ghost pro-pdp-buy-now" type="submit" name="buy_now" value="1" id="pdpBuyNowBtn">{{ __('Buy now') }}</button>
                </div>
            </form>

            @auth
                <form action="{{ route('wishlist.store') }}" method="post" class="d-inline">@csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button class="zm-btn zm-btn-ghost" type="submit"><i class="bi bi-heart"></i> Wishlist</button>
                </form>
            @endauth
        </div>
    </div>

    <section class="pro-pdp-details mt-5 pt-2">
        <div class="pro-pdp-details-card">
            <ul class="nav pro-pdp-tabs" id="pdpDetailTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active pro-pdp-tabs__btn" id="pdp-tab-desc-btn" data-bs-toggle="tab" data-bs-target="#pdp-tab-desc" type="button" role="tab" aria-controls="pdp-tab-desc" aria-selected="true">{{ __('Description') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link pro-pdp-tabs__btn" id="pdp-tab-rev-btn" data-bs-toggle="tab" data-bs-target="#pdp-tab-rev" type="button" role="tab" aria-controls="pdp-tab-rev" aria-selected="false">{{ __('Review') }}</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link pro-pdp-tabs__btn" id="pdp-tab-qa-btn" data-bs-toggle="tab" data-bs-target="#pdp-tab-qa" type="button" role="tab" aria-controls="pdp-tab-qa" aria-selected="false">{{ __('Q & A') }}</button>
                </li>
            </ul>
            <div class="tab-content pro-pdp-tab-content" id="pdpDetailTabsContent">
            <div class="tab-pane fade show active" id="pdp-tab-desc" role="tabpanel" aria-labelledby="pdp-tab-desc-btn" tabindex="0">
                @if(filled($product->description))
                    <div class="pro-pdp-long-desc text-body lh-lg">{!! nl2br(e($product->description)) !!}</div>
                @else
                    <p class="text-muted mb-0">{{ __('More details will be added soon.') }}</p>
                @endif
            </div>
            <div class="tab-pane fade" id="pdp-tab-rev" role="tabpanel" aria-labelledby="pdp-tab-rev-btn" tabindex="0">
                @auth
                    <form action="{{ route('reviews.store', $product) }}" method="post" class="mb-4">
                        @csrf
                        <div class="row g-2">
                            <div class="col-md-2 d-flex align-items-center">
                                <div class="d-flex align-items-center gap-1" id="pdpReviewStars" style="height: 38px;">
                                    <input type="hidden" name="rating" id="pdpReviewRatingInput" value="5" required>
                                    <i class="bi bi-star-fill pdp-review-star" data-value="1" title="1 Star"></i>
                                    <i class="bi bi-star-fill pdp-review-star" data-value="2" title="2 Stars"></i>
                                    <i class="bi bi-star-fill pdp-review-star" data-value="3" title="3 Stars"></i>
                                    <i class="bi bi-star-fill pdp-review-star" data-value="4" title="4 Stars"></i>
                                    <i class="bi bi-star-fill pdp-review-star" data-value="5" title="5 Stars"></i>
                                </div>
                            </div>
                            <div class="col-md-4"><input type="text" name="title" class="form-control" placeholder="{{ __('Title') }}"></div>
                            <div class="col-md-6"><input type="text" name="body" class="form-control" placeholder="{{ __('Tell others') }}"></div>
                        </div>
                        <button class="zm-btn zm-btn-primary btn-sm mt-2" type="submit">{{ __('Submit review') }}</button>
                    </form>
                @endauth
                @forelse($product->reviews as $r)
                    <div class="border-bottom py-3">
                        <strong>{{ $r->user->name }}</strong> &mdash; <i class="bi bi-star-fill text-warning" aria-hidden="true"></i> {{ $r->rating }}
                        <div class="small text-muted">{{ $r->title }}</div>
                        <div>{{ $r->body }}</div>
                    </div>
                @empty
                    <p class="text-muted mb-0">{{ __('No reviews yet. Be the first to review this product.') }}</p>
                @endforelse
            </div>
            <div class="tab-pane fade" id="pdp-tab-qa" role="tabpanel" aria-labelledby="pdp-tab-qa-btn" tabindex="0">
                <p class="text-muted mb-0">{{ __('Questions and answers will appear here. Chat with the seller for more help.') }}</p>
            </div>
            </div>
        </div>
    </section>

    @if($related->isNotEmpty())
        @push('head')
            <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css">
        @endpush
        <section class="zm-section-title mt-5"><h2>{{ __('Related products') }}</h2></section>
        <div class="pro-pdp-related-swiper position-relative">
            <div class="swiper pro-product-swiper" id="proPdpRelatedSwiper">
                <div class="swiper-wrapper">
                    @foreach($related as $rp)
                        <div class="swiper-slide">
                            <div class="swiper-slide__inner h-100">@include('market.partials.product-card', ['product' => $rp])</div>
                        </div>
                    @endforeach
                </div>
                <button type="button" class="swiper-button-prev pro-product-swiper__nav" aria-label="{{ __('Previous products') }}"></button>
                <button type="button" class="swiper-button-next pro-product-swiper__nav" aria-label="{{ __('Next products') }}"></button>
            </div>
        </div>
    @endif

    <div class="pro-sticky-buy d-lg-none">
        <div class="min-w-0 flex-grow-1">
            <div class="small text-truncate fw-semibold">{{ $product->name }}</div>
            <div class="fw-bold text-primary" id="stickyPriceLabel">{!! '&#8377;' !!}{{ number_format($fp, 0) }}</div>
        </div>
        <form action="{{ route('cart.add') }}" method="post" class="d-flex align-items-center gap-2 ms-auto" id="stickyAddForm">
            @csrf
            <input type="hidden" name="product_variant_id" value="{{ $defaultVariant?->id }}" id="stickyVariantId">
            <input type="hidden" name="qty" value="1" id="stickyQtyHidden">
            <button type="submit" class="btn btn-primary rounded-pill pro-pdp-sticky-add" id="stickyAddBtn" @if($activeVariants->isEmpty() || $activeVariants->every(fn ($v) => ! $v->isBuyable())) disabled @endif>{{ __('Add to cart') }}</button>
            <button type="submit" name="buy_now" value="1" class="btn btn-outline-primary rounded-pill pro-pdp-sticky-buy" id="stickyBuyBtn" @if($activeVariants->isEmpty() || $activeVariants->every(fn ($v) => ! $v->isBuyable())) disabled @endif>{{ __('Buy now') }}</button>
        </form>
    </div>
    @push('scripts')
    <script>
    (function () {
        var mainImg = document.getElementById('mainImg');
        var VARIANTS = @json($variantPayload);
        var galleryDefault = @json($pdpMain);
        var comparePrice = @json($product->compare_price ? (float) $product->compare_price : null);
        var msgOut = @json(__('Out of stock'));
        var msgLeft = @json(__('Only :n left in stock'));

        document.querySelectorAll('.pro-pdp-gallery__cell').forEach(function (cell) {
            cell.addEventListener('click', function () {
                var u = cell.getAttribute('data-img');
                if (u && mainImg) mainImg.src = u;
                document.querySelectorAll('.pro-pdp-gallery__cell').forEach(function (c) {
                    c.classList.toggle('is-active', c.getAttribute('data-img') === u);
                });
            });
        });

        var variantInput = document.getElementById('mainVariantId');
        var stickyVid = document.getElementById('stickyVariantId');
        var stickyPrice = document.getElementById('stickyPriceLabel');
        var pdpPriceNow = document.getElementById('pdpPriceNow');
        var pdpPriceWas = document.getElementById('pdpPriceWas');
        var pdpStockMsg = document.getElementById('pdpStockMsg');
        var pdpQty = document.getElementById('pdpQtyInput');
        var stickyQtyHidden = document.getElementById('stickyQtyHidden');
        var colorGroup = document.getElementById('pdpColorGroup');
        var sizeGroup = document.getElementById('pdpSizeGroup');

        function findVariant(id) {
            return VARIANTS.find(function (v) { return String(v.id) === String(id); });
        }

        function normalizeColor(c) {
            return String(c || '').trim().toLowerCase();
        }

        function findVariantByColorSize(color, size) {
            var nc = normalizeColor(color);
            var ns = String(size || '').trim();
            return VARIANTS.find(function (v) {
                return normalizeColor(v.color) === nc && String(v.size || '').trim() === ns;
            });
        }

        function findVariantByColor(color) {
            var nc = normalizeColor(color);
            return VARIANTS.find(function (v) { return normalizeColor(v.color) === nc; });
        }

        function swatchImageUrl(pill) {
            var sw = pill && pill.querySelector('.pro-pdp-color-pill__swatch');
            if (!sw) return '';
            var bg = sw.style.backgroundImage || window.getComputedStyle(sw).backgroundImage || '';
            var m = bg.match(/url\(["']?([^"')]+)["']?\)/);
            return m ? m[1] : '';
        }

        function setMainImageFromVariant(v) {
            if (!mainImg) return;
            if (v && v.image) {
                mainImg.src = v.image;
            } else {
                mainImg.src = galleryDefault;
            }
        }

        function updateStockUi(v) {
            if (!pdpStockMsg) return;
            if (!v) {
                pdpStockMsg.classList.add('d-none');
                return;
            }
            pdpStockMsg.classList.remove('d-none');
            if (!v.buyable) {
                pdpStockMsg.textContent = msgOut;
                return;
            }
            if (v.stock <= 5 && v.stock > 0) {
                pdpStockMsg.textContent = msgLeft.replace(':n', String(v.stock));
                return;
            }
            pdpStockMsg.classList.add('d-none');
        }

        function updatePriceUi(v) {
            if (!v) return;
            var eff = Number(v.effectivePrice);
            if (pdpPriceNow) pdpPriceNow.textContent = '\u20B9' + eff.toLocaleString();
            if (stickyPrice) stickyPrice.textContent = '\u20B9' + eff.toLocaleString();
            if (pdpPriceWas && comparePrice != null && comparePrice > eff) {
                pdpPriceWas.textContent = '\u20B9' + comparePrice.toLocaleString();
                pdpPriceWas.classList.remove('d-none');
            } else if (pdpPriceWas) {
                pdpPriceWas.classList.add('d-none');
            }
        }

        function syncQtyCap(v) {
            if (!pdpQty || !v) return;
            var max = v.buyable ? Math.max(1, parseInt(v.stock, 10) || 0) : 1;
            pdpQty.max = String(max);
            var q = parseInt(pdpQty.value, 10) || 1;
            if (q > max) pdpQty.value = String(max);
            if (stickyQtyHidden) stickyQtyHidden.value = pdpQty.value;
        }

        function setSelectedVariant(id) {
            if (!variantInput) return;
            variantInput.value = id;
            if (stickyVid) stickyVid.value = id;
            var v = findVariant(id);
            updatePriceUi(v);
            setMainImageFromVariant(v);
            updateStockUi(v);
            syncQtyCap(v);
            document.querySelectorAll('.pro-pdp-size-pill').forEach(function (b) {
                b.classList.toggle('is-active', b.getAttribute('data-variant-id') === String(id));
            });
            var addOk = v && v.buyable;
            ['stickyAddBtn', 'stickyBuyBtn', 'pdpAddCartBtn', 'pdpBuyNowBtn'].forEach(function (id) {
                var el = document.getElementById(id);
                if (el) el.disabled = !addOk;
            });
        }

        function selectedColor() {
            var active = colorGroup && colorGroup.querySelector('.pro-pdp-color-pill.is-active');
            return active ? active.getAttribute('data-color') : '';
        }

        /** Color-only products (e.g. Saree): no size row &mdash; pick variant by selected color. */
        function applyColorOnlySelection() {
            if (!colorGroup || sizeGroup) return;
            var c = selectedColor();
            if (!c) return;
            var match = findVariantByColor(c);
            if (match) setSelectedVariant(match.id);
        }

        function applyColorFilter() {
            if (!sizeGroup || !colorGroup) return;
            var c = selectedColor();
            var firstBuyableId = null;
            var firstVisibleId = null;
            sizeGroup.querySelectorAll('.pro-pdp-size-pill').forEach(function (btn) {
                var size = btn.getAttribute('data-size') || '';
                var match = findVariantByColorSize(c, size);
                if (match) {
                    btn.classList.remove('d-none');
                    btn.setAttribute('data-variant-id', String(match.id));
                    btn.setAttribute('data-color', match.color || '');
                    var buy = match.buyable;
                    btn.disabled = !buy;
                    btn.classList.toggle('is-disabled', !buy);
                    if (!firstVisibleId) firstVisibleId = String(match.id);
                    if (buy && !firstBuyableId) firstBuyableId = String(match.id);
                } else {
                    btn.classList.add('d-none');
                    btn.disabled = true;
                    btn.classList.add('is-disabled');
                }
            });
            if (firstBuyableId) {
                setSelectedVariant(firstBuyableId);
            } else if (firstVisibleId) {
                setSelectedVariant(firstVisibleId);
            } else {
                var fallback = findVariantByColor(c);
                if (fallback) setSelectedVariant(fallback.id);
            }
        }

        if (colorGroup) {
            colorGroup.querySelectorAll('.pro-pdp-color-pill').forEach(function (pill) {
                pill.addEventListener('click', function () {
                    colorGroup.querySelectorAll('.pro-pdp-color-pill').forEach(function (p) {
                        p.classList.remove('is-active');
                        p.setAttribute('aria-pressed', 'false');
                    });
                    pill.classList.add('is-active');
                    pill.setAttribute('aria-pressed', 'true');
                    var quickImg = swatchImageUrl(pill);
                    if (quickImg && mainImg) mainImg.src = quickImg;
                    if (sizeGroup) applyColorFilter();
                    else applyColorOnlySelection();
                });
            });
        }

        if (variantInput && stickyVid) {
            document.querySelectorAll('.pro-pdp-size-pill').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    if (btn.disabled) return;
                    var vid = btn.getAttribute('data-variant-id');
                    if (!vid) return;
                    setSelectedVariant(vid);
                });
            });

            if (colorGroup) {
                if (sizeGroup) applyColorFilter();
                else applyColorOnlySelection();
            } else {
                var id0 = variantInput.value;
                setSelectedVariant(id0);
            }
        }

        if (pdpQty && stickyQtyHidden) {
            pdpQty.addEventListener('input', function () {
                stickyQtyHidden.value = pdpQty.value;
            });
        }
    })();
    </script>
    <script>
    (function () {
        var ratingInput = document.getElementById('pdpReviewRatingInput');
        var stars = document.querySelectorAll('.pdp-review-star');
        var container = document.getElementById('pdpReviewStars');
        if (!ratingInput || !stars.length) return;

        var selectedRating = parseInt(ratingInput.value) || 5;

        function updateStars(rating) {
            stars.forEach(function (star) {
                var val = parseInt(star.getAttribute('data-value'));
                if (val <= rating) {
                    star.classList.remove('bi-star');
                    star.classList.add('bi-star-fill', 'text-warning');
                } else {
                    star.classList.remove('bi-star-fill', 'text-warning');
                    star.classList.add('bi-star');
                }
            });
        }

        stars.forEach(function (star) {
            star.addEventListener('click', function () {
                var val = parseInt(star.getAttribute('data-value'));
                selectedRating = val;
                ratingInput.value = val;
                updateStars(val);
            });

            star.addEventListener('mouseenter', function () {
                var val = parseInt(star.getAttribute('data-value'));
                updateStars(val);
            });
        });

        if (container) {
            container.addEventListener('mouseleave', function () {
                updateStars(selectedRating);
            });
        }

        // Initial setup
        updateStars(selectedRating);
    })();
    </script>
    @if(!empty($recentPurchaseFeed))
    <script>
    (function () {
        var feed = @json($recentPurchaseFeed);
        var toast = document.getElementById('proRecentPurchaseToast');
        if (!toast || !feed || !feed.length) return;

        var storageKey = 'pro_recent_purchase_toast_dismiss_v3';
        if (sessionStorage.getItem(storageKey) === '1') return;

        var link = toast.querySelector('.pro-recent-toast__link');
        var img = toast.querySelector('.pro-recent-toast__img');
        var nameEl = toast.querySelector('.pro-recent-toast__name');
        var agoEl = toast.querySelector('.pro-recent-toast__ago');
        var closeBtn = toast.querySelector('.pro-recent-toast__close');
        var idx = 0;
        var closeAfterTimer;
        var nextTickTimer;
        var hideTimer;
        var VISIBLE_MS = 30000;
        var INTERVAL_MS = 60000;

        function showEntry(i) {
            var e = feed[i];
            if (!e) return;
            link.href = e.url || '#';
            img.src = e.image || '';
            img.alt = e.name || '';
            nameEl.textContent = e.name || '';
            agoEl.textContent = e.ago || '';
        }

        function openToast() {
            toast.style.display = 'block';
            toast.setAttribute('aria-hidden', 'false');
            requestAnimationFrame(function () {
                requestAnimationFrame(function () {
                    toast.classList.add('is-visible');
                });
            });
        }

        function closeToast() {
            toast.classList.remove('is-visible');
            clearTimeout(hideTimer);
            hideTimer = setTimeout(function () {
                toast.style.display = 'none';
                toast.setAttribute('aria-hidden', 'true');
            }, 340);
        }

        function tick() {
            showEntry(idx % feed.length);
            idx += 1;
            openToast();
            clearTimeout(closeAfterTimer);
            closeAfterTimer = setTimeout(function () {
                closeToast();
                clearTimeout(nextTickTimer);
                nextTickTimer = setTimeout(tick, INTERVAL_MS);
            }, VISIBLE_MS);
        }

        closeBtn.addEventListener('click', function () {
            clearTimeout(closeAfterTimer);
            clearTimeout(nextTickTimer);
            clearTimeout(hideTimer);
            sessionStorage.setItem(storageKey, '1');
            toast.classList.remove('is-visible');
            toast.style.display = 'none';
            toast.setAttribute('aria-hidden', 'true');
        });

        setTimeout(tick, 2000);
    })();
    </script>
    @endif
    @if($related->isNotEmpty())
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script>
    (function () {
        if (typeof Swiper === 'undefined') return;
        function swiperOptions(el) {
            return {
                slidesPerView: 1,
                spaceBetween: 12,
                watchOverflow: true,
                observer: true,
                observeParents: true,
                observeSlideChildren: true,
                watchSlidesProgress: true,
                resizeObserver: true,
                centeredSlides: false,
                roundLengths: true,
                navigation: {
                    nextEl: el.querySelector('.swiper-button-next'),
                    prevEl: el.querySelector('.swiper-button-prev'),
                },
                breakpoints: {
                    576: { slidesPerView: 'auto', spaceBetween: 16 },
                    768: { slidesPerView: 'auto', spaceBetween: 16 },
                    992: { slidesPerView: 'auto', spaceBetween: 16 },
                },
            };
        }
        function mountPdpRelated() {
            var el = document.getElementById('proPdpRelatedSwiper');
            if (!el || el.querySelectorAll('.swiper-slide').length === 0) return;
            if (el.dataset.swiperReady === '1') {
                if (el.swiper) {
                    requestAnimationFrame(function () {
                        el.swiper.update();
                        if (el.swiper.navigation && el.swiper.navigation.update) el.swiper.navigation.update();
                    });
                }
                return;
            }
            el.dataset.swiperReady = '1';
            new Swiper(el, swiperOptions(el));
        }
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', mountPdpRelated);
        } else {
            mountPdpRelated();
        }
    })();
    </script>
    @endif
    @endpush
@endsection

@if(!empty($recentPurchaseFeed))
@push('body_end')
<div id="proRecentPurchaseToast" class="pro-recent-toast" role="status" aria-live="polite" aria-atomic="true" aria-hidden="true" data-pro-recent-toast>
    <button type="button" class="pro-recent-toast__close" aria-label="{{ __('Close') }}">&times;</button>
    <a href="#" class="pro-recent-toast__link text-decoration-none text-reset">
        <span class="pro-recent-toast__img-wrap flex-shrink-0">
            <img src="" alt="" class="pro-recent-toast__img" width="56" height="56" loading="lazy">
        </span>
        <span class="pro-recent-toast__body">
            <span class="pro-recent-toast__head d-block">{{ __('Someone recently purchased') }}</span>
            <span class="pro-recent-toast__name"></span>
            <span class="pro-recent-toast__ago d-block"></span>
        </span>
    </a>
</div>
@endpush
@endif

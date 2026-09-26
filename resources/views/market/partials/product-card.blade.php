@if(empty($product))
    {{-- Skip broken/deleted product references --}}
@else
@php
    $listing = !empty($listing);
    [$url1, $url2] = $product->cardImageUrls();
    $v = $product->variants->sortBy('id')->first(fn ($x) => $x->isBuyable())
        ?? $product->variants->sortBy('id')->first();
    $price = $v ? $v->effectivePrice() : (float) ($product->base_price ?? 0);
    $flash = $product->flashSale;
    $vendorName = $product->vendor->shop_name ?? optional($product->menuItem)->title ?? __('Shop');
    $compare = $product->compare_price ? (float) $product->compare_price : null;
    $pctOff = ($compare && $compare > $price) ? (int) round(100 - ($price / $compare) * 100) : null;
    $rating = (float) ($product->rating_avg ?? 0);
    $ratingDisplay = $rating > 0 ? number_format($rating, 1) : '0';
    $starN = $rating > 0 ? (int) min(5, max(1, round($rating))) : 5;
    $variantColors = $product->variants->pluck('color')->filter()->unique()->values();
    $swatchMap = [
        'black' => '#1a1a1a', 'white' => '#f3f4f6', 'red' => '#dc2626', 'blue' => '#2563eb',
        'green' => '#16a34a', 'yellow' => '#eab308', 'orange' => '#ea580c', 'pink' => '#db2777',
        'purple' => '#9333ea', 'beige' => '#d4c4a8', 'brown' => '#78350f', 'grey' => '#9ca3af', 'gray' => '#9ca3af',
        'navy' => '#1e3a5f', 'maroon' => '#7f1d1d', 'gold' => '#ca8a04', 'silver' => '#cbd5e1',
    ];
    $hasStock = $product->variants->contains(fn ($x) => $x->isBuyable());
    $wishlistIds = array_map('intval', (array) ($layoutWishlistProductIds ?? []));
    $isWishlisted = auth()->check() && in_array((int) $product->id, $wishlistIds, true);
    $wishIcon = $isWishlisted ? 'bi-heart-fill' : 'bi-heart';
    $wishTitle = $isWishlisted ? __('Remove from wishlist') : __('Wishlist');
    $qvDescription = \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', strip_tags((string) ($product->short_description ?: $product->description)))), 220);
    $qvImages = [];
    foreach ($product->images as $im) {
        $u = \App\Models\Product::publicImageUrl($im->path);
        if ($u) {
            $qvImages[] = \App\Support\OptimizedImage::url($u, 600);
        }
    }
    if ($qvImages === []) {
        $qvImages[] = \App\Support\OptimizedImage::url($url1, 600) ?: $url1;
    }
    $activeVariants = $product->variants->where('status', \App\Models\ProductVariant::STATUS_ACTIVE)->sortBy('id')->values();
    $hasMultipleVariants = $activeVariants->count() > 1;
    $variantPayload = [];
    if ($hasMultipleVariants) {
        $variantPayload = $activeVariants->map(fn ($vx) => [
            'id' => $vx->id,
            'color' => (string) ($vx->color ?? ''),
            'size' => (string) ($vx->size ?? ''),
            'stock' => (int) $vx->stock_qty,
            'effectivePrice' => $vx->effectivePrice(),
            'unitPrice' => $vx->unitPrice(),
            'image' => $vx->variantImageUrl(),
            'buyable' => $vx->isBuyable(),
        ])->values()->all();
    }
@endphp
<article class="zm-pro-card h-100 {{ $listing ? 'zm-pro-card--listing' : '' }}">
    <div class="zm-pro-card__media">
        @if(!$hasStock)
            <span class="zm-pro-card__badge bg-danger @if($listing) zm-pro-card__badge--corner @endif">{{ __('Out of stock') }}</span>
        @elseif(!$listing)
            @if($product->is_featured)
                <span class="zm-pro-card__badge zm-pro-card__badge--feat">{{ __('Featured') }}</span>
            @elseif($flash)
                <span class="zm-pro-card__badge">{{ __('Sale') }}</span>
            @endif
        @elseif($product->is_featured)
            <span class="zm-pro-card__badge zm-pro-card__badge--feat zm-pro-card__badge--corner">{{ __('Featured') }}</span>
        @elseif($flash)
            <span class="zm-pro-card__badge zm-pro-card__badge--corner">{{ __('Sale') }}</span>
        @endif
        <a href="{{ route('product.show', $product) }}" class="zm-pro-card__link">
            <img src="{{ \App\Support\OptimizedImage::url($url1, 420) }}" class="zm-pro-card__img zm-pro-card__img--primary" alt="{{ $product->name }}" title="{{ $product->name }}" loading="lazy" fetchpriority="low" width="420" height="560" decoding="async" onerror="this.onerror=null;this.src='{{ $product->namedPlaceholderUrl(false) }}';">
            @if($url2 && $url2 !== $url1)
            <img src="data:image/gif;base64,R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7" data-hover-src="{{ \App\Support\OptimizedImage::url($url2, 420) }}" class="zm-pro-card__img zm-pro-card__img--secondary" alt="{{ $product->name }} — alternate view" title="{{ $product->name }}" width="420" height="560" decoding="async" onerror="this.onerror=null;this.style.display='none';">
            @endif
        </a>
        @if($listing)
            <div class="zm-pro-card__img-rate" title="{{ __('Average rating') }}">
                <i class="bi bi-star-fill" aria-hidden="true"></i>
                <span>{{ $ratingDisplay }}</span>
            </div>
        @endif
        <div class="zm-pro-card__wish">
            @auth
                <form action="{{ route('wishlist.store') }}" method="post" class="d-inline js-ajax-wishlist">@csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                    <button type="submit" class="zm-pro-icon-btn zm-pro-icon-btn--round {{ $isWishlisted ? 'is-wishlisted' : '' }}" title="{{ $wishTitle }}" aria-pressed="{{ $isWishlisted ? 'true' : 'false' }}"><i class="bi {{ $wishIcon }}"></i></button>
                </form>
            @else
                <a href="{{ route('login') }}" class="zm-pro-icon-btn zm-pro-icon-btn--round" title="{{ __('Wishlist') }}"><i class="bi bi-heart"></i></a>
            @endauth
        </div>
        <div class="zm-pro-card__actions {{ $listing ? 'zm-pro-card__actions--listing' : '' }}">
            <div class="zm-pro-card__hover-actions">
                <button type="button" class="zm-pro-icon-btn {{ $listing ? 'zm-pro-icon-btn--round' : '' }} js-quick-view" title="{{ __('Quick view') }}" data-bs-toggle="modal" data-bs-target="#quickViewModal"
                    data-qv-name="{{ e($product->name) }}"
                    data-qv-brand="{{ e($vendorName) }}"
                    data-qv-desc="{{ e($qvDescription) }}"
                    data-qv-price="{{ $price }}"
                    data-qv-compare="{{ $compare && $compare > $price ? $compare : '' }}"
                    data-qv-img="{{ e($url1) }}"
                    data-qv-images="{{ json_encode($qvImages) }}"
                    data-qv-url="{{ route('product.show', $product) }}"
                    data-qv-variant="{{ $v?->id }}"
                    data-qv-variants="{{ json_encode($variantPayload) }}"
                    data-qv-label="{{ $product->variant_label ?: __('Size') }}">
                    <i class="bi bi-eye"></i>
                </button>
            </div>
        </div>
    </div>
    <div class="zm-pro-card__body">
        @if(!$listing)
            <div class="zm-pro-card__rating" aria-hidden="true">
                @for($i = 1; $i <= 5; $i++)
                    <i class="bi {{ $i <= $starN ? 'bi-star-fill' : 'bi-star' }}"></i>
                @endfor
                <span class="zm-pro-card__rating-num">({{ $ratingDisplay }})</span>
            </div>
        @endif
        <div class="zm-pro-card__brand-row">
            <span class="zm-pro-card__brand-name">{{ $vendorName }}</span>
            @if($variantColors->isNotEmpty())
                <span class="zm-pro-card__swatches">
                    @foreach($variantColors->take(3) as $cname)
                        @php $key = strtolower(trim($cname)); $hex = $swatchMap[$key] ?? '#d1d5db'; @endphp
                        <span class="zm-pro-card__swatch" style="--swatch: {{ $hex }}" title="{{ $cname }}"></span>
                    @endforeach
                    @if($variantColors->count() > 3)
                        <span class="zm-pro-card__swatch-more">+{{ $variantColors->count() - 3 }}</span>
                    @endif
                </span>
            @endif
        </div>
        <a href="{{ route('product.show', $product) }}" class="zm-pro-card__title {{ $listing ? 'zm-pro-card__title--listing' : '' }}">{{ $product->name }}</a>
        <div class="zm-pro-card__price {{ $listing ? 'zm-pro-card__price--listing' : '' }}">
            <span class="zm-pro-card__price-now js-card-price-now">₹{{ number_format($price, 0) }}</span>
            <del class="js-card-price-compare" @if(!($compare && $compare > $price)) hidden @endif>₹{{ number_format((float) ($compare ?? 0), 0) }}</del>
            <span class="zm-pro-card__off js-card-price-off" @if(!$pctOff) hidden @endif>{{ $pctOff ?: 0 }}% {{ __('Off') }}</span>
        </div>
        @if($listing && ($flash || ($pctOff && $pctOff > 0)))
            <div class="zm-pro-card__offer-strip">
                <i class="bi bi-clock-history" aria-hidden="true"></i>
                @if($flash)
                    <span>{{ __('Limited time offer') }}: ₹{{ number_format((float) $flash->sale_price, 0) }}</span>
                @else
                    <span>{{ __('Limited time offer') }}: {{ $pctOff }}% {{ __('off') }}</span>
                @endif
            </div>
        @endif
        @if($v)
            <div class="js-cart-add-container" data-variant-id="{{ $v->id }}" data-buyable="{{ $v->isBuyable() ? '1' : '0' }}">
                @php
                    $optionLabel = $product->variant_label ?: __('pack');
                @endphp
                @if($hasMultipleVariants)
                    <div class="zm-pro-card__options" role="group" aria-label="{{ __('Select :label', ['label' => $optionLabel]) }}">
                        @foreach($activeVariants as $vx)
                            @php
                                $optBuyable = $vx->isBuyable();
                                $optCompare = $vx->unitPrice() > $vx->effectivePrice() ? $vx->unitPrice() : null;
                            @endphp
                            <button type="button"
                                class="zm-pro-card__opt js-card-variant-pill {{ $vx->id === $v->id ? 'is-active' : '' }} {{ $optBuyable ? '' : 'is-disabled' }}"
                                data-id="{{ $vx->id }}"
                                data-price="{{ $vx->effectivePrice() }}"
                                data-compare="{{ $optCompare ?: '' }}"
                                data-buyable="{{ $optBuyable ? '1' : '0' }}"
                                data-image="{{ $vx->variantImageUrl() }}"
                                @if(! $optBuyable) disabled aria-disabled="true" title="{{ __('Out of stock') }}" @endif>
                                {{ $vx->size ?: $vx->color ?: $vx->label() }}
                            </button>
                        @endforeach
                    </div>
                @endif
                <form action="{{ route('cart.add') }}" method="post" class="zm-pro-add-form d-flex gap-2 js-ajax-add-to-cart @if(!$v->isBuyable()) d-none @endif">
                    @csrf
                    <input type="hidden" name="product_variant_id" value="{{ $v->id }}">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Add to cart') }}</button>
                    <button type="submit" name="buy_now" value="1" class="btn btn-outline-primary w-100">{{ __('Buy now') }}</button>
                </form>
                @if(!$v->isBuyable())
                    <div class="js-pdp-oos-pill w-100">
                        <button type="button" class="btn btn-secondary w-100" disabled>{{ __('Out of stock') }}</button>
                    </div>
                @endif
            </div>
        @endif
    </div>
</article>
@endif

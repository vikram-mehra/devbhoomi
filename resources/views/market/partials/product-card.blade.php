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
    $ratingDisplay = $rating > 0 ? number_format($rating, 1) : '—';
    $starN = $rating > 0 ? (int) min(5, max(1, round($rating))) : 5;
    $variantColors = $product->variants->pluck('color')->filter()->unique()->values();
    $swatchMap = [
        'black' => '#1a1a1a', 'white' => '#f3f4f6', 'red' => '#dc2626', 'blue' => '#2563eb',
        'green' => '#16a34a', 'yellow' => '#eab308', 'orange' => '#ea580c', 'pink' => '#db2777',
        'purple' => '#9333ea', 'beige' => '#d4c4a8', 'brown' => '#78350f', 'grey' => '#9ca3af', 'gray' => '#9ca3af',
        'navy' => '#1e3a5f', 'maroon' => '#7f1d1d', 'gold' => '#ca8a04', 'silver' => '#cbd5e1',
    ];
@endphp
<article class="zm-pro-card h-100 {{ $listing ? 'zm-pro-card--listing' : '' }}">
    <div class="zm-pro-card__media">
        @if(!$listing)
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
            <img src="{{ $url1 }}" class="zm-pro-card__img zm-pro-card__img--primary" alt="{{ $product->name }}" title="{{ $product->name }}" loading="lazy" width="520" height="390" decoding="async" onerror="this.onerror=null;this.src='{{ $product->namedPlaceholderUrl(false) }}';">
            <img src="{{ $url2 }}" class="zm-pro-card__img zm-pro-card__img--secondary" alt="{{ $product->name }} — alternate view" title="{{ $product->name }}" loading="lazy" width="520" height="390" decoding="async" onerror="this.onerror=null;this.style.display='none';">
        </a>
        @if($listing)
            <div class="zm-pro-card__img-rate" title="{{ __('Average rating') }}">
                <i class="bi bi-star-fill" aria-hidden="true"></i>
                <span>{{ $ratingDisplay }}</span>
            </div>
        @endif
        <div class="zm-pro-card__actions {{ $listing ? 'zm-pro-card__actions--listing' : '' }}">
            @if($listing)
                <div class="zm-pro-card__wish">
                    @auth
                        <form action="{{ route('wishlist.store') }}" method="post" class="d-inline">@csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="zm-pro-icon-btn zm-pro-icon-btn--round" title="{{ __('Wishlist') }}"><i class="bi bi-heart"></i></button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="zm-pro-icon-btn zm-pro-icon-btn--round" title="{{ __('Wishlist') }}"><i class="bi bi-heart"></i></a>
                    @endauth
                </div>
            @endif
            <div class="zm-pro-card__hover-actions">
                <button type="button" class="zm-pro-icon-btn {{ $listing ? 'zm-pro-icon-btn--round' : '' }} js-quick-view" title="{{ __('Quick view') }}" data-bs-toggle="modal" data-bs-target="#quickViewModal"
                    data-qv-name="{{ e($product->name) }}"
                    data-qv-brand="{{ e($vendorName) }}"
                    data-qv-price="{{ $price }}"
                    data-qv-compare="{{ $compare && $compare > $price ? $compare : '' }}"
                    data-qv-img="{{ e($url1) }}"
                    data-qv-url="{{ route('product.show', $product) }}"
                    data-qv-variant="{{ $v?->id }}">
                    <i class="bi bi-eye"></i>
                </button>
                @if(!$listing)
                    @auth
                        <form action="{{ route('wishlist.store') }}" method="post" class="d-inline">@csrf
                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                            <button type="submit" class="zm-pro-icon-btn" title="{{ __('Wishlist') }}"><i class="bi bi-heart"></i></button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="zm-pro-icon-btn" title="{{ __('Wishlist') }}"><i class="bi bi-heart"></i></a>
                    @endauth
                @endif
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
            <span class="zm-pro-card__price-now">₹{{ number_format($price, 0) }}</span>
            @if($compare && $compare > $price)
                <del>₹{{ number_format($compare, 0) }}</del>
                @if($pctOff)<span class="zm-pro-card__off">{{ $pctOff }}% {{ __('Off') }}</span>@endif
            @endif
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
            <div class="js-cart-add-container mt-2" data-variant-id="{{ $v->id }}">
                @php
                    $cartItem = ($layoutCartItems ?? collect())->firstWhere('product_variant_id', $v->id);
                @endphp
                <form action="{{ route('cart.add') }}" method="post" class="zm-pro-add-form d-flex gap-2 js-ajax-add-to-cart @if($cartItem) d-none @endif">
                    @csrf
                    <input type="hidden" name="product_variant_id" value="{{ $v->id }}">
                    <input type="hidden" name="qty" value="1">
                    <button type="submit" class="btn btn-primary w-100">{{ __('Add to cart') }}</button>
                    <button type="submit" name="buy_now" value="1" class="btn btn-outline-primary w-100">{{ __('Buy now') }}</button>
                </form>
                @if($cartItem)
                    <div class="js-qty-pill-selector d-flex gap-2 w-100" data-variant-id="{{ $v->id }}" data-item-id="{{ $cartItem->id }}">
                        <div class="d-flex align-items-center justify-content-between border rounded-pill bg-light px-2" style="height: 38px; width: 100%;">
                            <button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-minus" style="font-size: 1.1rem; line-height: 1; height: 100%; display: flex; align-items: center; justify-content: center; width: 30px;"><i class="bi bi-dash"></i></button>
                            <span class="fw-semibold js-selector-qty-val" style="font-size: 0.95rem; min-width: 24px; text-align: center;">{{ $cartItem->qty }}</span>
                            <button type="button" class="btn btn-sm p-0 border-0 text-primary js-selector-qty-plus" style="font-size: 1.1rem; line-height: 1; height: 100%; display: flex; align-items: center; justify-content: center; width: 30px;"><i class="bi bi-plus"></i></button>
                        </div>
                    </div>
                @endif
            </div>
        @endif
    </div>
</article>
@endif

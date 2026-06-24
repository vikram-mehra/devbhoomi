@forelse($layoutCartItems ?? [] as $citem)
    @php
        $v = $citem->variant;
        $p = $v->product;
        $im = $p->images->first();
        $cartThumb = \App\Models\Product::publicImageUrl($im?->path) ?? $p->namedPlaceholderUrl();
        $unit = $v->effectivePrice();
    @endphp
    <div class="pro-cart-item js-drawer-item-card" data-item-id="{{ $citem->id }}" data-unit-price="{{ $unit }}">
        <img src="{{ $cartThumb }}" alt="{{ $p->name }}" class="object-fit-cover flex-shrink-0" width="72" height="72" loading="lazy" decoding="async">
        <div class="flex-grow-1 min-w-0 d-flex flex-column justify-content-between">
            <div>
                <div class="d-flex justify-content-between align-items-start gap-2">
                    <a href="{{ route('product.show', $p) }}" class="pro-cart-item-title text-truncate d-block">{{ $p->name }}</a>
                    <button class="btn btn-link p-0 border-0 js-drawer-remove-btn" type="button" title="{{ __('Remove item') }}">
                        <i class="bi bi-trash3"></i>
                    </button>
                </div>
                @if($v->label() !== 'Default')
                    <div class="mt-1">
                        <span class="pro-cart-item-variant">{{ $v->label() }}</span>
                    </div>
                @endif
            </div>
            <div class="d-flex align-items-end justify-content-between gap-2 mt-2">
                <div class="pro-drawer-qty-container">
                    <button type="button" class="btn p-0 border-0 js-drawer-qty-minus" style="visibility: {{ $citem->qty < 2 ? 'hidden' : 'visible' }};"><i class="bi bi-dash"></i></button>
                    <span class="js-drawer-qty-val">{{ $citem->qty }}</span>
                    <button type="button" class="btn p-0 border-0 js-drawer-qty-plus"><i class="bi bi-plus"></i></button>
                </div>
                <div class="text-end">
                    @if($p->compare_price && (float) $p->compare_price > $unit)
                        <span class="text-muted text-decoration-line-through small me-1 js-drawer-item-compare" style="font-size: 0.8rem;" data-compare-unit="{{ $p->compare_price }}">₹{{ number_format($p->compare_price * $citem->qty, 0) }}</span>
                    @endif
                    <span class="pro-cart-item-total js-drawer-item-total">₹{{ number_format($unit * $citem->qty, 0) }}</span>
                    <div class="pro-cart-item-each">₹{{ number_format($unit, 0) }} {{ __('each') }}</div>
                </div>
            </div>
        </div>
    </div>
@empty
    <div class="text-center py-5" id="drawerCartEmptyState">
        <i class="bi bi-bag-x text-muted mb-2" style="font-size: 3rem; opacity: 0.4; display: block;"></i>
        <p class="text-muted small mb-0">{{ __('Your cart is empty.') }}</p>
    </div>
@endforelse

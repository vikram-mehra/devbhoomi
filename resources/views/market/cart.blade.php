@extends('layouts.market')

@section('title', 'Your cart')

@push('head')
<style>
/* Hide Chrome/Safari/Opera number input arrows */
input.js-cart-qty-input::-webkit-outer-spin-button,
input.js-cart-qty-input::-webkit-inner-spin-button {
    -webkit-appearance: none;
    margin: 0;
}
/* Hide Firefox number input arrows */
input.js-cart-qty-input {
    -moz-appearance: textfield;
}

.cart-item-img-container {
    width: 90px;
    height: 90px;
    background-color: #f8f9fa;
    border-radius: 12px;
    overflow: hidden;
    flex-shrink: 0;
}

.cart-item-img-container img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

/* Modern quantity input */
.qty-selector {
    border-radius: 20px;
    border: 1px solid var(--pro-border, #e8ebe6);
    background-color: #fcfdfc;
    display: inline-flex;
    align-items: center;
    padding: 2px 6px;
    height: 38px;
}

.qty-selector button {
    border: none;
    background: transparent;
    color: var(--pro-primary, #2d5a3d);
    padding: 0 8px;
    font-size: 1.1rem;
    line-height: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    height: 100%;
    transition: opacity 0.2s ease, transform 0.1s ease;
}

.qty-selector button:active {
    transform: scale(0.85);
}

.qty-selector input {
    width: 38px;
    text-align: center;
    border: none;
    background: transparent;
    font-weight: 600;
    color: var(--pro-ink, #1a2e22);
}

.qty-selector input:focus {
    outline: none;
}

.summary-card {
    border: 1px solid var(--pro-border, #e8ebe6);
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 4px 20px rgba(45, 90, 61, 0.05);
}

.cart-item-card {
    border: 1px solid var(--pro-border, #e8ebe6);
    border-radius: 16px;
    background: #ffffff;
    box-shadow: 0 2px 12px rgba(26, 46, 34, 0.03);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cart-item-card:hover {
    box-shadow: 0 4px 16px rgba(26, 46, 34, 0.06);
}

.btn-remove-item {
    font-size: 0.85rem;
    color: var(--pro-muted, #5f6d62);
    background: none;
    border: none;
    padding: 0;
    transition: color 0.2s ease;
}

.btn-remove-item:hover {
    color: #dc3545;
}

.sticky-summary {
    position: sticky;
    top: 100px;
    z-index: 10;
}

.hover-up {
    transition: transform 0.25s ease, box-shadow 0.25s ease;
}
.hover-up:hover {
    transform: translateY(-2px);
    box-shadow: 0 8px 24px rgba(45, 90, 61, 0.2) !important;
}
</style>
@endpush

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Cart'),
        'items' => [['label' => __('Cart')]],
    ])
@endpush

@section('content')
<div class="pro-cart-page container py-2">
    <h1 class="h3 fw-bold mb-4 font-anc-serif text-dark d-flex align-items-center gap-2">
        <i class="bi bi-bag-check-fill text-primary"></i> {{ __('Your Shopping Cart') }}
        @if($items->isNotEmpty())
            <span class="fs-6 fw-normal text-muted">({{ $items->count() }} {{ $items->count() === 1 ? __('item') : __('items') }})</span>
        @endif
    </h1>

    @if($items->isEmpty())
        <div class="text-center py-5 border rounded-4 shadow-sm bg-white">
            <div class="mb-4">
                <i class="bi bi-bag-x text-muted" style="font-size: 4.5rem; opacity: 0.4;"></i>
            </div>
            <h2 class="h4 fw-bold text-dark">{{ __('Your cart is empty') }}</h2>
            <p class="text-muted mb-4 px-3">{{ __('Add some of our natural, premium products to your cart and make a purchase.') }}</p>
            <a href="{{ route('market.home') }}" class="btn btn-primary px-4 py-2 rounded-pill fw-semibold shadow-sm">
                <i class="bi bi-arrow-left-short fs-5 align-middle me-1"></i> {{ __('Continue shopping') }}
            </a>
        </div>
    @else
        <div class="row g-4">
            <!-- Cart Items List (Column Left) -->
            <div class="col-lg-8">
                @foreach($items as $item)
                    @php
                        $v = $item->variant;
                        $p = $v->product;
                        $unit = $v->effectivePrice();
                        $cartImg = \App\Models\Product::publicImageUrl($p->images->first()?->path) ?? $p->namedPlaceholderUrl();
                    @endphp
                    <div class="cart-item-card p-3 mb-3 js-cart-item-card" data-item-id="{{ $item->id }}">
                        <div class="d-flex gap-3 align-items-center align-items-md-start position-relative">
                            
                            <!-- Image -->
                            <a href="{{ route('product.show', $p) }}" class="cart-item-img-container d-block">
                                <img src="{{ $cartImg }}" alt="{{ $p->name }}" loading="lazy">
                            </a>
                            
                            <!-- Content -->
                            <div class="flex-grow-1 min-w-0">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-1">
                                    <div>
                                        <a href="{{ route('product.show', $p) }}" class="fw-semibold text-dark text-decoration-none d-block fs-6 lh-sm hover-text-primary pe-3">{{ $p->name }}</a>
                                        @if($v->label() !== 'Default')
                                            <span class="badge bg-light text-muted border mt-1">{{ $v->label() }}</span>
                                        @endif
                                        @if($p->formattedWeightKg())
                                            <span class="badge bg-light text-muted border mt-1"><i class="bi bi-tag-fill me-1 small text-primary"></i>{{ $p->formattedWeightKg() }}</span>
                                        @endif
                                    </div>
                                    
                                    <!-- Desktop Delete Button -->
                                    <form action="{{ route('cart.destroy', $item) }}" method="post" class="js-cart-remove-form d-none d-md-block" data-remove-url="{{ route('cart.destroy', $item) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn btn-link text-decoration-none text-muted p-1 hover-text-danger" type="submit" title="{{ __('Remove item') }}">
                                            <i class="bi bi-trash3 fs-5"></i>
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Qty selector and prices -->
                                <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mt-3">
                                    <form action="{{ route('cart.update', $item) }}" method="post" class="js-cart-qty-form" data-update-url="{{ route('cart.update', $item) }}">
                                        @csrf @method('PATCH')
                                        <div class="qty-selector">
                                            <button type="button" class="js-qty-minus" aria-label="Decrease quantity" style="visibility: {{ $item->qty < 2 ? 'hidden' : 'visible' }};"><i class="bi bi-minus"></i></button>
                                            <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="99" class="js-cart-qty-input" aria-label="Quantity">
                                            <button type="button" class="js-qty-plus" aria-label="Increase quantity"><i class="bi bi-plus"></i></button>
                                        </div>
                                    </form>
                                    
                                    <div class="d-flex align-items-baseline gap-2">
                                        <span class="fw-bold text-dark fs-5 js-item-total">₹{{ number_format($unit * $item->qty, 0) }}</span>
                                        <span class="small text-muted js-item-unit" data-unit-price="{{ $unit }}">({{ __('₹') }}{{ number_format($unit, 0) }} {{ __('each') }})</span>
                                    </div>
                                </div>
                                
                                <!-- Mobile Delete Button -->
                                <div class="d-md-none mt-2">
                                    <form action="{{ route('cart.destroy', $item) }}" method="post" class="js-cart-remove-form" data-remove-url="{{ route('cart.destroy', $item) }}">
                                        @csrf @method('DELETE')
                                        <button class="btn-remove-item d-flex align-items-center gap-1" type="submit">
                                            <i class="bi bi-trash3"></i> <span>{{ __('Remove') }}</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                            
                        </div>
                    </div>
                @endforeach
            </div>
            
            <!-- Summary Sidebar -->
            <div class="col-lg-4">
                <div class="summary-card p-4 sticky-summary">
                    <h2 class="h5 fw-bold text-dark mb-4 pb-2 border-bottom">{{ __('Order Summary') }}</h2>
                    
                    <div id="cartSummary" class="mb-4">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">{{ __('Subtotal') }}</span>
                            <span id="cartSubtotal" class="fw-semibold text-dark">₹{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="text-muted">{{ __('Shipping') }}</span>
                            <span id="cartShipping" class="fw-semibold text-success">
                                @if($shipping_charge <= 0)
                                    {{ __('FREE') }}
                                @else
                                    ₹{{ number_format($shipping_charge, 2) }}
                                @endif
                            </span>
                        </div>
                        <hr class="my-3">
                        <div class="d-flex justify-content-between align-items-center h5 mb-0">
                            <span class="fw-bold text-dark">{{ __('Total') }}</span>
                            <span id="cartTotal" class="fw-bold text-primary">₹{{ number_format($total, 2) }}</span>
                        </div>
                    </div>
                    
                    @auth
                        <a href="{{ route('checkout.index') }}" class="btn btn-primary w-100 py-3 rounded-pill fw-bold text-uppercase shadow-sm hover-up">{{ __('Proceed to checkout') }}</a>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-primary w-100 py-3 rounded-pill fw-bold text-uppercase shadow-sm hover-up">{{ __('Login to checkout') }}</a>
                    @endauth
                </div>
            </div>
        </div>
    @endif
</div>
@endsection

@if($items->isNotEmpty())
@push('scripts')
<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var subtotalEl = document.getElementById('cartSubtotal');
    var shippingEl = document.getElementById('cartShipping');
    var totalEl = document.getElementById('cartTotal');
    if (!csrf || !subtotalEl || !shippingEl || !totalEl) return;

    function formatMoney(n) {
        return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function applySummary(data) {
        subtotalEl.textContent = formatMoney(data.subtotal);
        shippingEl.textContent = data.is_free_shipping ? {!! json_encode(__('FREE')) !!} : formatMoney(data.shipping_charge);
        totalEl.textContent = formatMoney(data.total);
    }

    function updateItemTotal(input) {
        var card = input.closest('.js-cart-item-card');
        if (!card) return;
        var unitEl = card.querySelector('.js-item-unit');
        var totalEl = card.querySelector('.js-item-total');
        if (unitEl && totalEl) {
            var unitPrice = parseFloat(unitEl.getAttribute('data-unit-price')) || 0;
            var qty = parseInt(input.value, 10) || 1;
            totalEl.textContent = '₹' + Math.round(unitPrice * qty).toLocaleString('en-IN');
        }
    }

    function patchCart(url, qty) {
        return fetch(url, {
            method: 'PATCH',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrf.getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({ qty: qty })
        })
        .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
        .then(function (res) {
            if (res.ok) {
                applySummary(res.data);
            }
            return res;
        });
    }

    var debounceTimer;

    document.querySelectorAll('.js-cart-qty-input').forEach(function (input) {
        input.addEventListener('change', function () {
            updateItemTotal(input);
            var card = input.closest('.js-cart-item-card');
            if (card) {
                var minusBtn = card.querySelector('.js-qty-minus');
                if (minusBtn) {
                    var val = parseInt(input.value, 10) || 1;
                    if (val < 2) {
                        minusBtn.style.visibility = 'hidden';
                    } else {
                        minusBtn.style.visibility = 'visible';
                    }
                }
            }
            var form = input.closest('.js-cart-qty-form');
            if (!form) return;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                patchCart(form.getAttribute('data-update-url'), parseInt(input.value, 10) || 1);
            }, 300);
        });
    });

    document.querySelectorAll('.js-qty-minus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.closest('.qty-selector').querySelector('.js-cart-qty-input');
            if (input) {
                var val = parseInt(input.value, 10) || 1;
                if (val > 1) {
                    input.value = val - 1;
                    input.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    document.querySelectorAll('.js-qty-plus').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var input = btn.closest('.qty-selector').querySelector('.js-cart-qty-input');
            if (input) {
                var val = parseInt(input.value, 10) || 1;
                if (val < 99) {
                    input.value = val + 1;
                    input.dispatchEvent(new Event('change'));
                }
            }
        });
    });

    document.querySelectorAll('.js-cart-qty-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            var input = form.querySelector('.js-cart-qty-input');
            patchCart(form.getAttribute('data-update-url'), parseInt(input && input.value, 10) || 1);
        });
    });

    document.querySelectorAll('.js-cart-remove-form').forEach(function (form) {
        form.addEventListener('submit', function (e) {
            e.preventDefault();
            fetch(form.getAttribute('data-remove-url'), {
                method: 'DELETE',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf.getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function () { window.location.reload(); });
        });
    });
})();
</script>
@endpush
@push('scripts')
@php $gaId = app(\App\Services\SeoService::class)->global('google_analytics_id'); @endphp
@if(filled($gaId))
<script>
    gtag('event', 'view_cart', {
        currency: 'INR',
        value: {{ (float) $total }},
        items: [
            @foreach($items as $citem)
            {
                item_id: {!! json_encode($citem->variant->sku ?: $citem->variant->id, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!},
                item_name: {!! json_encode($citem->variant->product->name, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!},
                price: {{ (float) $citem->variant->effectivePrice() }},
                quantity: {{ (int) $citem->qty }},
                item_variant: {!! json_encode($citem->variant->label(), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) !!}
            },
            @endforeach
        ]
    });
</script>
@endif
@endpush
@endif

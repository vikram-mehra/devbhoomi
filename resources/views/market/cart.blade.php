@extends('layouts.market')

@section('title', 'Your cart')

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Cart'),
        'items' => [['label' => __('Cart')]],
    ])
@endpush

@section('content')
<div class="pro-cart-page">
    @forelse($items as $item)
        @php
            $v = $item->variant;
            $p = $v->product;
            $unit = $v->effectivePrice();
            $cartImg = \App\Models\Product::publicImageUrl($p->images->first()?->path) ?? $p->namedPlaceholderUrl();
        @endphp
        <div class="zm-card p-3 mb-2 d-flex flex-wrap align-items-center gap-3 justify-content-between">
            <div class="d-flex gap-3 align-items-center">
                <img src="{{ $cartImg }}" width="72" height="72" class="rounded-2" alt="" loading="lazy" decoding="async">
                <div>
                    <a href="{{ route('product.show', $p) }}" class="fw-semibold">{{ $p->name }}</a>
                    <div class="small text-muted">{{ $v->label() }}</div>
                    <div class="zm-price">₹{{ number_format($unit, 0) }} × {{ $item->qty }}</div>
                </div>
            </div>
            <form action="{{ route('cart.update', $item) }}" method="post" class="d-flex align-items-center gap-2 js-cart-qty-form" data-update-url="{{ route('cart.update', $item) }}">
                @csrf @method('PATCH')
                <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="99" class="form-control form-control-sm js-cart-qty-input" style="width:70px">
                <button class="btn btn-sm btn-outline-secondary" type="submit">{{ __('Update') }}</button>
            </form>
            <form action="{{ route('cart.destroy', $item) }}" method="post" class="js-cart-remove-form" data-remove-url="{{ route('cart.destroy', $item) }}">@csrf @method('DELETE')
                <button class="btn btn-sm btn-outline-danger" type="submit">{{ __('Remove') }}</button>
            </form>
        </div>
    @empty
        <p>{{ __('Cart is empty.') }} <a href="{{ route('market.home') }}">{{ __('Continue shopping') }}</a></p>
    @endforelse

    @if($items->isNotEmpty())
        <div class="text-end mt-3">
            <div id="cartSummary" class="mb-3">
                <div class="d-flex justify-content-end gap-3 mb-1">
                    <span class="text-muted">{{ __('Subtotal') }}:</span>
                    <span id="cartSubtotal">₹{{ number_format($subtotal, 2) }}</span>
                </div>
                <div class="d-flex justify-content-end gap-3 mb-1">
                    <span class="text-muted">{{ __('Shipping') }}:</span>
                    <span id="cartShipping">
                        @if($shipping_charge <= 0)
                            {{ __('FREE') }}
                        @else
                            ₹{{ number_format($shipping_charge, 2) }}
                        @endif
                    </span>
                </div>
                <div class="d-flex justify-content-end gap-3 h5 mb-0">
                    <span>{{ __('Total') }}:</span>
                    <span id="cartTotal">₹{{ number_format($total, 2) }}</span>
                </div>
            </div>
            @auth
                <a href="{{ route('checkout.index') }}" class="zm-btn zm-btn-primary">{{ __('Checkout') }}</a>
            @else
                <a href="{{ route('login') }}" class="zm-btn zm-btn-primary">{{ __('Login to checkout') }}</a>
            @endauth
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
        shippingEl.textContent = data.is_free_shipping ? {{ json_encode(__('FREE')) }} : formatMoney(data.shipping_charge);
        totalEl.textContent = formatMoney(data.total);
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
            var form = input.closest('.js-cart-qty-form');
            if (!form) return;
            clearTimeout(debounceTimer);
            debounceTimer = setTimeout(function () {
                patchCart(form.getAttribute('data-update-url'), parseInt(input.value, 10) || 1);
            }, 300);
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
@endif

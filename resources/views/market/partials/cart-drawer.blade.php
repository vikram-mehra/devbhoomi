{{-- Shopping Cart Offcanvas Drawer --}}
<div class="offcanvas offcanvas-end pro-cart-drawer" tabindex="-1" id="cartDrawer"
    aria-labelledby="cartDrawerLabel">
    <div class="offcanvas-header border-bottom">
        <h2 class="h5 offcanvas-title fw-bold text-dark d-flex align-items-center gap-2" id="cartDrawerLabel">
            <span>{{ __('Your cart') }}</span>
            <span class="badge bg-primary rounded-pill fs-7 js-drawer-count-badge" @if(($layoutCartCount ?? 0) === 0)
            style="display:none;" @endif>{{ $layoutCartCount }}</span>
        </h2>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('Close') }}"></button>
    </div>
    <div class="offcanvas-body d-flex flex-column">
        <div class="flex-grow-1 overflow-y-auto pe-1" id="drawerCartItemsList">
            @include('market.partials.cart-drawer-items')
        </div>

        <div class="mt-auto pt-3 border-top" id="drawerCartSummaryBlock" @if(($layoutCartCount ?? 0) === 0)
        style="display:none;" @endif>
            <div class="d-flex justify-content-between text-muted mb-2 small">
                <span>{{ __('Subtotal') }}</span>
                <span id="drawerCartSubtotal">₹{{ number_format($layoutCartSubtotal ?? 0, 2) }}</span>
            </div>
            <div class="d-flex justify-content-between text-muted mb-2 small">
                <span>{{ __('Shipping Charge') }}</span>
                <span id="drawerCartShipping" class="text-success fw-semibold">
                    @if(($layoutCartShipping ?? 0) <= 0)
                        {{ __('FREE') }}
                    @else
                        ₹{{ number_format($layoutCartShipping ?? 0, 2) }}
                    @endif
                </span>
            </div>
            <hr class="my-2">
            <div class="d-flex justify-content-between fw-bold mb-3">
                <span>{{ __('Total') }}</span>
                <span class="text-primary"
                    id="drawerCartTotal">₹{{ number_format($layoutCartTotal ?? 0, 2) }}</span>
            </div>

            @if(config('holiday.enabled'))
                <div class="alert alert-warning border-0 rounded-3 p-2 small text-center mb-2">
                    <i class="bi bi-pause-circle-fill me-1"></i> {{ __('Checkout paused during holiday mode.') }}
                </div>
                <button type="button" class="btn btn-secondary w-100 rounded-pill py-2 fw-semibold mb-2 shadow-sm opacity-75" disabled>
                    {{ __('Checkout Paused') }}
                </button>
            @else
                @auth
                    <a href="{{ route('checkout.index') }}"
                        class="btn btn-primary w-100 rounded-pill py-2 fw-semibold mb-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                        {{ __('Proceed to checkout') }}
                        <i class="bi bi-arrow-right-short fs-5 transition-arrow"></i>
                    </a>
                @else
                    <a href="{{ route('checkout.index') }}"
                        class="btn btn-primary w-100 rounded-pill py-2 fw-semibold mb-2 shadow-sm d-flex align-items-center justify-content-center gap-1">
                        {{ __('Login to checkout') }}
                        <i class="bi bi-arrow-right-short fs-5 transition-arrow"></i>
                    </a>
                @endauth
            @endif
        </div>
    </div>
</div>

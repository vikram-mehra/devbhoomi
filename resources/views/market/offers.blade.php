@extends('layouts.market')

@section('title', __('Offers & Coupons').' | Devbhoomi Naturals')
@section('meta_description')
    {{ __('Active promo codes on organic Himalayan products. Extra 5% off prepaid orders & free delivery above ₹499 at Devbhoomi Naturals.') }}
@endsection
@section('canonical', route('offers.index'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Offers & Coupons'),
        'items' => [['label' => __('Offers')]],
    ])
@endpush

@section('content')
    <div class="py-2 py-md-3">
        <header class="mb-4">
            <p class="pro-section-head__eyebrow mb-1">{{ __('Save more') }}</p>
            <p class="text-muted mb-0">{{ __('Copy a code below and apply it on the checkout page. Offers shown here are public coupons only.') }}</p>
        </header>

        @if($coupons->isEmpty())
            <div class="pro-checkout-card text-center py-5">
                <p class="text-muted mb-3">{{ __('No active offers right now. Check back soon.') }}</p>
                <a href="{{ route('shop.search') }}" class="btn btn-primary rounded-pill">{{ __('Continue shopping') }}</a>
            </div>
        @else
            <div class="row g-3">
                @foreach($coupons as $coupon)
                    <div class="col-md-6 col-lg-4">
                        <div class="pro-checkout-coupon-chip h-100 d-flex flex-column">
                            <div class="pro-checkout-coupon-chip__title">{{ __('Special offer') }}</div>
                            <div class="pro-checkout-coupon-chip__code mb-1">#{{ $coupon->code }}</div>
                            <div class="small fw-semibold text-success mb-2">{{ $coupon->discountLabel() }}</div>
                            <div class="small text-muted mb-3">
                                @if((float) $coupon->min_cart > 0)
                                    {{ __('Min. cart ₹:amount', ['amount' => number_format((float) $coupon->min_cart, 0)]) }} ·
                                @endif
                                {{ optional($coupon->ends_at)->format('d M Y') }}
                            </div>
                            <div class="mt-auto d-flex flex-wrap gap-2">
                                <button type="button" class="pro-checkout-coupon-chip__copy js-offer-copy" data-code="{{ $coupon->code }}">{{ __('Copy code') }}</button>
                                @auth
                                    <a href="{{ route('checkout.index') }}" class="small fw-semibold text-decoration-none align-self-center">{{ __('Use at checkout') }}</a>
                                @else
                                    <a href="{{ route('login') }}" class="small fw-semibold text-decoration-none align-self-center">{{ __('Login to use') }}</a>
                                @endauth
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-4 d-flex justify-content-center">
                {{ $coupons->links() }}
            </div>
        @endif
    </div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    document.querySelectorAll('.js-offer-copy').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var code = btn.getAttribute('data-code') || '';
            if (!code) return;
            var ta = document.createElement('textarea');
            ta.value = code;
            ta.setAttribute('readonly', '');
            ta.style.position = 'fixed';
            ta.style.top = '0';
            ta.style.left = '0';
            ta.style.opacity = '0';
            document.body.appendChild(ta);
            ta.select();
            try { document.execCommand('copy'); } catch (e) {}
            document.body.removeChild(ta);
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(code).catch(function () {});
            }
            var label = btn.textContent;
            btn.textContent = {{ json_encode(__('Copied!')) }};
            setTimeout(function () { btn.textContent = label; }, 1600);
        });
    });
});
</script>
@endpush

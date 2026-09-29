@extends('layouts.market')

@section('title', __('Pay order'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'items' => [
            ['label' => __('Checkout'), 'url' => route('checkout.index')],
            ['label' => __('Payment')],
        ],
    ])
@endpush

@section('content')
    <div class="rzp-pay-overlay" id="rzpLoading" hidden role="alert" aria-live="polite">
        <div class="rzp-pay-overlay__inner">
            <div class="mk-page-preloader__plate">
                <span class="mk-page-preloader__ring" aria-hidden="true"></span>
            </div>
            <p class="rzp-pay-overlay__title">{{ __('Confirming your payment…') }}</p>
            <p class="rzp-pay-overlay__sub">{{ __('Please wait, do not refresh this page.') }}</p>
        </div>
    </div>
    <div class="alert alert-info small mb-3" role="status">
        {{ __('Demo Razorpay: no real payment is taken. Set RAZORPAY_KEY and RAZORPAY_SECRET in .env for live checkout.') }}
    </div>
    <h1 class="h4 mb-3">{{ __('Complete payment') }}</h1>
    <p class="mb-2">{{ __('Order') }} <strong>{{ $order->order_number }}</strong> — {{ __('Pay') }} ₹{{ number_format($payable, 2) }}</p>
    <form method="post" action="{{ route('pay.razorpay.dummy', $order) }}" class="mt-3" id="rzpDummyForm">
        @csrf
        <button type="submit" class="zm-btn zm-btn-primary" id="rzpDummyBtn">{{ __('Pay now (demo)') }}</button>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-link ms-2">{{ __('Cancel') }}</a>
    </form>
@endsection

@push('scripts')
<script>
(function () {
    var form = document.getElementById('rzpDummyForm');
    var overlay = document.getElementById('rzpLoading');
    var btn = document.getElementById('rzpDummyBtn');
    if (!form) return;
    form.addEventListener('submit', function () {
        if (btn) btn.disabled = true;
        if (overlay) overlay.hidden = false;
    });
})();
</script>
@endpush

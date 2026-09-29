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
    <div class="rzp-pay-overlay" id="rzpLoading" role="alert" aria-live="polite" aria-busy="true">
        <div class="rzp-pay-overlay__inner">
            <div class="mk-page-preloader__plate">
                <span class="mk-page-preloader__ring" aria-hidden="true"></span>
            </div>
            <p class="rzp-pay-overlay__title" id="rzpLoadingTitle">{{ __('Opening payment gateway…') }}</p>
            <p class="rzp-pay-overlay__sub" id="rzpLoadingSub">{{ __('Order') }} {{ $order->order_number }} — ₹{{ number_format($payable, 2) }}</p>
        </div>
    </div>
    <div class="text-center py-5" id="rzpFallback" hidden>
        <p id="rzpError" class="text-danger mb-3"></p>
        <button id="rzpBtn" type="button" class="zm-btn zm-btn-primary">{{ __('Try payment again') }}</button>
        <a href="{{ route('orders.show', $order) }}" class="btn btn-link ms-2">{{ __('View order') }}</a>
    </div>
@endsection

@push('scripts')
<script src="https://checkout.razorpay.com/v1/checkout.js"></script>
<script>
(function () {
    var loadingEl = document.getElementById('rzpLoading');
    var loadingTitle = document.getElementById('rzpLoadingTitle');
    var loadingSub = document.getElementById('rzpLoadingSub');
    var fallbackEl = document.getElementById('rzpFallback');
    var btn = document.getElementById('rzpBtn');
    var errEl = document.getElementById('rzpError');
    var started = false;
    var submitting = false;
    var hadFailure = false;
    var payPageUrl = @json(route('pay.razorpay', $order));
    var orderUrl = @json(route('orders.show', $order));
    var abandonUrl = @json(route('pay.razorpay.abandon', $order));
    var defaultSub = @json(__('Order') . ' ' . $order->order_number . ' — ₹' . number_format($payable, 2));

    function showLoader(title, sub) {
        if (loadingTitle && title) loadingTitle.textContent = title;
        if (loadingSub) loadingSub.textContent = sub || defaultSub;
        if (loadingEl) {
            loadingEl.hidden = false;
            loadingEl.setAttribute('aria-busy', 'true');
        }
        if (fallbackEl) fallbackEl.hidden = true;
    }

    function hideLoader() {
        if (loadingEl) {
            loadingEl.hidden = true;
            loadingEl.setAttribute('aria-busy', 'false');
        }
    }

    function showFallback(msg) {
        hideLoader();
        if (fallbackEl) fallbackEl.hidden = false;
        if (errEl && msg) errEl.textContent = msg;
    }

    function goToPayPage() {
        window.location.replace(payPageUrl);
    }

    function abandonThenLeave() {
        fetch(abandonUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': @json(csrf_token()),
                'Accept': 'application/json',
            },
            credentials: 'same-origin',
            keepalive: true,
        }).catch(function () {}).finally(goToPayPage);
    }

    function openRzp() {
        if (submitting) return;
        if (started && loadingEl && !loadingEl.hidden) return;
        started = true;
        hadFailure = false;
        showLoader(@json(__('Opening payment gateway…')));
        if (errEl) errEl.textContent = '';
        if (btn) btn.disabled = true;

        fetch(@json(route('pay.razorpay.order', $order)), {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': @json(csrf_token()),
                'Accept': 'application/json',
                'Content-Type': 'application/json',
            },
            credentials: 'same-origin',
        }).then(function (r) {
            return r.json().then(function (data) {
                return { ok: r.ok, status: r.status, data: data };
            });
        }).then(function (res) {
            if (btn) btn.disabled = false;
            if (!res.ok || (res.data && res.data.error)) {
                var key = res.data && res.data.error;
                if (key === 'already_paid' || key === 'not_payable') {
                    showLoader(
                        key === 'already_paid'
                            ? @json(__('This order is already paid.'))
                            : @json(__('This payment link is no longer valid.')),
                        @json(__('Redirecting…'))
                    );
                    goToPayPage();
                    return;
                }
                var msg = key === 'gateway_not_configured'
                    ? @json(__('Online payment is not configured.'))
                    : key === 'nothing_to_pay'
                    ? @json(__('Nothing to pay for this order.'))
                    : @json(__('Could not start payment. Please try again.'));
                showFallback(msg);
                started = false;
                return;
            }
            var options = {
                key: @json($key),
                amount: res.data.amount,
                currency: 'INR',
                name: @json(config('app.name')),
                description: @json($order->order_number),
                order_id: res.data.id,
                prefill: {
                    name: @json($prefill['name'] ?? ''),
                    email: @json($prefill['email'] ?? ''),
                    contact: @json($prefill['contact'] ?? ''),
                },
                theme: { color: '#2d5a3d' },
                handler: function (response) {
                    if (submitting) return;
                    submitting = true;
                    showLoader(@json(__('Confirming your payment…')), @json(__('Please wait, do not refresh this page.')));
                    var f = document.createElement('form');
                    f.method = 'POST';
                    f.action = @json(route('pay.razorpay.verify'));
                    f.innerHTML = '<input type="hidden" name="_token" value="' + @json(csrf_token()) + '">' +
                        '<input type="hidden" name="order_id" value="' + @json($order->id) + '">' +
                        '<input type="hidden" name="razorpay_order_id" value="' + response.razorpay_order_id + '">' +
                        '<input type="hidden" name="razorpay_payment_id" value="' + response.razorpay_payment_id + '">' +
                        '<input type="hidden" name="razorpay_signature" value="' + response.razorpay_signature + '">';
                    document.body.appendChild(f);
                    f.submit();
                },
                modal: {
                    ondismiss: function () {
                        if (submitting) return;
                        if (hadFailure) {
                            showLoader(@json(__('Payment failed.')), @json(__('Redirecting you now…')));
                            abandonThenLeave();
                            return;
                        }
                        showLoader(@json(__('Returning to your order…')));
                        window.location.href = orderUrl;
                    },
                },
            };
            if (typeof Razorpay === 'undefined') {
                showFallback(@json(__('Could not load the payment window. Please refresh and try again.')));
                started = false;
                return;
            }
            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function () {
                hadFailure = true;
            });
            rzp.open();
            hideLoader();
        }).catch(function () {
            if (btn) btn.disabled = false;
            showFallback(@json(__('Network error. Check your connection and try again.')));
            started = false;
        });
    }

    if (btn) {
        btn.addEventListener('click', openRzp);
    }

    if (typeof Razorpay !== 'undefined') {
        openRzp();
    } else {
        window.addEventListener('load', openRzp);
    }
})();
</script>
@endpush

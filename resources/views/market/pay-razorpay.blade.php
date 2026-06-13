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
    <div class="text-center py-5" id="rzpLoading">
        <p class="h5 mb-2">{{ __('Opening payment gateway…') }}</p>
        <p class="text-muted small mb-4">{{ __('Order') }} {{ $order->order_number }} — ₹{{ number_format($payable, 2) }}</p>
        <div class="spinner-border text-primary" role="status" aria-hidden="true"></div>
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
    var fallbackEl = document.getElementById('rzpFallback');
    var btn = document.getElementById('rzpBtn');
    var errEl = document.getElementById('rzpError');
    var started = false;

    function showFallback(msg) {
        if (loadingEl) loadingEl.hidden = true;
        if (fallbackEl) fallbackEl.hidden = false;
        if (errEl && msg) {
            errEl.textContent = msg;
        }
    }

    function openRzp() {
        if (started && loadingEl && !loadingEl.hidden) {
            return;
        }
        started = true;
        if (fallbackEl) fallbackEl.hidden = true;
        if (loadingEl) loadingEl.hidden = false;
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
            if (!res.ok || res.data.error) {
                var key = res.data && res.data.error;
                var msg = key === 'gateway_not_configured'
                    ? @json(__('Online payment is not configured.'))
                    : key === 'nothing_to_pay'
                    ? @json(__('Nothing to pay for this order.'))
                    : @json(__('Could not start payment. Please try again.'));
                showFallback(msg);
                started = false;
                return;
            }
            if (loadingEl) loadingEl.hidden = true;
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
                theme: { color: '#c67c4e' },
                handler: function (response) {
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
                        window.location.href = @json(route('orders.show', $order));
                    },
                },
            };
            var rzp = new Razorpay(options);
            rzp.on('payment.failed', function (resp) {
                showFallback(resp.error && resp.error.description
                    ? resp.error.description
                    : @json(__('Payment failed. Try another method or contact support.')));
                started = false;
            });
            rzp.open();
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

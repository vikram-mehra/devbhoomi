@extends('layouts.market')

@section('title', __('Checkout'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Checkout'),
        'items' => [
            ['label' => __('Cart'), 'url' => route('cart.index')],
            ['label' => __('Checkout')],
        ],
    ])
@endpush

@section('content')
@php
    $taxDisplay = (float) ($taxAmount ?? 0);
    $couponDiscount = (float) ($couponDiscount ?? 0);
    $shippingDisplay = (float) ($shippingCharge ?? 0);
    $estTotal = max(0, $subtotal + $shippingDisplay + $taxDisplay - $couponDiscount);
    $hasAppliedCoupon = $couponDiscount > 0 && ! empty($appliedCouponCode);
    $showNewAddressForm = true;
@endphp
<div class="pro-checkout">

    <form method="post" action="{{ route('checkout.store') }}" class="pro-checkout__form" id="checkoutMainForm">
        @csrf
        <div class="row g-4 align-items-start">
            <div class="col-lg-7">
                {{-- Shipping Address --}}
                <div class="pro-checkout-card mb-4">
                    <div class="pro-checkout-card__head">
                        <h2 class="pro-checkout-card__title mb-0">{{ __('Shipping Address') }}</h2>
                        @if($addresses->isNotEmpty())
                            <button type="button" class="pro-checkout-link border-0 bg-transparent p-0" id="checkoutAddNewAddress">{{ __('+ Add New') }}</button>
                        @endif
                    </div>

                    @if($addresses->isNotEmpty())
                        @php
                            $selectedAddressId = old('address_id', null);
                            if ($selectedAddressId === null) {
                                $pick = $addresses->firstWhere('is_default') ?? $addresses->first();
                                $selectedAddressId = $pick ? (string) $pick->id : '';
                            } else {
                                $selectedAddressId = (string) $selectedAddressId;
                            }
                            $showNewAddressForm = $selectedAddressId === ''
                                || $errors->hasAny(['name', 'phone', 'line1', 'city', 'state', 'pincode']);
                        @endphp
                        <div class="row g-3 pro-checkout-address-grid">
                            @foreach($addresses as $a)
                                <div class="col-md-6">
                                    <label class="pro-checkout-address-tile">
                                        <input type="radio" name="address_id" value="{{ $a->id }}" class="pro-checkout-address-tile__input"
                                            @if($selectedAddressId === (string) $a->id) checked @endif>
                                        <span class="pro-checkout-address-tile__box">
                                            <span class="pro-checkout-radio" aria-hidden="true"></span>
                                            <span class="pro-checkout-address-tile__body">
                                                <span class="pro-checkout-address-tile__label">{{ $a->label ?: __('Saved address') }}</span>
                                                <span class="pro-checkout-address-tile__line"><span class="text-muted">{{ __('Address') }}</span> {{ $a->line1 }}{{ $a->line2 ? ', '.$a->line2 : '' }}</span>
                                                <span class="pro-checkout-address-tile__line"><span class="text-muted">{{ __('Pin Code') }}</span> {{ $a->pincode }}</span>
                                                <span class="pro-checkout-address-tile__line"><span class="text-muted">{{ __('Phone') }}</span> {{ $a->phone ?: '—' }}</span>
                                            </span>
                                        </span>
                                    </label>
                                </div>
                            @endforeach
                            <input type="radio" name="address_id" value="" class="pro-checkout-address-tile__input visually-hidden" id="checkoutAddressNew"
                                @if($selectedAddressId === '') checked @endif tabindex="-1" aria-hidden="true">
                        </div>
                    @endif

                    <div id="pro-checkout-new-address" class="pro-checkout-new-address mt-4 pt-4 border-top border-light-subtle @if($addresses->isNotEmpty() && ! $showNewAddressForm) d-none @endif" @if($addresses->isNotEmpty() && ! $showNewAddressForm) hidden @endif>
                        <p class="small text-muted mb-3">{{ __('Fill below only if you are not using a saved address above.') }}</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="pro-checkout-field-label">{{ __('Full name') }}</label>
                                <input class="pro-checkout-input" name="name" placeholder="{{ __('Full name') }}" value="{{ old('name', auth()->user()->name) }}">
                            </div>
                            <div class="col-md-6">
                                <label class="pro-checkout-field-label">{{ __('Phone') }}</label>
                                <input class="pro-checkout-input" name="phone" placeholder="{{ __('Phone') }}" value="{{ old('phone') }}">
                            </div>
                            <div class="col-12">
                                <label class="pro-checkout-field-label">{{ __('Address line 1') }}</label>
                                <input class="pro-checkout-input" name="line1" placeholder="{{ __('Street, building') }}" value="{{ old('line1') }}">
                            </div>
                            <div class="col-12">
                                <label class="pro-checkout-field-label">{{ __('Line 2') }} <span class="text-muted fw-normal">({{ __('optional') }})</span></label>
                                <input class="pro-checkout-input" name="line2" placeholder="{{ __('Apartment, landmark') }}" value="{{ old('line2') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="pro-checkout-field-label">{{ __('City') }}</label>
                                <input class="pro-checkout-input" name="city" placeholder="{{ __('City') }}" value="{{ old('city') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="pro-checkout-field-label">{{ __('State') }}</label>
                                <input class="pro-checkout-input" name="state" placeholder="{{ __('State') }}" value="{{ old('state') }}">
                            </div>
                            <div class="col-md-4">
                                <label class="pro-checkout-field-label">{{ __('Pincode') }}</label>
                                <input class="pro-checkout-input" name="pincode" placeholder="{{ __('Pincode') }}" value="{{ old('pincode') }}">
                            </div>
                        </div>
                    </div>
                    @if($addresses->isNotEmpty())
                    <script>
                    (function () {
                        var addNewBtn = document.getElementById('checkoutAddNewAddress');
                        var newAddressForm = document.getElementById('pro-checkout-new-address');
                        var addressRadios = document.querySelectorAll('#checkoutMainForm input[name="address_id"]');
                        var syncing = false;

                        if (!addNewBtn || !newAddressForm || !addressRadios.length) {
                            return;
                        }

                        function showNewAddressForm() {
                            newAddressForm.classList.remove('d-none');
                            newAddressForm.removeAttribute('hidden');
                            newAddressForm.style.display = '';
                        }

                        function hideNewAddressForm() {
                            newAddressForm.classList.add('d-none');
                            newAddressForm.setAttribute('hidden', 'hidden');
                        }

                        function syncNewAddressForm() {
                            if (syncing) {
                                return;
                            }
                            var selected = document.querySelector('#checkoutMainForm input[name="address_id"]:checked');
                            if (selected && selected.value === '') {
                                showNewAddressForm();
                            } else {
                                hideNewAddressForm();
                            }
                        }

                        addNewBtn.addEventListener('click', function () {
                            syncing = true;
                            addressRadios.forEach(function (radio) {
                                radio.checked = radio.value === '';
                            });
                            syncing = false;
                            showNewAddressForm();
                            newAddressForm.scrollIntoView({ behavior: 'smooth', block: 'start' });
                            var firstField = newAddressForm.querySelector('input[name="name"], input[name="phone"], input[name="line1"]');
                            if (firstField) {
                                firstField.focus();
                            }
                        });

                        addressRadios.forEach(function (radio) {
                            radio.addEventListener('change', syncNewAddressForm);
                        });
                    })();
                    </script>
                    @endif
                </div>

                {{-- Payment: Razorpay only --}}
                <div class="pro-checkout-card mb-4">
                    <h2 class="pro-checkout-card__title mb-3">{{ __('Payment Options') }}</h2>
                    @unless($razorpayConfigured ?? false)
                        <p class="small text-muted mb-3">{{ __('Razorpay runs in demo mode until you add API keys.') }}</p>
                    @endunless
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="pro-checkout-pay-tile">
                                <input type="radio" name="payment_method" value="razorpay" class="pro-checkout-pay-tile__input" checked>
                                <span class="pro-checkout-pay-tile__box">
                                    <span class="pro-checkout-radio" aria-hidden="true"></span>
                                    <span class="pro-checkout-pay-tile__text">{{ __('Razorpay') }} — {{ __('UPI, cards, netbanking') }}</span>
                                    @unless($razorpayConfigured ?? false)
                                        <span class="d-block small text-muted mt-1">{{ __('Demo') }}</span>
                                    @endunless
                                </span>
                            </label>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="pro-checkout-card pro-checkout-card--sticky mb-4">
                    <h2 class="pro-checkout-card__title mb-1">{{ __('Summary order') }}</h2>
                    <p class="small text-muted mb-4">{{ __('Review items before placing your order.') }}</p>

                    <ul class="pro-checkout-lines list-unstyled mb-0">
                        @foreach($items as $item)
                            @php
                                $v = $item->variant;
                                $p = $v->product;
                                $unit = $v->effectivePrice();
                                $line = $unit * $item->qty;
                                $cartImg = \App\Models\Product::publicImageUrl($p->images->first()?->path) ?? $p->namedPlaceholderUrl();
                            @endphp
                            <li class="pro-checkout-line">
                                <img src="{{ $cartImg }}" alt="" class="pro-checkout-line__img" width="56" height="56" loading="lazy" decoding="async">
                                <div class="pro-checkout-line__mid">
                                    <div class="pro-checkout-line__name">{{ $p->name }}</div>
                                    <div class="pro-checkout-line__meta">₹{{ number_format($unit, 2) }} × {{ $item->qty }}</div>
                                </div>
                                <div class="pro-checkout-line__price">₹{{ number_format($line, 2) }}</div>
                            </li>
                        @endforeach
                    </ul>
                </div>

                <div class="pro-checkout-card">
                    <h3 class="pro-checkout-card__subtitle mb-3">{{ __('Billing summary') }}</h3>

                    @if($promoCoupons->isNotEmpty())
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <span class="small fw-semibold text-secondary">{{ __('Promo code') }}</span>
                        </div>
                        <div class="row g-2 mb-4">
                            @foreach($promoCoupons as $c)
                                <div class="col-md-6">
                                    <div class="pro-checkout-coupon-chip">
                                        <div class="pro-checkout-coupon-chip__title">{{ __('Special offer') }}</div>
                                        <div class="pro-checkout-coupon-chip__code">#{{ $c->code }}</div>
                                        <button type="button" class="pro-checkout-coupon-chip__copy js-copy-coupon" data-code="{{ $c->code }}">{{ __('Copy code') }}</button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif

                    <label class="pro-checkout-field-label">{{ __('Enter coupon code') }}</label>
                    <div class="pro-checkout-coupon-row">
                        <input type="text" name="coupon_code" id="checkoutCouponInput" class="pro-checkout-input pro-checkout-coupon-row__input" placeholder="{{ __('Enter coupon code here…') }}" value="{{ old('coupon_code', $appliedCouponCode ?? '') }}">
                        <button type="button" class="btn btn-outline-primary pro-checkout-coupon-row__btn js-checkout-apply-coupon{{ $hasAppliedCoupon ? ' d-none' : '' }}" id="checkoutApplyCoupon" @if($hasAppliedCoupon) hidden @endif>{{ __('Apply') }}</button>
                        <button type="button" class="btn btn-outline-danger pro-checkout-coupon-row__btn js-checkout-remove-coupon{{ $hasAppliedCoupon ? '' : ' d-none' }}" id="checkoutRemoveCoupon" @unless($hasAppliedCoupon) hidden @endunless>{{ __('Remove') }}</button>
                    </div>
                    <p id="checkoutCouponMsg" class="small mt-2 mb-1 {{ $errors->has('coupon_code') ? 'text-danger' : ($appliedCouponCode ? 'text-success' : 'text-muted') }}">
                        @if($errors->has('coupon_code'))
                            {{ $errors->first('coupon_code') }}
                        @elseif($appliedCouponCode)
                            {{ __('Coupon :code applied.', ['code' => $appliedCouponCode]) }}
                        @else
                            {{ __('Click Apply to see your discount. You can enter the code with or without the # symbol.') }}
                        @endif
                    </p>

                    <div class="pro-checkout-totals" id="checkoutTotals"
                         data-subtotal="{{ $subtotal }}"
                         data-shipping="{{ $shippingDisplay }}"
                         data-tax="{{ $taxDisplay }}"
                         data-validate-url="{{ $validateCouponPath ?? parse_url(url('/checkout/validate-coupon'), PHP_URL_PATH) }}"
                         data-remove-url="{{ $removeCouponPath ?? parse_url(url('/checkout/remove-coupon'), PHP_URL_PATH) }}">
                        <div class="pro-checkout-totals__row">
                            <span>{{ __('Sub total') }}</span>
                            <span class="pro-checkout-accent-text" id="checkoutSubtotal">₹{{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div class="pro-checkout-totals__row">
                            <span>{{ __('Shipping') }}</span>
                            <span class="pro-checkout-accent-text" id="checkoutShipping">
                                @if($isFreeShipping ?? $shippingDisplay <= 0)
                                    {{ __('FREE') }}
                                @else
                                    ₹{{ number_format($shippingDisplay, 2) }}
                                @endif
                            </span>
                        </div>
                        <div class="pro-checkout-totals__row" id="checkoutDiscountRow" @if($couponDiscount <= 0) style="display:none;" @endif>
                            <span>{{ __('Coupon discount') }}</span>
                            <span class="pro-checkout-accent-text text-success" id="checkoutDiscount">-₹{{ number_format($couponDiscount, 2) }}</span>
                        </div>
                        <div class="pro-checkout-totals__row">
                            <span>{{ __('Tax') }}</span>
                            <span class="pro-checkout-accent-text" id="checkoutTax">₹{{ number_format($taxDisplay, 2) }}</span>
                        </div>
                        <div class="pro-checkout-totals__row pro-checkout-totals__row--total">
                            <span>{{ __('Total') }}</span>
                            <span class="pro-checkout-accent-text pro-checkout-accent-text--lg" id="checkoutTotal">₹{{ number_format($estTotal, 2) }}</span>
                        </div>
                    </div>

                    <button type="submit" class="pro-checkout-place-btn mt-4">{{ __('Place order') }}</button>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    var MSG_EMPTY = @json(__('Please enter a coupon code.'));
    var MSG_INVALID = @json(__('Invalid coupon.'));
    var MSG_ERROR = @json(__('Could not validate coupon. Try again.'));
    var MSG_COPIED = @json(__('Copied!'));
    var MSG_FILLED = @json(__('Code filled'));
    var MSG_REMOVED = @json(__('Coupon removed.'));
    var MSG_HELP = @json(__('Click Apply to see your discount. You can enter the code with or without the # symbol.'));
    var LABEL_FREE = @json(__('FREE'));

    function copyText(str) {
        if (!str) {
            return false;
        }
        var ta = document.createElement('textarea');
        ta.value = str;
        ta.setAttribute('readonly', '');
        ta.style.position = 'fixed';
        ta.style.top = '0';
        ta.style.left = '0';
        ta.style.width = '2em';
        ta.style.height = '2em';
        ta.style.opacity = '0';
        document.body.appendChild(ta);
        ta.focus();
        ta.select();
        var ok = false;
        try {
            ok = document.execCommand('copy');
        } catch (e) {}
        document.body.removeChild(ta);
        if (!ok && window.isSecureContext && navigator.clipboard && navigator.clipboard.writeText) {
            navigator.clipboard.writeText(str).catch(function () {});
        }
        return ok;
    }

    var applyBtn = document.getElementById('checkoutApplyCoupon');
    var removeBtn = document.getElementById('checkoutRemoveCoupon');
    var couponInput = document.getElementById('checkoutCouponInput');
    var couponMsg = document.getElementById('checkoutCouponMsg');
    var totalsEl = document.getElementById('checkoutTotals');
    var discountRow = document.getElementById('checkoutDiscountRow');
    var discountEl = document.getElementById('checkoutDiscount');
    var totalEl = document.getElementById('checkoutTotal');
    var shippingEl = document.getElementById('checkoutShipping');
    var csrfMeta = document.querySelector('meta[name="csrf-token"]');
    var csrfInput = document.querySelector('#checkoutMainForm input[name="_token"]');
    var csrfToken = (csrfMeta && csrfMeta.getAttribute('content')) || (csrfInput && csrfInput.value) || '';
    var validateUrl = totalsEl ? totalsEl.getAttribute('data-validate-url') : '';
    var removeUrl = totalsEl ? totalsEl.getAttribute('data-remove-url') : '';
    var couponApplied = @json($hasAppliedCoupon);

    if (!applyBtn || !couponInput || !couponMsg || !totalsEl || !totalEl || !validateUrl || !csrfToken) {
        return;
    }

    function setCouponApplied(applied) {
        couponApplied = !!applied;
        if (couponApplied) {
            if (applyBtn) {
                applyBtn.hidden = true;
                applyBtn.classList.add('d-none');
            }
            if (removeBtn) {
                removeBtn.hidden = false;
                removeBtn.classList.remove('d-none');
            }
        } else {
            if (applyBtn) {
                applyBtn.hidden = false;
                applyBtn.classList.remove('d-none');
            }
            if (removeBtn) {
                removeBtn.hidden = true;
                removeBtn.classList.add('d-none');
            }
        }
    }

    setCouponApplied(couponApplied);

    function formatMoney(n) {
        return '₹' + Number(n).toLocaleString('en-IN', { minimumFractionDigits: 2, maximumFractionDigits: 2 });
    }

    function updateShippingDisplay(charge, label) {
        if (!shippingEl) {
            return;
        }
        var ship = parseFloat(charge);
        if (!isNaN(ship)) {
            totalsEl.setAttribute('data-shipping', String(ship));
        }
        if (label) {
            shippingEl.textContent = label;
        } else if (!isNaN(ship)) {
            shippingEl.textContent = ship <= 0 ? LABEL_FREE : formatMoney(ship);
        }
    }

    function updateTotals(discount, taxOverride, shippingOverride, shippingLabel) {
        var sub = parseFloat(totalsEl.getAttribute('data-subtotal')) || 0;
        if (shippingOverride !== undefined) {
            updateShippingDisplay(shippingOverride, shippingLabel);
        }
        var ship = parseFloat(totalsEl.getAttribute('data-shipping')) || 0;
        var tax = taxOverride !== undefined ? parseFloat(taxOverride) : (parseFloat(totalsEl.getAttribute('data-tax')) || 0);
        var disc = parseFloat(discount) || 0;
        var total = Math.max(0, sub + ship + tax - disc);
        var taxEl = document.getElementById('checkoutTax');
        if (taxEl) {
            taxEl.textContent = formatMoney(tax);
        }
        if (discountRow && discountEl) {
            if (disc > 0) {
                discountRow.style.display = '';
                discountEl.textContent = '-' + formatMoney(disc);
            } else {
                discountRow.style.display = 'none';
            }
        }
        totalEl.textContent = formatMoney(total);
    }

    function parseJsonResponse(r) {
        return r.text().then(function (text) {
            var data = {};
            if (text) {
                try {
                    data = JSON.parse(text);
                } catch (e) {
                    data = { message: r.status === 419 ? @json(__('Page expired. Please refresh and try again.')) : MSG_ERROR };
                }
            }
            return { ok: r.ok, status: r.status, data: data };
        });
    }

    function applyCouponCode() {
        var code = couponInput.value.trim();
        if (!code) {
            couponMsg.textContent = MSG_EMPTY;
            couponMsg.className = 'small mt-2 mb-1 text-danger';
            updateTotals(0);
            setCouponApplied(false);
            return Promise.resolve(false);
        }

        applyBtn.disabled = true;
        var body = new FormData();
        body.append('coupon_code', code);
        body.append('_token', csrfToken);

        return fetch(validateUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body,
            credentials: 'same-origin'
        })
        .then(parseJsonResponse)
        .then(function (res) {
            applyBtn.disabled = false;
            if (!res.ok) {
                couponMsg.textContent = res.data.message || MSG_INVALID;
                couponMsg.className = 'small mt-2 mb-1 text-danger';
                updateTotals(0);
                setCouponApplied(false);
                return false;
            }
            couponInput.value = res.data.code || code;
            couponMsg.textContent = res.data.message || MSG_INVALID;
            couponMsg.className = 'small mt-2 mb-1 text-success';
            updateTotals(res.data.discount, res.data.tax, res.data.shipping_charge, res.data.shipping_label);
            setCouponApplied(true);
            return true;
        })
        .catch(function () {
            applyBtn.disabled = false;
            couponMsg.textContent = MSG_ERROR;
            couponMsg.className = 'small mt-2 mb-1 text-danger';
            return false;
        });
    }

    function removeCouponCode() {
        if (!removeUrl) {
            couponInput.value = '';
            couponMsg.textContent = MSG_HELP;
            couponMsg.className = 'small mt-2 mb-1 text-muted';
            updateTotals(0);
            setCouponApplied(false);
            return Promise.resolve(true);
        }

        if (removeBtn) {
            removeBtn.disabled = true;
        }
        applyBtn.disabled = true;

        var body = new FormData();
        body.append('_token', csrfToken);

        return fetch(removeUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: body,
            credentials: 'same-origin'
        })
        .then(parseJsonResponse)
        .then(function (res) {
            if (removeBtn) {
                removeBtn.disabled = false;
            }
            applyBtn.disabled = false;
            if (!res.ok) {
                couponMsg.textContent = res.data.message || MSG_ERROR;
                couponMsg.className = 'small mt-2 mb-1 text-danger';
                return false;
            }
            couponInput.value = '';
            couponMsg.textContent = res.data.message || MSG_REMOVED;
            couponMsg.className = 'small mt-2 mb-1 text-muted';
            updateTotals(0, res.data.tax, res.data.shipping_charge, res.data.shipping_label);
            setCouponApplied(false);
            return true;
        })
        .catch(function () {
            if (removeBtn) {
                removeBtn.disabled = false;
            }
            applyBtn.disabled = false;
            couponMsg.textContent = MSG_ERROR;
            couponMsg.className = 'small mt-2 mb-1 text-danger';
            return false;
        });
    }

    applyBtn.addEventListener('click', function (e) {
        e.preventDefault();
        applyCouponCode();
    });

    if (removeBtn) {
        removeBtn.addEventListener('click', function (e) {
            e.preventDefault();
            removeCouponCode();
        });
    }

    couponInput.addEventListener('keydown', function (e) {
        if (e.key === 'Enter') {
            e.preventDefault();
            applyCouponCode();
        }
    });

    document.querySelectorAll('.js-copy-coupon').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var code = btn.getAttribute('data-code') || '';
            if (!code) {
                return;
            }
            couponInput.value = code;
            var label = btn.textContent;
            var copied = copyText(code);
            btn.textContent = copied ? MSG_COPIED : MSG_FILLED;
            setTimeout(function () { btn.textContent = label; }, copied ? 1600 : 1800);
            applyCouponCode();
        });
    });
});
</script>
@endpush
@endsection

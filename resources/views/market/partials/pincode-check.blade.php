{{-- Shared pincode serviceability checker (PDP + checkout) --}}
@php
    $pinId = $pinId ?? 'pincodeCheck';
    $pinValue = $pinValue ?? '';
    $pinAutofill = !empty($pinAutofill);
@endphp
<div class="pro-pincode-check" data-check-url="{{ route('pincode.check') }}" data-autofill="{{ $pinAutofill ? '1' : '0' }}">
    <label class="pro-pincode-check__label" for="{{ $pinId }}">{{ __('Check delivery') }}</label>
    <div class="pro-pincode-check__row">
        <input type="text" id="{{ $pinId }}" class="pro-pincode-check__input js-pincode-input" inputmode="numeric" maxlength="6" pattern="\d{6}" placeholder="{{ __('Enter pincode') }}" value="{{ $pinValue }}" autocomplete="postal-code">
        <button type="button" class="pro-pincode-check__btn js-pincode-check-btn">{{ __('Check') }}</button>
    </div>
    <p class="pro-pincode-check__msg js-pincode-msg" hidden></p>
</div>

@once
@push('scripts')
<script>
(function () {
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';

    function digits(value) {
        return String(value || '').replace(/\D/g, '').slice(0, 6);
    }

    function setMsg(wrap, text, ok) {
        var msg = wrap.querySelector('.js-pincode-msg');
        if (!msg) return;
        msg.hidden = !text;
        msg.textContent = text || '';
        msg.classList.toggle('is-ok', !!ok);
        msg.classList.toggle('is-bad', !ok && !!text);
    }

    function applyAutofill(data) {
        var city = document.querySelector('.js-checkout-city-field');
        if (city && data.city) city.value = data.city;
        var stateHidden = document.getElementById('stateHiddenInput');
        var stateLabel = document.getElementById('stateDropdownLabel');
        if (stateHidden && data.state) {
            stateHidden.value = data.state;
            if (stateLabel) stateLabel.textContent = data.state;
        }
    }

    function setCheckoutReady(serviceable) {
        var btn = document.getElementById('checkoutPlaceBtn');
        var form = document.getElementById('checkoutMainForm');
        if (btn) {
            btn.disabled = !serviceable;
            btn.classList.toggle('is-disabled', !serviceable);
        }
        if (form) form.setAttribute('data-pincode-ok', serviceable ? '1' : '0');
    }

    function checkWrap(wrap, pin) {
        var url = wrap.getAttribute('data-check-url');
        var btn = wrap.querySelector('.js-pincode-check-btn');
        pin = digits(pin);
        wrap.querySelector('.js-pincode-input').value = pin;
        if (pin.length !== 6) {
            setMsg(wrap, 'Enter a valid 6-digit pincode.', false);
            if (document.getElementById('checkoutMainForm')) setCheckoutReady(false);
            return;
        }
        if (btn) btn.disabled = true;
        fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': csrfToken,
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin',
            body: JSON.stringify({ pincode: pin })
        })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                var data = res.data || {};
                var serviceable = !!data.serviceable;
                setMsg(wrap, data.message || '', serviceable);
                if (serviceable && wrap.getAttribute('data-autofill') === '1') applyAutofill(data);
                if (document.getElementById('checkoutMainForm')) setCheckoutReady(serviceable);
            })
            .catch(function () {
                setMsg(wrap, 'Could not check this pincode. Try again.', false);
                if (document.getElementById('checkoutMainForm')) setCheckoutReady(false);
            })
            .finally(function () {
                if (btn) btn.disabled = false;
            });
    }

    document.querySelectorAll('.pro-pincode-check').forEach(function (wrap) {
        var input = wrap.querySelector('.js-pincode-input');
        var btn = wrap.querySelector('.js-pincode-check-btn');
        if (!input || !btn) return;
        btn.addEventListener('click', function () { checkWrap(wrap, input.value); });
        input.addEventListener('keydown', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                checkWrap(wrap, input.value);
            }
        });
        input.addEventListener('input', function () {
            input.value = digits(input.value);
        });
        if (digits(input.value).length === 6 && document.getElementById('checkoutMainForm')) {
            checkWrap(wrap, input.value);
        }
    });

    var checkoutWrap = document.querySelector('#checkoutMainForm .pro-pincode-check');
    if (checkoutWrap) {
        document.querySelectorAll('#checkoutMainForm input[name="address_id"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                var pin = radio.getAttribute('data-pincode') || '';
                if (digits(pin).length === 6) checkWrap(checkoutWrap, pin);
                else if (radio.value === '') {
                    var field = document.querySelector('.js-checkout-pincode-field');
                    if (field && digits(field.value).length === 6) checkWrap(checkoutWrap, field.value);
                    else setCheckoutReady(false);
                }
            });
        });
        var field = document.querySelector('.js-checkout-pincode-field');
        if (field) {
            field.addEventListener('input', function () {
                field.value = digits(field.value);
                var checkInput = checkoutWrap.querySelector('.js-pincode-input');
                if (checkInput) checkInput.value = field.value;
                if (field.value.length === 6) checkWrap(checkoutWrap, field.value);
            });
        }
        var form = document.getElementById('checkoutMainForm');
        if (form) {
            form.addEventListener('submit', function (e) {
                if (form.getAttribute('data-pincode-ok') !== '1') {
                    e.preventDefault();
                    var msg = checkoutWrap.querySelector('.js-pincode-msg');
                    if (msg && msg.hidden) setMsg(checkoutWrap, 'Please check delivery for your pincode before placing the order.', false);
                    checkoutWrap.scrollIntoView({ behavior: 'smooth', block: 'center' });
                }
            });
        }
    }
})();
</script>
@endpush
@endonce


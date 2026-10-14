@php
    $otpService = app(\App\Services\Msg91OtpService::class);
    $otpAvailable = $otpService->isAvailable();
    $otpLength = $otpService->otpLength();
    $otpPhone = old('phone', '');
    $otpPhoneDigits = preg_replace('/\D+/', '', (string) $otpPhone) ?? '';
    $otpPhoneValid = (bool) preg_match('/^[6-9][0-9]{9}$/', $otpPhoneDigits);
    $otpCooldown = $otpPhoneValid ? $otpService->cooldownRemaining($otpPhoneDigits) : 0;
    $showOtpStep = $errors->has('code');
    $otpSendDisabled = ! $otpAvailable || $otpCooldown > 0 || ! $otpPhoneValid;
    $otpResendDisabled = $otpCooldown > 0;
    $otpDisplayPhone = $otpPhoneValid ? '+91 '.substr($otpPhoneDigits, 0, 5).' '.substr($otpPhoneDigits, 5) : '';
@endphp

@if(! $otpAvailable)
    <p class="small text-muted mb-3">{{ __('Mobile OTP login is not enabled yet. Use email or Google sign-in.') }}</p>
@elseif($otpService->isDummy())
    <p class="small mb-3" style="background:#fff7ed;color:#9a3412;border:1px solid #fed7aa;border-radius:8px;padding:0.65rem 0.8rem;">
        {{ __('Dummy OTP mode (no MSG91 template yet). Use code :code', ['code' => $otpService->dummyCode()]) }}
    </p>
@endif
    <form method="POST" action="{{ route('login.phone.send') }}" class="mb-3" id="otpSendForm" novalidate>
        @csrf
        <label class="form-label" for="otp-phone">{{ __('Mobile number') }}</label>
        <div class="input-group mb-2">
            <span class="input-group-text">+91</span>
            <input id="otp-phone" type="tel" name="phone" class="form-control @error('phone') is-invalid @enderror" value="{{ $otpPhone }}" required inputmode="numeric" autocomplete="off" maxlength="10" pattern="[6-9][0-9]{9}" placeholder="9876543210" data-lpignore="true" data-1p-ignore>
        </div>
        <div id="otpPhoneError" class="otp-phone-error" @if(! $errors->has('phone')) hidden @endif>{{ $errors->first('phone') ?: __('Kindly enter a valid mobile number') }}</div>
        <button type="submit" class="btn btn-primary auth-login-submit w-100 text-white" id="otpSendBtn" @if($otpSendDisabled) disabled @endif>
            {{ __('Send OTP') }}
        </button>
        <p class="small text-muted mt-2 mb-0" id="otpSendCountdown" @if($otpCooldown === 0) hidden @endif>
            {{ __('You can resend in') }} <span id="otpSendSec">{{ $otpCooldown }}</span>s
        </p>
    </form>

@once
@push('head')
<style>
    #otpSendBtn.auth-login-submit:disabled {
        background: #c4c8ce !important;
        border-color: #c4c8ce !important;
        color: #5f6368 !important;
        box-shadow: none !important;
        transform: none !important;
        opacity: 1 !important;
        cursor: not-allowed;
    }
    #otpPhoneError.otp-phone-error {
        color: #dc3545;
        font-size: 0.8125rem;
        margin: -0.25rem 0 0.75rem;
    }
    #otpPhoneError[hidden] {
        display: none !important;
    }
    .otp-verify-modal .modal-content {
        border: 0;
        border-radius: 16px;
        box-shadow: 0 18px 48px rgba(15, 23, 42, 0.16);
    }
    .otp-verify-modal__title {
        font-size: 1.25rem;
        font-weight: 700;
        color: #222;
        margin: 0;
    }
    .otp-verify-modal__phone {
        color: #4b5563;
        font-size: 0.9rem;
        margin: 0.35rem 0 0;
    }
    .otp-verify-modal__phone strong { color: #111827; letter-spacing: 0.02em; }
    .otp-edit-mobile {
        border: 0;
        background: none;
        color: #ec8951;
        font-weight: 600;
        font-size: 0.8125rem;
        padding: 0;
        margin-left: 0.35rem;
        text-decoration: none;
    }
    .otp-edit-mobile:hover { text-decoration: underline; color: #d67840; }
    .otp-verify-status {
        font-size: 0.8125rem;
        border-radius: 8px;
        padding: 0.6rem 0.75rem;
        margin: 0.85rem 0 0;
        text-align: center;
    }
    .otp-verify-status[hidden] { display: none !important; }
    .otp-verify-status.is-success { background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; }
    .otp-verify-status.is-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .otp-verify-status.is-info { background: #fff7ed; color: #9a3412; border: 1px solid #fed7aa; }
    .otp-digit-row {
        display: flex;
        justify-content: center;
        gap: clamp(0.35rem, 2vw, 0.55rem);
        margin: 1.15rem 0 0.25rem;
    }
    .otp-digit-box {
        width: clamp(2.35rem, 11vw, 2.85rem);
        height: clamp(2.6rem, 12vw, 3.1rem);
        text-align: center;
        font-size: 1.25rem;
        font-weight: 700;
        border: 1px solid #e5e7eb;
        border-radius: 10px;
        color: #111827;
        background: #fff;
    }
    .otp-digit-box:focus {
        outline: none;
        border-color: #ec8951;
        box-shadow: 0 0 0 3px rgba(236, 137, 81, 0.16);
    }
    .otp-digit-box.is-error { border-color: #dc3545; }
    .otp-resend-wrap {
        margin-top: 1.15rem;
        padding-top: 1rem;
        border-top: 1px solid #f1f5f9;
        text-align: center;
    }
    .otp-resend-wrap__hint {
        color: #6b7280;
        font-size: 0.8125rem;
        margin: 0 0 0.65rem;
    }
    #otpModalResendBtn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        min-width: 11.5rem;
        padding: 0.55rem 1.15rem;
        border: 1px solid #ec8951;
        border-radius: 8px;
        background: #fff;
        color: #ec8951;
        font-weight: 600;
        font-size: 0.875rem;
        line-height: 1.2;
        text-decoration: none;
        box-shadow: none;
        transition: background 0.2s ease, color 0.2s ease, border-color 0.2s ease;
    }
    #otpModalResendBtn:hover:not(:disabled) {
        background: #fff7ed;
        color: #d67840;
        border-color: #d67840;
    }
    #otpModalResendBtn:disabled {
        background: #f8fafc !important;
        border-color: #e5e7eb !important;
        color: #9ca3af !important;
        cursor: not-allowed;
        box-shadow: none !important;
        transform: none !important;
        opacity: 1 !important;
    }
</style>
@endpush
@push('body_end')
<div class="modal fade otp-verify-modal" id="otpVerifyModal" tabindex="-1" aria-labelledby="otpVerifyModalTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0 align-items-start">
                <div>
                    <h2 class="otp-verify-modal__title" id="otpVerifyModalTitle">{{ __('Verify mobile') }}</h2>
                    <p class="otp-verify-modal__phone">
                        {{ __('OTP sent to') }}
                        <strong id="otpModalPhoneLabel">{{ $otpDisplayPhone }}</strong>
                        <button type="button" class="otp-edit-mobile" id="otpEditMobileBtn">{{ __('Edit mobile') }}</button>
                    </p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('Close') }}"></button>
            </div>
            <div class="modal-body pt-2 pb-4">
                <div id="otpModalStatus" class="otp-verify-status" hidden></div>
                <form method="POST" action="{{ route('login.phone.verify') }}" id="otpVerifyForm" novalidate>
                    @csrf
                    <input type="hidden" name="phone" id="otpVerifyPhone" value="{{ $otpPhoneDigits }}">
                    <input type="hidden" name="code" id="otpVerifyCode" value="">
                    <div class="otp-digit-row" id="otpDigitRow" data-length="{{ $otpLength }}">
                        @for($i = 0; $i < $otpLength; $i++)
                            <input
                                type="text"
                                class="otp-digit-box"
                                inputmode="numeric"
                                pattern="[0-9]*"
                                maxlength="1"
                                autocomplete="{{ $i === 0 ? 'one-time-code' : 'off' }}"
                                aria-label="{{ __('Digit :n', ['n' => $i + 1]) }}"
                                data-otp-index="{{ $i }}"
                            >
                        @endfor
                    </div>
                    <button type="submit" class="btn btn-primary auth-login-submit w-100 text-white mt-3" id="otpVerifyBtn">
                        {{ __('Verify & sign in') }}
                    </button>
                </form>
                <div class="otp-resend-wrap">
                    <p class="otp-resend-wrap__hint">{{ __('Didn’t receive the code?') }}</p>
                    <button type="button" class="otp-resend-btn" id="otpModalResendBtn" @if($otpResendDisabled) disabled @endif>
                        {{ $otpCooldown > 0 ? __('Resend in :s s', ['s' => $otpCooldown]) : __('Resend OTP') }}
                    </button>
                    <span id="otpModalCountdown" hidden></span>
                    <span id="otpModalSec" hidden>{{ $otpCooldown }}</span>
                </div>
            </div>
        </div>
    </div>
</div>
@endpush
@push('scripts')
<script>
(function () {
    var phone = document.getElementById('otp-phone');
    var errorEl = document.getElementById('otpPhoneError');
    var sendForm = document.getElementById('otpSendForm');
    var sendBtn = document.getElementById('otpSendBtn');
    var countdown = document.getElementById('otpSendCountdown');
    var secondsEl = document.getElementById('otpSendSec');
    var remaining = {{ (int) $otpCooldown }};
    var otpAvailable = {{ $otpAvailable ? 'true' : 'false' }};
    var otpLength = {{ (int) $otpLength }};
    var lastSentPhone = @json($showOtpStep ? $otpPhoneDigits : '');
    var sendLabel = @json(__('Send OTP'));
    var sendingLabel = @json(__('Sending…'));
    var verifyLabel = @json(__('Verify & sign in'));
    var verifyingLabel = @json(__('Verifying…'));
    var resendLabel = @json(__('Resend OTP'));
    var networkError = @json(__('Network error. Please try again.'));
    var genericError = @json(__('Something went wrong. Please try again.'));
    var modalEl = document.getElementById('otpVerifyModal');
    var modalStatus = document.getElementById('otpModalStatus');
    var modalPhoneLabel = document.getElementById('otpModalPhoneLabel');
    var verifyForm = document.getElementById('otpVerifyForm');
    var verifyPhone = document.getElementById('otpVerifyPhone');
    var verifyCode = document.getElementById('otpVerifyCode');
    var verifyBtn = document.getElementById('otpVerifyBtn');
    var boxes = modalEl ? modalEl.querySelectorAll('.otp-digit-box') : [];
    var editBtn = document.getElementById('otpEditMobileBtn');
    var resendBtn = document.getElementById('otpModalResendBtn');
    var modalCountdown = document.getElementById('otpModalCountdown');
    var modalSec = document.getElementById('otpModalSec');
    var cooldownTimer = null;
    var verifying = false;
    var modalInstance = null;
    var openOnLoad = {{ $showOtpStep ? 'true' : 'false' }};
    var resendUrl = @json(route('login.phone.resend'));
    var csrf = document.querySelector('meta[name="csrf-token"]');
    var csrfToken = csrf ? csrf.getAttribute('content') : '';

    function isValidIndianMobile(value) {
        return /^[6-9][0-9]{9}$/.test(String(value || ''));
    }

    function displayPhone(national) {
        var d = String(national || '').replace(/\D/g, '').slice(0, 10);
        if (d.length !== 10) return '+91 ' + d;
        return '+91 ' + d.slice(0, 5) + ' ' + d.slice(5);
    }

    function showPhoneError(show, message) {
        if (!errorEl) return;
        if (message) errorEl.textContent = message;
        errorEl.hidden = !show;
        if (phone) phone.classList.toggle('is-invalid', show);
    }

    function setSendEnabled(enabled) {
        if (!sendBtn) return;
        sendBtn.disabled = !enabled;
        if (enabled) sendBtn.removeAttribute('disabled');
        else sendBtn.setAttribute('disabled', 'disabled');
    }

    function canSend(value) {
        var cooling = remaining > 0 && value === lastSentPhone;
        return otpAvailable && !cooling && isValidIndianMobile(value);
    }

    function syncPhone() {
        if (!phone) return '';
        phone.value = String(phone.value || '').replace(/\D/g, '').slice(0, 10);
        var value = phone.value;
        showPhoneError(value.length === 10 && ! isValidIndianMobile(value));
        setSendEnabled(canSend(value) && sendBtn && sendBtn.textContent !== sendingLabel);
        return value;
    }

    function firstError(data, fallback) {
        if (data && data.errors) {
            var keys = Object.keys(data.errors);
            if (keys.length && data.errors[keys[0]] && data.errors[keys[0]][0]) {
                return data.errors[keys[0]][0];
            }
        }
        return (data && data.message) || fallback;
    }

    function setModalStatus(message, type) {
        if (!modalStatus) return;
        if (!message) {
            modalStatus.hidden = true;
            modalStatus.textContent = '';
            modalStatus.className = 'otp-verify-status';
            return;
        }
        modalStatus.hidden = false;
        modalStatus.textContent = message;
        modalStatus.className = 'otp-verify-status is-' + (type || 'info');
    }

    function setResendLabel() {
        if (!resendBtn) return;
        resendBtn.textContent = remaining > 0
            ? @json(__('Resend in :s s')).replace(':s', String(Math.max(0, remaining)))
            : resendLabel;
    }

    function startCooldown(seconds) {
        remaining = Math.max(0, parseInt(seconds, 10) || 0);
        clearInterval(cooldownTimer);
        var cooling = remaining > 0;
        if (countdown) countdown.hidden = !cooling;
        if (secondsEl) secondsEl.textContent = String(remaining);
        if (modalSec) modalSec.textContent = String(remaining);
        if (resendBtn) resendBtn.disabled = cooling;
        setResendLabel();
        syncPhone();
        if (!cooling) return;
        cooldownTimer = setInterval(function () {
            remaining -= 1;
            if (secondsEl) secondsEl.textContent = String(Math.max(0, remaining));
            if (modalSec) modalSec.textContent = String(Math.max(0, remaining));
            setResendLabel();
            if (remaining <= 0) {
                clearInterval(cooldownTimer);
                if (countdown) countdown.hidden = true;
                if (resendBtn) resendBtn.disabled = false;
                setResendLabel();
                syncPhone();
            }
        }, 1000);
    }

    function getModal() {
        if (modalInstance) return modalInstance;
        if (window.bootstrap && bootstrap.Modal && modalEl) {
            modalInstance = bootstrap.Modal.getOrCreateInstance(modalEl, { backdrop: 'static', keyboard: true });
        }
        return modalInstance;
    }

    function showModal() {
        var inst = getModal();
        if (inst) {
            inst.show();
            return;
        }
        if (!modalEl) return;
        modalEl.classList.add('show');
        modalEl.style.display = 'block';
        modalEl.removeAttribute('aria-hidden');
        document.body.classList.add('modal-open');
    }

    function hideModal() {
        var inst = getModal();
        if (inst) {
            inst.hide();
            return;
        }
        if (!modalEl) return;
        modalEl.classList.remove('show');
        modalEl.style.display = 'none';
        modalEl.setAttribute('aria-hidden', 'true');
        document.body.classList.remove('modal-open');
    }

    function currentCode() {
        var code = '';
        boxes.forEach(function (box) { code += String(box.value || '').replace(/\D/g, ''); });
        if (verifyCode) verifyCode.value = code;
        return code;
    }

    function clearDigits() {
        boxes.forEach(function (box) {
            box.value = '';
            box.classList.remove('is-error');
        });
        if (verifyCode) verifyCode.value = '';
        verifying = false;
        if (verifyBtn) {
            verifyBtn.disabled = false;
            verifyBtn.textContent = verifyLabel;
        }
    }

    function fillDigits(digits) {
        var chars = String(digits || '').replace(/\D/g, '').slice(0, otpLength).split('');
        boxes.forEach(function (box, i) { box.value = chars[i] || ''; });
        currentCode();
        var focusAt = Math.min(chars.length, boxes.length - 1);
        if (boxes[focusAt]) boxes[focusAt].focus();
    }

    function markBoxesError(on) {
        boxes.forEach(function (box) { box.classList.toggle('is-error', !!on); });
    }

    function openVerifyModal(payload) {
        var national = (payload && payload.phone) || (phone ? phone.value : '');
        lastSentPhone = national;
        if (verifyPhone) verifyPhone.value = national;
        if (modalPhoneLabel) modalPhoneLabel.textContent = (payload && payload.display_phone) || displayPhone(national);
        clearDigits();
        markBoxesError(false);
        setModalStatus((payload && payload.message) || '', payload && payload.dummy ? 'info' : 'success');
        showModal();
        window.setTimeout(function () {
            if (boxes[0]) boxes[0].focus();
        }, 220);
        if (payload && payload.cooldown) startCooldown(payload.cooldown);
    }

    function submitVerify() {
        if (!verifyForm || verifying) return;
        var code = currentCode();
        if (code.length !== otpLength) return;
        verifying = true;
        markBoxesError(false);
        if (verifyBtn) {
            verifyBtn.disabled = true;
            verifyBtn.textContent = verifyingLabel;
        }
        setModalStatus('', '');
        var fd = new FormData(verifyForm);
        fetch(verifyForm.action, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-TOKEN': csrfToken
            },
            body: fd
        })
        .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, status: r.status, data: data }; }); })
        .then(function (res) {
            if (res.ok && res.data && res.data.ok) {
                setModalStatus(res.data.message || @json(__('Mobile number verified. Signing you in…')), 'success');
                window.setTimeout(function () {
                    window.location.href = res.data.redirect || '/';
                }, 700);
                return;
            }
            verifying = false;
            if (verifyBtn) {
                verifyBtn.disabled = false;
                verifyBtn.textContent = verifyLabel;
            }
            setModalStatus(firstError(res.data, genericError), 'error');
            clearDigits();
            markBoxesError(true);
            if (boxes[0]) boxes[0].focus();
        })
        .catch(function () {
            verifying = false;
            if (verifyBtn) {
                verifyBtn.disabled = false;
                verifyBtn.textContent = verifyLabel;
            }
            setModalStatus(networkError, 'error');
        });
    }

    if (phone) {
        ['input', 'change', 'keyup', 'paste'].forEach(function (evt) {
            phone.addEventListener(evt, function () {
                if (evt === 'paste') {
                    window.setTimeout(syncPhone, 0);
                    return;
                }
                syncPhone();
            });
        });
        phone.addEventListener('blur', function () {
            showPhoneError(phone.value.length === 10 && ! isValidIndianMobile(phone.value));
        });
        syncPhone();
    }

    if (sendForm) {
        sendForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var value = syncPhone();
            if (! isValidIndianMobile(value)) {
                showPhoneError(true);
                if (phone) phone.focus();
                return;
            }
            setSendEnabled(false);
            if (sendBtn) sendBtn.textContent = sendingLabel;
            fetch(sendForm.action, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: new FormData(sendForm)
            })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (sendBtn) sendBtn.textContent = sendLabel;
                if (res.ok && res.data && res.data.ok) {
                    showPhoneError(false);
                    openVerifyModal(res.data);
                    return;
                }
                syncPhone();
                showPhoneError(true, firstError(res.data, genericError));
            })
            .catch(function () {
                if (sendBtn) sendBtn.textContent = sendLabel;
                syncPhone();
                showPhoneError(true, networkError);
            });
        });
    }

    boxes.forEach(function (box, index) {
        box.addEventListener('input', function () {
            var digits = String(box.value || '').replace(/\D/g, '');
            markBoxesError(false);
            if (digits.length > 1) {
                fillDigits((currentCode().slice(0, index) + digits).slice(0, otpLength));
                if (currentCode().length === otpLength) submitVerify();
                return;
            }
            box.value = digits.slice(0, 1);
            currentCode();
            if (box.value && index < boxes.length - 1) boxes[index + 1].focus();
            if (currentCode().length === otpLength) submitVerify();
        });
        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace') {
                e.preventDefault();
                markBoxesError(false);
                if (box.value) {
                    box.value = '';
                } else if (index > 0) {
                    boxes[index - 1].value = '';
                    boxes[index - 1].focus();
                }
                currentCode();
                return;
            }
            if (e.key === 'ArrowLeft' && index > 0) {
                e.preventDefault();
                boxes[index - 1].focus();
            }
            if (e.key === 'ArrowRight' && index < boxes.length - 1) {
                e.preventDefault();
                boxes[index + 1].focus();
            }
        });
        box.addEventListener('paste', function (e) {
            e.preventDefault();
            var pasted = ((e.clipboardData || window.clipboardData).getData('text') || '').replace(/\D/g, '');
            fillDigits(pasted);
            if (currentCode().length === otpLength) submitVerify();
        });
        box.addEventListener('focus', function () { box.select(); });
    });

    if (verifyForm) {
        verifyForm.addEventListener('submit', function (e) {
            e.preventDefault();
            var code = currentCode();
            if (code.length !== otpLength) {
                setModalStatus(@json(__('Please enter the :n-digit OTP.', ['n' => $otpLength])), 'error');
                markBoxesError(true);
                if (boxes[0]) boxes[0].focus();
                return;
            }
            submitVerify();
        });
    }

    if (editBtn) {
        editBtn.addEventListener('click', function () {
            hideModal();
            clearDigits();
            setModalStatus('', '');
            if (phone) {
                phone.focus();
                phone.select();
            }
        });
    }

    if (resendBtn) {
        resendBtn.addEventListener('click', function () {
            var value = (verifyPhone && verifyPhone.value) || (phone && phone.value) || '';
            if (! isValidIndianMobile(value)) return;
            resendBtn.disabled = true;
            var fd = new FormData();
            fd.append('_token', csrfToken);
            fd.append('phone', value);
            fetch(resendUrl, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: fd
            })
            .then(function (r) { return r.json().then(function (data) { return { ok: r.ok, data: data }; }); })
            .then(function (res) {
                if (res.ok && res.data && res.data.ok) {
                    clearDigits();
                    setModalStatus(res.data.message, res.data.dummy ? 'info' : 'success');
                    if (res.data.cooldown) startCooldown(res.data.cooldown);
                    if (boxes[0]) boxes[0].focus();
                    return;
                }
                resendBtn.disabled = remaining > 0;
                setModalStatus(firstError(res.data, genericError), 'error');
            })
            .catch(function () {
                resendBtn.disabled = false;
                setModalStatus(networkError, 'error');
            });
        });
    }

    function bootModal() {
        if (remaining > 0) startCooldown(remaining);
        else syncPhone();
        if (openOnLoad) {
            openVerifyModal({
                phone: lastSentPhone,
                display_phone: modalPhoneLabel ? modalPhoneLabel.textContent : displayPhone(lastSentPhone),
                message: @json($errors->first('code') ?: session('status')),
                dummy: {{ $otpService->isDummy() ? 'true' : 'false' }}
            });
            if ({{ $errors->has('code') ? 'true' : 'false' }}) {
                setModalStatus(@json($errors->first('code')), 'error');
            }
        }
    }

    function resetLoginOtpUi() {
        hideModal();
        clearDigits();
        setModalStatus('', '');
        lastSentPhone = '';
        remaining = 0;
        if (phone) phone.value = '';
        syncPhone();
    }

    window.addEventListener('pageshow', function (e) {
        if (e.persisted) {
            resetLoginOtpUi();
        }
    });

    function waitForBootstrap(tries) {
        if (window.bootstrap && bootstrap.Modal) {
            bootModal();
            return;
        }
        if (tries <= 0) {
            bootModal();
            return;
        }
        window.setTimeout(function () { waitForBootstrap(tries - 1); }, 40);
    }

    waitForBootstrap(40);
})();
</script>
@endpush
@endonce

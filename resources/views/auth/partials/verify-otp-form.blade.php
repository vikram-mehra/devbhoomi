@php
    $codeLength = (int) config('verification.code_length', 6);
    $emailValue = old('email', $email ?? '');
    $service = app(\App\Services\EmailVerificationService::class);
    $resendCooldown = (int) ($resendCooldown ?? ($emailValue ? $service->resendCooldownRemaining($emailValue) : 0));
    $otpExpiresIn = (int) ($otpExpiresIn ?? 0);
    $oldCode = old('code', '');
@endphp

<form method="POST" action="{{ route('verification.verify') }}" class="auth-verify-otp-form" id="verifyOtpForm" novalidate>
    @csrf
    <input type="hidden" name="email" value="{{ $emailValue }}">

    <label class="auth-verify-otp-form__label" for="verify-code-single">{{ __('Verification code') }}</label>
    <div class="auth-verify-otp-inputs" id="verifyOtpInputs" data-length="{{ $codeLength }}">
        @for($i = 0; $i < $codeLength; $i++)
            <input
                type="text"
                class="auth-verify-otp-inputs__box"
                inputmode="numeric"
                pattern="[0-9]*"
                maxlength="1"
                autocomplete="one-time-code"
                aria-label="{{ __('Digit :n', ['n' => $i + 1]) }}"
                data-otp-index="{{ $i }}"
            >
        @endfor
    </div>
    <input type="hidden" name="code" id="verify-code-single" value="{{ $oldCode }}">
    @error('code')<div class="text-danger small mt-2 text-center">{{ $message }}</div>@enderror

    <button type="submit" class="btn btn-primary auth-verify-otp-form__submit w-100 mt-3">
        {{ __('Verify email') }}
    </button>
</form>

<div class="auth-verify-otp-resend mt-3">
    <p class="small text-muted mb-2 text-center">{{ __('Did not receive the code?') }}</p>
    <button type="button" class="btn btn-outline-secondary btn-sm w-100" id="verifyResendBtn" @if($resendCooldown > 0) disabled @endif>
        {{ __('Resend code') }}
    </button>
    <p class="auth-verify-otp-resend__countdown small text-muted mb-0 mt-2 text-center" id="verifyResendCountdown" @if($resendCooldown <= 0) hidden @endif>
        {{ __('Resend available in') }} <span id="verifyResendSec">{{ $resendCooldown }}</span>s
    </p>
    <div id="verifyResendToast" class="auth-verify-otp-resend__toast" role="status" hidden></div>
</div>

@once
@push('head')
<style>
    .auth-verify-otp-form { text-align: left; margin-top: 1rem; }
    .auth-verify-otp-form__label { display: block; font-weight: 600; font-size: 0.8125rem; color: #444; margin-bottom: 0.65rem; text-align: center; }
    .auth-verify-otp-inputs { display: flex; justify-content: center; gap: clamp(0.35rem, 2vw, 0.5rem); margin-bottom: 0.25rem; }
    .auth-verify-otp-inputs__box {
        width: clamp(2.4rem, 11vw, 2.75rem);
        height: clamp(2.6rem, 12vw, 3rem);
        text-align: center;
        font-size: clamp(1.1rem, 4vw, 1.35rem);
        font-weight: 700;
        border: 1px solid #e2e8f0;
        border-radius: 8px;
        color: #0f766e;
    }
    .auth-verify-otp-inputs__box:focus {
        outline: none;
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    .auth-verify-otp-form__submit {
        background: linear-gradient(135deg, #0d9488, #0f766e);
        border: none;
        font-weight: 600;
        border-radius: 8px;
        padding: 0.7rem 1.25rem;
    }
    .auth-verify-otp-resend__toast {
        margin-top: 0.65rem;
        padding: 0.6rem 0.75rem;
        border-radius: 8px;
        font-size: 0.8125rem;
        text-align: center;
    }
    .auth-verify-otp-resend__toast.is-success { background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; }
    .auth-verify-otp-resend__toast.is-error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
</style>
@endpush
@push('scripts')
<script>
(function () {
    var form = document.getElementById('verifyOtpForm');
    var boxes = document.querySelectorAll('.auth-verify-otp-inputs__box');
    var hidden = document.getElementById('verify-code-single');
    var resendBtn = document.getElementById('verifyResendBtn');
    var countdownEl = document.getElementById('verifyResendCountdown');
    var countdownSec = document.getElementById('verifyResendSec');
    var resendToast = document.getElementById('verifyResendToast');
    var cooldown = {{ $resendCooldown }};
    var timer = null;
    var email = @json($emailValue);

    function syncHidden() {
        if (!hidden) return;
        var code = '';
        boxes.forEach(function (b) { code += (b.value || '').replace(/\D/g, ''); });
        hidden.value = code;
    }

    function fillFromHidden() {
        if (!hidden || !hidden.value) return;
        var digits = String(hidden.value).replace(/\D/g, '').split('');
        boxes.forEach(function (b, i) { b.value = digits[i] || ''; });
    }

    boxes.forEach(function (box, index) {
        box.addEventListener('input', function () {
            box.value = box.value.replace(/\D/g, '').slice(0, 1);
            syncHidden();
            if (box.value && index < boxes.length - 1) boxes[index + 1].focus();
        });
        box.addEventListener('keydown', function (e) {
            if (e.key === 'Backspace' && !box.value && index > 0) boxes[index - 1].focus();
        });
        box.addEventListener('paste', function (e) {
            e.preventDefault();
            var pasted = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            for (var i = 0; i < boxes.length; i++) boxes[i].value = pasted[i] || '';
            syncHidden();
            boxes[Math.min(pasted.length, boxes.length - 1)].focus();
        });
    });

    if (form) form.addEventListener('submit', function () { syncHidden(); });
    fillFromHidden();
    if (boxes.length && !boxes[0].value) boxes[0].focus();

    function startCooldown(seconds) {
        cooldown = Math.max(0, parseInt(seconds, 10) || 0);
        if (cooldown <= 0) {
            if (countdownEl) countdownEl.hidden = true;
            if (resendBtn) resendBtn.disabled = false;
            return;
        }
        if (resendBtn) resendBtn.disabled = true;
        if (countdownEl) countdownEl.hidden = false;
        if (countdownSec) countdownSec.textContent = String(cooldown);
        clearInterval(timer);
        timer = setInterval(function () {
            cooldown -= 1;
            if (countdownSec) countdownSec.textContent = String(Math.max(0, cooldown));
            if (cooldown <= 0) {
                clearInterval(timer);
                if (countdownEl) countdownEl.hidden = true;
                if (resendBtn) resendBtn.disabled = false;
            }
        }, 1000);
    }

    if (cooldown > 0) startCooldown(cooldown);

    function showResendToast(msg, ok) {
        if (!resendToast) return;
        resendToast.hidden = false;
        resendToast.textContent = msg;
        resendToast.className = 'auth-verify-otp-resend__toast ' + (ok ? 'is-success' : 'is-error');
    }

    function applyDevCode(code) {
        if (!code) return;
        var devVal = document.getElementById('authVerifyDevCodeValue');
        if (devVal) devVal.textContent = code;
        fillDigits(code);
    }

    function fillDigits(code) {
        var digits = String(code).replace(/\D/g, '').split('');
        boxes.forEach(function (b, i) { b.value = digits[i] || ''; });
        if (hidden) hidden.value = String(code).replace(/\D/g, '');
    }

    if (resendBtn && email) {
        resendBtn.addEventListener('click', function () {
            resendBtn.disabled = true;
            fetch(@json(route('verification.resend')), {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': @json(csrf_token()),
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ email: email }),
            })
            .then(function (r) { return r.json().then(function (d) { return { ok: r.ok, data: d }; }); })
            .then(function (res) {
                var msg = (res.data && res.data.message) || @json(__('Something went wrong.'));
                showResendToast(msg, res.ok);
                if (res.data && res.data.dev_code) applyDevCode(res.data.dev_code);
                if (res.data && res.data.cooldown) startCooldown(res.data.cooldown);
                else if (!res.ok) resendBtn.disabled = false;
            })
            .catch(function () {
                showResendToast(@json(__('Network error. Please try again.')), false);
                resendBtn.disabled = false;
            });
        });
    }
})();
</script>
@endpush
@endonce

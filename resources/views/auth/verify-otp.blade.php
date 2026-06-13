@extends('layouts.market')

@section('title', __('Verify your email').' — '.config('app.name'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Verify email'),
        'items' => [['label' => __('Verify email')]],
    ])
@endpush

@push('head')
<style>
    .verify-otp-page {
        max-width: 480px;
        margin: 2rem auto 3.5rem;
        padding: clamp(1.25rem, 4vw, 2rem);
        border-radius: 16px;
        background: #fff;
        box-shadow: 0 4px 24px rgba(15, 23, 42, 0.08);
    }
    .verify-otp-page__icon {
        width: 56px; height: 56px; margin: 0 auto 1rem;
        border-radius: 50%;
        background: rgba(13, 148, 136, 0.12);
        color: #0d9488;
        display: flex; align-items: center; justify-content: center;
        font-size: 1.5rem;
    }
    .verify-otp-page h1 {
        font-size: clamp(1.2rem, 4vw, 1.35rem);
        font-weight: 700;
        text-align: center;
        color: #0f172a;
        margin-bottom: 0.35rem;
    }
    .verify-otp-page__lead {
        text-align: center;
        color: #64748b;
        font-size: 0.9rem;
        line-height: 1.55;
        margin-bottom: 1rem;
    }
    .verify-otp-page__email {
        font-weight: 600;
        color: #0d9488;
        word-break: break-all;
    }
    .verify-otp-alert {
        border-radius: 10px;
        padding: 0.75rem 1rem;
        font-size: 0.8125rem;
        margin-bottom: 1rem;
    }
    .verify-otp-alert--success { background: #ecfdf5; color: #166534; border: 1px solid #bbf7d0; }
    .verify-otp-alert--error { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }
    .verify-otp-alert--warning { background: #fffbeb; color: #92400e; border: 1px solid #fde68a; }
    .verify-otp-dev {
        text-align: center;
        padding: 1rem;
        border-radius: 12px;
        background: linear-gradient(135deg, #ecfdf5, #f0fdfa);
        border: 1px solid #99f6e4;
        margin-bottom: 1rem;
    }
    .verify-otp-dev__code {
        font-size: clamp(1.5rem, 6vw, 2rem);
        font-weight: 800;
        letter-spacing: 0.3em;
        color: #0f766e;
        font-variant-numeric: tabular-nums;
    }
    .verify-otp-meta {
        display: flex;
        justify-content: center;
        gap: 1rem;
        flex-wrap: wrap;
        font-size: 0.75rem;
        color: #64748b;
        margin-bottom: 0.5rem;
    }
    .verify-otp-meta strong { color: #0f766e; }
</style>
@endpush

@section('content')
@php
    $displayCode = $devCode ?? session('dev_verification_code');
    $showLocalCode = !empty($displayCode);
@endphp
<div class="verify-otp-page">
    <div class="verify-otp-page__icon" aria-hidden="true">
        <i class="bi bi-{{ $showLocalCode ? 'shield-check' : 'envelope-check' }}"></i>
    </div>
    <h1>{{ __('Verify your email') }}</h1>

    @if(session('status'))
        <div class="verify-otp-alert verify-otp-alert--success" role="status">{{ session('status') }}</div>
    @endif
    @if(session('error'))
        <div class="verify-otp-alert verify-otp-alert--error" role="alert">{{ session('error') }}</div>
    @endif
    @if(session('warning'))
        <div class="verify-otp-alert verify-otp-alert--warning" role="status">{{ session('warning') }}</div>
    @endif

    @if($showLocalCode)
        <p class="verify-otp-page__lead mb-2">
            {{ __('SMTP is not configured. Use this code to verify (localhost only):') }}
        </p>
        <div class="verify-otp-dev" id="authVerifyDevCode">
            <div class="verify-otp-dev__code" id="authVerifyDevCodeValue">{{ $displayCode }}</div>
        </div>
    @else
        <p class="verify-otp-page__lead">
            {{ __('Enter the :digit-digit code sent to', ['digit' => config('verification.code_length', 6)]) }}
            @if($email)
                <span class="verify-otp-page__email d-block mt-1">{{ $email }}</span>
            @endif
        </p>
    @endif

    @if($email && ($otpExpiresIn ?? 0) > 0)
        <div class="verify-otp-meta">
            <span>{{ __('Code expires in') }} <strong id="otpExpiryTimer">--:--</strong></span>
        </div>
    @endif

    @if($email)
        @include('auth.partials.verify-otp-form', [
            'email' => $email,
            'resendCooldown' => $resendCooldown ?? 0,
            'otpExpiresIn' => $otpExpiresIn ?? 0,
        ])
    @else
        <a href="{{ route('verification.notice') }}" class="btn btn-primary w-100 mt-3">{{ __('Enter your email') }}</a>
    @endif

    <p class="text-center small text-muted mt-4 mb-0">
        <a href="{{ route('login') }}" class="text-decoration-none" style="color:#0d9488;font-weight:600;">{{ __('Back to sign in') }}</a>
    </p>
</div>
@endsection

@push('scripts')
@if($email && ($otpExpiresIn ?? 0) > 0)
<script>
(function () {
    var sec = {{ (int) ($otpExpiresIn ?? 0) }};
    var el = document.getElementById('otpExpiryTimer');
    if (!el) return;
    function tick() {
        if (sec <= 0) { el.textContent = '0:00'; return; }
        var m = Math.floor(sec / 60);
        var s = sec % 60;
        el.textContent = m + ':' + (s < 10 ? '0' : '') + s;
        sec--;
        setTimeout(tick, 1000);
    }
    tick();
})();
</script>
@endif
@endpush

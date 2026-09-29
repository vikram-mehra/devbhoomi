@extends('layouts.market')

@section('title', __('Sign in').' — '.config('app.name'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Sign in'),
        'items' => [['label' => __('Sign in')]],
    ])
@endpush

@push('head')
<style>
    .auth-login-page { padding: 1.5rem 0 3.5rem; font-family: 'Poppins', sans-serif; }
    .auth-login-shell { max-width: 960px; margin: 0 auto; }
    .auth-login-panel {
        display: flex;
        flex-wrap: wrap;
        min-height: min(560px, calc(100vh - 220px));
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06), 0 24px 48px rgba(236, 137, 81, 0.08);
        background: #fff;
    }
    .auth-login-art {
        flex: 1 1 100%;
        position: relative;
        background: linear-gradient(152deg, #ec8951 0%, #d67840 42%, #b8642e 100%);
        color: #fff;
        padding: 2.25rem 1.75rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
    }
    @media (min-width: 992px) {
        .auth-login-art { flex: 0 0 42%; max-width: 42%; min-height: 520px; padding: 2.75rem 2.25rem; }
    }
    .auth-login-art::before,
    .auth-login-art::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.14);
        pointer-events: none;
    }
    .auth-login-art::before {
        width: 220px; height: 220px;
        top: -72px; right: -48px;
        animation: authFloat 14s ease-in-out infinite;
    }
    .auth-login-art::after {
        width: 160px; height: 160px;
        bottom: 10%; left: -40px;
        animation: authFloat 11s ease-in-out infinite reverse;
    }
    .auth-login-blob {
        position: absolute;
        width: 120px; height: 120px;
        background: rgba(255,255,255,0.08);
        border-radius: 40% 60% 55% 45%;
        top: 45%; right: 12%;
        animation: authMorph 12s ease-in-out infinite;
    }
    @keyframes authFloat {
        0%, 100% { transform: translate(0, 0) rotate(0deg); opacity: 0.9; }
        50% { transform: translate(-12px, 14px) rotate(6deg); opacity: 1; }
    }
    @keyframes authMorph {
        0%, 100% { border-radius: 40% 60% 55% 45%; transform: rotate(0deg) scale(1); }
        50% { border-radius: 55% 45% 40% 60%; transform: rotate(12deg) scale(1.06); }
    }
    @media (prefers-reduced-motion: reduce) {
        .auth-login-art::before, .auth-login-art::after, .auth-login-blob { animation: none; }
    }
    .auth-login-brand {
        position: relative;
        z-index: 1;
        font-weight: 700;
        font-size: 1.35rem;
        letter-spacing: -0.02em;
        margin-bottom: 0.75rem;
    }
    .auth-login-brand span { opacity: 0.92; font-weight: 500; }
    .auth-login-tagline {
        position: relative;
        z-index: 1;
        font-size: 1.5rem;
        font-weight: 700;
        line-height: 1.25;
        margin-bottom: 1.25rem;
        text-shadow: 0 2px 20px rgba(0,0,0,0.12);
    }
    .auth-login-perks {
        position: relative;
        z-index: 1;
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.875rem;
        opacity: 0.95;
        line-height: 1.85;
    }
    .auth-login-perks li {
        display: flex;
        align-items: flex-start;
        gap: 0.5rem;
        margin-bottom: 0.35rem;
    }
    .auth-login-perks li::before {
        content: '✓';
        font-weight: 700;
        flex-shrink: 0;
        opacity: 0.85;
    }
    .auth-login-form-wrap {
        flex: 1 1 100%;
        padding: 2rem 1.5rem 2.25rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #fff;
    }
    @media (min-width: 992px) {
        .auth-login-form-wrap { flex: 1 1 58%; max-width: 58%; padding: 2.75rem 2.5rem 3rem; }
    }
    .auth-login-form-wrap h1 {
        font-size: 1.65rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 0.35rem;
    }
    .auth-login-sub { color: #777; font-size: 0.9rem; margin-bottom: 1.75rem; }
    .auth-login-form-wrap .form-label { font-weight: 600; font-size: 0.8125rem; color: #444; margin-bottom: 0.4rem; }
    .auth-login-form-wrap .form-control {
        border-radius: 8px;
        border: 1px solid #e8e8e8;
        padding: 0.65rem 0.9rem;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .auth-login-form-wrap .form-control:focus {
        border-color: #ec8951;
        box-shadow: 0 0 0 3px rgba(236, 137, 81, 0.12);
    }
    .auth-login-submit {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.7rem 1.25rem;
        background: #ec8951;
        border: none;
        transition: transform 0.2s ease, box-shadow 0.2s ease, background 0.2s ease;
    }
    .auth-login-submit:hover {
        background: #d67840;
        transform: translateY(-1px);
        box-shadow: 0 8px 20px rgba(236, 137, 81, 0.35);
        color: #fff;
    }
    .auth-login-links a { color: #ec8951; text-decoration: none; font-weight: 500; font-size: 0.875rem; }
    .auth-login-links a:hover { text-decoration: underline; }
    .auth-social-or {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.35rem 0 1.15rem;
        color: #9ca3af;
        font-size: 0.8125rem;
        font-weight: 400;
        text-transform: lowercase;
    }
    .auth-social-or::before,
    .auth-social-or::after {
        content: '';
        flex: 1;
        height: 1px;
        background: #e5e7eb;
    }
    .auth-google-btn {
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 0.65rem;
        width: 100%;
        padding: 0.72rem 1.15rem;
        border: 1px solid #dadce0;
        border-radius: 8px;
        background: #fff;
        color: #3c4043;
        font-weight: 600;
        font-size: 0.9375rem;
        text-decoration: none;
        transition: background 0.2s ease, box-shadow 0.2s ease, border-color 0.2s ease;
    }
    .auth-google-btn:hover:not(:disabled) {
        background: #f8f9fa;
        border-color: #c6c9cc;
        box-shadow: 0 1px 3px rgba(60, 64, 67, 0.12);
        color: #202124;
    }
    .auth-google-btn--disabled {
        opacity: 0.72;
        cursor: not-allowed;
    }
    .auth-google-btn__icon {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        flex-shrink: 0;
        line-height: 0;
    }
    .auth-google-btn__label {
        line-height: 1.2;
        font-weight: 600;
        color: #3c4043;
    }
    .auth-google-setup-hint code {
        font-size: 0.75rem;
        background: #f3f4f6;
        padding: 0.35rem 0.5rem;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<div class="auth-login-page">
    <div class="auth-login-shell">
        <div class="auth-login-panel">
            <div class="auth-login-art d-none d-lg-flex">
                <div class="auth-login-blob" aria-hidden="true"></div>
                <div class="auth-login-brand">{{ config('app.name') }} <span>Market</span></div>
                <p class="auth-login-tagline">{{ __('Shop smarter. Sign in to track orders & wishlists.') }}</p>
                <ul class="auth-login-perks">
                    <li>{{ __('Secure checkout & saved addresses') }}</li>
                    <li>{{ __('Order history in one place') }}</li>
                    <li>{{ __('Wishlist sync across devices') }}</li>
                </ul>
            </div>
            <div class="auth-login-form-wrap">
                <div class="d-lg-none text-center pb-3 mb-3 border-bottom" style="border-color:#f0f0f0!important;">
                    <div class="fw-bold" style="color:#ec8951;font-size:1.1rem;">{{ config('app.name') }}</div>
                    <div class="small text-muted">{{ __('Sign in to your account') }}</div>
                </div>
                <h1>{{ __('Welcome back') }}</h1>
                <p class="auth-login-sub">{{ __('Enter your details to continue shopping.') }}</p>

                <form method="POST" action="{{ route('login') }}" class="auth-login-form">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="login-email">{{ __('Email') }}</label>
                        <input id="login-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="username" placeholder="name@example.com">
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="login-password">{{ __('Password') }}</label>
                        @include('partials.password-input', [
                            'id' => 'login-password',
                            'name' => 'password',
                            'inputClass' => $errors->has('password') ? 'is-invalid' : '',
                            'required' => true,
                            'autocomplete' => 'current-password',
                            'placeholder' => '••••••••',
                        ])
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-4">
                        <div class="form-check m-0">
                            <input class="form-check-input" type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                            <label class="form-check-label small" for="remember">{{ __('Remember me') }}</label>
                        </div>
                        @if (Route::has('password.request'))
                            <a href="{{ route('password.request') }}" class="small auth-login-links">{{ __('Forgot password?') }}</a>
                        @endif
                    </div>
                    <button type="submit" class="btn btn-primary auth-login-submit w-100 text-white">{{ __('Sign in') }}</button>
                </form>

                @include('auth.partials.google-signin')

                @if (Route::has('register'))
                    <p class="text-center text-muted small mt-4 mb-0">
                        {{ __('New here?') }}
                        <a href="{{ route('register') }}">{{ __('Create an account') }}</a>
                    </p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

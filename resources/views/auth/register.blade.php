@extends('layouts.market')

@section('title', __('Create account').' — '.config('app.name'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Create account'),
        'items' => [['label' => __('Create account')]],
    ])
@endpush

@push('head')
<style>
    .auth-register-page { padding: 1.5rem 0 3.5rem; font-family: 'Poppins', sans-serif; }
    .auth-register-shell { max-width: 960px; margin: 0 auto; }
    .auth-register-panel {
        display: flex;
        flex-wrap: wrap;
        min-height: min(620px, calc(100vh - 200px));
        border-radius: 16px;
        overflow: hidden;
        box-shadow: 0 2px 10px rgba(0,0,0,0.06), 0 24px 48px rgba(13, 148, 136, 0.1);
        background: #fff;
    }
    .auth-register-art {
        flex: 1 1 100%;
        position: relative;
        background: linear-gradient(152deg, #0f766e 0%, #0d9488 38%, #14b8a6 72%, #2dd4bf 100%);
        color: #fff;
        padding: 2.25rem 1.75rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        overflow: hidden;
    }
    @media (min-width: 992px) {
        .auth-register-art { flex: 0 0 42%; max-width: 42%; min-height: 560px; padding: 2.75rem 2.25rem; }
    }
    .auth-register-art::before,
    .auth-register-art::after {
        content: '';
        position: absolute;
        border-radius: 50%;
        border: 1px solid rgba(255,255,255,0.16);
        pointer-events: none;
    }
    .auth-register-art::before {
        width: 240px; height: 240px;
        top: -80px; right: -56px;
        animation: authRegFloat 15s ease-in-out infinite;
    }
    .auth-register-art::after {
        width: 180px; height: 180px;
        bottom: 8%; left: -48px;
        animation: authRegFloat 12s ease-in-out infinite reverse;
    }
    .auth-register-blob {
        position: absolute;
        width: 140px; height: 140px;
        background: rgba(255,255,255,0.1);
        border-radius: 45% 55% 50% 50%;
        top: 38%; right: 8%;
        animation: authRegMorph 13s ease-in-out infinite;
    }
    @keyframes authRegFloat {
        0%, 100% { transform: translate(0, 0) rotate(0deg); opacity: 0.88; }
        50% { transform: translate(-14px, 16px) rotate(-5deg); opacity: 1; }
    }
    @keyframes authRegMorph {
        0%, 100% { border-radius: 45% 55% 50% 50%; transform: rotate(0deg) scale(1); }
        50% { border-radius: 58% 42% 48% 52%; transform: rotate(-10deg) scale(1.05); }
    }
    @media (prefers-reduced-motion: reduce) {
        .auth-register-art::before, .auth-register-art::after, .auth-register-blob { animation: none; }
    }
    .auth-register-brand {
        position: relative;
        z-index: 1;
        font-weight: 700;
        font-size: 1.35rem;
        letter-spacing: -0.02em;
        margin-bottom: 0.75rem;
    }
    .auth-register-brand span { opacity: 0.9; font-weight: 500; }
    .auth-register-tagline {
        position: relative;
        z-index: 1;
        font-size: 1.45rem;
        font-weight: 700;
        line-height: 1.28;
        margin-bottom: 1.35rem;
        text-shadow: 0 2px 24px rgba(0,0,0,0.15);
    }
    .auth-register-perks {
        position: relative;
        z-index: 1;
        list-style: none;
        padding: 0;
        margin: 0;
        font-size: 0.875rem;
        opacity: 0.96;
        line-height: 1.85;
    }
    .auth-register-perks li {
        display: flex;
        align-items: flex-start;
        gap: 0.55rem;
        margin-bottom: 0.4rem;
    }
    .auth-register-perks li::before {
        content: '✓';
        font-weight: 700;
        flex-shrink: 0;
        opacity: 0.9;
    }
    .auth-register-form-wrap {
        flex: 1 1 100%;
        padding: 2rem 1.5rem 2.25rem;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #fff;
    }
    @media (min-width: 992px) {
        .auth-register-form-wrap { flex: 1 1 58%; max-width: 58%; padding: 2.75rem 2.5rem 3rem; }
    }
    .auth-register-form-wrap h1 {
        font-size: 1.6rem;
        font-weight: 700;
        color: #222;
        margin-bottom: 0.35rem;
    }
    .auth-register-sub { color: #777; font-size: 0.9rem; margin-bottom: 1.5rem; }
    .auth-register-form-wrap .form-label { font-weight: 600; font-size: 0.8125rem; color: #444; margin-bottom: 0.4rem; }
    .auth-register-form-wrap .form-control {
        border-radius: 8px;
        border: 1px solid #e8e8e8;
        padding: 0.65rem 0.9rem;
        transition: border-color 0.25s ease, box-shadow 0.25s ease;
    }
    .auth-register-form-wrap .form-control:focus {
        border-color: #0d9488;
        box-shadow: 0 0 0 3px rgba(13, 148, 136, 0.15);
    }
    .auth-register-submit {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.72rem 1.25rem;
        background: linear-gradient(135deg, #0d9488, #0f766e);
        border: none;
        color: #fff;
        transition: transform 0.2s ease, box-shadow 0.2s ease, filter 0.2s ease;
    }
    .auth-register-submit:hover {
        filter: brightness(1.06);
        transform: translateY(-1px);
        box-shadow: 0 10px 28px rgba(13, 148, 136, 0.38);
        color: #fff;
    }
    .auth-register-links a { color: #0d9488; text-decoration: none; font-weight: 600; font-size: 0.875rem; }
    .auth-register-links a:hover { text-decoration: underline; }
    .auth-social-or {
        display: flex;
        align-items: center;
        gap: 1rem;
        margin: 1.35rem 0 1.15rem;
        color: #9ca3af;
        font-size: 0.8125rem;
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
    .auth-google-btn--disabled { opacity: 0.72; cursor: not-allowed; }
    .auth-google-btn__icon { display: inline-flex; flex-shrink: 0; line-height: 0; }
    .auth-google-btn__label { font-weight: 600; color: #3c4043; }
    .auth-google-setup-hint code {
        font-size: 0.75rem;
        background: #f3f4f6;
        padding: 0.35rem 0.5rem;
        border-radius: 6px;
    }
</style>
@endpush

@section('content')
<div class="auth-register-page">
    <div class="auth-register-shell">
        <div class="auth-register-panel">
            <div class="auth-register-art d-none d-lg-flex">
                <div class="auth-register-blob" aria-hidden="true"></div>
                <div class="auth-register-brand">{{ config('app.name') }} <span>{{ __('Market') }}</span></div>
                <p class="auth-register-tagline">{{ __('Create your account — unlock the full shopping experience.') }}</p>
                <ul class="auth-register-perks">
                    <li>{{ __('Faster checkout with saved details') }}</li>
                    <li>{{ __('Track orders and returns in real time') }}</li>
                    <li>{{ __('Wishlist & exclusive offers for members') }}</li>
                </ul>
            </div>
            <div class="auth-register-form-wrap">
                <div class="d-lg-none text-center pb-3 mb-3 border-bottom" style="border-color:#f0f0f0!important;">
                    <div class="fw-bold" style="color:#0d9488;font-size:1.1rem;">{{ config('app.name') }}</div>
                    <div class="small text-muted">{{ __('Join in a minute') }}</div>
                </div>
                <h1>{{ __('Create your account') }}</h1>
                <p class="auth-register-sub">{{ __('Fill in your details — we will keep your data secure.') }}</p>

                <form method="POST" action="{{ route('register') }}" class="auth-register-form">
                    @csrf
                    <div class="visually-hidden" aria-hidden="true">
                        <label for="reg-website">{{ __('Website') }}</label>
                        <input id="reg-website" type="text" name="website" tabindex="-1" autocomplete="off" value="">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reg-name">{{ __('Name') }}</label>
                        <input id="reg-name" type="text" class="form-control @error('name') is-invalid @enderror" name="name" value="{{ old('name') }}" required autofocus autocomplete="name" placeholder="{{ __('Your full name') }}">
                        @error('name')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reg-email">{{ __('Email') }}</label>
                        <input id="reg-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com">
                        @error('email')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="reg-password">{{ __('Password') }}</label>
                        @include('partials.password-input', [
                            'id' => 'reg-password',
                            'name' => 'password',
                            'inputClass' => $errors->has('password') ? 'is-invalid' : '',
                            'required' => true,
                            'autocomplete' => 'new-password',
                            'placeholder' => __('At least 8 characters'),
                        ])
                        @error('password')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                    </div>
                    <div class="mb-4">
                        <label class="form-label" for="reg-password-confirm">{{ __('Confirm password') }}</label>
                        @include('partials.password-input', [
                            'id' => 'reg-password-confirm',
                            'name' => 'password_confirmation',
                            'required' => true,
                            'autocomplete' => 'new-password',
                            'placeholder' => __('Repeat password'),
                        ])
                    </div>
                    <button type="submit" class="btn auth-register-submit w-100">{{ __('Create account') }}</button>
                </form>

                @include('auth.partials.google-signin')

                <p class="text-center text-muted small mt-4 mb-0 auth-register-links">
                    {{ __('Already have an account?') }}
                    <a href="{{ route('login') }}">{{ __('Sign in') }}</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

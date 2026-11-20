@extends('layouts.market')

@section('title', __('Sign in with mobile OTP').' — '.config('app.name'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Sign in with OTP'),
        'items' => [['label' => __('Sign in with OTP')]],
    ])
@endpush

@push('head')
<style>
    .auth-login-page { padding: 1.5rem 0 3.5rem; font-family: 'Poppins', sans-serif; }
    .auth-login-form-wrap .form-label { font-weight: 600; font-size: 0.8125rem; color: #444; margin-bottom: 0.4rem; }
    .auth-login-form-wrap .form-control {
        border-radius: 8px;
        border: 1px solid #e8e8e8;
        padding: 0.65rem 0.9rem;
    }
    .auth-login-submit {
        border-radius: 8px;
        font-weight: 600;
        padding: 0.7rem 1.25rem;
        background: #ec8951;
        border: none;
    }
    .auth-login-submit:disabled {
        background: #c4c8ce;
        color: #5f6368;
        box-shadow: none;
        opacity: 1;
        cursor: not-allowed;
    }
    .auth-login-links a, .auth-login-links { color: #ec8951; text-decoration: none; font-weight: 500; }
</style>
@endpush

@section('content')
<div class="auth-login-page">
    <div class="row justify-content-center">
        <div class="col-md-5">
            <div class="auth-login-form-wrap p-4 bg-white rounded-3 shadow-sm">
                @php $showMobileOtpLogin = true; @endphp
                <h1 class="h4 fw-bold mb-2">{{ __('Login with Mobile OTP') }}</h1>
                <p class="text-muted small mb-4">{{ __('We will send a one-time code to your mobile number.') }}</p>
                <div @unless($showMobileOtpLogin) hidden @endunless>
                    @include('auth.partials.otp-login')
                </div>
                <p class="text-center small mt-4 mb-0 auth-login-links">
                    <a href="{{ route('login') }}">{{ __('Back to email sign-in') }}</a>
                </p>
            </div>
        </div>
    </div>
</div>
@endsection

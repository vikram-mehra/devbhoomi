@extends('layouts.market')

@section('title', __('Verify your email').' — '.config('app.name'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Verify your email'),
        'items' => [['label' => __('Verify email')]],
    ])
@endpush

@push('head')
<style>
    .auth-verify-page { max-width: 520px; margin: 2rem auto 3.5rem; padding: 2rem 1.5rem; border-radius: 16px; background: #fff; box-shadow: 0 2px 10px rgba(0,0,0,0.06); text-align: center; }
    .auth-verify-page h1 { font-size: 1.35rem; font-weight: 700; color: #222; margin-bottom: 0.35rem; }
    .auth-verify-page .lead { color: #64748b; font-size: 0.9rem; margin-bottom: 1.25rem; }
    .auth-verify-page .form-label { font-weight: 600; font-size: 0.8125rem; text-align: left; }
    .auth-verify-page .form-control { border-radius: 8px; text-align: left; }
    .auth-verify-email-submit { border-radius: 8px; font-weight: 600; background: linear-gradient(135deg, #0d9488, #0f766e); border: none; }
</style>
@endpush

@section('content')
<div class="auth-verify-page">
    <h1>{{ __('Verify your email') }}</h1>
    <p class="lead">{{ __('Enter your email to receive a verification code, or enter the code if you already have one.') }}</p>

    @if($email)
        <p class="small text-muted">{{ __('Code sent to') }} <strong style="color:#0d9488;">{{ $email }}</strong></p>
        @include('auth.partials.verify-otp-form', ['email' => $email])
    @else
        <form method="GET" action="{{ route('verification.sent') }}" class="text-start">
            <input type="hidden" name="send" value="1">
            <div class="mb-3">
                <label class="form-label" for="verify-email-lookup">{{ __('Email address') }}</label>
                <input type="email" class="form-control" id="verify-email-lookup" name="email" value="{{ old('email') }}" required autocomplete="email" placeholder="name@example.com">
            </div>
            <button type="submit" class="btn btn-primary auth-verify-email-submit w-100">{{ __('Send verification code') }}</button>
        </form>
    @endif

    <p class="small text-muted mt-4 mb-0">
        <a href="{{ route('login') }}" style="color:#0d9488;font-weight:600;text-decoration:none;">{{ __('Back to sign in') }}</a>
    </p>
</div>
@endsection

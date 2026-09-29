@extends('layouts.market')

@push('head')
    @include('auth.partials.market-auth-styles')
@endpush

@section('content')
<div class="auth-login-page">
    <div class="auth-login-shell">
        <div class="auth-login-panel">
            <div class="auth-login-art d-none d-lg-flex">
                <div class="auth-login-blob" aria-hidden="true"></div>
                <div class="auth-login-brand">{{ config('app.name') }} <span>Market</span></div>
                <p class="auth-login-tagline">
                    @hasSection('auth-art-tagline')
                        @yield('auth-art-tagline')
                    @else
                        {{ __('Secure account recovery') }}
                    @endif
                </p>
                <ul class="auth-login-perks">
                    @hasSection('auth-art-perks')
                        @yield('auth-art-perks')
                    @else
                        <li>{{ __('Reset link sent to your email') }}</li>
                        <li>{{ __('Link expires for your security') }}</li>
                        <li>{{ __('Choose a strong new password') }}</li>
                    @endif
                </ul>
            </div>
            <div class="auth-login-form-wrap">
                <div class="d-lg-none text-center pb-3 mb-3 border-bottom" style="border-color:#f0f0f0!important;">
                    <div class="fw-bold" style="color:#ec8951;font-size:1.1rem;">{{ config('app.name') }}</div>
                    <div class="small text-muted">@yield('auth-mobile-label', __('Account'))</div>
                </div>

                <h1>@yield('auth-heading')</h1>
                <p class="auth-login-sub">@yield('auth-subheading')</p>

                @if (session('status'))
                    <div class="auth-login-alert" role="status">{{ session('status') }}</div>
                @endif

                @yield('auth-form')

                @hasSection('auth-footer')
                    <p class="text-center auth-login-links mt-4 mb-0">@yield('auth-footer')</p>
                @endif
            </div>
        </div>
    </div>
</div>
@endsection

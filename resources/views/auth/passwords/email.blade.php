@extends('layouts.auth-market')

@section('title', __('Forgot password').' — '.config('app.name'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Forgot password'),
        'items' => [
            ['label' => __('Sign in'), 'url' => route('login')],
            ['label' => __('Forgot password')],
        ],
    ])
@endpush

@section('auth-mobile-label', __('Reset password'))

@section('auth-art-tagline')
    {{ __('We will email you a secure reset link.') }}
@endsection

@section('auth-art-perks')
    <li>{{ __('Check your inbox and spam folder') }}</li>
    <li>{{ __('Link works for a limited time') }}</li>
    <li>{{ __('Set a new password in one step') }}</li>
@endsection

@section('auth-heading')
    {{ __('Forgot your password?') }}
@endsection

@section('auth-subheading')
    {{ __('Enter the email on your account and we will send reset instructions.') }}
@endsection

@section('auth-form')
    <form method="POST" action="{{ route('password.email') }}">
        @csrf
        <div class="mb-4">
            <label class="form-label" for="reset-email">{{ __('Email') }}</label>
            <input id="reset-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ old('email') }}" required autofocus autocomplete="email" placeholder="name@example.com">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>
        <button type="submit" class="btn btn-primary auth-login-submit w-100 text-white">
            {{ __('Send reset link') }}
        </button>
    </form>
@endsection

@section('auth-footer')
    <a href="{{ route('login') }}">{{ __('Back to sign in') }}</a>
@endsection

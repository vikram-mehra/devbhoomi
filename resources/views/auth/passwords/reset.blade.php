@extends('layouts.auth-market')

@section('title', __('Reset password').' — '.config('app.name'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Reset password'),
        'items' => [
            ['label' => __('Sign in'), 'url' => route('login')],
            ['label' => __('Reset password')],
        ],
    ])
@endpush

@section('auth-mobile-label', __('New password'))

@section('auth-art-tagline')
    {{ __('Choose a strong password for your account.') }}
@endsection

@section('auth-art-perks')
    <li>{{ __('At least 8 characters recommended') }}</li>
    <li>{{ __('Mix letters and numbers') }}</li>
    <li>{{ __('Keep it private and unique') }}</li>
@endsection

@section('auth-heading')
    {{ __('Set a new password') }}
@endsection

@section('auth-subheading')
    {{ __('Enter your email and choose a new password below.') }}
@endsection

@section('auth-form')
    <form method="POST" action="{{ route('password.update') }}">
        @csrf
        <input type="hidden" name="token" value="{{ $token }}">

        <div class="mb-3">
            <label class="form-label" for="reset-email">{{ __('Email') }}</label>
            <input id="reset-email" type="email" class="form-control @error('email') is-invalid @enderror" name="email" value="{{ $email ?? old('email') }}" required autofocus autocomplete="email" placeholder="name@example.com">
            @error('email')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-3">
            <label class="form-label" for="reset-password">{{ __('New password') }}</label>
            @include('partials.password-input', [
                'id' => 'reset-password',
                'name' => 'password',
                'inputClass' => $errors->has('password') ? 'is-invalid' : '',
                'required' => true,
                'autocomplete' => 'new-password',
                'placeholder' => '••••••••',
            ])
            @error('password')
                <div class="invalid-feedback d-block">{{ $message }}</div>
            @enderror
        </div>

        <div class="mb-4">
            <label class="form-label" for="reset-password-confirm">{{ __('Confirm password') }}</label>
            @include('partials.password-input', [
                'id' => 'reset-password-confirm',
                'name' => 'password_confirmation',
                'required' => true,
                'autocomplete' => 'new-password',
                'placeholder' => '••••••••',
            ])
        </div>

        <button type="submit" class="btn btn-primary auth-login-submit w-100 text-white">
            {{ __('Reset password') }}
        </button>
    </form>
@endsection

@section('auth-footer')
    <a href="{{ route('login') }}">{{ __('Back to sign in') }}</a>
@endsection

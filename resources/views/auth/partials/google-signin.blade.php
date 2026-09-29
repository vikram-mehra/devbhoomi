@php
    $googleConfigured = app(\App\Services\GoogleAuthService::class)->isConfigured();
@endphp

<div class="auth-social-or" role="separator" aria-label="{{ __('or') }}">
    <span>{{ __('or') }}</span>
</div>

@if($googleConfigured)
    <a href="{{ route('auth.google') }}" class="auth-google-btn" role="button">
        <span class="auth-google-btn__icon" aria-hidden="true">
            @include('auth.partials.google-icon-svg')
        </span>
        <span class="auth-google-btn__label">{{ __('Continue with Google') }}</span>
    </a>
@else
    <button type="button" class="auth-google-btn auth-google-btn--disabled" disabled aria-disabled="true" title="{{ __('Google sign-in is not configured yet') }}">
        <span class="auth-google-btn__icon" aria-hidden="true">
            @include('auth.partials.google-icon-svg')
        </span>
        <span class="auth-google-btn__label">{{ __('Continue with Google') }}</span>
    </button>
@endif

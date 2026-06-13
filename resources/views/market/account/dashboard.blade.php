@extends('layouts.account')

@section('account_title', __('My account'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('My account'),
        'items' => [['label' => __('My account')]],
    ])
@endpush

@section('account_content')
    @php
        $displayName = strtoupper($user->name);
    @endphp
    <h1 class="h4 fw-bold mb-2">{{ __('Hello, :name !', ['name' => $displayName]) }}</h1>
    <p class="text-muted small mb-4">{{ __('Here is your account snapshot — orders and profile details in one place.') }}</p>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="account-dash__stat-card">
                <div class="account-dash__stat-icon"><i class="bi bi-box-seam" aria-hidden="true"></i></div>
                <div>
                    <div class="account-dash__stat-value">{{ $ordersCount }}</div>
                    <div class="account-dash__stat-label">{{ __('Total orders') }}</div>
                </div>
            </div>
        </div>
    </div>

    <h2 class="h6 fw-bold mb-3">{{ __('Account information') }}</h2>
    <ul class="account-dash__list mb-4">
        <li><span class="text-muted">{{ __('Full name') }}:</span> {{ $user->name }}</li>
        <li><span class="text-muted">{{ __('Phone') }}:</span> {{ $user->phone ?: '—' }}</li>
        <li><span class="text-muted">{{ __('Address') }}:</span> {{ $addressLine ?: __('No saved address yet.') }}</li>
    </ul>

    <h2 class="h6 fw-bold mb-3">{{ __('Login details') }}</h2>
    <div class="row g-4">
        <div class="col-sm-6">
            <div class="small text-muted text-uppercase fw-semibold mb-1">{{ __('Email') }}</div>
            <div class="text-break">{{ $user->email }}</div>
            <a href="{{ route('account.details') }}" class="account-dash__edit-link small d-inline-block mt-2">{{ __('Edit') }}</a>
        </div>
        <div class="col-sm-6">
            <div class="small text-muted text-uppercase fw-semibold mb-1">{{ __('Password') }}</div>
            <div class="font-monospace">••••••••</div>
            <a href="{{ route('account.details') }}#account-password" class="account-dash__edit-link small d-inline-block mt-2">{{ __('Edit') }}</a>
        </div>
    </div>
@endsection

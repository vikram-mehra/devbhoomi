@extends('layouts.market')

@section('title', 'Login with OTP')

@push('breadcrumb')
    @include('market.partials.breadcrumbs', ['items' => [['label' => __('Sign in with OTP')]]])
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-5">
            <h1 class="h4 mb-3">Mobile OTP login</h1>
            <div class="zm-card p-4 mb-3">
                <form method="post" action="{{ route('login.phone.send') }}">
                    @csrf
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control mb-2" required value="{{ old('phone') }}">
                    <button class="zm-btn zm-btn-primary" type="submit">Send OTP</button>
                </form>
            </div>
            <div class="zm-card p-4">
                <form method="post" action="{{ route('login.phone.verify') }}">
                    @csrf
                    <label class="form-label">Phone</label>
                    <input name="phone" class="form-control mb-2" required value="{{ old('phone') }}">
                    <label class="form-label">6-digit code</label>
                    <input name="code" class="form-control mb-2" required maxlength="6">
                    <button class="zm-btn zm-btn-primary" type="submit">Verify & login</button>
                </form>
            </div>
            @if(session('otp_debug'))
                <p class="small text-muted mt-2">Dev OTP: {{ session('otp_debug') }}</p>
            @endif
        </div>
    </div>
@endsection

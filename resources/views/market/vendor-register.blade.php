@extends('layouts.market')

@section('title', 'Sell on alluringstyle')

@push('breadcrumb')
    @include('market.partials.breadcrumbs', ['items' => [['label' => __('Sell with us')]]])
@endpush

@section('content')
    <div class="row justify-content-center">
        <div class="col-md-6">
            <h1 class="h3 mb-3">Open your shop</h1>
            <form method="post" action="{{ route('vendor.register.store') }}" class="zm-card p-4">
                @csrf
                <div class="mb-2"><label class="form-label">Your name</label><input name="name" class="form-control" required value="{{ old('name') }}"></div>
                <div class="mb-2"><label class="form-label">Email</label><input type="email" name="email" class="form-control" required value="{{ old('email') }}"></div>
                <div class="mb-2">
                    <label class="form-label" for="vendor-password">{{ __('Password') }}</label>
                    @include('partials.password-input', ['id' => 'vendor-password', 'name' => 'password', 'required' => true, 'autocomplete' => 'new-password'])
                </div>
                <div class="mb-2">
                    <label class="form-label" for="vendor-password-confirm">{{ __('Confirm') }}</label>
                    @include('partials.password-input', ['id' => 'vendor-password-confirm', 'name' => 'password_confirmation', 'required' => true, 'autocomplete' => 'new-password'])
                </div>
                <div class="mb-2"><label class="form-label">Shop name</label><input name="shop_name" class="form-control" required value="{{ old('shop_name') }}"></div>
                <div class="mb-2"><label class="form-label">City</label><input name="city" class="form-control" value="{{ old('city') }}"></div>
                <div class="mb-3"><label class="form-label">State</label><input name="state" class="form-control" value="{{ old('state') }}"></div>
                <button class="zm-btn zm-btn-primary w-100" type="submit">Submit application</button>
            </form>
        </div>
    </div>
@endsection

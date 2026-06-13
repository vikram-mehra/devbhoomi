@extends('layouts.account')

@section('account_title', __('Account details'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'title' => __('Account details'),
        'items' => [
            ['label' => __('My account'), 'url' => route('account.dashboard')],
            ['label' => __('Account details')],
        ],
    ])
@endpush

@section('account_content')
    @include('market.account.partials.account-tabs', ['activeTab' => 'details'])

    <div class="account-details__panel mb-5">
        <form method="post" action="{{ route('account.details.update') }}" enctype="multipart/form-data" class="account-details__form">
            @csrf
            <div class="row g-4 align-items-start">
                <div class="col-md-4 text-center text-md-start">
                    <label class="form-label small fw-semibold text-secondary d-block mb-2">{{ __('Profile photo') }}</label>
                    <div class="account-details__avatar-wrap mx-auto mx-md-0">
                        @if($user->avatarUrl())
                            <img src="{{ $user->avatarUrl() }}" alt="" class="account-details__avatar-img" width="160" height="160">
                        @else
                            @php $initial = strtoupper(mb_substr(trim($user->name ?: $user->email), 0, 1)); @endphp
                            <div class="account-details__avatar-placeholder">{{ $initial }}</div>
                        @endif
                    </div>
                    <input type="file" name="avatar" class="form-control form-control-sm mt-3" accept="image/jpeg,image/png,image/jpg,image/webp,image/gif">
                    <p class="form-text small text-muted mb-0">{{ __('JPG, PNG or WebP. Max 2 MB.') }}</p>
                    @error('avatar')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-8">
                    <div class="row g-3">
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">{{ __('Full name') }} <span class="text-danger">*</span></label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required maxlength="255">
                            @error('name')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">{{ __('Email') }} <span class="text-danger">*</span></label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required maxlength="255" autocomplete="email">
                            @error('email')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label small fw-semibold text-secondary">{{ __('Phone') }}</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" maxlength="32" autocomplete="tel">
                            @error('phone')<div class="text-danger small">{{ $message }}</div>@enderror
                        </div>
                    </div>
                    <button type="submit" class="btn address-book__btn-add mt-4">{{ __('Save changes') }}</button>
                </div>
            </div>
        </form>
    </div>

    <div id="account-password" class="account-details__password-section">
        <h2 class="h6 fw-bold mb-3">{{ __('Change password') }}</h2>
        <p class="text-muted small mb-4">{{ __('Use a strong password you have not used elsewhere.') }}</p>
        <form method="post" action="{{ route('account.password.update') }}" class="account-details__form account-dash__form-max">
            @csrf
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary" for="acct-current-password">{{ __('Current password') }}</label>
                @include('partials.password-input', [
                    'id' => 'acct-current-password',
                    'name' => 'current_password',
                    'required' => true,
                    'autocomplete' => 'current-password',
                ])
                @error('current_password')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary" for="acct-new-password">{{ __('New password') }}</label>
                @include('partials.password-input', [
                    'id' => 'acct-new-password',
                    'name' => 'password',
                    'inputClass' => $errors->has('password') ? 'is-invalid' : '',
                    'required' => true,
                    'autocomplete' => 'new-password',
                ])
                @error('password')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="mb-3">
                <label class="form-label small fw-semibold text-secondary" for="acct-confirm-password">{{ __('Confirm new password') }}</label>
                @include('partials.password-input', [
                    'id' => 'acct-confirm-password',
                    'name' => 'password_confirmation',
                    'required' => true,
                    'autocomplete' => 'new-password',
                ])
            </div>
            <button type="submit" class="btn address-book__btn-add">{{ __('Update password') }}</button>
        </form>
    </div>
@endsection

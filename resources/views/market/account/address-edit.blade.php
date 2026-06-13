@extends('layouts.account')

@section('account_title', __('Edit address'))

@push('breadcrumb')
    @include('market.partials.breadcrumbs', [
        'items' => [
            ['label' => __('My account'), 'url' => route('account.dashboard')],
            ['label' => __('Address book'), 'url' => route('account.addresses.index')],
            ['label' => __('Edit address')],
        ],
    ])
@endpush

@section('account_content')
    @include('market.account.partials.account-tabs', ['activeTab' => 'addresses'])

    <p class="mb-3">
        <a href="{{ route('account.addresses.index') }}" class="account-dash__edit-link small text-decoration-none">{{ __('← Back to address book') }}</a>
    </p>
    <h1 class="address-book__title mb-4">{{ __('Edit address') }}</h1>

    <div class="address-book__form-panel address-book__form-panel--narrow">
        <form method="post" action="{{ route('account.addresses.update', $address) }}" class="address-book__form">
            @csrf
            @method('PATCH')
            @include('market.account.partials.address-fields', ['address' => $address])
            <div class="mt-4 d-flex flex-wrap gap-2">
                <button type="submit" class="btn address-book__btn-add">{{ __('Save changes') }}</button>
                <a href="{{ route('account.addresses.index') }}" class="btn btn-outline-secondary">{{ __('Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
